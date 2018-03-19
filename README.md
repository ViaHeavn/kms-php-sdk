# KMS PHP SDK
### 开发准备

在开始使用 KMS SDK 之前，需要准备如下信息:

- 依赖环境：PHP5.3.0版本及以上

- keyId、SecretID、SecretKey

> `keyId`是密钥ID，可以在[密钥管理服务控制台](https://console.cloud.tencent.com/kms)获取，`SecretID`和`SecretKey`是API密钥，可以在[API密钥管理控制台](https://console.qcloud.com/cam/capi)获取。

## 生成客户端对象

``` 
$secretId = "";
$secretKey = "";
$endPoint = "";
$kms_account = new KMSAccount($endPoint,$secretId,$secretKey);
```
> `$endPoint`需要填写接口请求域名，域名信息如下

外网接口请求域名：https://kms-region.api.qcloud.com

内网接口请求域名：https://kms-region.api.tencentyun.com

> 接口请求域名中 `region` 需用具体地域替换，如下表所示：

|区域|替换值|外网请求域名|内网请求域名|
|---------|---------|---------|---------|
|北京|bj|https://kms-bj.api.qcloud.com|https://kms-bj.api.tencentyun.com|
|上海|sh|https://kms-sh.api.qcloud.com|https://kms-sh.api.tencentyun.com|
|广州|gz|https://kms-gz.api.qcloud.com|https://kms-gz.api.tencentyun.com|

## 初始化客户端配置

客户端默认使用sha1 签名算法，可以调用签名算法修改签名方式

### 方法原型
```
public function set_sign_method($method='sha1')
```

## 密钥管理操作
### 创建主密钥
#### 方法原型

```
public function create_key($Alias = NULL, $Description = NULL, $KeyUsage="ENCRYPT/DECRYPT")
```

#### 参数说明

| 参数名 | 类型 | 默认值 | 参数描述 |
|---------|---------|---------|---------|
|Description|string|NULL|主密钥描述|
|Alias|string|NULL|主密钥别名|
|KeyUsage|string|ENCRYPT/DECRYPT|主密钥用途：默认是加解密|

返回值 $kms_meta 结构体 描述如下：

| 属性名称 | 类型 | 含义 |
|---------|---------|---------|
|KeyId|string|密钥id|
|CreateTime|uinx time|创建时间|
|Description|string|密钥描述|
|KeyState|string|密钥状态|
|KeyUsage|string|密钥用途|
|Alias|string|密钥别名|

#### 使用示例

```
$Description = "test";
$Alias = "test";
$KeyUsage= "ENCRYPT/DECRYPT";
$kms_meta = $kms_account->create_key($Alias,$Description,$KeyUsage);
```

### 获取主密钥属性
#### 方法原型

```
public function get_key_attributes($KeyId = NULL)
```

#### 参数说明

| 参数名 | 类型 | 默认值 | 参数描述 |
|---------|---------|---------|---------|
|KeyId|string|None|主密钥Id|

返回值 $kms_meta 结构体 描述如下：

| 属性名称 | 类型 | 含义 |
|---------|---------|---------|
|KeyId|string|密钥id|
|CreateTime|uinx time|创建时间|
|Description|string|密钥描述|
|KeyState|string|密钥状态|
|KeyUsage|string|密钥用途|
|Alias|string|密钥别名|

#### 使用示例

```
$keyId="";
$kms_meta = $kms_account->get_key_attributes($keyId);
```

### 获取主密钥列表
#### 方法原型

```
public function list_key($offset = 0, $limit = 10)
```

#### 参数说明

| 参数名 | 类型 | 默认值 | 参数描述 |
|---------|---------|---------|---------|
|offset|int|0|返回列表偏移值|
|limit|int|10|本次返回列表限制个数，不填写默认为返回10个|

#### 使用示例

```
$ret_pkg = $kms_account->list_key();
```
### 生成数据密钥
#### 方法原型

```
public function generate_data_key($KeyId = NULL, $KeySpec = "", $NumberOfBytes = 1024,$EncryptionContext =NULL)
```

#### 参数说明

|参数名|类型|默认值|参数描述|
|---------|---------|---------|---------|
|KeyId|string|None|主密钥Id|
|KeySpec|string|None|生成数据密钥算法|
|NumberOfBytes|int|None|生成指定长度的数据密钥|
|EncryptionContext|json string |无|生成数据密钥时提供的额外的json key-value|
|Plaintext|string|无|生成的数据密钥明文|
|CiphertextBlob|string|无|生成的数据密钥密文|

返回字典：
|参数名|类型|参数描述|
|---------|---------|---------|
|plaintext|string|表示生成的数据密钥明文(输入参数返回)|
|ciphertextBlob|string|表示生成的数据密钥密文|

#### 使用示例

```
$KeySpec = "AES_128";
$ret_pkg = $kms_account->generate_data_key($kms_meta->KeyId,$KeySpec,1024,"");
$Plaintext = $ret_pkg['plaintext'];
$CiphertextBlob = $ret_pkg['ciphertextBlob'];
```
### 启用主密钥
#### 方法原型

```
public function enable_key($KeyId = NULL)
```

#### 参数说明

|参数名|类型|默认值|参数描述|
|---------|---------|---------|---------|
|KeyId|string|None|主密钥Id|

返回值 无

#### 使用示例

```
$KeyId= "";
$kms_account->enable_key($KeyId);
```
### 禁用主密钥
#### 方法原型

```
public function disable_key($KeyId= NULL)
```

#### 参数说明

|参数名|类型|默认值|参数描述|
|---------|---------|---------|---------|
|KeyId|string|None|主密钥Id|

返回值 无
#### 使用示例

```
$KeyId= "";
$kms_account->disable_key($KeyId);
```

## 加解密操作
### 加密
#### 方法原型

```
public function encrypt($KeyId = NULL, $Plaintext=NULL,$EncryptionContext =NULL)
```

#### 参数说明

|参数名|类型|默认值|参数描述|
|---------|---------|---------|---------|
|KeyId|string|None|主密钥Id|
|Plaintext|string|空字符串|明文|
|EncryptionContext|string|None|key/value对的json字符串，如果指定了该参数，则在调用Decrypt API时需要提供同样的参数|

返回值$ciphertextBlob：

|参数名|类型|参数描述|
|---------|---------|---------|
|ciphertextBlob|string|表示生成的密文|

#### 使用示例

```
$ciphertextBlob = $kms_account->encrypt($KeyId,$Plaintext);
```
### 解密
#### 方法原型

```
public function decrypt($CiphertextBlob = NULL,$EncryptionContext = NULL)
```

#### 参数说明

|参数名|类型|默认值|参数描述|
|---------|---------|---------|---------|
|CiphertextBlob|string|空字符串|密文|
|EncryptionContext|string|None|key/value对的json字符串，如果指定了该参数，则在调用Decrypt API时需要提供同样的参数。|

返回值$plaintext：

|参数名|类型|参数描述|
|---------|---------|---------|
|plaintext|string|表示通过密文解密得到的明文|

#### 使用示例

```
$plaintext = $kms_account->decrypt($CiphertextBlob);
```
