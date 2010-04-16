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
	private $anonymized = 0; //false;
	
	/**
	 * Defines if a forum can show ranking statistics
	 * @access private 
	 */	
	private $statistics_enabled = 0; //false;
	
	/**
	 * Activation of new posts
	 * @access	private
	 */
	private $post_activation_enabled = 0; //false; 

	/**
	 * Activation of (CRS/GRP) forum notification by mod/admin
	 * @access	private
	 */
	private $admin_force_noti = false;
	/**
	 * Activation of allowing members to deactivate (CRS/GRP)forum notification
	 * @access	private
	 */
	private $user_toggle_noti = false;

	/**
	 *
	 * Allow ratings in forum threads
	 *
	 * @access	private
	 * @type	boolean
	 * @var		boolean
	 */
	private $thread_ratings_allowed = false;

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
			$res = $this->db->queryf('
				SELECT * FROM frm_settings
				WHERE obj_id = %s',
				array('integer'), array($this->obj_id));
			
			$row = $this->db->fetchObject($res);
			
			if (is_object($row))
			{
				$this->default_view = $row->default_view;
				$this->anonymized = $row->anonymized ;// == 1 ? true : false;
				$this->statistics_enabled = $row->statistics_enabled ;// == 1 ? true : false;
				$this->post_activation_enabled = $row->post_activation ;// == 1 ? true : false;
				$this->admin_force_noti = $row->admin_force_noti == 1 ? true : false;
				$this->user_toggle_noti = $row->user_toggle_noti == 1 ? true : false;
				
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
			$statement = $this->db->manipulateF('INSERT INTO frm_settings 
				(	obj_id,
					default_view,
					anonymized,
					statistics_enabled,
					post_activation,
					admin_force_noti,
					user_toggle_noti
				)
				VALUES( %s, %s, %s, %s, %s, %s, %s)',
			array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
			array($this->obj_id, $this->default_view, $this->anonymized, $this->statistics_enabled, $this->post_activation_enabled, $this->admin_force_noti, $this->user_toggle_noti));
			
			return true;
		}
		
		return false;
	}
	
	public function update()
	{
		if ($this->obj_id)
		{
			$statement = $this->db->manipulateF('UPDATE frm_settings 
				SET default_view = %s, 
					anonymized = %s, 
					statistics_enabled = %s, 
					post_activation  = %s,
					admin_force_noti = %s,
					user_toggle_noti = %s
				WHERE obj_id = %s', 
				array ('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
				array($this->default_view, $this->anonymized, $this->statistics_enabled, $this->post_activation_enabled, $this->admin_force_noti, $this->user_toggle_noti, $this->obj_id));
			
			return true;
		}		
		return false;		
	}
	
	public function copy($a_new_obj_id)
	{
		if ($a_new_obj_id)
		{		
			$statement = $this->db->manipulateF('INSERT INTO frm_settings
				(	obj_id,
					default_view,
					anonymized,
					statistics_enabled,
					post_activation,
					admin_force_noti,
					user_toggle_noti
				)
				VALUES( %s, %s, %s, %s, %s, %s, %s)',
				array ('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
				array($a_new_obj_id, $this->default_view, $this->anonymized, $this->statistics_enabled, $this->post_activation_enabled, $this->admin_force_noti, $this->user_toggle_noti));
			
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
		
		$result = $ilDB->queryf("SELECT anonymized FROM frm_settings WHERE obj_id = %s",
		     	 	array('integer'),array($a_obj_id));

		while($record = $ilDB->fetchAssoc($result))
		{
			return $record['anonymized'];
		}
		
		return 0;
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

	public function setAdminForceNoti($a_admin_force)
	{
		$this->admin_force_noti = $a_admin_force;
	}

	public function isAdminForceNoti()
	{
		return $this->admin_force_noti;
	}

	public function setUserToggleNoti($a_user_toggle)
	{
		$this->user_toggle_noti = $a_user_toggle;
	}

	public function isUserToggleNoti()
	{
		return $this->user_toggle_noti;
	}

	static function _isAdminForceNoti($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT admin_force_noti FROM frm_settings WHERE obj_id = %s",
		     	 	array('integer'),
		     	 	array($a_obj_id));
		while($record = $ilDB->fetchAssoc($res))
		{
			return $record['admin_force_noti'];
		}

		return 0;
	}

	static function _isUserToggleNoti($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT user_toggle_noti FROM frm_settings WHERE obj_id = %s",
		     	 	array('integer'),
		     	 	array($a_obj_id));
		while($record = $ilDB->fetchAssoc($res))
		{
			return $record['user_toggle_noti'];
		}
		return 0;
	}

	public function isThreadRatingAllowed($a_allow_rating = null)
	{
		if(null === $a_allow_rating)
		{
			return $this->thread_ratings_allowed;
		}

		$this->thread_ratings_allowed = $a_allow_rating;

		return $this;
	}
}
?>
