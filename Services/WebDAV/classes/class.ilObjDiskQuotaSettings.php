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
* Class ilObjDiskQuotaSettings
*
* This class encapsulates accesses to settings which are relevant for the
* disk quota functionality of ILIAS.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
*
* @version $Id: class.ilObjDiskQuotaSettings.php 15697 2008-01-08 20:04:33Z hschottm $
*
* @extends ilObject
* @package WebDAV
*/

include_once "classes/class.ilObject.php";

class ilObjDiskQuotaSettings extends ilObject
{
	/**
	 * Boolean property. Set this to true, to enable Disk Quota support.
	 */
	private $diskQuotaEnabled;

	/** DiskQuota Settings variable */
	private static $dqSettings;

	/**
	* Constructor
	*
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function ilObjDiskQuotaSettings($a_id = 0,$a_call_by_reference = true)
	{
		// NOTE: We share the facs object with ilObjFileAccessSettings!
		$this->type = "facs";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* Sets the diskQuotaEnabled property.
	* 
	* @param	boolean	new value
	* @return	void
	*/
	public function setDiskQuotaEnabled($newValue)
	{
		$this->diskQuotaEnabled = $newValue;
	}
	/**
	* Gets the diskQuotaEnabled property.
	* 
	* @return	boolean	value
	*/
	public function isDiskQuotaEnabled()
	{
		return $this->diskQuotaEnabled;
	}
	/**
	* create
	*
	* note: title, description and type should be set when this function is called
	*
	* @return	integer		object id
	*/
	public function create()
	{
		parent::create();
		$this->write();
	}
	/**
	* update object in db
	*
	* @return	boolean	true on success
	*/
	public function update()
	{
		parent::update();
		$this->write();
	}
	/**
	* write object data into db
	* @param	boolean
	*/
	private function write()
	{
		$settings = new ilSetting('disk_quota');
		$settings->set('enabled', $this->diskQuotaEnabled);
	}
	/**
	* read object data from db into object
	* @param	boolean
	*/
	public function read($a_force_db = false)
	{
		parent::read($a_force_db);

		$settings = new ilSetting('disk_quota');
		$this->diskQuotaEnabled = $settings->get('enabled') == true;
	}

	/** Returns true, if disk quota is active. */
	public static function _isActive()
	{
		if (self::$dqSettings == null)
		{
			self::$dqSettings = new ilSetting('disk_quota');
		}

		return self::$dqSettings->get('enabled') == true;
	}

} // END class.ilObjDiskQuotaSettings
?>
