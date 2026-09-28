<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Logging;

enum ILIASLogLevel: int
{
    protected const array STRING_MAP = [
        'DEBUG' => self::DEBUG,
        'INFO' => self::INFO,
        'NOTICE' => self::NOTICE,
        'WARNING' => self::WARNING,
        'ERROR' => self::ERROR,
        'CRITICAL' => self::CRITICAL,
        'ALERT' => self::ALERT,
        'EMERGENCY' => self::EMERGENCY,
        'OFF' => self::OFF,
    ];

    case DEBUG = 100;
    case INFO = 200;
    case NOTICE = 250;
    case WARNING = 300;
    case ERROR = 400;
    case CRITICAL = 500;
    case ALERT = 550;
    case EMERGENCY = 600;

    case OFF = 1000;

    public static function tryFromString(string $value): ?self
    {
        return self::STRING_MAP[$value] ?? null;
    }

    public function toString(): string
    {
        return array_search($this, self::STRING_MAP, true);
    }
}
