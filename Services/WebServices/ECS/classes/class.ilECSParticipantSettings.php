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
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSettings
{
	private static $instance = null;
	protected $storage = null;
	
	protected $enabled = array();
	protected $all_enabled = array();
	
	
	
	/**
	 * Constructor (Singleton)
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
	 	$this->initStorage();
	 	$this->read();
	}
	
	/**
	 * get instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilECSParticipantSettings();
	}
	
	/**
	 * get number of participants that are enabled
	 *
	 * @access public
	 * 
	 */
	public function getEnabledParticipants()
	{
	 	return $this->enabled ? $this->enabled : array();
	}
	
	/**
	 * is partivcipant enabled
	 *
	 * @access public
	 * @param int mid
	 * 
	 */
	public function isEnabled($a_mid)
	{
	 	return in_array($a_mid,$this->enabled);
	}
	
	/**
	 * set enabled participants by community
	 *
	 * @access public
	 * @param int community id
	 * @param array participant ids
	 */
	public function setEnabledParticipants($a_parts)
	{
	 	$this->enabled = (array) $a_parts;
	}
	
	/**
	 * save
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		$this->storage->set('enabled',addslashes(serialize($this->enabled)));
	}
	
	/**
	 * Init storage class (ilSetting)
	 * @access private
	 * 
	 */
	private function initStorage()
	{
	 	include_once('./Services/Administration/classes/class.ilSetting.php');
	 	$this->storage = new ilSetting('ecs_participants');
	}
	
	/**
	 * Read settings
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
		$enabled = $this->storage->get('enabled');
		if($enabled)
		{
			$this->enabled = unserialize(stripslashes($enabled));
		}
	}
	
}


?>