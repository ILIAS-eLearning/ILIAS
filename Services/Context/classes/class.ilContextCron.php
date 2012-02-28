<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Context/classes/class.ilContextBase.php";

/** 
 * Service context for cron
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesContext
 */
class ilContextCron extends ilContextBase
{			
	/**
	 * Are redirects supported?
	 * 
	 * @return bool 
	 */
	public static function supportsRedirects()
	{
		return false;
	}
	
	/**
	 * Based on user authentication?
	 *  
	 * @return bool
	 */
	public static function hasUser()
	{
		return true;
	}
}

?>