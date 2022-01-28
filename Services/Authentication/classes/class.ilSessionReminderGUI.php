<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

class ilSessionReminderGUI
{
    protected ilSessionReminder $sessionReminder;
    protected ilGlobalTemplateInterface $page;
    protected ilLanguage $lng;

    public function __construct(
        ilSessionReminder $sessionReminder,
        ilGlobalTemplateInterface $page,
        ilLanguage $language
    ) {
        $this->sessionReminder = $sessionReminder;
        $this->page = $page;
        $this->lng = $language;
    }

    public function populatePage() : void
    {
        if (!$this->sessionReminder->isActive()) {
            return;
        }

        iljQueryUtil::initjQuery($this->page);
        ilYuiUtil::initCookie();

        $this->page->addJavaScript('./Services/Authentication/js/session_reminder.js');

        $url = './sessioncheck.php?client_id=' . CLIENT_ID . '&lang=' . $this->lng->getLangKey();
        $devMode = defined('DEVMODE') && DEVMODE ? 1 : 0;
        $clientId = defined('CLIENT_ID') ? CLIENT_ID : '';
        $sessionId = session_id();
        $sessionHash = md5($sessionId);

        $javascript = <<<JS
(function($) {
    $("body").ilSessionReminder({
        url: "$url",
        client_id: "$clientId",
        hash: "$sessionHash",
        frequency: 60,
        debug: $devMode
    });
})(jQuery);
JS;

        $this->page->addOnLoadCode($javascript);
    }
}
