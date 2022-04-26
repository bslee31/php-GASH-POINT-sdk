<?php

namespace GashPoint\Tests;

use GashPoint\GashPoint;
use GashPoint\GashPointSDKException;
use PHPUnit\Framework\TestCase;

class GashPointTest extends TestCase
{
    /** @var array */
    protected $config = [
        'mid' => 'M1000950',
        'cid' => 'C009500002490',
        'key1' => 'yrafRg1C9mHOP/IU09JaP5S9GekAyESG',
        'key2' => 'UNvI+LOQQRI=',
        'password' => 'KGGLSJHGSGjj',
    ];

    /** @var bool */
    protected $enableSandboxMode = true;

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testInstantiatingWithoutMidThrows(): void
    {
        $this->expectException(GashPointSDKException::class);

        $config = [
            'cid' => '${CID}',
            'key1' => '${KEY1}',
            'key2' => '${KEY2}',
            'password' => '${PASSWORD}',
        ];
        new GashPoint($config, $this->enableSandboxMode);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testInstantiatingWithoutCidThrows(): void
    {
        $this->expectException(GashPointSDKException::class);

        $config = [
            'mid' => '${MID}',
            'key1' => '${KEY1}',
            'key2' => '${KEY2}',
            'password' => '${PASSWORD}',
        ];
        new GashPoint($config, $this->enableSandboxMode);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testInstantiatingWithoutKey1Throws(): void
    {
        $this->expectException(GashPointSDKException::class);

        $config = [
            'mid' => '${MID}',
            'cid' => '${CID}',
            'key2' => '${KEY2}',
            'password' => '${PASSWORD}',
        ];
        new GashPoint($config, $this->enableSandboxMode);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testInstantiatingWithoutKey2Throws(): void
    {
        $this->expectException(GashPointSDKException::class);

        $config = [
            'mid' => '${MID}',
            'cid' => '${CID}',
            'key1' => '${KEY1}',
            'password' => '${PASSWORD}',
        ];
        new GashPoint($config, $this->enableSandboxMode);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testInstantiatingWithoutPasswordThrows(): void
    {
        $this->expectException(GashPointSDKException::class);

        $config = [
            'mid' => '${MID}',
            'cid' => '${CID}',
            'key1' => '${KEY1}',
            'key2' => '${KEY2}',
        ];
        new GashPoint($config, $this->enableSandboxMode);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateOrder(): void
    {
        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);

        $dateTime = new \DateTime();
        $result = $gashPoint->createOrder([
            'USER_ACCTID' => '9999999999',
            'COID' => $dateTime->format('YmdHisv'),
            'CUID' => 'TWD',
            'AMOUNT' => '5',
            'RETURN_URL' => 'https://your-app-domain',
            'ORDER_TYPE' => 'E',
        ]);

        preg_match('/value="(.*?)"/', $result, $matches);
        $simpleXMLElement = new \SimpleXMLElement(base64_decode($matches[1]));

        $this->assertEquals($dateTime->format('YmdHisv'), $simpleXMLElement->COID);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testGetCreateOrderResponse(): void
    {
        $_POST['data'] = 'PFRSQU5TPg0KICA8TVNHX1RZUEU+MDExMDwvTVNHX1RZUEU+DQogIDxQQ09ERT4zMDAwMDA8L1BDT0RFPg0KICA8Q0lEPkMwMDk1MDAwMDI0OTA8L0NJRD4NCiAgPENPSUQ+MjAyMjA0MjUxMTU4NDQ2MDY8L0NPSUQ+DQogIDxSUk4+R1AyMjA0MjVTMDAwMDAwNDwvUlJOPg0KICA8Q1VJRD5UV0Q8L0NVSUQ+DQogIDxQQUlEPkNPUEdBTTA1PC9QQUlEPg0KICA8QU1PVU5UPjU8L0FNT1VOVD4NCiAgPEVSUEM+WFRLa01pN3dTeHpwS2o5dC9XY2JMVHNyS2NVPTwvRVJQQz4NCiAgPE9SREVSX1RZUEU+PC9PUkRFUl9UWVBFPg0KICA8UEFZX1NUQVRVUz5TPC9QQVlfU1RBVFVTPg0KICA8UEFZX1JDT0RFPjAwMDA8L1BBWV9SQ09ERT4NCiAgPFJDT0RFPjAwMDA8L1JDT0RFPg0KICA8RVJQX0lEPlBJTkhBTEw8L0VSUF9JRD4NCiAgPE1JRD5NMTAwMDk1MDwvTUlEPg0KICA8QklEPjwvQklEPg0KICA8TUVNTz48L01FTU8+DQogIDxQUk9EVUNUX05BTUU+R0FTSFBPSU5U6YeR5rWB5pyN5YuZPC9QUk9EVUNUX05BTUU+DQogIDxQUk9EVUNUX0lEPlBJTkhBTEw8L1BST0RVQ1RfSUQ+DQogIDxQQVNTX1BST0RJRD48L1BBU1NfUFJPRElEPg0KICA8VVNFUl9BQ0NUSUQ+PC9VU0VSX0FDQ1RJRD4NCiAgPFVTRVJfR1JPVVBJRD48L1VTRVJfR1JPVVBJRD4NCiAgPFVTRVJfSVA+MjExLjIxLjEyNy4xOTI8L1VTRVJfSVA+DQogIDxFWFRFTlNJT04+JmFtcDtDT01QQU5ZX05BTUU9R0FTSCsoSEspPC9FWFRFTlNJT04+DQogIDxHUFNfSU5GTz48L0dQU19JTkZPPg0KICA8VFhUSU1FPjIwMjIwNDI1MTE1ODQ1PC9UWFRJTUU+DQogIDxSTVNHPlNVQ0NFU1NGVUxfQVBQUk9WQUxfQ09NUExFVElPTjwvUk1TRz4NCiAgPFJNU0dfQ0hJPuioiuaBr+iZleeQhuaIkOWKnzwvUk1TR19DSEk+DQogIDxTWVNUSU1FPjIwMjItMDQtMjUgMTI6MDI6NDE8L1NZU1RJTUU+DQogIDxNT0JJTEVOVU1CRVI+MDkwNDgwMDAwMzwvTU9CSUxFTlVNQkVSPg0KPC9UUkFOUz4=';

        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);
        $response = $gashPoint->getCreateOrderResponse();
        $this->assertObjectHasAttribute('MSG_TYPE', $response);
        $this->assertObjectHasAttribute('PCODE', $response);
        $this->assertObjectHasAttribute('ERPC', $response);
        $this->assertObjectHasAttribute('PAY_RCODE', $response);
        $this->assertObjectHasAttribute('RCODE', $response);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testGetCreateOrderResponseWithEmptyDataThrows(): void
    {
        $this->expectException(GashPointSDKException::class);

        $_POST['data'] = '';

        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);
        $gashPoint->getCreateOrderResponse();
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testGetCreateOrderResponseWithInvalidErpcThrows(): void
    {
        $this->expectException(GashPointSDKException::class);

        $_POST['data'] = 'PFRSQU5TPg0KICA8TVNHX1RZUEU+MDExMDwvTVNHX1RZUEU+DQogIDxQQ09ERT4zMDAwMDA8L1BDT0RFPg0KICA8Q0lEPkMwMDk1MDAwMDI0OTA8L0NJRD4NCiAgPENPSUQ+MjAyMjA0MjUxMTU4NDQ2MDY8L0NPSUQ+DQogIDxSUk4+R1AyMjA0MjVTMDAwMDAwNDwvUlJOPg0KICA8Q1VJRD5UV0Q8L0NVSUQ+DQogIDxQQUlEPkNPUEdBTTA1PC9QQUlEPg0KICA8QU1PVU5UPjU8L0FNT1VOVD4NCiAgPEVSUEM+WFRLa01pN3dTeHpwS2o5dC9XY2JMVHNyS3NVPTwvRVJQQz4NCiAgPE9SREVSX1RZUEU+PC9PUkRFUl9UWVBFPg0KICA8UEFZX1NUQVRVUz5TPC9QQVlfU1RBVFVTPg0KICA8UEFZX1JDT0RFPjAwMDA8L1BBWV9SQ09ERT4NCiAgPFJDT0RFPjAwMDA8L1JDT0RFPg0KICA8RVJQX0lEPlBJTkhBTEw8L0VSUF9JRD4NCiAgPE1JRD5NMTAwMDk1MDwvTUlEPg0KICA8QklEPjwvQklEPg0KICA8TUVNTz48L01FTU8+DQogIDxQUk9EVUNUX05BTUU+R0FTSFBPSU5U6YeR5rWB5pyN5YuZPC9QUk9EVUNUX05BTUU+DQogIDxQUk9EVUNUX0lEPlBJTkhBTEw8L1BST0RVQ1RfSUQ+DQogIDxQQVNTX1BST0RJRD48L1BBU1NfUFJPRElEPg0KICA8VVNFUl9BQ0NUSUQ+PC9VU0VSX0FDQ1RJRD4NCiAgPFVTRVJfR1JPVVBJRD48L1VTRVJfR1JPVVBJRD4NCiAgPFVTRVJfSVA+MjExLjIxLjEyNy4xOTI8L1VTRVJfSVA+DQogIDxFWFRFTlNJT04+JmFtcDtDT01QQU5ZX05BTUU9R0FTSCsoSEspPC9FWFRFTlNJT04+DQogIDxHUFNfSU5GTz48L0dQU19JTkZPPg0KICA8VFhUSU1FPjIwMjIwNDI1MTE1ODQ1PC9UWFRJTUU+DQogIDxSTVNHPlNVQ0NFU1NGVUxfQVBQUk9WQUxfQ09NUExFVElPTjwvUk1TRz4NCiAgPFJNU0dfQ0hJPuioiuaBr+iZleeQhuaIkOWKnzwvUk1TR19DSEk+DQogIDxTWVNUSU1FPjIwMjItMDQtMjUgMTI6MDI6NDE8L1NZU1RJTUU+DQogIDxNT0JJTEVOVU1CRVI+MDkwNDgwMDAwMzwvTU9CSUxFTlVNQkVSPg0KPC9UUkFOUz4=';

        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);
        $gashPoint->getCreateOrderResponse();
    }

    /**
     * @return void
     * @throws GashPointSDKException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSettle(): void
    {
        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);
        $result = $gashPoint->settle([
            'PCODE' => '300000',
            'COID' => '20220425115844606',
            'CUID' => 'TWD',
            'AMOUNT' => '5',
        ]);

        $this->assertEquals('20220425115844606', $result->COID);
        $this->assertObjectHasAttribute('MSG_TYPE', $result);
        $this->assertObjectHasAttribute('PCODE', $result);
        $this->assertObjectHasAttribute('ERPC', $result);
        $this->assertObjectHasAttribute('RCODE', $result);
    }

    public function testFindOrder(): void
    {
        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);
        $result = $gashPoint->findOrder([
            'COID' => '20220425115844606',
            'CUID' => 'TWD',
            'AMOUNT' => '5',
        ]);

        $this->assertEquals('20220425115844606', $result->COID);
        $this->assertObjectHasAttribute('MSG_TYPE', $result);
        $this->assertObjectHasAttribute('PCODE', $result);
        $this->assertObjectHasAttribute('ERPC', $result);
        $this->assertObjectHasAttribute('PAY_RCODE', $result);
        $this->assertObjectHasAttribute('RCODE', $result);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     */
    public function testEnableSandboxMode(): void
    {
        $gashPoint = new GashPoint($this->config);
        $this->assertEquals($gashPoint->getBaseUrl(), GashPoint::BASE_URL);

        $gashPoint->enableSandboxMode(true);
        $this->assertEquals($gashPoint->getBaseUrl(), GashPoint::BASE_URL_SANDBOX);
    }

    /**
     * @return void
     * @throws GashPointSDKException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetOriginalResponse(): void
    {
        $gashPoint = new GashPoint($this->config, $this->enableSandboxMode);

        $dateTime = new \DateTime();
        $gashPoint->createOrder([
            'USER_ACCTID' => '9999999999',
            'COID' => $dateTime->format('YmdHisv'),
            'CUID' => 'TWD',
            'AMOUNT' => '5',
            'RETURN_URL' => 'https://your-app-domain',
            'ORDER_TYPE' => 'E',
        ]);
        $response = $gashPoint->getOriginalResponse();

        $this->assertIsString($response);
    }
}