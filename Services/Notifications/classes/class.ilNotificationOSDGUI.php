<?php

/**
 * Class ilNotificationOSDGUI
 */
class ilNotificationOSDGUI
{
    /** @var \ilObjUser */
    protected $user;
    /** @var \ilGlobalTemplateInterface */
    protected $page;
    /** @var \ilLanguage */
    protected $lng;
    /** @var \ILIAS\DI\UIServices */
    private $ui;

    /**
     * ilNotificationOSDGUI constructor.
     * @param ilGlobalTemplateInterface $page
     * @param ilLanguage $language
     */
    public function __construct(\ilGlobalTemplateInterface $page, \ilLanguage $language)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->page = $page;
        $this->lng = $language;
        $this->ui = $DIC->ui();
    }

    /**
     *
     */
    public function populatePage() : void
    {
        if ($this->user->isAnonymous() || 0 === (int) $this->user->getId()) {
            return;
        }

        $notificationSettings = new \ilSetting('notifications');

        $osdTemplate = new \ilTemplate('tpl.osd_notifications.js', true, true, 'Services/Notifications');

        $osdTemplate->setVariable(
            'OSD_INTERVAL',
            $notificationSettings->get('osd_interval') ? $notificationSettings->get('osd_interval') : '60'
        );
        $osdTemplate->setVariable(
            'OSD_PLAY_SOUND',
            $notificationSettings->get('play_sound') && $this->user->getPref('play_sound') ? 'true' : 'false'
        );

        iljQueryUtil::initjQuery($this->page);
        ilPlayerUtil::initMediaElementJs($this->page);

        $this->page->addJavaScript('Services/Notifications/templates/default/notifications.js');
        $this->page->addCSS('Services/Notifications/templates/default/osd.css');
        $this->page->addOnLoadCode($osdTemplate->get());
    }
}
