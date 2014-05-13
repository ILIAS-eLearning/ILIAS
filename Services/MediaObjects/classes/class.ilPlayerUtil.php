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
	private static $mejs_ver = "2_14_2";
	
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
		
		foreach (self::getJsFilePaths() as $js_path)
		{
			$a_tpl->addJavaScript($js_path);
		}
		foreach (self::getCssFilePaths() as $css_path)
		{
			$a_tpl->addCss($css_path);
		}
	}
	
	/**
	 * Get css file paths
	 *
	 * @param
	 * @return
	 */
	static function getCssFilePaths()
	{
		return array(self::getLocalMediaElementCssPath());
	}
	
	/**
	 * Get js file paths
	 *
	 * @param
	 * @return
	 */
	static function getJsFilePaths()
	{
		return array(self::getLocalMediaElementJsPath());
	}
	

	/**
	 * Get flash video player directory
	 *
	 * @return
	 */
	static function getFlashVideoPlayerDirectory()
	{
		return "Services/MediaObjects/media_element_2_14_2";
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
	
	/**
	 * Copy css files to target dir
	 *
	 * @param
	 * @return
	 */
	function copyPlayerFilesToTargetDirectory($a_target_dir)
	{
		ilUtil::rCopy("./Services/MediaObjects/media_element_".self::$mejs_ver,
			$a_target_dir);
	}
	
}

?>
