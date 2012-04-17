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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup Services/User
*/
class ilUserFormSettings
{
	protected $db;
	protected $user_id;
	protected $id;
	protected $settings = array();
	
	/**
	 * Constructor
	 *
	 * @param int $a_user_id
	 * @param int $a_id
	 */
	public function __construct($a_user_id,$a_id)
	{
	 	global $ilDB;
	 	
	 	$this->user_id = (int)$a_user_id;
	 	$this->id = (string)$a_id;
	 	$this->db = $ilDB;
	 	
	 	$this->read();
	}
	
	/**
	 * Set Settings
	 *
	 * @param array Array of Settings
	 */
	public function set($a_data)
	{
	 	$this->settings = $a_data;
	}
	
	/**
	 * Check if a specific option is enabled
	 *
	 * @param string option
	 * @return bool
	 */
	public function enabled($a_option)
	{
	 	if(array_key_exists($a_option,(array) $this->settings) && $this->settings[$a_option])
	 	{
	 		return true;
	 	}
	 	return false;
	}		
	
	/**
	 * Store settings in DB
	 */
	public function store()
	{	 	
		$this->delete();
	 		
		$query = "INSERT INTO usr_form_settings (user_id,id,settings) ".
			"VALUES( ".
				$this->db->quote($this->user_id ,'integer').", ".
				$this->db->quote($this->id ,'text').", ".
				$this->db->quote(serialize($this->settings) ,'text')." ".
			")";
		$ilDB->manipulate($query);
	}
	
	/**
	 * Read store settings
	 *
	 * @access private
	 * @param
	 * 
	 */
	protected function read()
	{
	 	$query = "SELECT * FROM usr_form_settings".
			" WHERE user_id = ".$this->db->quote($this->user_id ,'integer').
			" AND id = ".$this->db->quote($this->id,'text');
	 	$res = $this->db->query($query);
		
		$this->settings = array();
	 	if($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->settings = unserialize($row->settings);
	 	}
		return true;
	}
		
	/**
	 * Delete user related data
	 */
	public function delete()
	{	 	
	 	$query = "DELETE FROM usr_form_settings".
			" WHERE user_id = ".$this->db->quote($this->user_id ,'integer').
			" AND id = ".$this->db->quote($this->id,'text');
	 	$res = $ilDB->manipulate($query);
	}
}

?>