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

namespace ILIAS\Scripts\PHPStan;

/**
 * The major version of the ILIAS installation the analysis runs against.
 *
 * Read straight from ilias_version.php, so the rules do not depend on a
 * bootstrapped ILIAS. Rule-violation allowances are granted for one major
 * version; see {@see Attributes\AllowRuleViolation}.
 */
final class IliasVersion
{
    private static ?int $major = null;

    public static function major(): int
    {
        return self::$major ??= self::read();
    }

    private static function read(): int
    {
        $file = dirname(__DIR__, 2) . '/ilias_version.php';
        if (!is_readable($file)) {
            throw new \RuntimeException('Cannot read the ILIAS version from ' . $file . '.');
        }

        if (!preg_match('/ILIAS_VERSION_NUMERIC\s*=\s*[\'"](\d+)/', (string) file_get_contents($file), $match)) {
            throw new \RuntimeException('Cannot find ILIAS_VERSION_NUMERIC in ' . $file . '.');
        }

        return (int) $match[1];
    }
}
