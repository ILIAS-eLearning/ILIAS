<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

// @deprecated
define('DEFAULT_TIMEOUT',5);
// @deprecated
define('DEFAULT_RESPONSE_TIMEOUT',30);

include_once './webservice/soap/lib/nusoap.php';


/**
* Wrapper class for NuSOAP soap_client
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/
class ilSoapClient
{
	const DEFAULT_CONNECT_TIMEOUT = 10;
	const DEFAULT_RESPONSE_TIMEOUT = 5;

	var $server = '';
	var $timeout = null;
	var $response_timeout = null;
	var $use_wsdl = true;

	function __construct($a_server = '')
	{
		$this->log = ilLoggerFactory::getLogger('wsrv');

		$this->__setServer($a_server);
	}

	function __setServer($a_server)
	{
		global $ilSetting; 
		
		if(strlen($a_server))
		{
			return $this->server = $a_server;
		}		
		
		if(strlen(trim($ilSetting->get('soap_wsdl_path'))))
		{
			return $this->server = trim($ilSetting->get('soap_wsdl_path'));
		}

		$this->setTimeout($ilSetting->get('soap_connect_timeout', self::DEFAULT_CONNECT_TIMEOUT));
		
		$this->server = ilUtil::_getHttpPath().'/webservice/soap/server.php?wsdl';
	}

	function getServer()
	{
		return $this->server;
	}
	
	function setTimeout($a_timeout)
	{
		$this->timeout = $a_timeout;
	}
	function getTimeout()
	{
		return $this->timeout ? $this->timeout : self::DEFAULT_CONNECT_TIMEOUT;
	}

	function setResponseTimeout($a_timeout)
	{
		$this->response_timeout = $a_timeout;
	}
	function getResponseTimeout()
	{
		return $this->response_timeout;
	}

	function enableWSDL($a_status)
	{
		$this->use_wsdl = (bool) $a_status;
	}
	function enabledWSDL()
	{
		return (bool) $this->use_wsdl;
	}

	function init()
	{
		$this->client = new nusoap_client($this->getServer(),
										$this->enabledWSDL(),
										false, // no proxy support in the moment
										false,
										false,
										false,
										$this->getTimeout(),
										$this->getResponseTimeout());
		
		if($error = $this->client->getError())
		{
			if(stristr($error, 'socket read of headers') === FALSE)
			{
				$this->log->debug('Error calling soap server: '. $this->getServer().' ERROR: '. $error);
			}
			return false;
		}
		return true;
	}

	function &call($a_operation,$a_params)
	{
		$res = $this->client->call($a_operation,$a_params);

		#$GLOBALS['ilLog']->write(__METHOD__.': '.$this->client->request);
		#$GLOBALS['ilLog']->write(__METHOD__.': '.$this->client->response);

		if($error = $this->client->getError())
		{
			if(stristr($error, 'socket read of headers') === FALSE)
			{
				$this->log->debug($this->client->request);
				$this->log->debug($this->client->response);
				$this->log->info('Error calling soap server: '. $this->getServer().' ERROR: '. $error);
			}
		}

		return $res;
		// Todo cannot check errors here since it's not possible to distinguish between 'timeout' and other errors.
	}
}
?>