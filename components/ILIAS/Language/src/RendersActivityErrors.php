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

namespace ILIAS\Language;

use ILIAS\Language\Activities\SafeToDisplayActivityError;

/**
 * Shared activityErrorMessage() for the GUI classes calling an Activity's maybePerformAs().
 * The using class must assign `$this->activity_error_logger` once in its constructor and provide `$this->lng`.
 */
trait RendersActivityErrors
{
    private readonly \ilLogger $activity_error_logger;

    /**
     * Turns a maybePerformAs() error into text for setOnScreenMessage() (which renders HTML unescaped):
     *  - string: an already localized, trusted message of the Activity, returned as-is
     *  - SafeToDisplayActivityError: may embed caller-controlled data, so this is the one place that escapes it
     *  - any other \Throwable: may carry raw SQL, so only its cause chain is logged and a generic message shown
     */
    private function activityErrorMessage(\Throwable|string $error): string
    {
        if (is_string($error)) {
            return $error;
        }

        if ($error instanceof SafeToDisplayActivityError) {
            // ENT_SUBSTITUTE: invalid UTF-8 would make htmlspecialchars() return an empty string
            return htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE);
        }

        $this->activity_error_logger->error($this->describeThrowableChain($error));

        return $this->lng->txt('action_aborted');
    }

    /**
     * Renders $error and every exception it was caused by (getPrevious()), so a wrapper never hides the original.
     */
    private function describeThrowableChain(\Throwable $error): string
    {
        $descriptions = [];
        for ($current = $error; $current !== null; $current = $current->getPrevious()) {
            $descriptions[] = get_class($current) . ': ' . $current->getMessage() . "\n" . $current->getTraceAsString();
        }

        return implode("\nCaused by:\n", $descriptions);
    }
}
