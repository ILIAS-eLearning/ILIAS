<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * SecurImage Library Utility functions
 * @author     Alex Killing <alex.killing@gmx.de>
 * @author     Michael Jansen <mjansen@databay.de>
 * @ingroup    ServicesCaptcha
 * @version    $Id$
 */
class ilSecurImageUtil
{
	/**
	 * @var string
	 */
	private static $ver = '3_5_1';

	/**
	 * @return string
	 */
	public static function getDirectory()
	{
		return './Services/Captcha/lib/securimage_' . self::$ver;
	}

	/**
	 * @return string
	 */
	public static function getImageScript()
	{
		return self::getDirectory() . '/il_securimage_show.php';
	}

	/**
	 * @return string
	 */
	public static function getAudioScript()
	{
		return self::getDirectory() . '/securimage_play.swf?audio_file=' . self::getDirectory() . '/il_securimage_play.php&amp;icon_file=' . ilUtil::getImagePath('amarok.png');
	}

	/**
	 *
	 */
	public function includeSecurImage()
	{
		require_once 'Services/Captcha/lib/securimage_' . self::$ver . '/securimage.php';
	}
}