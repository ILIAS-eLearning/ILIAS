<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Audio/Video Player Utility 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilPlayerUtil
{
	private static $mejs_ver = "2_9_5";
	
	/**
	 * Get local path of jQuery file
	 */
	function getLocalMediaElementJsPath()
	{
		return "./Services/MediaObjects/media_element_".self::$mejs_ver."/mediaelement-and-player.js";
 	}

	/**
	 * Get local path of jQuery file
	 */
	function getLocalMediaElementCssPath()
	{
		return "./Services/MediaObjects/media_element_".self::$mejs_ver."/mediaelementplayer.min.css";
 	}

 	/**
	 * Init mediaelement.js scripts
	 */
	static function initMediaElementJs($a_tpl = null)
	{
		global $tpl;
		
		if ($a_tpl == null)
		{
			$a_tpl = $tpl;
		}
		
		$a_tpl->addJavaScript(self::getLocalMediaElementJsPath());
		$a_tpl->addCss(self::getLocalMediaElementCssPath());
	}

	/**
	 * Get flash video player directory
	 *
	 * @return
	 */
	static function getFlashVideoPlayerDirectory()
	{
		return "Services/MediaObjects/media_element_2_9_5";
	}
	
	
	/**
	 * Get flash video player file name
	 *
	 * @return
	 */
	static function getFlashVideoPlayerFilename($a_fullpath = false)
	{
		$file = "flashmediaelement.swf";
		if ($a_fullpath)
		{
			return self::getFlashVideoPlayerDirectory()."/".$file;
		}
		return $file;
	}
	
}

?>
