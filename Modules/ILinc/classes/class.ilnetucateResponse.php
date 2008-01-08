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


require_once("./classes/class.ilSaxParser.php");

/**
* process reponse from Centra Server
* (c) Sascha Hofmann, 2004
*  
* @author	Sascha Hofmann <saschahofmann@gmx.de>
* @version	$Id$
* 
*/
class ilnetucateResponse extends ilSaxParser
{
	/**
	* Constructor
	* @access	public
	*/
	function ilnetucateResponse($a_str)
	{
		$xml_str = $this->validateInput($a_str);

		parent::ilSaxParser($xml_str);
				
		$this->startParsing();
	}
	
	function validateInput($a_str)
	{
		$response = split("\r\n\r\n",$a_str);
	
		$header = $response[0];
		$response_data = $response[1];
		
		if (strpos($response_data,"<?xml") === false)
		{
			echo "netucateResponse::validateInput() : No valid response data!<br/>";
			var_dump($header,$response_data);
			exit;
		}
        
        return chop($response_data);
	}
	
	function isError()
	{
		if ($this->data['response']['status'] == "error" or $this->data['response']['status'] == "")
		{
			return true;
		}
		
		return false;
	}
	
	function getErrorMsg()
	{
		if ($this->data['response']['status'] == "error" or $this->data['response']['status'] == "")
		{
			return trim($this->data['result']['cdata']);
		}
	}
	
	function getResultMsg()
	{
		return trim($this->data['result']['cdata']);
	}
	
	function getFirstID()
	{
		reset($this->data['id']);
		return current($this->data['id']);
	}
		

	/**
	 * set event handler
	 * should be overwritten by inherited class
	 * @access	private
	 */
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	 * start the parser
	 */
	function startParsing()
	{
		$xml_parser = $this->createParser();
		$this->setOptions($xml_parser);
		$this->setHandlers($xml_parser);
		$this->parse($xml_parser,$this->xml_file);
		$this->freeParser($xml_parser);
		return true;
	}
	
	/**
	* parse xml file
	* 
	* @access	private
	*/
	function parse($a_xml_parser,$a_xml_str)
	{
		$parseOk = xml_parse($a_xml_parser,$a_xml_str,true);

		if (!$parseOk && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE))
		{
				$this->ilias->raiseError("XML Parse Error: ".xml_error_string(xml_get_error_code($a_xml_parser)),$this->ilias->error_obj->FATAL);
		}
	}


	/**
	 * handler for begin of element
	 */
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $ilErr;

		switch($a_name)
		{
			case "netucate.API.Response":
				$this->data['response']['failureCount'] = $a_attribs['failureCount'];
				$this->data['response']['operationTotal'] = $a_attribs['operationTotal'];
				$this->data['response']['status'] = $a_attribs['status'];
				$this->data['response']['successCount'] = $a_attribs['successCount'];
				break;

			case "netucate.Result":
				$this->data['result']['code'] = $a_attribs['code'];
				$this->data['result']['id'] = $a_attribs['id'];
				$this->data['result']['name'] = $a_attribs['name'];
				$this->data['result']['request'] = $a_attribs['request'];
				break;

			case "netucate.ElementID":
				$this->data['element']['type'] = $a_attribs['type'];
				break;

			case "netucate.URL":
				break;

			case "netucate.ID":
				break;
				
			case "netucate.Class.List":
			case "netucate.User.List":
				break;
				
			case "netucate.Class":
				$this->data['classes'][$a_attribs['classid']] = array (
																		'name' => $a_attribs['name'],
																		'instructoruserid' => $a_attribs['instructoruserid'],
																		'bandwidth' => $a_attribs['bandwidth'],
																		'appsharebandwidth' => $a_attribs['appsharebandwidth'],
																		'description' => $a_attribs['description'],
																		'password' => $a_attribs['password'],
																		'message' => $a_attribs['message'],
																		'floorpolicy' => $a_attribs['floorpolicy'],
																		'conferencetypeid' => $a_attribs['conferencetypeid'],
																		'videobandwidth' => $a_attribs['videobandwidth'],
																		'videoframerate' => $a_attribs['videoframerate'],
																		'enablepush' => $a_attribs['enablepush'],
																		'issecure' => $a_attribs['issecure'],
																		'alwaysopen' => $a_attribs['alwaysopen'],
																		'akclassvalue1' => $a_attribs['akclassvalue1'],
																		'akclassvalue2' => $a_attribs['akclassvalue2']
																		);
				break;
				
			case "netucate.User":
				$this->data['users'][$a_attribs['userid']] = array (
																		'fullname' => $a_attribs['fullname'],
																		'authority' => $a_attribs['authority'],
																		'email' => $a_attribs['email'],
																		'homepage' => $a_attribs['homepage'],
																		'contactinfo' => $a_attribs['contactinfo'],
																		'comment' => $a_attribs['comments'],
																		'phonenumber' => $a_attribs['phonenumber'],
																		'akuservalue1' => $a_attribs['akuservalue1'],
																		'akuservalue2' => $a_attribs['akuservalue2'],
																		);
				break;
		}
	}


	function handlerEndTag($a_xml_parser, $a_name)
	{
		switch($a_name)
		{
			case "netucate.API.Response":
				$this->data['response']['cdata'] = $this->cdata;
				break;

			case "netucate.Result":
				$this->data['result']['cdata'] = $this->cdata;
				break;

			case "netucate.ElementID":
				//$this->data['element']['cdata'] = $this->cdata;
				break;

			case "netucate.URL":
				$this->data['url']['cdata'] = trim($this->cdata);
				break;

			case "netucate.ID":
				$this->data['id'][trim($this->cdata)] = trim($this->cdata);
				break;
				
			case "netucate.Class.List":
			case "netucate.Class":
			case "netucate.User.List":
			case "netucate.User":
				break;
		}
		
		$this->cdata = '';
	}
	
	/**
	 * handler for character data
	 */
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		if(!empty($a_data))
		{
			$this->cdata .= $a_data;
		}
	}
	
}
?>