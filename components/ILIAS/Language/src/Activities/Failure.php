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

namespace ILIAS\Language\Activities;

/**
 * Error of maybePerformAs(): 4xx exceptions answer the call and pass unchanged, anything else is
 * a defect whose message may leak internals, so it is masked and kept as previous.
 */
final class Failure
{
    public static function of(\Throwable $e): \Exception
    {
        // is_int(): a PDOException carries its SQLSTATE as string, and '42S02' compares as 4xx
        $code = $e->getCode();
        if ($e instanceof \Exception && is_int($code) && $code >= 400 && $code < 500) {
            return $e;
        }

        return new \RuntimeException('The activity could not be performed.', 500, $e);
    }
}
