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

define("LINKS_USER_ID",1);
define("LINKS_SESSION_ID",2);
define("LINKS_LOGIN",3);

// Errors
define("LINKS_ERR_NO_NAME",1);
define("LINKS_ERR_NO_VALUE",2);
define("LINKS_ERR_NO_NAME_VALUE",3);

/**
* Class ilParameterAppender
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @ingroup ModulesWebResource
*/
class ilParameterAppender
{
	var $webr_id = null;
	var $db = null;

	var $err = null;


	/**
	* Constructor
	* @access public
	*/
	function ilParameterAppender($webr_id)
	{
		global $ilDB;

		$this->webr_id = $webr_id;
		$this->db =& $ilDB;
	}

	function getErrorCode()
	{
		return $this->err;
	}

	// SET GET
	function getObjId()
	{
		return $this->webr_id;
	}

	function setName($a_name)
	{
		$this->name = $a_name;
	}
	function getName()
	{
		return $this->name;
	}
	function setValue($a_value)
	{
		$this->value = $a_value;
	}
	function getValue()
	{
		return $this->value;
	}

	function validate()
	{
		if(!strlen($this->getName()) and !$this->getValue())
		{
			$this->err = LINKS_ERR_NO_NAME_VALUE;
			return false;
		}
		if(!strlen($this->getName()))
		{
			$this->err = LINKS_ERR_NO_NAME;
			return false;
		}
		if(!$this->getValue())
		{
			$this->err = LINKS_ERR_NO_VALUE;
			return false;
		}
		return true;
	}

	
	function add($a_link_id)
	{
		global $ilDB;
		
		if(!$a_link_id)
		{
			return false;
		}
		if(!strlen($this->getName() or !strlen($this->getValue())))
		{
			return false;
		}

		$query = "INSERT INTO webr_params ".
			"SET webr_id = ".$ilDB->quote($this->getObjId()).", ".
			"link_id = ".$ilDB->quote($a_link_id).", ".
			"name = ".$ilDB->quote($this->getName()).", ".
			"value = ".$ilDB->quote($this->getValue());

		$this->db->query($query);

		return $this->db->getLastInsertId();
	}
	
	function delete($a_param_id)
	{
		global $ilDB;
		
		$this->db->query("DELETE FROM webr_params WHERE param_id = ".
			$ilDB->quote((int) $a_param_id)." AND webr_id = ".$ilDB->quote($this->getObjId()));

		return true;
	}
	
	// Static
	function _isEnabled()
	{
		global $ilias;

		return $ilias->getSetting('links_dynamic',false) ? true : false;
	}

	function &_append(&$a_link_data)
	{
		global $ilUser;

		if(!is_array($a_link_data))
		{
			return false;
		}
		if(count($params = ilParameterAppender::_getParams($a_link_data['link_id'])))
		{
			// Check for prefix
			foreach($params as $param_data)
			{
				if(!strpos($a_link_data['target'],'?'))
				{
					$a_link_data['target'] .= "?";
				}
				else
				{
					$a_link_data['target'] .= "&";
				}
				$a_link_data['target'] .= ($param_data['name']."=");
				switch($param_data['value'])
				{
					case LINKS_LOGIN:
						$a_link_data['target'] .= (urlencode($ilUser->getLogin()));
						break;
						
					case LINKS_SESSION_ID:
						$a_link_data['target'] .= (session_id());
						break;
						
					case LINKS_USER_ID:
						$a_link_data['target'] .= ($ilUser->getId());
						break;
				}
			}
		}
		return $a_link_data;
	}
		
	function _getParams($a_link_id)
	{
		global $ilDB;

		$res = $ilDB->query("SELECT * FROM webr_params WHERE link_id = ".
			$ilDB->quote((int) $a_link_id));
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$params[$row->param_id]['name'] = $row->name;
			$params[$row->param_id]['value'] = $row->value;
		}

		return count($params) ? $params : array();
	}

	function _deleteAll($a_webr_id)
	{
		global $ilDB;

		$ilDB->query("DELETE FROM webr_params WHERE webr_id = ".
			$ilDB->quote((int) $a_webr_id));

		return true;
	}

	function _getOptionSelect()
	{
		global $lng;

		return array(0 => $lng->txt('links_select_one'),
					 LINKS_USER_ID => $lng->txt('links_user_id'),
					 LINKS_LOGIN => $lng->txt('links_user_name'),
					 LINKS_SESSION_ID => $lng->txt('links_session_id'));
	}
}
?>