<?php

namespace GashPoint;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use SimpleXMLElement;

class GashPoint
{
    /** @var string */
    public const VERSION = '1.0';

    /** @var string */
    public const BASE_URL = 'https://api.eg.gashplus.com';
    /** @var string */
    public const BASE_URL_SANDBOX = 'https://stage-api.eg.gashplus.com';

    /** @var bool */
    private $enableSandboxMode;

    /** @var string 商家代號(MID) */
    private $mid;

    /** @var string 商家服務代碼(CID) */
    private $cid;

    /** @var string 交易密鑰1 */
    private $key1;

    /** @var string 交易密鑰2 */
    private $key2;

    /** @var string 交易密碼 */
    private $password;

    /** @var HttpClient */
    private $httpClientHandler;

    /** @var string */
    private $httpOriginalResponse;

    /**
     * @param array $config [
     *   'mid => 'string', // 商家代號
     *   'cid' => 'string', // 商家服務代碼
     *   'key1' => 'string', // 交易密鑰1
     *   'key2' => 'string', // 交易密鑰2
     *   'password' => 'string', // 交易密碼
     * ]
     * @param bool $sandboxMode
     * @throws GashPointSDKException
     */
    public function __construct(array $config, bool $sandboxMode = false)
    {
        if (empty($config['mid'])) {
            throw new GashPointSDKException('Required "mid" key not supplied in config.');
        }
        if (empty($config['cid'])) {
            throw new GashPointSDKException('Required "cid" key not supplied in config.');
        }
        if (empty($config['key1'])) {
            throw new GashPointSDKException('Required "key1" key not supplied in config.');
        }
        if (empty($config['key2'])) {
            throw new GashPointSDKException('Required "key2" key not supplied in config.');
        }
        if (empty($config['password'])) {
            throw new GashPointSDKException('Required "password" key not supplied in config.');
        }

        $this->mid = $config['mid'];
        $this->cid = $config['cid'];
        $this->key1 = $config['key1'];
        $this->key2 = $config['key2'];
        $this->password = $config['password'];
        $this->enableSandboxMode = $sandboxMode;
        $this->httpClientHandler = new HttpClient();
    }

    /**
     * @param bool $sandboxMode
     * @return void
     */
    public function enableSandboxMode(bool $sandboxMode = true): void
    {
        $this->enableSandboxMode = $sandboxMode;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->enableSandboxMode ? static::BASE_URL_SANDBOX : static::BASE_URL;
    }

    /**
     * @return string
     */
    public function getOriginalResponse(): string
    {
        return $this->httpOriginalResponse;
    }

    /**
     * 一般交易
     *
     * @param array $params [
     *   'COID' => 'string', // M 商家訂單編號
     *   'CUID' => 'string', // M 幣別
     *   'PAID' => 'string', // M 付款代收業者代碼
     *   'AMOUNT' => 'string', // M 交易金額
     *   'RETURN_URL' => 'string', // M 商家接收交易結果網址
     *   'ORDER_TYPE' => 'string', // M 是否指定付款代收業者
     * ]
     * @return string
     * @throws GuzzleException
     */
    public function createOrder(array $params): string
    {
        // MSG_TYPE : 交易授權 Request 0100
        // PCODE : // M 交易處理代碼 300000
        // MID : O 商家代碼
        // CID : M 商家服務代碼
        // ERQC : M 商家交易驗證資料壓碼

        $params = array_merge([
            'MID' => $this->mid,
            'CID' => $this->cid,
            'MSG_TYPE' => '0100',
            'PCODE' => '300000',
            'COID' => '',
            'CUID' => '',
            'PAID' => '',
            'AMOUNT' => '',
            'RETURN_URL' => '',
            'ORDER_TYPE' => '',
        ], $params);
        $params['ERQC'] = $this->createRequestERQC($params['COID'], $params['CUID'], $params['AMOUNT']);

        return $this->sendRequest('/CP_Module/order.aspx', $params);
    }

    /**
     * 取得一般交易回傳結果
     *
     * @return SimpleXMLElement
     * @throws GashPointSDKException
     * @throws Exception
     */
    public function getCreateOrderResponse(): SimpleXMLElement
    {
        $transData = $_POST['data'] ?? '';
        if ($transData === '') {
            throw new GashPointSDKException('$_POST[\'data\'] is empty.');
        }

        // 第二次之後的主動通知需要把空白取代為+
        $transData = preg_replace('/\s/', '+', $transData);

        $transaction = new SimpleXMLElement(base64_decode($transData));
        $expectedErqc = $this->verifyERQC(
            $transaction->COID,
            $transaction->RRN,
            $transaction->CUID,
            $transaction->AMOUNT,
            $transaction->RCODE
        );
        if ($transaction->ERPC != $expectedErqc) {
            throw new GashPointSDKException('Invalid ERPC.');
        }

        return $transaction;
    }

    /**
     * 請款服務 SOAP
     *
     * @param array $params [
     *   'PCODE' => 'string', // M 交易處理代碼 300000|303000
     *   'COID' => 'string', // M 商家訂單編號
     *   'CUID' => 'string', // M 幣別
     *   'PAID' => 'string', // M 付款代收業者代碼
     *   'AMOUNT' => 'string', // M 交易金額
     * ]
     * @return SimpleXMLElement
     * @throws GuzzleException
     * @throws Exception
     */
    public function settle(array $params): SimpleXMLElement
    {
        // MSG_TYPE : 結帳請款 Request 0500
        // MID : O 商家代碼
        // CID : M 商家服務代碼
        // ERQC : M 商家交易驗證資料壓碼

        $params = array_merge([
            'MID' => $this->mid,
            'CID' => $this->cid,
            'MSG_TYPE' => '0500',
            'PCODE' => '',
            'COID' => '',
            'CUID' => '',
            'PAID' => '',
            'AMOUNT' => '',
        ], $params);
        $params['ERQC'] = $this->createRequestERQC($params['COID'], $params['CUID'], $params['AMOUNT']);
        $response = $this->sendSoapRequest('/CP_Module/settle.asmx?WSDL', $params);

        return $this->parseSoapResponseData($response);
    }

    /**
     * 訂單查詢服務 SOAP
     *
     * @param array $params [
     *   'COID' => 'string', // M 商家訂單編號
     *   'CUID' => 'string', // M 幣別
     *   'AMOUNT' => 'string', // M 交易金額
     * ]
     * @return SimpleXMLElement
     * @throws GuzzleException
     * @throws Exception
     */
    public function findOrder(array $params): SimpleXMLElement
    {
        // MSG_TYPE : 交易授權 Request 0100
        // PCODE : 查詢訂單 200000
        // CID : M 商家服務代碼
        // ERQC : M 商家交易驗證資料壓碼

        $params = array_merge([
            'CID' => $this->cid,
            'MSG_TYPE' => '0100',
            'PCODE' => '200000',
            'COID' => '',
            'CUID' => '',
            'AMOUNT' => '',
        ], $params);
        $params['ERQC'] = $this->createRequestERQC($params['COID'], $params['CUID'], $params['AMOUNT']);
        $response = $this->sendSoapRequest('/CP_Module/checkorder.asmx?WSDL', $params);

        return $this->parseSoapResponseData($response);
    }

    /**
     * 產生商家交易驗證壓碼
     * @param string $coid COID : Content Order ID (商家訂單編號)
     * @param string $cuid CUID : Currency code (幣別) (ex: TWD)
     * @param string $amount 12 碼整數 + 2 碼小數，不含小數點 (ex:50 TWD，AMOUNT = 00000000005000)
     * @return string
     */
    public function createRequestERQC(string $coid, string $cuid, string $amount): string
    {
        // CID : Content ID (商家服務代碼)
        // PASSWORD : CID 所屬的 MID 密碼 (交易密碼)

        return $this->generateERQC([
            'cid' => $this->cid,
            'coid' => $coid,
            'cuid' => $cuid,
            'amount' => $amount,
            'password' => $this->password
        ]);
    }

    /**
     * @param string $coid Content Order ID (商家訂單編號)
     * @param string $rrn GPS Order ID (GPS 交易編號)
     * @param string $cuid Currency code (幣別) (ex: TWD)
     * @param string $amount 12 碼整數 + 2 碼小數，不含小數點 (ex:50 TWD，AMOUNT = 00000000005000)
     * @param string $rcode 交易結果代碼
     * @return string
     */
    public function verifyERQC(string $coid, string $rrn, string $cuid, string $amount, string $rcode): string
    {
        // CID : Content ID (商家服務代碼)

        return $this->generateERQC([
            'cid' => $this->cid,
            'coid' => $coid,
            'rrn' => $rrn,
            'cuid' => $cuid,
            'amount' => $amount,
            'rcode' => $rcode
        ]);
    }

    private function generateERQC(array $params): string
    {
        if (isset($params['amount'])) {
            $params['amount'] = sprintf('%015.2f', $params['amount']);
            preg_match('/\d*(\d{12})\.(\d{2})/', $params['amount'], $matches);
            $params['amount'] = $matches[1] . $matches[2];
        }

        $des = new Crypt3Des($this->key1, $this->key2);
        $encryptData = $des->encrypt(implode($params));

        return base64_encode(sha1($encryptData, true));
    }

    /**
     * @param array $params
     * @return string
     */
    private function generateXML(array &$params): string
    {
        $simpleXMLElement = new SimpleXMLElement('<TRANS />');
        foreach ($params as $key => $value) {
            $simpleXMLElement->$key = $value;
        }

        return $simpleXMLElement->asXML();
    }

    /**
     * @param array $params
     * @return string
     */
    private function generateSOAP(array &$params): string
    {
        $simpleXMLElement = new SimpleXMLElement('<TRANS />');
        foreach ($params as $key => $value) {
            $simpleXMLElement->$key = $value;
        }

        preg_match('/(<TRANS>.*<\/TRANS>)/', $simpleXMLElement->asXML(), $matches);
        $data = base64_encode($matches[1]);

        $soap = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <getResponse xmlns="http://egsys.org/">
      <data>$data</data>
    </getResponse>
  </soap:Body>
</soap:Envelope>
SOAP;

        return preg_replace('/\n\s*/', '', $soap);
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     * @throws GuzzleException
     */
    private function sendRequest(string $path, array &$params): string
    {
        $uri = $this->getBaseUrl() . $path;
        $xml = $this->generateXML($params);
        $data = base64_encode($xml);

        $options = ['form_params' => ['data' => $data]];
        $response = $this->httpClientHandler->post($uri, $options);

        $body = $response->getBody();
        $contents = $body->getContents();
        $this->httpOriginalResponse = $contents;

        return $this->httpOriginalResponse;
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     * @throws GuzzleException
     */
    private function sendSoapRequest(string $path, array &$params): string
    {
        $uri = $this->getBaseUrl() . $path;
        $soap = $this->generateSOAP($params);

        $options = [
            'headers' => ['Content-Type' => 'text/xml'],
            'body' => $soap
        ];
        $response = $this->httpClientHandler->post($uri, $options);

        $body = $response->getBody();
        $contents = $body->getContents();
        $this->httpOriginalResponse = $contents;

        return $this->httpOriginalResponse;
    }

    /**
     * @param string $data
     * @return SimpleXMLElement
     * @throws Exception
     */
    private function parseSoapResponseData(string $data): SimpleXMLElement
    {
        preg_match('/<getResponseResult>(.*?)<\/getResponseResult>/', $data, $matches);
        return new SimpleXMLElement(base64_decode($matches[1]));
    }
}