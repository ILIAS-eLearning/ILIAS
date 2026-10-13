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

use ILIAS\Authentication\SessionReminder\TokenCipher;
use ILIAS\Logging\Config\ConfigInterface as LoggingConfig;

readonly class ilSessionReminderGUI
{
    public function __construct(
        private ilSessionReminder $session_reminder,
        private ilGlobalTemplateInterface $page,
        private ilLanguage $lng,
        private LoggingConfig $logging_config,
        private ?TokenCipher $token_cipher
    ) {
    }

    public function populatePage(): void
    {
        if (!$this->session_reminder->isActive()) {
            return;
        }

        if ($this->token_cipher === null) {
            return;
        }

        $this->page->addJavaScript('assets/js/SessionReminder.min.js');

        $url = './sessioncheck.php?client_id=' . CLIENT_ID . '&lang=' . $this->lng->getLangKey();
        $client_id = defined('CLIENT_ID') ? CLIENT_ID : '';
        $user_id = $this->session_reminder->getUser()->getId();
        $hash = hash(
            'sha256',
            implode('', [
                session_id(),
                $user_id,
                $this->session_reminder->getUser()->getCreateDate()
            ])
        );
        $token = $this->token_cipher->encode($user_id, $hash);
        // Identifies the session for the client (e.g. to detect a new login in another tab), but must not reveal the hash
        $session_identifier = hash('sha256', 'session_reminder_state' . $hash);
        $log_level = $this->logging_config->byComponent()->level('auth')->value;

        $javascript = <<<JS
            il.SessionReminder.init({
                url: "$url",
                clientId: "$client_id",
                hash: "$session_identifier",
                token: "$token",
                frequency: 60,
                logLevel: $log_level
            }, window, console);
            il.SessionReminder.run();
        JS;

        $this->page->addOnLoadCode($javascript);
    }
}
