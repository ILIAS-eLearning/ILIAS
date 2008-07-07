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
* Stores calendar categories
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar 
*/

class ilCalendarCategory
{
	const DEFAULT_COLOR = '#04427e';
	
	const TYPE_USR = 1;
	const TYPE_OBJ = 2;
	const TYPE_GLOBAL = 3;
	
	protected $cat_id;
	protected $color;
	protected $type = self::TYPE_USR;
	protected $obj_id;
	protected $title;
	
	protected $db;
	
	
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_cat_id = 0)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->cat_id = $a_cat_id;
		
		$this->read();
	}
	
	
	/**
	 * get category id
	 *
	 * @access public
	 * @return int category id
	 */
	public function getCategoryID()
	{
		return $this->cat_id;
	}
	
	/**
	 * set title
	 *
	 * @access public
	 * @param string title
	 * @return
	 */
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	/**
	 * get title
	 *
	 * @access public
	 * @return string title
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	
	/**
	 * set color
	 *
	 * @access public
	 * @param string color
	 */
	public function setColor($a_color)
	{
		$this->color = $a_color;
	}
	
	/**
	 * get color
	 *
	 * @access public
	 * @return
	 */
	public function getColor()
	{
		return $this->color;
	}
	
	/**
	 * set type
	 *
	 * @access public
	 * @param int type 
	 */
	public function setType($a_type)
	{
		$this->type = $a_type;
	}
	
	/**
	 * get type
	 *
	 * @access public
	 * @return
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * set obj id
	 *
	 * @access public
	 * @param int obj_id
	 */
	public function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
	}
	
	/**
	 * get obj_id
	 *
	 * @access public
	 * @return
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	/**
	 * add new category
	 *
	 * @access public
	 * @return
	 */
	public function add()
	{
		$query = "INSERT INTO cal_categories ".
			"SET obj_id = ".$this->db->quote($this->getObjId()).", ".
			"color = ".$this->db->quote($this->getColor()).", ".
			"type = ".$this->db->quote($this->getType()).", ".
			"title = ".$this->db->quote($this->getTitle())." ";
			
		$this->db->query($query);
		$this->cat_id = $this->db->getLastInsertId();
		return $this->cat_id;
	}
	
	/**
	 * update
	 *
	 * @access public
	 * @return
	 */
	public function update()
	{
		$query = "UPDATE cal_categories ".
			"SET obj_id = ".$this->db->quote($this->getObjId()).", ".
			"color = ".$this->db->quote($this->getColor()).", ".
			"type = ".$this->db->quote($this->getType()).", ".
			"title = ".$this->db->quote($this->getTitle())." ".
			"WHERE cat_id = ".$this->db->quote($this->cat_id)." ";
		$this->db->query($query);
		return true;
	}

	/**
	 * delete
	 *
	 * @access public
	 * @return
	 */
	public function delete()
	{
		$query = "DELETE FROM cal_categories ".
			"WHERE cat_id = ".$this->db->quote($this->cat_id)." ";
		$this->db->query($query);

		include_once('./Services/Calendar/classes/class.ilCalendarHidden.php');
		ilCalendarHidden::_deleteCategories($this->cat_id);
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		foreach(ilCalendarCategoryAssignments::_getAssignedAppointments($this->cat_id) as $app_id)
		{
			include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
			ilCalendarEntry::_delete($app_id);
		}
		ilCalendarCategoryAssignments::_deleteByCategoryId($this->cat_id);
	}
	
	/**
	 * validate
	 *
	 * @access public
	 * @return bool
	 */
	public function validate()
	{
		return strlen($this->getTitle()) and strlen($this->getColor()) and $this->getType();
	}
	
	/**
	 * read
	 *
	 * @access protected
	 */
	private function read()
	{
		if(!$this->cat_id)
		{
			return true;
		}
		
		$query = "SELECT * FROM cal_categories ".
			"WHERE cat_id = ".$this->db->quote($this->getCategoryID())." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->cat_id = $row->cat_id;
			$this->obj_id = $row->obj_id;
			$this->type = $row->type;
			$this->color = $row->color;
			$this->title = $row->title;
		}
		if($this->getType() == self::TYPE_OBJ)
		{
			$this->title = ilObject::_lookupTitle($this->getObjId());
		}
	}
}
?>