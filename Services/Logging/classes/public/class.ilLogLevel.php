<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Logging factory
 *
 * This class supplies an implementation for the locator.
 * The locator will send its output to ist own frame, enabling more flexibility in
 * the design of the desktop.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilLogLevel
{
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;
    
    const OFF = 1000;

    
    
    public static function getLevels()
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


    /**
     * Get log level options
     * @return type
     */
    public static function getLevelOptions()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
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
