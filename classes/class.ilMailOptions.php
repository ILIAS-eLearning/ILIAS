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

define("IL_MAIL_LOCAL", 0);
define("IL_MAIL_EMAIL", 1);
define("IL_MAIL_BOTH", 2);

/**
* Class UserMail
* this class handles user mails 
* 
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
class ilMailOptions
{
	var $ilias;

	// SOME QUASI STATIC CONSTANTS (possible values of incoming type)
	var $LOCAL = 0;
	var $EMAIL = 1;
	var $BOTH = 2;

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
	var $incoming_type;

	/**
	* Constructor
	* setup an mail object
	* @param int user_id
	* @access	public
	*/
	function ilMailOptions($a_user_id)
	{
		global $ilias;

		define("DEFAULT_LINEBREAK",60);

		$this->ilias =& $ilias;
		$this->table_mail_options = 'mail_options';

		$this->user_id = $a_user_id;
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
    	global $ilDB;
    		
		/* Get setting for incoming mails */
		if (!($incomingMail = $this->ilias->getSetting("mail_incoming_mail")))
		{
			/* No setting found -> set it to "local and forwarding" [2] */
			$incomingMail = IL_MAIL_BOTH;
		}

        $query = "INSERT INTO $this->table_mail_options " .
                "VALUES(" . $ilDB->quote($this->user_id) . "," . $ilDB->quote(DEFAULT_LINEBREAK) . ",'',".$ilDB->quote($incomingMail).")";

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
		global $ilDB;
		
		$query = "SELECT * FROM $this->table_mail_options ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		
		$this->signature = stripslashes($row->signature);
		$this->linebreak = stripslashes($row->linebreak);
		$this->incoming_type = $row->incoming_type;
		
		if(!strlen(ilObjUser::_lookupEmail($this->user_id)))
		{
			$this->incoming_type = $this->LOCAL;
		}

		return true;
	}

	/**
	* update user options
	* @param string Signature
	* @param int linebreak
	* @return	boolean
	*/
	function updateOptions($a_signature, $a_linebreak,$a_incoming_type)
	{
		global $ilDB;
		
		$query = "UPDATE $this->table_mail_options ".
			"SET signature = ".$ilDB->quote($a_signature).",".
			"linebreak = ".$ilDB->quote($a_linebreak).", ".
			"incoming_type = ".$ilDB->quote($a_incoming_type)." ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ";

		$res = $this->ilias->db->query($query);

		$this->signature = $a_signature;
		$this->linebreak = $a_linebreak;
		$this->incoming_type = $a_incoming_type;

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

	function getIncomingType()
	{
		return $this->incoming_type;
	}
	
} // END class.ilFormatMail
?>
