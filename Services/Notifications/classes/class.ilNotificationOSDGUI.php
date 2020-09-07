<?php

/**
 * Class ilNotificationOSDGUI
 */
class ilNotificationOSDGUI
{
    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilTemplate
     */
    protected $mainTemplate;

    /**
     * @var \ilLanguage
     */
    protected $lng;
    
    /**
     * ilNotificationOSDGUI constructor.
     * @param \ilObjUser $user
     * @param \ilTemplate $mainTemplate
     * @param \ilLanguage $lng
     */
    public function __construct(\ilObjUser $user, \ilTemplate $mainTemplate, \ilLanguage $lng)
    {
        $this->user = $user;
        $this->mainTemplate = $mainTemplate;
        $this->lng = $lng;
    }
    
    public function render()
    {
        $notificationSettings = new \ilSetting('notifications');
        $chatSettings = new \ilSetting('chatroom');

        $osdTemplate = new \ilTemplate('tpl.osd_notifications.js', true, true, 'Services/Notifications');

        require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';
        require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

        $notifications = \ilNotificationOSDHandler::getNotificationsForUser($this->user->getId());
        $osdTemplate->setVariable('NOTIFICATION_CLOSE_HTML', json_encode(ilGlyphGUI::get(ilGlyphGUI::CLOSE, $this->lng->txt('close'))));
        $osdTemplate->setVariable('INITIAL_NOTIFICATIONS', json_encode($notifications));
        $osdTemplate->setVariable('OSD_POLLING_INTERVALL', $notificationSettings->get('osd_polling_intervall') ? $notificationSettings->get('osd_polling_intervall') : '60');
        $osdTemplate->setVariable('OSD_PLAY_SOUND', $chatSettings->get('play_invitation_sound') && $this->user->getPref('chat_play_invitation_sound') ? 'true' : 'false');

        require_once "Services/jQuery/classes/class.iljQueryUtil.php";
        iljQueryUtil::initjQuery();

        require_once 'Services/MediaObjects/classes/class.ilPlayerUtil.php';
        ilPlayerUtil::initMediaElementJs();

        $this->mainTemplate->addJavaScript('Services/Notifications/templates/default/notifications.js');
        $this->mainTemplate->addCSS('Services/Notifications/templates/default/osd.css');
        $this->mainTemplate->addOnLoadCode($osdTemplate ->get());
    }
}
