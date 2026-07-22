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

namespace ILIAS\Init\ErrorHandling\Notification;

use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;

/**
 * Formats the user-facing message that references a reported error incident.
 */
final class ErrorIncidentUserMessage
{
    public function format(
        ErrorIncident $incident,
        ?\ilLanguage $language = null,
        ?Settings $notification_settings = null
    ): string {
        $identifier = $incident->identifier()->value();

        $mail = '';
        if ($notification_settings !== null) {
            $mail = $notification_settings->errorRecipient();
        }

        if ($language !== null) {
            $language->loadLanguageModule('logging');
            $message = \sprintf($language->txt('log_error_message'), $identifier);

            if ($mail !== '') {
                $message .= ' ' . \sprintf(
                    $language->txt('log_error_message_send_mail'),
                    $mail,
                    $identifier,
                    $mail
                );
            }

            return $message;
        }

        $message = 'Sorry, an error occured. A logfile has been created which can be identified via the code "'
            . $identifier . '"';

        if ($mail !== '') {
            $message .= ' ' . 'Please send a mail to <a href="mailto:' . $mail
                . '?subject=code: ' . $identifier . '">' . $mail . '</a>';
        }

        return $message;
    }
}
