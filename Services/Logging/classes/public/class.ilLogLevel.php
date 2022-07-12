<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Logging factory
 *
 * This class supplies an implementation for the locator.
 * The locator will send its output to ist own frame, enabling more flexibility in
 * the design of the desktop.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLogLevel
{
    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 550;
    public const EMERGENCY = 600;
    
    public const OFF = 1000;

    
    
    public static function getLevels() : array
    {
        return array(
            self::DEBUG,
            self::INFO,
            self::NOTICE,
            self::WARNING,
            self::ERROR,
            self::CRITICAL,
            self::ALERT,
            self::EMERGENCY,
            self::OFF
        );
    }


    public static function getLevelOptions() : array
    {
        global $DIC;

        $lng = $DIC->language();
        return array(
            self::DEBUG => $lng->txt('log_level_debug'),
            self::INFO => $lng->txt('log_level_info'),
            self::NOTICE => $lng->txt('log_level_notice'),
            self::WARNING => $lng->txt('log_level_warning'),
            self::ERROR => $lng->txt('log_level_error'),
            self::CRITICAL => $lng->txt('log_level_critical'),
            self::ALERT => $lng->txt('log_level_alert'),
            self::EMERGENCY => $lng->txt('log_level_emergency'),
            self::OFF => $lng->txt('log_level_off')
        );
    }
}
