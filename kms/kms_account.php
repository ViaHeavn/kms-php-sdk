<?php

require_once KMSAPI_ROOT_PATH . '/kms_client.php';

class KeyMetadata
{
	/*
            @attribute              @description                                 @value
            KeyId                  key id
            CreateTime             create time of the key                        unis time stamp
            Description            the description of the key
            KeyState               the state of the key                          Enabled|Disabled
            KeyUsage               the usage of the key                          ENCRYPT|DECRYPT
    */
	public $KeyId;
	public $CreateTime;
	public $Description;
	public $KeyState;
	public $KeyUsage;
	public $Alias ;
	public $DeleteTime;
	
	public function __construct()
	{
		
		$this->KeyId="";
		$this->CreateTime= -1 ;
		$this->Description = "";
		$this->KeyState="";
		$this->KeyUsage="";
		$this->DeleteTime = 0;
		$this->Alias;
	}
	
	public function __toString()
	{
		$info = array(
			"keyId" => $this->KeyId,
		    "createTime" => date("Y-m-d H:i:s",$this->CreateTime),
			"description" =>$this->Description,
			"keyState" =>$this->KeyState,
			"keyUsage" =>$this->KeyUsage,
			"alias"   => $this->Alias
		);
		return json_encode($info);
	}
}

/*
  KMSAccount
 */
class KMSAccount
{
	private $secretId;
	private $secretKey;
	private $kms_client;

	public function __construct($host, $secretId, $secretKey) {
		$this->host = $host;
		$this->secretId = $secretId;
		$this->secretKey = $secretKey;
		$this->kms_client = new KMSClient($host, $secretId, $secretKey);
	}

    public function set_sign_method($method)
    {
    	$this->kms_client->set_sign_method($method);
    }
	public function set_client($host, $secretId=NULL, $secretKey=NULL) {
		if ($secretId == NULL) {
			$secretId = $this->secretId;
		}
		if ($secretKey == NULL) {
			$secretKey = $this->secretKey;
		}
		$this->kms_client = new KMSClient($host, $secretId, $secretKey);
	}


	public function get_client() {
		return $this->kms_client;
	}
	
	protected function __resp2meta__(&$kms_meta, $resp)
	{
		if(isset($resp['keyId']))
			$kms_meta->KeyId = $resp['keyId'];
		if(isset($resp['createTime']))
			$kms_meta->CreateTime = $resp['createTime'];
		if(isset($resp['description']))
		    $kms_meta->Description = $resp['description'];
		if(isset($resp['keyState']))
		    $kms_meta->KeyState = $resp['keyState'];	
		if(isset($resp['keyUsage']))
		    $kms_meta->KeyUsage = $resp['keyUsage'];	
        if(isset($resp['alias']))
        	$kms_meta->Alias = $resp['alias'];
        if(isset($resp['deleteTime']))
        	$kms_meta->DeleteTime = $resp['deleteTime'];
        
	}
	
	/* create master key
	 @params            @description                       @type            @default
	 input:
	 Description       the description of the key          string           ""
	 KeyUsage          the usage of the key                string           "ENCRYPT/DECRYPT"
	
	 return:
	 KeyMetadata       the key information                 KeyMeta class
	 KMSExceptionBase  exception                           KMSException
	 */
	public function create_key($Alias = NULL, $Description = NULL, $KeyUsage="ENCRYPT/DECRYPT")
	{
		$params =  array('keyUsage' =>$KeyUsage);
		if ($Description != NULL)
			$params['description'] = $Description;
		if ($Alias != NULL)
			$params['alias'] = $Alias;
		$ret_pkg = $this->kms_client->create_key($params);
		$kms_meta = new KeyMetadata();
		$this->__resp2meta__($kms_meta, $ret_pkg);
		return $kms_meta;
	}
	/* generate data key for  encryption or decryption
            @params            @description                       @type            @default     @value
            input:
            KeyId             the key id                          string
            KeySpace          The encryption algorithm            string                        AES_128 |AES_256
            NumberOfBytes     the length of the data key          int                           1-1024
            EncryptionContext for encryption context              json
            return:
            KeyId             the key id                          string
            Plaintext
            CiphertextBlob
            KMSExceptionBase  exception                           KMSException
      */
	public function generate_data_key($KeyId = NULL, $KeySpec = "", $NumberOfBytes = 1024,$EncryptionContext =NULL)
	{
		$params = array('keySpec' => $KeySpec,
		          'numberOfBytes' => $NumberOfBytes
		);
		if ($KeyId != NULL)
			$params['keyId'] = $KeyId;
		if ($EncryptionContext != NULL)
			$params['encryptionContext'] = $EncryptionContext;
		$ret_pkg = $this->kms_client->generate_data_key($params);
		return base64_decode($ret_pkg['plaintext']);		
	}
	/* encryption
            @params            @description                       @type            @default     @value
            input:
            KeyId             the key id                          string
            Plaintext         the data needs encrpt               string
            EncryptionContext for encryption context              json
            return:
            KeyId             the key id                          string
            CiphertextBlob
            KMSExceptionBase  exception                           KMSException
      */
	public function encrypt($KeyId = NULL, $Plaintext=NULL,$EncryptionContext =NULL)
	{
		$params=array();
		if ($KeyId != NULL)
			$params['keyId']= $KeyId;
	    if ($Plaintext != NULL)
	    	$params['plaintext'] =base64_encode($Plaintext);
	    if ($EncryptionContext != NULL)
	    	$params['encryptionContext'] =$EncryptionContext;
	    $ret_pkg = $this->kms_client->encrypt($params);
	    return $ret_pkg['ciphertextBlob'];
	    
		
	}
	
	/* decryption
            @params            @description                       @type            @default     @value
            input:
            CiphertextBlob
            EncryptionContext for encryption context              json
            return:
            plaintext             the key id                          string
            plaintext         
            KMSExceptionBase  exception                           KMSException
    */
	public function decrypt($CiphertextBlob = NULL,$EncryptionContext = NULL)
	{
	    $params=array();
	    if ($CiphertextBlob != NULL)
	    	$params['ciphertextBlob'] =$CiphertextBlob;
	    if ($EncryptionContext != NULL)
	    	$params['encryptionContext'] =$EncryptionContext;
	    $ret_pkg = $this->kms_client->decrypt($params);
	    return base64_decode($ret_pkg['plaintext']);
	}
	
    public function set_key_attributes($KeyId = NULL, $Alias)
    {
    	$params = array();
    	if ($KeyId != NULL)
    		$params['keyId'] = $KeyId;
    	$ret_pkg = $this->kms_client->set_key_attributes($params);
    	
    }
    
	public function get_key_attributes($KeyId = NULL)
	{
		$params=array();
		if ($KeyId != NULL)
			$params['keyId'] = $KeyId;
		$ret_pkg = $this->kms_client->get_key_attributes($params);
		$kms_meta = new KeyMetadata();
		$this->__resp2meta__($kms_meta, $ret_pkg);
		return $kms_meta ; 
	}	
	/* enable a data key
            @params            @description                       @type            @default     @value
            input:
            KeyId             the key id
            return:
            KMSExceptionBase  exception                           KMSException
    */
	public function enable_key($KeyId = NULL)
	{
		$params=array();
		if ($KeyId != NULL)
			$params['keyId'] = $KeyId;
		$this->kms_client->enable_key($params);
	}
	/* disable a data key
            @params            @description                       @type            @default     @value
            input:
            KeyId             the key id
            return:
            KMSExceptionBase  exception                           KMSException
     */
	
	public function disable_key($KeyId= NULL)
	{
	    $params=array();
	    if ($KeyId != NULL)
	    	$params['keyId'] = $KeyId;
	    $this->kms_client->disable_key($params);
	}
	
	/* list the data keys
            @params            @description                       @type            @default     @value
            input:
            Offset                                                int                0
            Limit             limit of the number of the keys     int               10
            return:
            Keys              the keys array                      array
            TotalCount        the number of the keys              int
            Offset                                                int
            Limit                                                 int
            KMSExceptionBase  exception                           KMSException
    */
	public function list_key($offset = 0, $limit = 10)
	{
		$params=array(
			'offset' =>$offset,
			'limit'  =>$limit
		);
		$ret_pkg = $this->kms_client->list_key($params);
	}
	
	public function schedule_key_deletion($keyId, $pendingWindowInDays)
	{
		$params=array(
				'keyId' =>$keyId,
				'pendingWindowInDays'  =>$pendingWindowInDays
		);
		$ret_pkg = $this->kms_client->schedule_key_deletion($params);
		
	}
	
	public function cancel_key_deletion($keyId)
	{
		$params=array(
				'keyId' =>$keyId
		);
		$ret_pkg = $this->kms_client->cancel_key_deletion($params);
	}
	
}
?>
