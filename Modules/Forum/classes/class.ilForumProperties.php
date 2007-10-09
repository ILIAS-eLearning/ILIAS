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
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumProperties
{	
	/**
	 * Object id of current forum
	 * @access	private
	 */
	private $obj_id;
	
	/**
	 * Default view ( 1 => 'order by answers', 2 => 'order by date')
	 * @access	private
	 */
	private $default_view = 1;
	
	/**
	 * Defines if a forum is anonymized or not
	 * @access	private
	 */
	private $anonymized = false;
	
	/**
	 * Defines if a forum can show ranking statistics
	 * @access private 
	 */	
	private $statistics_enabled = false;
	
	/**
	 * Activation of new posts
	 * @access	private
	 */
	private $post_activation_enabled = false;

	/**
	 * DB Object
	 * @access	private
	 */
	private $db = null;	
	
	static private $instances = array();	
	
	protected function __construct($a_obj_id = 0)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->obj_id = $a_obj_id;
		$this->read();
	}
	
	private function __clone()
	{		
	}
	
	static public function getInstance($a_obj_id = 0)
	{
		if (!self::$instances[$a_obj_id])
		{
	    	self::$instances[$a_obj_id] = new ilForumProperties($a_obj_id);
	    }
	    
	    return self::$instances[$a_obj_id];	    	    
	}
	
	private function read()
	{
		if ($this->obj_id)
		{
			$query = "SELECT * 
					  FROM frm_settings 
					  WHERE 1
					  AND obj_id = " . $this->db->quote($this->obj_id) . " ";
			$row = $this->db->getrow($query);
	
			if (is_object($row))
			{
				$this->default_view = $row->default_view;
				$this->anonymized = $row->anonymized == 1 ? true : false;
				$this->statistics_enabled = $row->statistics_enabled == 1 ? true : false;
				$this->post_activation_enabled = $row->post_activation == 1 ? true : false;
				
				return true;
			}
			
			return false;
		}
		
		return false;
	}
	
	public function insert()
	{
		if ($this->obj_id)
		{
			$query = "INSERT INTO frm_settings "
					."SET "
					."obj_id = " . $this->db->quote($this->obj_id). ", "
					."default_view = " . $this->db->quote($this->default_view). ", "
					."anonymized = " . $this->db->quote($this->anonymized). ", "
					."statistics_enabled = " . $this->db->quote($this->statistics_enabled). ", "
					."post_activation = " . $this->db->quote($this->post_activation_enabled). " ";
			$this->db->query($query);
			
			return true;
		}
		
		return false;
	}
	
	public function update()
	{
		if ($this->obj_id)
		{
			$query = "UPDATE frm_settings "
					."SET "
					."default_view = " . $this->db->quote($this->default_view). ", "
					."anonymized = " . $this->db->quote($this->anonymized). ", "
					."statistics_enabled = " . $this->db->quote($this->statistics_enabled). ", "
					."post_activation = " . $this->db->quote($this->post_activation_enabled). " "
					."WHERE obj_id = ". $this->db->quote($this->obj_id) ." ";
			$this->db->query($query);
			
			return true;
		}
		
		return false;		
	}
	
	public function copy($a_new_obj_id)
	{
		if ($a_new_obj_id)
		{		
			$query = "INSERT INTO frm_settings "
					."SET "
					."obj_id = " . $this->db->quote($a_new_obj_id). ", "
					."default_view = " . $this->db->quote($this->default_view). ", "
					."anonymized = " . $this->db->quote($this->anonymized). ", "
					."statistics_enabled = " . $this->db->quote($this->statistics_enabled). ", "
					."post_activation = " . $this->db->quote($this->post_activation_enabled). " ";
			$this->db->query($query);
			
			return true;
		}
		
		return false;		
	}
	
	public function setDefaultView($a_default_view)
	{
		$this->default_view = $a_default_view;
	}
	public function getDefaultView()
	{
		return $this->default_view;
	}
	public function setStatisticsStatus($a_statistic_status)
	{
		$this->statistics_enabled = $a_statistic_status;
	}
	public function isStatisticEnabled()
	{
		return $this->statistics_enabled;
	}
	public function setAnonymisation($a_anonymized)
	{
		$this->anonymized = $a_anonymized;
	}
	public function isAnonymized()
	{
		return $this->anonymized;
	}		
	static function _isAnonymized($a_obj_id)
	{
		global $ilDB;
		
		$q = "SELECT anonymized FROM frm_settings WHERE ";
		$q .= "obj_id = ".$ilDB->quote($a_obj_id)."";
		return $ilDB->getOne($q);
	}
	
	public function setPostActivation($a_post_activation)
	{
		$this->post_activation_enabled = $a_post_activation;
	}	
	public function isPostActivationEnabled()
	{
		return $this->post_activation_enabled;
	}
	public function setObjId($a_obj_id = 0)
	{
		$this->obj_id = $a_obj_id;
		$this->read();
	}
	public function getObjId()
	{
		return $this->obj_id;
	}	
}
?>
