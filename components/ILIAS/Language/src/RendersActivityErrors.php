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

use ILIAS\Language\Activities\InvalidLanguageFilesException;

/**
 * Shared activityErrorMessage() for the GUI classes calling an Activity's maybePerformAs().
 * The using class must assign `$this->activity_error_logger` once in its constructor and provide `$this->lng`.
 */
trait RendersActivityErrors
{
    private readonly \ilLogger $activity_error_logger;

    /**
     * Turns a maybePerformAs() error into text for setOnScreenMessage() (which renders HTML unescaped):
     *  - code 403: the localized "no permission" message
     *  - invalid language files: the localized message naming the languages
     *  - other 4xx: an answer to the call that may embed caller-controlled data, so this is the one place that escapes it
     *  - anything else: may carry raw SQL, so only its cause chain is logged and a generic message shown
     */
    private function activityErrorMessage(\Throwable $error): string
    {
        if ($error->getCode() === 403) {
            return $this->lng->txt('msg_no_perm_write');
        }

        if ($error instanceof InvalidLanguageFilesException) {
            return sprintf(
                $this->lng->txt('lng_invalid_language_files'),
                htmlspecialchars(implode(', ', $error->getLanguageKeys()), ENT_QUOTES | ENT_SUBSTITUTE)
            );
        }

        if ($this->isActivityInputError($error)) {
            // ENT_SUBSTITUTE: invalid UTF-8 would make htmlspecialchars() return an empty string
            return htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE);
        }

        $this->activity_error_logger->error($this->describeThrowableChain($error));

        return $this->lng->txt('action_aborted');
    }

    /** A 4xx other than 403: the call itself was rejected, its message is meant for the caller. */
    private function isActivityInputError(\Throwable $error): bool
    {
        $code = $error->getCode();

        return is_int($code) && $code >= 400 && $code < 500 && $code !== 403;
    }

    /** Includes every getPrevious(), so a wrapper never hides the original. */
    private function describeThrowableChain(\Throwable $error): string
    {
        $descriptions = [];
        for ($current = $error; $current !== null; $current = $current->getPrevious()) {
            $descriptions[] = get_class($current) . ': ' . $current->getMessage() . "\n" . $current->getTraceAsString();
        }

        return implode("\nCaused by:\n", $descriptions);
    }
}
