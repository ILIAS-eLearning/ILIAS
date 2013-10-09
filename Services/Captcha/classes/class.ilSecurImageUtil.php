<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * SecurImage Library Utility functions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup	ServicesCaptcha
 * @version $Id$
 */
class ilSecurImageUtil
{
	private static $ver = "3_0_1";
	
	/**
	 * Get directory
	 *
	 * @param
	 * @return
	 */
	static function getDirectory()
	{
		return "./Services/Captcha/lib/securimage_".self::$ver;
	}
	
	
	/**
	 * Get path of image creation script
	 */
	static function getImageScript()
	{
		return self::getDirectory()."/il_securimage_show.php";
	}

	/**
	 * @return string
	 */
	public static function getAudioScript()
	{
		return self::getDirectory() . '/securimage_play.swf?audio_file=' . self::getDirectory(). '/il_securimage_play.php';
	}
	
	/**
	 * Inlcude securimage script
	 */
	function includeSecurImage()
	{
		include_once("./Services/Captcha/lib/securimage_".self::$ver."/securimage.php");
	}
	
}
?>