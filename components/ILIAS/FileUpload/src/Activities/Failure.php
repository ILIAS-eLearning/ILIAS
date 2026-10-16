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

namespace ILIAS\FileUpload\Activities;

/**
 * What the file activities hand to `Data\Factory::error()` when something
 * failed: the result only takes exceptions, and `maybePerformAs()` promises to
 * wrap every failure in a result - an `\Error` from deep inside a legacy service
 * included.
 *
 * Only an answer to the request leaves as it is: an exception with a code of
 * the 4xx range, which is how the activities say what was wrong with a call.
 * Everything else is a defect, and its message may carry what a caller must not
 * see - a query, a path in the storage. It is replaced by a generic one and kept
 * as the previous throwable for whoever debugs it.
 *
 * The code has to be an int: a `PDOException` carries its SQLSTATE as a string,
 * and `'42S22'` compares as being between 400 and 500.
 */
final class Failure
{
    public static function of(\Throwable $e): \Exception
    {
        $code = $e->getCode();

        if ($e instanceof \Exception && is_int($code) && $code >= 400 && $code < 500) {
            return $e;
        }

        return new \RuntimeException('The activity could not be performed.', 500, $e);
    }
}
