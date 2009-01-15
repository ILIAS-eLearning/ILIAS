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
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

class ilSearchSettings
{
	const LIKE_SEARCH = 0;
	const INDEX_SEARCH = 1;
	const LUCENE_SEARCH = 2;
	
	protected static $instance = null;
	
	var $ilias = null;
	var $max_hits = null;
	var $index = null;

	function ilSearchSettings()
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->__read();
	}
	
	/**
	 * 
	 *
	 * @return
	 * @static
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			return self::$instance = new ilSearchSettings();
		}
		return self::$instance;
	}

	/**
	* Read the ref_id of Search Settings object. normally used for rbacsystem->checkAccess()
	* @return int ref_id
	* @access	public
	*/
	function _getSearchSettingRefId()
	{
		global $ilDB;

		static $seas_ref_id = 0;

		if($seas_ref_id)
		{
			return $seas_ref_id;
		}
		$query = "SELECT object_reference.ref_id as ref_id FROM object_reference,tree,object_data ".
			"WHERE tree.parent = '".SYSTEM_FOLDER_ID."' ".
			"AND object_data.type = 'seas' ".
			"AND object_reference.ref_id = tree.child ".
			"AND object_reference.obj_id = object_data.obj_id";
			
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $seas_ref_id = $row->ref_id;
	}

	function enabledIndex()
	{
		return $this->index ? true : false;
	}
	function enableIndex($a_status)
	{
		$this->index = $a_status;
	}
	function enabledLucene()
	{
		return $this->lucene ? true : false;
	}
	function enableLucene($a_status)
	{
		$this->lucene = $a_status ? true : false;
	}

	function getMaxHits()
	{
		return $this->max_hits;
	}
	function setMaxHits($a_max_hits)
	{
		$this->max_hits = $a_max_hits;
	}


	function update()
	{
		// setSetting writes to db
		$this->ilias->setSetting('search_max_hits',$this->getMaxHits());
		$this->ilias->setSetting('search_index',$this->enabledIndex());
		$this->ilias->setSetting('search_lucene',(int) $this->enabledLucene());

		return true;
	}

	// PRIVATE
	function __read()
	{
		$this->setMaxHits($this->ilias->getSetting('search_max_hits',50));
		$this->enableIndex($this->ilias->getSetting('search_index',0));
		$this->enableLucene($this->ilias->getSetting('search_lucene',0));
	}
	// BEGIN PATCH Lucene search
}
?>
