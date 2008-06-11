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

include_once('./Services/WebServices/ECS/classes/class.ilECSConnectorException.php');

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/

class ilECSResult
{
	const RESULT_TYPE_JSON = 1;
	
	protected $log;
	
	protected $result_string = '';
	protected $result_header = '';
	protected $http_code = '';
	protected $result;
	protected $result_type;
	protected $header_parsing = false;
	
	protected $headers = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string result_string
	 * @param int result type
	 * @throws ilECSConnectorException
	 * 
	 */
	public function __construct($a_res,$with_headers = false,$a_type = self::RESULT_TYPE_JSON)
	{
	 	global $ilLog;
	 	
	 	$this->log = $ilLog;
	 	
	 	$this->result_string = $a_res;
	 	$this->result_type = $a_type;
	 	
	 	if($with_headers)
	 	{
	 		$this->header_parsing = true;
	 	}
	
		$this->init();
	}
	
	/**
	 * set HTTP return code
	 *
	 * @access public
	 * @param string http code
	 * 
	 */
	public function setHTTPCode($a_code)
	{
	 	$this->http_code = $a_code;
	}
	
	/**
	 * get HTTP code
	 *
	 * @access public
	 */
	public function getHTTPCode()
	{
	 	return $this->http_code;
	}
	
	/**
	 * get unformated result string
	 *
	 * @access public
	 * 
	 */
	public function getPlainResultString()
	{
	 	return $this->result_string;
	}

	/**
	 * get result
	 *
	 * @access public
	 * @return mixed JSON object, array of objects or false in case of errors.
	 * 
	 */
	public function getResult()
	{
		return $this->result;	 	
	}
	
	/**
	 * get headers
	 *
	 * @access public
	 */
	public function getHeaders()
	{
	 	return $this->headers ? $this->headers : array();
	}
	
	/**
	 * init result (json_decode) 
	 * @access private
	 * 
	 */
	private function init()
	{
		if(!$this->result_string)
		{
			$this->result = array();
			return true;
		}
		
		if($this->header_parsing)
		{
			$this->splitHeader();
			$this->parseHeader();
		}

	 	switch($this->result_type)
	 	{
	 		case self::RESULT_TYPE_JSON:
				$this->result = json_decode($this->result_string);
				break;
	 	}
	 	return true;
	}
	
	/**
	 * Split header and content 
	 *
	 * @access private
	 * @throws ilECSConnectorException
	 * 
	 */
	private function splitHeader()
	{
	 	$pos = strpos($this->result_string,"\r\n\r\n");
	 	if($pos !== false)
	 	{
	 		$this->result_header = substr($this->result_string,0,$pos + 2);
	 		$this->result_string = substr($this->result_string,$pos + 2,-1);
	 		return true;
	 	}
	 	else
	 	{
			$this->log->write(__METHOD__.': Cannot find header entry');
	 		throw new ilECSConnectorException('Cannot find header part.');
	 	}
	}
	
	/**
	 * Parse header
	 *
	 * @access private
	 * 
	 */
	private function parseHeader()
	{
		// In the moment only look for "Location:" value
		$location_start = strpos($this->result_header,"Location:");
		if($location_start !== false)
		{
			$location_start += 10;
			$location_end = strpos($this->result_header,"\r\n",$location_start);
			
			$location = substr($this->result_header,$location_start,$location_end - $location_start);
			$this->headers['Location'] = $location;
		}
		return true;
	}
}

?>