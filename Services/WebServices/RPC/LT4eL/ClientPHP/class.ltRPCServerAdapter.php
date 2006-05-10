<?php
/*
	+-----------------------------------------------------------------------------+
	| LT4eL - Language Technology for e-Learning                                  |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2006 LT4eL Consortium                                         |
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

/**
* Example PHP Adapter class for communication between client application
* (LMS) and ltRPCServer.
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
define("RPC_TIMEOUT",10);

define("RPC_SERVER_PATH","/RPC2");
define("RPC_SERVER_HOST","127.0.0.1");
define("RPC_SERVER_PORT","11112");
define("RPC_SERVER_ALIVE",true);

//include_once('Services/WebServices/RPC/classes/class.ilRPCServerSettings.php');

class ltRPCServerAdapter
{
	var $log = null;
	var $db = null;
	var $err = null;

	var $settings_obj  = null;

	var $rpc_client = null;
	var $rpc_message = null;

	function ltRPCServerAdapter()
	{
		$this->__checkPear();

		//$this->settings_obj =& new ilRPCServerSettings();
	}

	/**
	 * Send message to remote rpc server and get response.
	 *
	 * @return object result object. Type is depend on the calles method
	 *
	 * @access protected
	 */	
	function &send()
	{
		include_once 'XML/RPC.php';
		if(!$response =& $this->rpc_client->send($this->rpc_message))
		{
			//$this->log->write("ilRPCServerAdapter: Communication error");
			echo "<br />ltRPCServerAdapter: Communication error";
			return null;
		}
		if($response->faultCode())
		{
			//$this->log->write("ilRPCServerAdapter: Communication error: ". $response->faultString());
			echo "<br />ltRPCServerAdapter: Communication error: ". $response->faultString();
			return null;
		}
		return XML_RPC_decode($response->value());
	}
	// PRIVATE
	function __checkPear()
	{
		if(!include_once('XML/RPC.php'))
		{
			//$this->log->write('ltRPCServerAdapter(): Cannot find pear library XML_RPC. Aborting');
			echo "<br />ltRPCServerAdapter(): Cannot find pear library XML_RPC. Aborting";
			//$this->err->raiseError("Cannot find pear package 'XML_RPC'. Aborting ",$this->err->MESSAGE);
		}
		return true;
	}

	/**
	 * Create RPC client object. Settings are read from class RPCSServerSettings.
	 *
	 * @return boolean success
	 *
	 * @access protected
	 */	
	function __initClient()
	{
		include_once 'XML/RPC.php';

		//$this->rpc_client =& new XML_RPC_Client($this->settings_obj->getPath(),
		//										$this->settings_obj->getHost(),
		//										$this->settings_obj->getPort());
		
		$this->rpc_client =& new XML_RPC_Client(RPC_SERVER_PATH,
												RPC_SERVER_HOST,
												RPC_SERVER_PORT);
		$this->rpc_client->setDebug(1);

		return true;
	}


	/**
	 * Create RPC message object
	 *
	 * @param string message name. Something like 'Indexer.ilFileIndexer'
	 * @param array of objects. Array of XML_RPC_Value objects. (Params of remote procedures)
	 * @return boolean success
	 *
	 * @access protected
	 */	
	function __initMessage($a_message_name,$params)
	{
		include_once 'XML/RPC.php';
		
		$this->rpc_message =& new XML_RPC_Message($a_message_name,$params);

		return true;
	}
}
?>
