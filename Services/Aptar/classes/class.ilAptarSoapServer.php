<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarSoapServer
 */
class ilAptarSoapServer
{
	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var ilAptarSoapServerPHPWrapper
	 */
	protected $php_server;

	/**
	 * @var string
	 */
	protected $http_path;

	/**
	 * constructor
	 */
	protected function __construct()
	{
		ini_set('soap.wsdl_cache', 0);
		ini_set('soap.wsdl_cache_enabled', 0);

		$soap_config = array(
			'encoding' => 'UTF-8'
		);

		$protocol = 'http://';
		if(
			isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
		)
		{
			$protocol = 'https://';
		}
		if(isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] == 'on')
		{
			$protocol = 'https://';
		}

		$base_url = $_SERVER['SCRIPT_URI'] ? $_SERVER['SCRIPT_URI'] : $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

		$this->http_path = substr($base_url, 0, strpos($base_url, '/Services/'));

		if(!$this->sendWSDL())
		{
			$wsdl_path = $this->http_path . '/Services/Aptar/server.php?sendwsdl=1';
			require_once 'Services/Aptar/classes/class.ilAptarSoapServerPHPWrapper.php';
			$this->php_server = new ilAptarSoapServerPHPWrapper($wsdl_path, $soap_config);
			require_once 'Services/Aptar/classes/class.ilAptarSoapRequestHandler.php';
			$this->php_server->setClass('ilAptarSoapRequestHandler');
		}
	}

	/**
	 * @return ilAptarSoapServer
	 */
	public static function getInstance()
	{
		if(!(self::$instance instanceof self))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return bool
	 */
	protected function sendWSDL()
	{
		return ($_GET['sendwsdl'] == 1);
	}

	/**
	 * Handle request
	 */
	public function handleRequest()
	{
		if($this->sendWSDL())
		{
			header('Content-type: text/xml');
			$wsdl = file_get_contents('./Services/Aptar/wsdl/wsdl.xml');
			$wsdl = str_replace("{{HTTP_PATH}}", $this->http_path . '/Services/Aptar/server.php', $wsdl);
			echo $wsdl;
			return;
		}

		$this->php_server->handle();
	}

}