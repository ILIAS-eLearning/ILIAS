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
* Class ilObjiLincUser
* iLinc related user settings
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
*/

class ilObjiLincUser
{
	/**
	* Constructor
	* @access	public
	* @param	object	ilias user 
	*/
	function ilObjiLincUser(&$a_user_obj,$a_from_ilinc = 'false')
	{
		global $ilias,$lng;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->user =& $a_user_obj;
		
		$this->__init($a_from_ilinc);
	}
	
	function __init(&$a_from_ilinc)
	{
		global $ilErr, $ilDB;
		
		$r = $ilDB->queryf('
			SELECT ilinc_id, ilinc_login, ilinc_passwd FROM usr_data
			WHERE usr_data.usr_id = %s',
			array('integer'),
			array($this->user->getId()));
		
		if ($ilDB->numRows($r) > 0)
		{
			$data = $ilDB->fetchAssoc($r);
			
			$this->id = $data['ilinc_id'];
			$this->login = $data['ilinc_login'];
			$this->passwd = $data['ilinc_passwd'];
		}
		else
		{
			$ilErr->raiseError("<b>Error: There is no dataset with id ".
							   $this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__.
							   "<br />Line: ".__LINE__, $ilErr->FATAL);
		}
	}
	
	/**
	* updates ilinc data of a record "user" and write it into ILIAS database
	* @access	public
	*/
	function update()
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('
			UPDATE usr_data 
			SET last_update = %s,
          		ilinc_id = %s,
            	ilinc_login = %s,
            	ilinc_passwd = %s  
            WHERE usr_id = 	%s',
			array('timestamp', 'integer', 'text', 'text', 'integer'),
			array(date('Y-m-d H:i:s', time()), $this->id, $this->login, $this->passwd, $this->user->getId()));
		
		return true;
	}
	
	function syncILIAS2iLinc()
	{
		// for future use
	}
	
	function synciLinc2ILIAS()
	{
		// for future use
	}
	
	function getErrorMsg()
	{
		$err_msg = $this->error_msg;
		$this->error_msg = "";

		return $err_msg;
	}
	
	/**
	 * creates login and password for ilinc
	 * login format is: <first 3 letter of ilias login> _ <user_id> _ <inst_id> _ <timestamp>
	 * some characters are not allowed in login in ilinc. These chars will be converted to <underscore>
	 * passwd format is a random md5 hash
	 * 
	 */
	function __createLoginData($a_user_id,$a_user_login,$a_inst_id)
	{
		if (!$a_inst_id)
		{
			$a_inst_id = "0";
		}
		
		$chars = preg_split('//', substr($a_user_login,0,3), -1, PREG_SPLIT_NO_EMPTY);
		//$chars = str_split(substr($a_user_login,0,3)); // PHP5 only
	
		// convert non-allowed chars in login to <underscore>
		// not allowed: ~!@#$%^&*()`-=+[]{};:'\|/?<>,
		$result = preg_replace('@[^a-zA-Z0-9_]@','_',$chars);

		$data["login"] = $result."_".$a_user_id."_".$a_inst_id."_".time();
		$data["passwd"] = md5(microtime().$a_user_login.rand(10000, 32000));

		$this->id = '';
		$this->login = $data['login'];
		$this->passwd = $data['passwd'];
		
		return $data;
	}
	
	// create user account on iLinc server
	function add()
	{
		include_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');

		$this->ilincAPI = new ilnetucateXMLAPI();

		// create login and passwd for iLinc account
		$login_data = $this->__createLoginData($this->user->getId(),$this->user->getLogin(),$this->ilias->getSetting($inst_id));
		
		//$this->ilincAPI->addUser($login_data,$this->user);
		$this->ilincAPI->addUser($this);
		$response = $this->ilincAPI->sendRequest();

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_add_user";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		$this->id = $response->getFirstID();
		$this->login = $login_data["login"];
		$this->passwd = $login_data["passwd"];

		$this->update();
		
		return true;
	}
	
	// edit user account on iLinc server
	function edit()
	{
		include_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');

		$this->ilincAPI = new ilnetucateXMLAPI();

		//$this->ilincAPI->addUser($login_data,$this->user);
		$this->ilincAPI->editUser($this);
		$response = $this->ilincAPI->sendRequest();

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_edit_user";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * find user account on iLinc server
	 * Returns one user recordset, if an userid is given
	 * or returns a list of all the users that match a specified keyword,
	 * if a keyword in loginname or fullname is given in the order userid, loginname, fullname.
	 * Returns the recordsets of all users, if all attribut values are empty
	 * 
	 * @access	public
	 * @param	integer	ilinc_user_id
	 * @param	string	ilinc_login
	 * @param	string	ilinc_fullname
	 * @return	boolean/array	false on error; array of found user record(s)
	 */
	function find($a_id = '',$a_login = '', $a_fullname = '')
	{
		include_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');

		$this->ilincAPI = new ilnetucateXMLAPI();

		$this->ilincAPI->findUser($a_id,$a_login,$a_fullname);
		$response = $this->ilincAPI->sendRequest();

		if ($response->isError())
		{
			if (!$response->getErrorMsg())
			{
				$this->error_msg = "err_find_user";
			}
			else
			{
				$this->error_msg = $response->getErrorMsg();
			}
			
			return false;
		}
		
		return $response->data;
	}
	
	function setVar($a_varname, $a_value)
	{
		$this->$a_varname = $a_value;
	}
} // END class.ilObjiLincUser
?>