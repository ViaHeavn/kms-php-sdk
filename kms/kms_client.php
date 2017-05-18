<?php
require_once  KMSAPI_ROOT_PATH . '/kms_exception.php ';
require_once  KMSAPI_ROOT_PATH . '/kms_http.php';
require_once  KMSAPI_ROOT_PATH . '/sign.php';


class KMSClient
{
	private $host;
	private $secretId;
	private $secretKey;
	private $version;
	private $http;
	private $method;
	private $path='/v2/index.php';
	
	public function __construct($host, $secretId, $secretKey,$version='SDK_PHP_1.0' , $method='POST' )
	{
		$this->process_host($host);
		$this->secretId = $secretId;
		$this->secretKey = $secretKey;
		$this->version = $version;
		$this->method = $method;
		$this->http = new KMSHttp($this->host);
		$this->signMethod = 'sha1';
		}
		
		protected function process_host($host) {
			// we only support https

			if (strpos($host,"https://") === 0) {
				$_host = substr($host,8,strlen($host)-8);
			}
			else {
				throw new KMSClientParameterException("Only support https prototol. Invalid endpoint:" . $host);
			}
			
			if ($_host[strlen($_host)-1]=="/") {
				$this->host = substr($_host,0,strlen($_host)-1);
			}
			else {
				$this->host = $_host;
			}
		}
		public function set_sign_method($method='sha1')
		{
			if($method != 'sha256' && $method != 'sha1')
				throw new KMSClientParameterException('Only support  sha1 or sha256');
			$this->signMethod = $method;
		}
		public function set_method($method='POST') {
			$this->method = $method;
		}
		
		public function set_connection_timeout($connection_timeout) {
			$this->http->set_connection_timeout($connection_timeout);
		}
		
		public function set_keep_alive($keep_alive) {
			$this->http->set_keep_alive($keep_alive);
		}
		
		protected function build_req_inter($action, $params, &$req_inter) {
			$_params = $params;
			$_params['Action'] = ucfirst($action);
			$_params['RequestClient'] = $this->version;
			if($this->signMethod =='sha256')
		        $_params['SignatureMethod']='HmacSHA256';
			else
				$_params['SignatureMethod']='HmacSHA1';
			
			if(!isset($_params['SecretId']))
				$_params['SecretId'] = $this->secretId;
		
			if (!isset($_params['Nonce']))
				$_params['Nonce'] = rand(1, 65535);
		
			if (!isset($_params['Timestamp']))
				$_params['Timestamp'] = time();
		     
			$plainText = Signature::makeSignPlainText($_params,
						$this->method, $this->host, $req_inter->uri);
			$_params['Signature'] = Signature::sign($plainText, $this->secretKey,$this->signMethod);
		
			$req_inter->data = http_build_query($_params);
			$this->build_header($req_inter);
		}
		
		protected function build_header(&$req_inter) {
			if ($this->http->is_keep_alive()) {
				$req_inter->header["Connection"] = "Keep-Alive";
			}
		}
		
		protected function check_status($resp_inter) {
			if ($resp_inter->status != 200) {
				throw new KMSServerNetworkException($resp_inter->status, $resp_inter->header, $resp_inter->data);
			}
		
			$resp = json_decode($resp_inter->data, TRUE);
			$code = $resp['code'];
			$message = $resp['message'];
			$requestId = $resp['requestId'];
		
			if ($code != 0) {
				throw new KMSServerException($message=$message, $request_id=$requestId, $code=$code, $data=$resp);
			}
		}
		
		protected function request($action, $params) {
			// make request internal
			$req_inter = new RequestInternal($this->method, $this->path);
			$this->build_req_inter($action, $params, $req_inter);	
			// send request
			$resp_inter = $this->http->send_request($req_inter);
		
			return $resp_inter;
		}
		
		#----------------------------------kms account operation----------------------#
		
		
	    public function create_key($params)
	    {
	    	$resp_inter  = $this->request("CreateKey",$params);
	    	$this->check_status($resp_inter);   	
	    	$ret = json_decode($resp_inter->data,TRUE);
	    	return $ret['keyMeta'] ;
	    }
	    public function generate_data_key($params)
	    {
	    	$resp_inter = $this->request("GenerateDataKey", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    public function encrypt($params)
	    {
	    	$resp_inter = $this->request("Encrypt", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    public function decrypt($params)
	    {
	    	$resp_inter = $this->request("Decrypt", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    
	    public function set_key_attributes($params)
	    {
	    	$resp_inter = $this->request("SetKeyAttributes",$params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret ;
	    }
	    public function get_key_attributes($params)
	    {
	    	$resp_inter = $this->request("GetKeyAttributes", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret['keyMeta'];
	    }
	    public function enable_key($params)
	    {
	    	$resp_inter = $this->request("EnableKey", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    public function disable_key($params)
	    {
	    	$resp_inter = $this->request("DisableKey", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    public function list_key($params)
	    {
	    	$resp_inter = $this->request("ListKey", $params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    public function schedule_key_deletion($params)
	    {
	    	$resp_inter = $this->request("ScheduleKeyDeletion",$params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
	    public function cancel_key_deletion($params)
	    {
	    	$resp_inter = $this->request("CancelKeyDeletion",$params);
	    	$this->check_status($resp_inter);
	    	$ret = json_decode($resp_inter->data, TRUE);
	    	return $ret;
	    }
				
}
	
?>


