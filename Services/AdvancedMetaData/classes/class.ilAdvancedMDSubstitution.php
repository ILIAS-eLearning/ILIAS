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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData 
*/
class ilAdvancedMDSubstitution
{
	private static $instances = null; 

	protected $db;
	
	protected $type;
	protected $substitution;
	
	
	/*
	 * Singleton class
	 * Use _getInstance
	 * @access private
	 * @param
	 */
	private function __construct($a_type)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->type = $a_type;
		
		$this->read();	
	}
	
	/**
	 * Singleton: use this method to get an instance
	 * 
	 * @param string ilias object type (3 or 4 characters)
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstanceByObjectType($a_type)
	{
		if(isset(self::$instances[$a_type]))
		{
			return self::$instances[$a_type];
		}
		return self::$instances[$a_type] = new ilAdvancedMDSubstitution($a_type);
	}
	
	/**
	 * get substitution string
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getSubstitutionString()
	{
	 	return $this->substitution;
	}
	
	/**
	 * Set substitution
	 *
	 * @access public
	 * @param string substitution
	 * 
	 */
	public function setSubstitutionString($a_substitution)
	{
	 	$this->substitution = $a_substitution;
	}
	
	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	$query = "REPLACE INTO adv_md_substitutions ".
	 		"SET obj_type = ".$this->db->quote($this->type).", ".
	 		"substitution = ".$this->db->quote($this->getSubstitutionString())." ";
	 	$res = $this->db->query($query);
	}
	
	/**
	 * Read db entries
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	$query = "SELECT * FROM adv_md_substitutions ".
	 		"WHERE obj_type = ".$this->db->quote($this->type)." ";
			
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->substitution = $row->substitution;
	 	}
	}
}
?>