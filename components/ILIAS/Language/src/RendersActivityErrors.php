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
 * Shared implementation of activityErrorMessage(), used by every GUI class
 * in this component that calls an Activity's maybePerformAs() and needs to
 * turn its possible Result\Error into text safe to show via
 * setOnScreenMessage() - see ilObjLanguageFolderGUI/ilObjLanguageExtGUI,
 * which used to duplicate this method byte-for-byte.
 *
 * Requires the using class to:
 *  - assign `$this->activity_error_logger` exactly once, in its own
 *    constructor - the same "resolved once in the constructor, never
 *    reached into via global $DIC from an action method" idiom already
 *    used for every Activity these GUI classes inject (see e.g.
 *    ilObjLanguageFolderGUI::__construct()); and
 *  - provide `$this->lng` (both ilObjLanguageFolderGUI/ilObjLanguageExtGUI
 *    already do, inherited from ilObjectGUI).
 */
trait RendersActivityErrors
{
    private readonly \ilLogger $activity_error_logger;

    /**
     * Turns a maybePerformAs() Result\Error's error() into text safe to show
     * to the user via setOnScreenMessage() (which renders it as HTML,
     * unescaped - see every GUI caller's own literal "<br/>" concatenations
     * alongside $this->lng->txt() calls).
     *  - A plain string (e.g. `msg_no_perm_write`) is already a localized,
     *    trusted, HTML-safe message produced by the Activity itself (via
     *    $this->lng->txt(), the same source every GUI caller's own literal
     *    markup already trusts) and is returned as-is, unescaped.
     *  - A \Throwable implementing SafeToDisplayActivityError (e.g.
     *    InvalidInputException, AmbiguousLanguageTitleException - both marker
     *    implementations with no logic of their own) is a genuine, actionable
     *    problem already reduced to a concrete, non-sensitive message - but,
     *    unlike the plain-string case above, its text may embed raw,
     *    caller-controlled data (a language key, an unknown form field
     *    name, ...): the Activity/domain layer deliberately returns this
     *    unescaped (see e.g. UninstallLanguage::perform()), so THIS is the
     *    one place that escapes it for safe HTML display, rather than each
     *    Activity escaping it itself.
     *  - Any OTHER \Throwable is an unexpected failure (e.g. a database
     *    error) whose message is hardcoded English, not meant for end
     *    users, and in the worst case (\ilDatabaseException) may even
     *    carry raw SQL - so it is never shown directly: its full cause
     *    chain (class, message and stack trace of the exception itself,
     *    plus of every getPrevious() wrapped underneath it) is logged via
     *    this component's established "lang" logger channel, and a
     *    generic, localized message (again already trusted, see above) is
     *    shown instead.
     */
    private function activityErrorMessage(\Throwable|string $error): string
    {
        if (is_string($error)) {
            return $error;
        }

        if ($error instanceof SafeToDisplayActivityError) {
            // ENT_SUBSTITUTE: without it, invalid UTF-8 in getMessage() (e.g.
            // caller-controlled data embedded by the domain layer, see the
            // docblock above) makes htmlspecialchars() return an empty
            // string instead of the message - the user would then see a
            // blank error box with no indication anything went wrong, rather
            // than the actual (partially substituted) message.
            return htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE);
        }

        $this->activity_error_logger->error($this->describeThrowableChain($error));

        return $this->lng->txt('action_aborted');
    }

    /**
     * Renders $error and, recursively, every exception it was caused by
     * (getPrevious()) - so a wrapper exception (e.g. the
     * \RuntimeException/InvalidInputException that GrindsFormInput::grind()
     * wraps a lower-level \Throwable in) never hides the original failure's
     * own class, message and trace from the log.
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
