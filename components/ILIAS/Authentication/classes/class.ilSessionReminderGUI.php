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

class ilSessionReminderGUI
{
    public function __construct(
        private readonly ilSessionReminder $session_reminder,
        private readonly ilGlobalTemplateInterface $page,
        private readonly ilLanguage $lng,
        private readonly ilLoggerFactory $logger_factory
    ) {
    }

    public function populatePage(): void
    {
        if (!$this->session_reminder->isActive()) {
            return;
        }

        $this->page->addJavaScript('assets/js/SessionReminder.min.js');

        $url = './sessioncheck.php?client_id=' . CLIENT_ID . '&lang=' . $this->lng->getLangKey();
        $client_id = defined('CLIENT_ID') ? CLIENT_ID : '';
        $hash = hash(
            'sha256',
            implode('', [
                session_id(),
                $this->session_reminder->getUser()->getId(),
                $this->session_reminder->getUser()->getCreateDate()
            ])
        );
        $log_level = $this->logger_factory->getSettings()->getLevelByComponent('auth');

        $javascript = <<<JS
            il.SessionReminder.init({
                url: "$url",
                clientId: "$client_id",
                hash: "$hash",
                frequency: 60,
                logLevel: $log_level
            }, window, console);
            il.SessionReminder.run();
        JS;

        $this->page->addOnLoadCode($javascript);
    }
}
