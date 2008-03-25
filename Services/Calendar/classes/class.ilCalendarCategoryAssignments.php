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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar 
*/

class ilCalendarCategoryAssignments
{
	protected $db;
	
	protected $cal_entry_id = 0;
	protected $assignments = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int calendar entry id
	 */
	public function __construct($a_cal_entry_id)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->cal_entry_id = $a_cal_entry_id;
		
		$this->read();
	}
	
	/**
	 * lookup categories
	 *
	 * @access public
	 * @param int cal_id
	 * @return array of categories
	 * @static
	 */
	public static function _lookupCategories($a_cal_id)
	{
		global $ilDB;
		
		$query = "SELECT cat_id FROM cal_category_assignments ".
			"WHERE cal_id = ".$ilDB->quote($a_cal_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cat_ids[] = $row->cat_id;
		}
		return $cat_ids ? $cat_ids : array();
	}
	
	/**
	 * get first assignment
	 *
	 * @access public
	 * @return
	 */
	public function getFirstAssignment()
	{
		return isset($this->assignments[0]) ? $this->assignments[0] : false;
	}
	
	/**
	 * get assignments
	 *
	 * @access public
	 * @return
	 */
	public function getAssignments()
	{
		return $this->assignments ? $this->assignments : array();
	}
	
	/**
	 * add assignment
	 *
	 * @access public
	 * @param int calendar category id
	 * @return
	 */
	public function addAssignment($a_cal_cat_id)
	{
		$query = "INSERT INTO cal_cat_assignments ".
			"SET cal_id ".$this->db->quote($this->cal_id).", ".
			"cat_id = ".$this->db->quote($a_cal_cat_id)." ";
		$this->db->query($query);
		$this->assignments[] = (int) $a_cal_cat_id;
		
		return true;
	}
	
	/**
	 * delete assignment
	 *
	 * @access public
	 * @param int calendar category id
	 * @return
	 */
	public function deleteAssignment($a_cat_id)
	{
		$query = "DELETE FROM cal_cat_assignments ".
			"WHERE cal_id = ".$this->db->quote($this->cal_id).", ".
			"AND cat_id = ".$this->db->quote($a_cat_id)." ";
		$this->db->query($query);
		
		if(($key = array_search($a_cat_id,$this->assignments)) !== false)
		{
			unset($this->assignments[$key]);
		}
		return true;
	}

	
	/**
	 * read assignments
	 *
	 * @access private
	 * @return
	 */
	private function read()
	{
		$query = "SELECT * FROM cal_cat_assignments ".
			"WHERE cal_id = ".$this->db->quote($this->cal_entry_id)." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->assignments[] = $row->cal_cat_id;
		}
	}
}
?>