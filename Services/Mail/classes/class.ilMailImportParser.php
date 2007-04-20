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

require_once("classes/class.ilSaxParser.php");
require_once("Services/Mail/classes/class.ilMailbox.php");

/**
* Mail Import Parser
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$Id: class.ilMailImportParser.php,v 1.2 2004/04/08 09:59:55 smeyer Exp $
*
* @extends ilSaxParser
*/
class ilMailImportParser extends ilSaxParser
{
	var $mode;			// "check" or "import"
	var $counter;		// counter for building executeMultiple array


	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/
	function ilMailImportParser($a_xml_file,$a_mode)
	{
		define('EXPORT_VERSION',4);

		parent::ilSaxParser($a_xml_file);
		$this->mode = $a_mode;
		$this->counter = -1;
	}

	function getMode()
	{
		return $this->mode;
	}

	function getCountImported()
	{
		return count($this->mails);
	}

	function getNotAssignableUsers()
	{
		if(count($this->not_imported))
		{
			return implode("<br/>",$this->not_imported);
		}
		return "";
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
		parent::startParsing();

		switch($this->getMode())
		{
			case "check":
				if(count($this->not_imported))
				{
					return false;
				}
				break;

			case "import":
				$this->__insert();
				break;
		}
		return true;
	}


	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $ilErr;

		switch($a_name)
		{
			case "users":
				if($a_attribs["exportVersion"] < EXPORT_VERSION)
				{
					$ilErr->raiseError("!!! This export Version isn't supported, update your ILIAS 2 installation"
											 ,$ilErr->WARNING);
				}
				break;

			case "user":
				if(!($this->i3_id = ilObjUser::_getImportedUserId($a_attribs["id"])))
				{
					$this->not_imported[] = $a_attribs["id"];
				}
				break;
				
			case "mail":
				if($this->i3_id)
				{
					$this->mails[++$this->counter]["usr_id"] = $this->i3_id;
					$this->mails[$this->counter]["m_email"] = $a_attribs["asEmail"];
					
					// SET FOLDER ID = 0 FOR SYSTEM MESSAGES
					if($a_attribs["systemMessage"])
					{
						$this->mails[$this->counter]["folder_id"] = 0;
					}
				}
				break;

			case "sender":
				if($this->i3_id)
				{
					$sender = ilObjUser::_getImportedUserId($a_attribs["id"]);
					$this->mails[$this->counter]["sender_id"] = $sender;
					$this->mails[$this->counter]["import_name"] = $a_attribs["import_name"];
				}
				break;
					
				
			default: 
				// Do nothing
				break;
		}
	}


	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
		// STOP IF USER IS NOT ASSIGNABLE
		if(!$this->i3_id)
		{
			$this->cdata = '';
			return;
		}
		switch($a_name)
		{
			case "targetFolder":
				if(!isset($this->mails[$this->counter]["folder_id"]))
				{
					$tmp_mailbox =& new ilMailbox($this->i3_id);
					switch($this->cdata)
					{
						case "inbox":
							$this->mails[$this->counter]["folder_id"] = $tmp_mailbox->getInboxFolder();
							$this->mails[$this->counter]["read"] = "unread";
							break;
						case "sentbox":
							$this->mails[$this->counter]["folder_id"] = $tmp_mailbox->getSentFolder();
							$this->mails[$this->counter]["read"] = "read";
							break;
						case "draft":
							$this->mails[$this->counter]["folder_id"] = $tmp_mailbox->getDraftsFolder();
							$this->mails[$this->counter]["read"] = "read";
							break;
						case "trash":
							$this->mails[$this->counter]["folder_id"] = $tmp_mailbox->getTrashFolder();
							$this->mails[$this->counter]["read"] = "read";
							break;
					}
				}
				break;

			case "sendTime":
				$this->mails[$this->counter]["send_time"] = date("Y-m-d H:i:s",$this->cdata);
				$this->mails[$this->counter]["timest"] = date("YmdHis",$this->cdata);
				break;

			case "to":
				$this->mails[$this->counter]["rcp_to"] = $this->cdata;
				break;
				
			case "cc":
				$this->mails[$this->counter]["rcp_cc"] = $this->cdata;
				break;

			case "bcc":
				$this->mails[$this->counter]["rcp_bcc"] = $this->cdata;
				break;
				
			case "url":
				if($this->cdata)
				{
					$this->mails[$this->counter]["m_message"] = "Url: ".$this->cdata." <br>";
				}
				break;

			case "urlDescription":
				if($this->cdata)
				{
					$this->mails[$this->counter]["m_message"] .= $this->cdata."<br>";
				}
				break;

			case "subject":
				$this->mails[$this->counter]["m_subject"] = $this->cdata;
				break;

			case "message":
				$this->mails[$this->counter]["m_message"] .= $this->cdata;
				break;
		}
		$this->cdata = '';
	}


	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		// i don't know why this is necessary, but
		// the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
		// in character data, but we don't want that, because it's the
		// way we mask user html in our content, so we convert back...
		$a_data = str_replace("<","&lt;",$a_data);
		$a_data = str_replace(">","&gt;",$a_data);

		if(!empty($a_data))
		{
			$this->cdata .= $a_data;
		}
	}

	function __insert()
	{
		global $ilDB;

		$sth = $ilDB->prepare("INSERT INTO mail VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

		$ilDB->executeMultiple($sth,$this->__buildSQLArray());

		return true;
	}
	function __buildSQLArray()
	{
		global $ilDB;
		
		$sql = array();

		if(!count($this->mails))
		{
			return array();
		}

		foreach($this->mails as $mail)
		{
			$sql[] =  (array('0',
							 addslashes($mail["usr_id"]),
							 addslashes($mail["folder_id"]),
							 addslashes($mail["sender_id"]),
							 addslashes(serialize(array())),
							 addslashes($mail["send_time"]),
							 addslashes($mail["rcp_to"]),
							 addslashes($mail["rcp_cc"]),
							 addslashes($mail["rcp_bcc"]),
							 addslashes($mail["read"]),
							 addslashes(serialize(array("normal"))),
							 addslashes($mail["m_email"]),
							 addslashes($mail["m_subject"]),
							 addslashes($mail["m_message"]),
							 addslashes($mail["import_name"])));
			
		}
		return $sql ? $sql :array();
	}
}
?>
