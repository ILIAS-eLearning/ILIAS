<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarSoapServerPHPWrapper
 */
class ilAptarSoapServerPHPWrapper extends SoapServer
{
	/**
	 * Constructor
	 *
	 * @param string $wsdl_path
	 * @param array $soap_config
	 */
	public function __construct($wsdl_path, $soap_config)
	{
		parent::__construct($wsdl_path, $soap_config);
	}

	/**
	 * 
	 */
	public function handle()
	{
		// don't modify anything, if wsdl is sent
		if($_SERVER['QUERY_STRING'] == 'wsdl')
		{
			parent::handle();
			return;
		}

		ob_start();
		parent::handle();
		$response = ob_get_contents();
		ob_clean();
		if(strlen($response) === 0)
		{
			$response =
				'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
				'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header /><SOAP-ENV:Body /></SOAP-ENV:Envelope>' . "\n";
			header('Content-type: text/xml; charset=UTF-8');
			http_response_code(200);
		}
		echo $response;
		ob_get_flush();
		return;
	}
}