<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cache/classes/class.ilCache.php");

/**
 * Caches (check) access information on list items.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesObject
 */
class ilListItemAccessCache extends ilCache
{
	static $disabled = false;
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		global $ilSetting;
		parent::__construct("ServicesObject", "CheckAccess", false);
		$this->setExpiresAfter($ilSetting->get("rep_cache") * 60);
		if ((int) $ilSetting->get("rep_cache") == 0)
		{
			self::$disabled = true;
		}
	}
	
	/**
	 * Read an entry
	 */
	function readEntry($a_id)
	{
		if (!self::$disabled)
		{
			return parent::readEntry($a_id);
		}
		return false;
	}
	
	
	/**
	 * Id is user_id:ref_id, we store ref_if additionally
	 */
	function storeEntry($a_id, $a_value, $a_ref_id = 0)
	{
		global $ilSetting;
		if (!self::$disabled)
		{
			parent::storeEntry($a_id, $a_value, $a_ref_id);
		}
	}

	/**
	 * This one can be called, e.g. 
	 */
	function deleteByRefId($a_ref_id)
	{
		parent::deleteByAdditionalKeys($a_ref_id);
	}
}
?>
