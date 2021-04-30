<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilSessionReminderGUI
{
    /** @var ilSessionReminder */
    protected $sessionReminder;

    /** @var \ilGlobalTemplateInterface */
    protected $page;

    /** @var \ilLanguage */
    protected $lng;

    /**
     * @param ilSessionReminder $sessionReminder
     * @param ilGlobalTemplateInterface $page
     * @param ilLanguage $language
     */
    public function __construct(
        ilSessionReminder $sessionReminder,
        ilGlobalTemplateInterface $page,
        \ilLanguage $language
    ) {
        $this->sessionReminder = $sessionReminder;
        $this->page = $page;
        $this->lng = $language;
    }

    /**
     *
     */
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
