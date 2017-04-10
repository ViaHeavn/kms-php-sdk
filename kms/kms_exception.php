<?php
if(class_exists('KMSExceptionBase') !=true)
{
class KMSExceptionBase extends RuntimeException
{
	/*
	 @type code: int
	 @param code: 错误类型

	 @type message: string
	 @param message: 错误描述

	 @type data: array
	 @param data: 错误数据
	 */

	public $code;
	public $message;
	public $data;

	public function __construct($message, $code=-1, $data=array())
	{
		parent::__construct($message, $code);
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}

	public function __toString()
	{
		return "KMSExceptionBase  " .  $this->get_info();
	}

	public function get_info()
	{
		$info = array("code" => $this->code,
				"data" => json_encode($this->data),
				"message" => $this->message);
		return json_encode($info);
	}
}


class KMSClientException extends KMSExceptionBase
{
	public function __construct($message, $code=-1, $data=array())
	{
		parent::__construct($message, $code, $data);
	}

	public function __toString()
	{
		return "KMSClientException  " .  $this->get_info();
	}
}

class KMSClientNetworkException extends KMSClientException
{
	/* 网络异常

	@note: 检查endpoint是否正确、本机网络是否正常等;
	*/
	public function __construct($message, $code=-1, $data=array())
	{
		parent::__construct($message, $code, $data);
	}

	public function __toString()
	{
		return "KMSClientNetworkException  " .  $this->get_info();
	}
}

class KMSClientParameterException extends KMSClientException
{
	/* 参数格式错误

	@note: 请根据提示修改对应参数;
	*/
	public function __construct($message, $code=-1, $data=array())
	{
		parent::__construct($message, $code, $data);
	}

	public function __toString()
	{
		return "KMSClientParameterException  " .  $this->get_info();
	}
}

class KMSServerNetworkException extends KMSExceptionBase
{
	//服务器网络异常

	public $status;
	public $header;
	public $data;

	public function __construct($status = 200, $header = NULL, $data = "")
	{
		if ($header == NULL) {
			$header = array();
		}
		$this->status = $status;
		$this->header = $header;
		$this->data = $data;
	}

	public function __toString()
	{
		$info = array("status" => $this->status,
				"header" => json_encode($this->header),
				"data" => $this->data);

		return "KMSServerNetworkException  " . json_encode($info);
	}
}

class KMSServerException extends KMSExceptionBase
{
	/* KMS处理异常
	@note: 根据code进行分类处理，常见错误类型：	
	: 更多错误类型请登录腾讯云消息服务官网进行了解；
	*/

	public $request_id;
	public function __construct($message, $request_id, $code=-1, $data=array())
	{
		parent::__construct($message, $code, $data);
		$this->request_id = $request_id;
	}

	public function __toString()
	{
		return "KMSServerException  " .  $this->get_info() . ", RequestID:" . $this->request_id;
	}
}

}
?>