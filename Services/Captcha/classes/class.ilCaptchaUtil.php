<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Captcha util
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilCaptchaUtil
{
	/**
	 * Check whether captcha support is active
	 *
	 * @param
	 * @return
	 */
	static function isActive()
	{
		global $ilSetting;
		
		if (function_exists("imageftbbox") &&
			$ilSetting->get('activate_captcha_anonym'))
		{
			return true;
		}
		return false;
	}

	/**
	 * Check whether captcha support is active
	 *
	 * @param
	 * @return
	 */
	static function checkFreetype()
	{
		global $ilSetting;
		
		if (function_exists("imageftbbox"))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Check whether captcha support is active
	 *
	 * @param
	 * @return
	 */
	static function getPreconditionsMessage()
	{
		global $lng;
		
		$lng->loadLanguageModule("cptch");
		return "<a target='_blank' href='http://php.net/manual/en/image.installation.php'>".$lng->txt("cptch_freetype_support_needed")."</a>";
		if (function_exists("imageftbbox"))
		{
			return true;
		}
		return false;
	}
}

?>
