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
	public function __construct($a_id,$a_user_id = null)
	{
	 	global $ilDB, $ilUser;
	 	
	 	$this->user_id = (int)$a_user_id;
	 	$this->id = (string)$a_id;
	 	$this->db = $ilDB;
		
		if(!$this->user_id)
		{
			$this->user_id = $ilUser->getId();
		}
	 	
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
	 * Remove all settings (internally) 
	 */
	public function reset()
	{
		$this->settings = array();
	}
	
	/**
	 * Check if a specific option is enabled
	 *
	 * @param string $a_option
	 * @return bool
	 */
	public function enabled($a_option)
	{
		return (bool)$this->getValue($a_option);	 	
	}		
	
	/**
	 * Get value
	 * 
	 * @param string $a_option
	 * @return mixed
	 */
	public function getValue($a_option)
	{
		if($this->valueExists($a_option))
	 	{
	 		return $this->settings[$a_option];
	 	}		
	}
	
	/**
	 * Set value
	 * 
	 * @param string $a_option
	 * @param mmixed $a_value
	 */
	public function setValue($a_option,$a_value)
	{
		$this->settings[$a_option] = $a_value;
	}
	
	/**
	 * Delete value
	 * 
	 * @param string $a_option 
	 */	
	public function deleteValue($a_option)
	{
		if($this->valueExists($a_option))
	 	{
			unset($this->settings[$a_option]);
		}
	}
	
	/**
	 * Does value exist in settings?
	 * 
	 * @param string  $a_option
	 * @return bool
	 */
	public function valueExists($a_option)
	{		
		return array_key_exists($a_option,(array)$this->settings);
	}

	/**
	 * Store settings in DB
	 */
	public function store()
	{	 	
		$this->delete(false);
	 		
		$query = "INSERT INTO usr_form_settings (user_id,id,settings) ".
			"VALUES( ".
				$this->db->quote($this->user_id,'integer').", ".
				$this->db->quote($this->id,'text').", ".
				$this->db->quote(serialize($this->settings),'text')." ".
			")";
		$this->db->manipulate($query);
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
			" WHERE user_id = ".$this->db->quote($this->user_id,'integer').
			" AND id = ".$this->db->quote($this->id,'text');
	 	$res = $this->db->query($query);
		
		$this->reset();
	 	if($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->settings = unserialize($row->settings);
	 	}
		return true;
	}
		
	/**
	 * Delete user related data
	 * 
	 * @param bool $a_reset
	 */
	public function delete($a_reset = true)
	{	 	
	 	$query = "DELETE FROM usr_form_settings".
			" WHERE user_id = ".$this->db->quote($this->user_id,'integer').
			" AND id = ".$this->db->quote($this->id,'text');
	 	$this->db->manipulate($query);
		
		if($a_reset)
		{
			$this->reset();
		}
	}
	
	/**
	 * Delete all settings for user id 
	 */
	public static function deleteAllForUser($a_user_id)
	{
		$query = "DELETE FROM usr_form_settings".
			" WHERE user_id = ".$this->db->quote($a_user_id,'integer');
		$this->db->manipulate($query);
	}
	
	/**
	 * Import settings from form
	 * 
	 * @param ilPropertyFormGUI $a_form	
	 */
	public function importFromForm(ilPropertyFormGUI $a_form)
	{
		$this->reset();
		
		foreach($a_form->getItems() as $item)
		{
			if(method_exists($item, "getPostVar"))
			{
				$field = $item->getPostVar();		
				
				if(method_exists($item, "getDate"))
				{
					$value = $item->getDate();
					if($value && !$value->isNull())
					{
						$value = $value->get(IL_CAL_DATETIME);
					}
				}
				else if(method_exists($item, "getChecked"))
				{		
					$value = $item->getChecked();
				}
				else if(method_exists($item, "getMulti") && $item->getMulti())
				{		
					$value = $item->getMultiValues();
				}
				else if(method_exists($item, "getValue"))
				{
					$value = $item->getValue();
				}
				
				$this->setValue($field, $value);		
			}
		}		
	}
	
	/**
	 * Export settings from form
	 * 
	 * @param ilPropertyFormGUI $a_form	
	 */
	public function exportToForm(ilPropertyFormGUI $a_form)
	{				
		foreach($a_form->getItems() as $item)
		{
			if(method_exists($item, "getPostVar"))
			{
				$field = $item->getPostVar();	
				
				if($this->valueExists($field))
				{
					$value = $this->getValue($field);

					if(method_exists($item, "setDate"))
					{
						$date = new ilDateTime($value, IL_CAL_DATETIME);
						$item->setDate($date);
					}
					else if(method_exists($item, "setChecked"))
					{						
						$item->setChecked((bool)$value);
					}
					else if(method_exists($item, "setValue"))
					{
						$item->setValue($value);					
					}									
				}
			}
		}						
	}
}

?>