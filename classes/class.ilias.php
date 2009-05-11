<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* ILIAS base class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @todo review the concept how the object type definition is loaded. We need a concept to
* edit the definitions via webfrontend in the admin console.
*/
class ILIAS
{
	/**
	* ini file
	* @var string
	*/
 	var $INI_FILE;

	/**
	* database connector
	* @var string
	* @access public
	*/
	var $dsn = "";

	/**
	* database handle
	* @var object database
	* @access private
	*/
	var $db;

	/**
	* template path
	* @var string
	* @access private
	*/
	var $tplPath = "./templates/";

	/**
	* user account
	* @var object user
	* @access public
	*/
	var $account;

	/**
	* auth parameters
	* @var array
	* @access private
	*/
	var $auth_params = array();

	/**
	* auth handler
	* @var object auth
	* @access public
	*/
	var $auth;

 	/**
	* system settings
	* @var array
	* @access public
	*/
	var $ini = array();

	/**
	* Error Handling
	* @var object Error
	* @access public
	*/
	var $error_obj;

	/**
	* object factory
	*
	* @var object factory
	* @access public
	*/
	var $obj_factory;

	/**
	* styles
	*
	* @var	array	list of stylesheets
	* @access	public
	*/
	var $styles;

	/**
	* skins (template sets)
	*
	* @var	array	list of skins
	* @access	public
	*/
	var $skins;
	
	/**
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function ILIAS($a_client_id = 0)
	{
		global $ilErr, $ilDB, $ilIliasIniFile, $ilClientIniFile, $ilAuth;

		$this->ini_ilias =& $ilIliasIniFile;
		$this->client_id = CLIENT_ID;
		$this->ini =& $ilClientIniFile;
		$this->db =& $ilDB;
		$this->error_obj =& $ilErr;
		$this->auth =& $ilAuth;

		// create instance of object factory
		include_once("./classes/class.ilObjectFactory.php");
		$this->obj_factory =& new ilObjectFactory();
	}

	/**
	* Destructor
	* @access	private
	* @return	boolean
	*/
	function _ILIAS()
	{
		if ($this->ini->readVariable("db", "type") != "")
		{
			$this->db->disconnect();
		}
		
		return true;
	}
	
	
	/**
	* set authentication error (should be set after Auth->start() via
	* $ilias->setAuthError($ilErr->getLastError());
	*
	* @param	object		$a_error_obj	pear error object
	*/
	function setAuthError($a_error_obj)
	{
		$this->auth_error =& $a_error_obj;
	}
	
	/**
	* get (last) authentication error object
	*/
	function &getAuthError()
	{
		return $this->auth_error;
	}

	/**
	* read one value from settingstable
	* @access	public
	* @param	string	keyword
	* @param	string	default_value This value is returned, when no setting has
    *								  been found for the keyword.
	* @return	string	value
	*/
	function getSetting($a_keyword, $a_default_value = false)
	{
		global $ilSetting;
		
		return $ilSetting->get($a_keyword, $a_default_value);
	}
	
	/**
	* delete one value from settingstable
	* @access	public
	* @param	string	keyword
	* @return	string	value
	*/
	function deleteSetting($a_keyword)
	{
		global $ilSetting;
		
		return $ilSetting->delete($a_keyword);
	}


	/**
	* read all values from settingstable
	* @access	public
	* @return	array	keyword/value pairs
	*/
	function getAllSettings()
	{
		global $ilSetting;
		
		return $ilSetting->getAll();
	}

	/**
	* write one value to db-table settings
	* @access	public
	* @param	string		keyword
	* @param	string		value
	* @return	boolean		true on success
	* 
	* TODO: change to replace-statement
	*/
	function setSetting($a_key, $a_val)
	{
		global $ilSetting;
		
		return $ilSetting->set($a_key, $a_val);
	}


	/**
	* get first available stylesheet from skindirectory
	* @param	string
	* @return	string	style name
	* @access	public
	*/
	function getFirstStyle($a_skin)
	{
		if (!is_array($this->styles))
		{
			$this->getStyles($a_skin);
		}

		return $this->styles[0]["name"];
	}
	
	/**
	* check if a template name exists on the server
	* @param	string	template name
	* @return	boolean	true if file exists
	* @access	public
	*/
	function checkTemplate($a_name)
	{
		return file_exists($this->tplPath.$a_name);
	}

	/**
	* get current user account
	*/
	function &getCurrentUser()
	{
		return $this->account;
	}
	
	function getClientId()
	{
		return $this->client_id;
	}
	
	/**
	* wrapper for downward compability
	*/
	function raiseError($a_msg,$a_err_obj)
	{
		global $ilErr;

		$ilErr->raiseError($a_msg,$a_err_obj);
	}



} // END class.ilias
?>
