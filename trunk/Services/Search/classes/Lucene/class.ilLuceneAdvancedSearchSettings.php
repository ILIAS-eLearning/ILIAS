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

include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';
include_once './Services/Administration/classes/class.ilSetting.php';

/** 
* En/disable single lom/advanced meta data fields
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchSettings
{
	private static $instance = null;
	private $fields = array();

	protected $storage = null;

	/**
	 * Constructor
	 */
	private function __construct()
	{
		global $ilSetting;
		
		$this->storage = new ilSetting('lucene_adv_search');
		$this->read();		
	}
	
	public static function getInstance()
	{
		if(isset(self::$instance) and self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new ilLuceneAdvancedSearchSettings();
	}
	
	/**
	 * check if field is active
	 */
	public function isActive($a_field)
	{
		return $this->fields[$a_field] ? $this->fields[$a_field] : false;
	}
	
	/**
	 * set field active status
	 * @param bool	active 
	 */
	public function setActive($a_field,$a_status)
	{
		$this->fields[$a_field] = (bool) $a_status;
	}
	
	public function save()
	{
		foreach($this->fields as $name => $status)
		{
			$this->storage->set($name,$status ? "1" : "0");
		}
		return true;
	}
	
	private function read()
	{
		foreach(ilLuceneAdvancedSearchFields::getFields() as $name => $translation)
		{
			$this->fields[$name] = (bool) $this->storage->get($name,true);
		}
	}
}
?>
