<?php
/**
* Class UserMail
* this class handles user mails 
* 
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package	ilias-mail
*/
require_once "classes/class.ilMail.php";

class ilFormatMail extends ilMail
{
	/**
	* linebreak
	* @var integer
	* @access public
	*/
	var $linebreak;

	/**
	* signature
	* @var string signature
	* @access public
	*/
	var $signature;

	/**
	* table name of user options
	* @var string
	* @access private
	*/
	var $table_mail_options;

	/**
	* Constructor
	* setup an mail object
	* @param int user_id
	* @access	public
	*/
	function ilFormatMail($a_user_id)
	{
		parent::ilMail($a_user_id);
		
		define("DEFAULT_LINEBREAK",60);
		$this->table_mail_options = 'mail_options';
		$this->getOptions();
	}

	/**
	* create entry in table_mail_options for a new user
	* this method should only be called from createUser()
	* @access	public
	* @return	boolean
	*/
	function createMailOptionsEntry()
	{
		$query = "INSERT INTO $this->table_mail_options ".
			"VALUES('".$this->user_id."','".DEFAULT_LINEBREAK."','')";

		$res = $this->ilias->db->query($query);
		return true;
	}

	/**
	* get options of user and set variables $signature and $linebreak
	* this method shouldn't bew called from outside
	* use getSignature() and getLinebreak()
	* @access	private
	* @return	boolean
	*/
	function getOptions()
	{
		$query = "SELECT * FROM $this->table_mail_options ".
			"WHERE user_id = '".$this->user_id."'";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		
		$this->signature = stripslashes($row->signature);
		$this->linebreak = stripslashes($row->linebreak);

		return true;
	}

	/**
	* update user options
	* @param string Signature
	* @param int linebreak
	* @return	boolean
	*/
	function updateOptions($a_signature, $a_linebreak)
	{
		$query = "UPDATE $this->table_mail_options ".
			"SET signature = '".addslashes($a_signature)."',".
			"linebreak = '".addslashes($a_linebreak)."' ".
			"WHERE user_id = '".$this->user_id."'";

		$res = $this->ilias->db->query($query);

		$this->signature = $a_signature;
		$this->linebreak = $a_linebreak;

		return true;
	}
	/**
	* get linebreak of user
	* @access	public
	* @return	array	mails
	*/
	function getLinebreak()
	{
		return $this->linebreak;
	}

	/**
	* get signature of user
	* @access	public
	* @return	array	mails
	*/
	function getSignature()
	{
		return $this->signature;
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
		$bodylines = explode("\n", $this->mail_data["m_message"]);
		for ($i = 0; $i < count($bodylines); $i++)
		{
			$bodylines[$i] = "> ".$bodylines[$i];
		}
		return $this->mail_data["m_message"] = implode("\n", $bodylines);
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

		require_once "classes/class.ilObjUser.php";

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
		$formatted = '';

		$linebreak = $this->getLinebreak();
		// SPLIT INTO LINES returns always an array
		$lines = explode("\n",$a_message);
		foreach($lines as $line)
		{
			if(substr($line,0,1) != '>')
			{
				$formatted .= wordwrap($line,$linebreak);
			}
			else
			{
				$formatted .= $line.'\n';
			}
		}
		return $formatted;
	}
					
				

	/**
	* append signature to mail body
	* @access	public
	* @return string
	*/
	function appendSignature()
	{
		return $this->mail_data["m_message"] .= "\n\n\n".$this->getSignature();
	}
} // END class.ilFormatMail
?>
