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


/**
* Class UserMail
* this class handles user mails 
* 
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
include_once "Services/Mail/classes/class.ilMail.php";

class ilFormatMail extends ilMail
{

	/**
	* Constructor
	* setup an mail object
	* @param int user_id
	* @access	public
	*/
	function ilFormatMail($a_user_id)
	{
		parent::ilMail($a_user_id);
	}

	/**
	* format a reply message
	* @access	public
	* @return string
	*/
	function formatReplyMessage()
	{
		if(empty($this->mail_data))
		{
			return false;
		}
#		debug($this->mail_data["m_message"]);
		$bodylines = explode(chr(13).chr(10), $this->mail_data["m_message"]);
#		var_dump("<pre>",$bodylines,"</pre");
		for ($i = 0; $i < count($bodylines); $i++)
		{
			$bodylines[$i] = "> ".$bodylines[$i];
		}
		return $this->mail_data["m_message"] = implode(chr(13).chr(10), $bodylines);
	}

	/**
	* format a reply subject
	* @access	public
	* @return string
	*/
	function formatReplySubject()
	{
		if(empty($this->mail_data))
		{
			return false;
		}
		return $this->mail_data["m_subject"] = "RE: ".$this->mail_data["m_subject"];
	}
	/**
	* get reply recipient
	* @access	public
	* @return string
	*/
	function formatReplyRecipient()
	{
		if(empty($this->mail_data))
		{
			return false;
		}

		require_once './Services/User/classes/class.ilObjUser.php';

		$user = new ilObjUser($this->mail_data["sender_id"]);
		return $this->mail_data["rcp_to"] = $user->getLogin();
	}
	/**
	* format a forward subject
	* @access	public
	* @return string
	*/
	function formatForwardSubject()
	{
		if(empty($this->mail_data))
		{
			return false;
		}
		return $this->mail_data["m_subject"] = "[FWD: ".$this->mail_data["m_subject"]."]";
	}

	/**
	* append search result to recipient
	* @access	public
	* @param array names to append
	* @param string rcp type ('to','cc','bc')
	* @return string
	*/
	function appendSearchResult($a_names,$a_type)
	{
		if(empty($this->mail_data))
		{
			return false;
		}
		$name_str = implode(',',$a_names);
		switch($a_type)
		{
			case 'to':
				$this->mail_data["rcp_to"] = trim($this->mail_data["rcp_to"]);
				if($this->mail_data["rcp_to"])
				{
					$this->mail_data["rcp_to"] = $this->mail_data["rcp_to"].",";
				}
				$this->mail_data["rcp_to"] = $this->mail_data["rcp_to"] . $name_str;
				break;

			case 'cc':
				$this->mail_data["rcp_cc"] = trim($this->mail_data["rcp_cc"]);
				if($this->mail_data["rcp_cc"])
				{
					$this->mail_data["rcp_cc"] = $this->mail_data["rcp_cc"].",";
				}
				$this->mail_data["rcp_cc"] = $this->mail_data["rcp_cc"] . $name_str;
				break;

			case 'bc':
				$this->mail_data["rcp_bcc"] = trim($this->mail_data["rcp_bcc"]);
				if($this->mail_data["rcp_bcc"])
				{
					$this->mail_data["rcp_bcc"] = $this->mail_data["rcp_bcc"].",";
				}
				$this->mail_data["rcp_bcc"] = $this->mail_data["rcp_bcc"] . $name_str;
				break;

		}
		return $this->mail_data;
	}
	/**
	* format message according to linebreak option
	* @param string message
	* @access	public
	* @return string formatted message
	*/
	function formatLinebreakMessage($a_message)
	{
		$formatted = array();

#		debug($a_message);
		$linebreak = $this->mail_options->getLinebreak();
		// SPLIT INTO LINES returns always an array
		$lines = explode(chr(13).chr(10),$a_message);
		for($i=0;$i<count($lines);$i++)
		{
			if(substr($lines[$i],0,1) != '>')
			{
				$formatted[] = wordwrap($lines[$i],$linebreak,chr(13).chr(10));
			}
			else
			{
				$formatted[] = $lines[$i];
			}
		}
		$formatted = implode(chr(13).chr(10),$formatted);
#		debug($formatted);
		return $formatted;
	}
					
				

	/**
	* append signature to mail body
	* @access	public
	* @return string
	*/
	function appendSignature()
	{
		return $this->mail_data["m_message"] .= chr(13).chr(10).$this->mail_options->getSignature();
	}
} // END class.ilFormatMail
?>
