## GASH POINT SDK for PHP

### 簡介
GASH POINT SDK 提供建立一般交易、請款、訂單查詢等功能。

### 開始使用前的準備
請向 GASH 申請串接金流服務，並取得商家代號(MID)、商家服務代碼(CID)、交易密鑰1、交易密鑰2、交易密碼等串接資料。

### 建立 SDK
```php
$sdk = new GashPoint([
    'mid' => '${mid}',
    'cid' => '${cid}',
    'key1' => '${key1}',
    'key2' => '${key2}',
    'password' => '${password}',
], true);
```

### 建立一般交易
```php
$dateTime = new DateTime();
$result = $sdk->createOrder([
    'USER_ACCTID' => '9999999999',
    'COID' => $dateTime->format('YmdHisv'),
    'CUID' => 'TWD',
    'AMOUNT' => '5',
    'RETURN_URL' => 'https://your-app-domain',
    'ORDER_TYPE' => 'E',
]);
```
$result 是 html 資料，請把 $result 傳遞給瀏覽器，瀏覽器將會導向到 GASH 儲值系統。

$result 範例
```html
<html>
<head><title>Global Payment System</title></head>
<body onload="javascript:form1.submit();">
<form id="form1" name="form1" action="https://stage-api.eg.gashplus.com/GPSv2/order.aspx" method="post"><input name="data" id="data" type="hidden" value="PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxUUkFOUz48TUlEPk0xMDAwOTUwPC9NSUQ+PENJRD5DMDA5NTAwMDAyNDkwPC9DSUQ+PE1TR19UWVBFPjAxMDA8L01TR19UWVBFPjxQQ09ERT4zMDAwMDA8L1BDT0RFPjxDT0lEPjIwMjIwNDI2MTUxNTUwOTYwPC9DT0lEPjxDVUlEPlRXRDwvQ1VJRD48UEFJRD48L1BBSUQ+PEFNT1VOVD41PC9BTU9VTlQ+PFJFVFVSTl9VUkw+aHR0cHM6Ly9hcGkzLmluY29nbml0b2h1Yi5jb20vdGVzdC5waHA8L1JFVFVSTl9VUkw+PE9SREVSX1RZUEU+RTwvT1JERVJfVFlQRT48VVNFUl9BQ0NUSUQ+OTk5OTk5OTk5OTwvVVNFUl9BQ0NUSUQ+PEVSUUM+bVhVZGRYMk9YT2N2NmJ4cUJlMXJ4bU5jazZFPTwvRVJRQz48L1RSQU5TPgo="></input></form>
</body>
</html>
```

### 取得一般交易結果
```php
$response = $sdk->getCreateOrderResponse();
```
交易結果會透過建立一般交易指定的 RETURN_URL 參數 URL 和申請 GASH 時所申請的主動通知的 URL 來傳遞，透過 SDK 提供的 getCreateOrderResponse() 就可以取得一般交易結果並會做 ERQC 的驗證。

### 請款
```php
$response = $sdk->settle([
    'PCODE' => '300000',
    'COID' => '20220425115844606',
    'CUID' => 'TWD',
    'AMOUNT' => '5',
]);
```

### 訂單查詢
```php
$response = $sdk->findOrder([
    'COID' => '20220425115844606',
    'CUID' => 'TWD',
    'AMOUNT' => '5',
]);
```