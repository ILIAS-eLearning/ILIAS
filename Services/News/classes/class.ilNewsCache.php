<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cache/classes/class.ilCache.php");

/**
 * News cache
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesNews
 */
class ilNewsCache extends ilCache
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
		
		$news_set = new ilSetting("news");
		$news_set->get("acc_cache_mins");
		
		parent::__construct("ServicesNews", "News", true);
		$this->setExpiresAfter($news_set->get("acc_cache_mins") * 60);
		if ((int) $news_set->get("acc_cache_mins") == 0)
		{
			self::$disabled = true;
		}
	}
	
	/**
	 * Check if cache is disabled
	 * @return 
	 */
	public function isDisabled()
	{
		return self::$disabled or parent::isDisabled();
	}
	
	
	/**
	 * Read an entry
	 */
	function readEntry($a_id)
	{
		if (!$this->isDisabled())
		{
			return parent::readEntry($a_id);
		}
		return false;
	}
	
	
	/**
	 * Id is user_id:ref_id, we store ref_if additionally
	 */
	function storeEntry($a_id, $a_value)
	{
		global $ilSetting;
		if(!$this->isDisabled())
		{
			parent::storeEntry($a_id, $a_value);
		}
	}
}
?>