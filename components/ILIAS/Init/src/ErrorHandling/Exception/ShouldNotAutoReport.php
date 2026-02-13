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

namespace ILIAS\Init\ErrorHandling\Exception;

/**
 * Marker interface for exceptions that must not be automatically
 * reported (e.g., not written to error log files or application logs, not
 * sent as e-mail or to other components/systems).
 *
 * Use this for expected or high-volume exceptions (e.g., routing failures,
 * invalid request parameters) where reporting would cause unnecessary I/O
 * or log noise. The user may still see an error page; only reporting is skipped.
 *
 * Only exception classes (subclasses of Exception or Error) should implement
 * this interface, since only throwables can reach the error handler.
 */
interface ShouldNotAutoReport
{
}
