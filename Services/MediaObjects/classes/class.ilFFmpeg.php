<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * FFmpeg wrapper 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services/MediaObjects
 */
class ilFFmpeg
{
	/**
	 * Checks, whether FFmpeg support is enabled (path is set in the setup)
	 *
	 * @param
	 * @return
	 */
	static function enabled()
	{
		if (defined("PATH_TO_FFMPEG") && PATH_TO_FFMPEG != "")
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Get ffmpeg command
	 */
	private static function getCmd()
	{
		return PATH_TO_FFMPEG;
	}

	/**
	 * Execute ffmpeg
	 *
	 * @param
	 * @return
	 */
	function exec($args)
	{
		return ilUtil::execQuoted(self::getCmd(), $args);
	}
	
	/**
	 * Get all supported codecs
	 *
	 * @return
	 */
	static function getSupportedCodecsInfo()
	{
		$codecs = self::exec("-codecs");
		
		return $codecs;
	}

	/**
	 * Get all supported formats
	 *
	 * @return
	 */
	static function getSupportedFormatsInfo()
	{
		$formats = self::exec("-formats");
		
		return $formats;
	}
}

?>
