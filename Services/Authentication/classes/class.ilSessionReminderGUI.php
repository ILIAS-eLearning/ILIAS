<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilSessionReminderGUI
{
    /**
     * @var ilSessionReminder
     */
    protected $sessionReminder;

    /**
     * @param ilSessionReminder $sessionReminder
     */
    public function __construct(ilSessionReminder $sessionReminder)
    {
        $this->setSessionReminder($sessionReminder);
    }

    /**
     * @param ilGlobalTemplateInterface $page
     * @param ilLanguage $language
     */
    public function populatePage(ilGlobalTemplateInterface $page, \ilLanguage $language) : void
    {
        if (!$this->getSessionReminder()->isActive()) {
            return;
        }

        iljQueryUtil::initjQuery($page);
        ilYuiUtil::initCookie();

        $page->addJavaScript('./Services/Authentication/js/session_reminder.js');
        
        $url = './sessioncheck.php?client_id=' . CLIENT_ID . '&lang=' . $language->getLangKey();
        $devMode = defined('DEVMODE') && DEVMODE ? 1 : 0;
        $clientId = defined('CLIENT_ID') ? CLIENT_ID : '';
        $sessionName = session_name();
        $sessionId = session_id();
        $sessionHash = md5($sessionId);

        $javascript = <<<JS
(function($) {
    $("body").ilSessionReminder({
        url: "$url",
        client_id: "$clientId",
        session_name: "$sessionName",
        session_id: "$sessionId",
        session_id_hash: "$sessionHash",
        frequency: 60,
        debug: $devMode
    });
})(jQuery);
JS;

        $page->addOnLoadCode($javascript);
    }

    /**
     * @param ilSessionReminder $sessionReminder
     * @return $this
     */
    public function setSessionReminder(ilSessionReminder $sessionReminder) : self
    {
        $this->sessionReminder = $sessionReminder;

        return $this;
    }

    /**
     * @return ilSessionReminder
     */
    public function getSessionReminder() : ilSessionReminder
    {
        return $this->sessionReminder;
    }
}
