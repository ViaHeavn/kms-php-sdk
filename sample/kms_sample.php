<?php
require_once '../kms/kms_api.php';
require_once KMSAPI_ROOT_PATH . '/kms_account.php';
require_once KMSAPI_ROOT_PATH . '/kms_exception.php';

class KmsDemo
{
    private $secretId;
    private $secretKey;
    private $endpoint;

    public function __construct( $endpoint,$secretId, $secretKey)
    {
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->endpoint = $endpoint;
    }

    public function run()
    {
            try
            {
            	$kms_account = new KMSAccount($this->endpoint,$this->secretId,$this->secretKey);
            	$Description = "test";
            	$Alias = "test";
            	$KeyUsage= "ENCRYPT/DECRYPT";
            	$kms_meta = $kms_account->create_key($Alias,$Description,$KeyUsage);
            	print "------------create the custom key--------------" ;
            	print $kms_meta;

                //create a data key
            	$KeySpec = "AES_128";
                $ret_pkg = $kms_account->generate_data_key($kms_meta->KeyId,$KeySpec,1024,"");
                $Plaintext = $ret_pkg['plaintext'];
                $CiphertextBlob = $ret_pkg['ciphertextBlob'];

                //encrypt the data string
                $CiphertextBlob = $kms_account->encrypt($kms_meta->KeyId,$Plaintext);

                //decrypt the encrypted data string
                $Plaintext = $kms_account->decrypt($CiphertextBlob);
                // get key meta
                $kms_meta = $kms_account->get_key_attributes($kms_meta->KeyId);

                // set key alias
                $Alias = "for test" ;
                $kms_account->set_key_attributes($kms_meta->KeyId,$Alias);

                //disable a custom key
                $kms_account->disable_key($kms_meta->KeyId);

                //enable a custom key
                $kms_account->enable_key($kms_meta->KeyId);
                //schedule key deletion 
                $kms_account->schedule_key_deletion($kms_meta->KeyId, 7);
                //cancel key deletion 
                $kms_account->cancel_key_deletion($kms_meta->KeyId);
                
                //list the custom key
                $ret_pkg = $kms_account->list_key();

                }
            catch (KMSExceptionBase $e)
                {
                    echo " Exception: " . $e;
                return;
                }
    }
}
// 从腾讯云官网查看云api的密钥信息
$secretId = "";
$secretKey = "";
$endPoint = "";

$instance = new  KmsDemo($secretId, $secretKey, $endPoint);
$instance->run();
