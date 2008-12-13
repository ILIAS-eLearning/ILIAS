<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesWebServicesECS 
*/

include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
include_once('Services/WebServices/ECS/classes/class.ilECSResult.php');
include_once('Services/WebServices/Curl/classes/class.ilCurlConnection.php');

class ilECSConnector
{
	const HTTP_CODE_CREATED = 201;
	const HTTP_CODE_OK = 200;
	const HTTP_CODE_NOT_FOUND = 404;
	
	protected $path_postfix = '';
	
	protected $settings;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
	 	$this->settings = ilECSSettings::_getInstance();
	}
	
	///////////////////////////////////////////////////////
	// auths methods 
	///////////////////////////////////////////////////////
	
	/**
	 * Add auth resource
	 *
	 * @access public
	 * @param string post data 
	 * @return int new econtent id
	 * @throws ilECSConnectorException 
	 * 
	 */
	public function addAuth($a_post)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Add new Auth resource...');

	 	$this->path_postfix = '/auths';
	 	
	 	try 
	 	{
	 		$this->prepareConnection();
	 		$this->curl->setOpt(CURLOPT_POST,true);
	 		$this->curl->setOpt(CURLOPT_POSTFIELDS,$a_post);
			$res = $this->call();
			
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			$ilLog->write(__METHOD__.': Checking HTTP status...');
			if($info != self::HTTP_CODE_CREATED)
			{
				$ilLog->write(__METHOD__.': Cannot create auth resource, did not receive HTTP 201. ');
				$ilLog->write(__METHOD__.': POST was: '.$a_post);
				$ilLog->write(__METHOD__.': HTTP code: '.$info);
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$ilLog->write(__METHOD__.': ... got HTTP 201 (created)');
			return true;
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	/**
	 * get auth resource
	 *
	 * @access public
	 * @param auth hash (transfered via GET)
	 * @throws ilECSConnectorException 
	 */
	public function getAuth($a_hash)
	{
		global $ilLog;
		
		if(!strlen($a_hash))
		{
			$ilLog->write(__METHOD__.': No auth hash given. Aborting.');
			throw new ilECSConnectorException('No auth hash given.');
		}
		
		$this->path_postfix = '/auths/'.$a_hash;

	 	try 
	 	{
	 		$this->prepareConnection();
			$res = $this->call();
			
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			$ilLog->write(__METHOD__.': Checking HTTP status...');
			if($info != self::HTTP_CODE_OK)
			{
				$ilLog->write(__METHOD__.': Cannot get auth resource, did not receive HTTP 200. ');
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$ilLog->write(__METHOD__.': ... got HTTP 200 (ok)');
			return new ilECSResult($res);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	///////////////////////////////////////////////////////
	// eventqueues methods
	///////////////////////////////////////////////////////
	
	/**
	 * get event queue 
	 *
	 * @access public
	 * @throw ilECSConnectorException
	 */
	public function getEventQueues()
	{
		global $ilLog;
		
		$this->path_postfix = '/eventqueues';

	 	try 
	 	{
	 		$this->prepareConnection();
	 		
			$res = $this->call();
			
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			$ilLog->write(__METHOD__.': Checking HTTP status...');
			if($info != self::HTTP_CODE_OK)
			{
				$ilLog->write(__METHOD__.': Cannot get event queue, did not receive HTTP 200. ');
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$ilLog->write(__METHOD__.': ... got HTTP 200 (ok)');			
			return new ilECSResult($res);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	
	///////////////////////////////////////////////////////
	// econtents methods
	/////////////////////////////////////////////////////// 
	
	/**
	 * Get resources from ECS server.
	 *  
	 * 
	 *
	 * @access public
	 * @param int e-content id
	 * @return object ECSResult 
	 * @throws ilECSConnectorException 
	 */
	public function getResources($a_econtent_id = 0)
	{
	 	global $ilLog;
		
		if($a_econtent_id)
		{
			$ilLog->write(__METHOD__.': Get resource with ID: '.$a_econtent_id);
		}
		else
		{
			$ilLog->write(__METHOD__.': Get all resources ...');
		}
	 	
	 	
	 	$this->path_postfix = '/econtents';
	 	if($a_econtent_id)
	 	{
	 		$this->path_postfix .= ('/'.(int) $a_econtent_id);
	 	}
	 	
	 	try 
	 	{
	 		$this->prepareConnection();
	 		$this->curl->setOpt(CURLOPT_HEADER,false);
			$res = $this->call();

			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
			
			$result = new ilECSResult($res);
			$result->setHTTPCode($info);
			
			return $result;
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	/**
	 * Add resource
	 *
	 * @access public
	 * @param string post data 
	 * @return int new econtent id
	 * @throws ilECSConnectorException 
	 * 
	 */
	public function addResource($a_post)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Add new EContent...');

	 	$this->path_postfix = '/econtents';
	 	
	 	try 
	 	{
	 		$this->prepareConnection();
	 		$this->curl->setOpt(CURLOPT_HEADER,true);
	 		$this->curl->setOpt(CURLOPT_POST,true);
	 		$this->curl->setOpt(CURLOPT_POSTFIELDS,$a_post);
			$res = $this->call();
			
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			$ilLog->write(__METHOD__.': Checking HTTP status...');
			if($info != self::HTTP_CODE_CREATED)
			{
				$ilLog->write(__METHOD__.': Cannot create econtent, did not receive HTTP 201. ');
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$ilLog->write(__METHOD__.': ... got HTTP 201 (created)');			
			$result = new ilECSResult($res,true);
			$headers = $result->getHeaders();

			include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
			return ilECSUtils::_fetchEContentIdFromHeader($headers);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	/**
	 * update resource
	 *
	 * @access public
	 * @param int econtent id
	 * @param string post content
	 * @throws ilECSConnectorException
	 */
	public function updateResource($a_econtent_id,$a_post_string)
	{
	 	global $ilLog;
		
		$ilLog->write(__METHOD__.': Update resource with id '.$a_econtent_id);

	 	$this->path_postfix = '/econtents';
	 	
	 	if($a_econtent_id)
	 	{
	 		$this->path_postfix .= ('/'.(int) $a_econtent_id);
	 	}
	 	else
	 	{
	 		throw new ilECSConnectorException('Error calling updateResource: No content id given.');
	 	}
	 	try 
	 	{
	 		$this->prepareConnection();
	 		$this->curl->setOpt(CURLOPT_PUT,true);

	 		$fp = fopen('ecs_content.txt','w');
	 		fwrite($fp,$a_post_string);
	 		fclose($fp);
	 		
	 		#$this->curl->setOpt(CURLOPT_POSTFIELDS,$a_post_string);

			$this->curl->setOpt(CURLOPT_UPLOAD,true);
	 		$this->curl->setOpt(CURLOPT_INFILESIZE,filesize('ecs_content.txt'));
			$fp = fopen('ecs_content.txt','r');
	 		$this->curl->setOpt(CURLOPT_INFILE,$fp);
	 		#fclose($fp);
	 		
			$res = $this->call();
			
			return new ilECSResult($res);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	/**
	 * Delete resource
	 *
	 * @access public
	 * @param string econtent id
	 * @throws ilECSConnectorException 
	 */
	public function deleteResource($a_econtent_id)
	{
	 	global $ilLog;
		
		$ilLog->write(__METHOD__.': Delete resource with id '.$a_econtent_id);

	 	$this->path_postfix = '/econtents';
	 	
	 	if($a_econtent_id)
	 	{
	 		$this->path_postfix .= ('/'.(int) $a_econtent_id);
	 	}
	 	else
	 	{
	 		throw new ilECSConnectorException('Error calling deleteResource: No content id given.');
	 	}
	
	 	try 
	 	{
	 		$this->prepareConnection();
	 		$this->curl->setOpt(CURLOPT_CUSTOMREQUEST,'DELETE');
			$res = $this->call();
			return new ilECSResult($res);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	 	
	}
	
	///////////////////////////////////////////////////////
	// membership methods
	///////////////////////////////////////////////////////

	/**
	 * 
	 *
	 * @access public
	 * @param int membership id
	 * @throw ilECSConnectorException
	 */
	public function getMemberships($a_mid = 0)
	{
	 	global $ilLog;
		
		$ilLog->write(__METHOD__.': Get existing memberships');

	 	$this->path_postfix = '/memberships';
	 	if($a_mid)
	 	{
			$ilLog->write(__METHOD__.': Read membership with id: '.$a_mid);
	 		$this->path_postfix .= ('/'.(int) $a_mid);
	 	}
	 	try 
	 	{
	 		$this->prepareConnection();
			$res = $this->call();
			
			return new ilECSResult($res);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}

	/**
	 * prepare connection
	 *
	 * @access private
	 * @throws ilCurlConnectionException
	 */
	private function prepareConnection()
	{
	 	try
	 	{
	 		$this->curl = new ilCurlConnection($this->settings->getServerURI().$this->path_postfix);
 			$this->curl->init();
	 		if($this->settings->getProtocol() == ilECSSettings::PROTOCOL_HTTPS)
	 		{
	 			$this->curl->setOpt(CURLOPT_HTTPHEADER,array(0 => 'Accept: application/json'));
	 			$this->curl->setOpt(CURLOPT_SSL_VERIFYPEER,1);
	 			$this->curl->setOpt(CURLOPT_SSL_VERIFYHOST,1);
	 			$this->curl->setOpt(CURLOPT_RETURNTRANSFER,1);
	 			$this->curl->setOpt(CURLOPT_VERBOSE,1);
	 			$this->curl->setOpt(CURLOPT_CAINFO,$this->settings->getCACertPath());
	 			$this->curl->setOpt(CURLOPT_SSLCERT,$this->settings->getClientCertPath());
	 			$this->curl->setOpt(CURLOPT_SSLKEY,$this->settings->getKeyPath());
	 			$this->curl->setOpt(CURLOPT_SSLKEYPASSWD,$this->settings->getKeyPassword());
				
	 		}
	 	}
		catch(ilCurlConnectionException $exc)
		{
			throw($exc);
		}
	}
	
	/**
	 * call peer
	 *
	 * @access private
	 * @throws ilCurlConnectionException 
	 */
	private function call()
	{
 		try
 		{
 			$res = $this->curl->exec();
 			return $res;
 		}	 	
		catch(ilCurlConnectionException $exc)
		{
			throw($exc);
		}
	}
}
?>