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
	/**
	 * Get flash video player directory
	 *
	 * @return
	 */
	static function getFlashVideoPlayerDirectory()
	{
		return "Services/MediaObjects/media_element_2_7_0";
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
