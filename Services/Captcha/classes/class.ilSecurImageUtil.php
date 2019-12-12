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
        $script = self::getDirectory() . '/securimage_play.swf';
        $script = ilUtil::appendUrlParameterString($script, 'audio_file=' . self::getDirectory() . '/il_securimage_play.php', true);
        $script = ilUtil::appendUrlParameterString($script, 'icon_file=' . ilUtil::getImagePath('icon_audiocaptcha-19.png'), true);
        return $script;
    }

    /**
     *
     */
    public static function includeSecurImage()
    {
        require_once 'Services/Captcha/lib/securimage_' . self::$ver . '/securimage.php';
    }
}
