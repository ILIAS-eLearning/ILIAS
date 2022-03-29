<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author            Jan Posselt <jposselt@databay.de.de> Ingmar Szmais <iszmais@databay.de>
 * @ilCtrl_Calls      ilNotificationGUI:
 * @ilCtrl_IsCalledBy ilNotificationGUI: ilPersonalProfileGUI, ilDashboardGUI
 * @ingroup           ServicesNotifications
 */
class ilNotificationGUI implements ilCtrlBaseClassInterface
{
    private $handler = array();

    private Container $dic;

    /** @var ilObjUser|ilUser */
    private $user;

    /** @var ilTemplat */
    private $template;

    /** @var ilCtrl */
    private $controller;

    /** @var ilLanguage */
    private $language;

    /** @var ilLocatorGUI */
    private $locatorGUI;

    /**
     * @access    public
     * @param ilUser|null $user
     * @param ilTemplate|null $template
     * @param ilCtrl|null $controller
     * @param ilLanguage|null $language
     * @param ilLocatorGUI|null $locatorGUI
     * @param \ILIAS\DI\Container|null $dic
     */
    public function __construct(
        \ilUser $user = null,
        \ilTemplate $template = null,
        \ilCtrl $controller = null,
        \ilLanguage $language = null,
        \ilLocatorGUI $locatorGUI = null,
        \ILIAS\DI\Container $dic = null
    ) {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }
        $this->dic = $dic;

        if ($user === null) {
            $user = $dic->user();
        }
        $this->user = $user;

        if ($template === null) {
            $template = $dic->ui()->mainTemplate();
        }
        $this->template = $template;

        if ($controller === null) {
            $controller = $dic->ctrl();
        }
        $this->controller = $controller;

        if ($language === null) {
            $language = $dic->language();
        }
        $this->language = $language;

        if ($locatorGUI === null) {
            $locatorGUI = $dic['ilLocator'];
        }
        $this->locatorGUI = $locatorGUI;
        $this->type = "not";
    }

    public static function _forwards()
    {
        return array();
    }

    public function executeCommand()
    {
        if (!$this->controller->getCmd()) {
            return;
        }

        $cmd = $this->controller->getCmd() . 'Object';
        $this->$cmd();
    }

    public function getHandler($type)
    {
        return $this->handler[$type];
    }

    private function getAvailableTypes($types = array())
    {
        return ilNotificationDatabaseHandler::getAvailableTypes($types);
    }

    private function getAvailableChannels($types = array())
    {
        return ilNotificationDatabaseHandler::getAvailableChannels($types);
    }

    /**
     * Returns the pending on screen notifications for a user request
     * @todo this method should move to a better place as it handels channel
     *       sprecific things.
     * @global ilUser $ilUser
     * @return string
     */
    public function getOSDNotificationsObject() : void
    {
        $settings = new ilSetting('notifications');

        ilSession::enableWebAccessWithoutSession(true);

        $notifications = ilNotificationOSDHandler::getNotificationsForUser(
            $this->user->getId(),
            true,
            (int) $_REQUEST['max_age']
        );

        $result = new stdClass();
        $result->notifications = $notifications;
        $toasts = [];
        foreach ($result->notifications as $notification) {
            $toast = $this->dic->ui()->factory()->toast()->standard(
                $notification['data']->title,
                $this->dic->ui()->factory()->symbol()->icon()->custom($notification['data']->iconPath, ''))
            ->withAction('ilias.php?' . http_build_query([
                    'baseClass' => 'ilNotificationGUI',
                    'cmd' => 'removeOSDNotifications',
                    'cmdMode' => 'asynch',
                    'notification_id' => $notification['notification_osd_id']
            ]))
            ->withDescription($notification['data']->shortDescription)
            ->withVanishTime($settings->get('osd_vanish') * 1000)
            ->withDelayTime($settings->get('osd_delay'));
            foreach ($notification['data']->links as $link) {
                $toast = $toast->withAdditionalLink($this->dic->ui()->factory()->link()->standard(
                    $link->getTitle(),
                    $link->getUrl()
                ));
            }
            $toasts[] = $toast;
        }
        $this->dic->http()->saveResponse(
            $this->dic->http()->response()
                ->withBody(Streams::ofString(
                    $this->dic->ui()->renderer()->renderAsync($toasts)
                ))
        );
        $this->dic->http()->sendResponse();
        $this->dic->http()->close();
    }

    public function removeOSDNotificationsObject()
    {
        ilSession::enableWebAccessWithoutSession(true);
        ilNotificationOSDHandler::removeNotification($_REQUEST['notification_id']);
        exit;
    }

    public function addHandler($channel, ilNotificationHandler $handler)
    {
        if (!array_key_exists($channel, $this->handler) || !is_array($this->handler[$channel])) {
            $this->handler[$channel] = array();
        }

        $this->handler[$channel][] = $handler;
    }

    private function saveCustomizingOptionObject()
    {
        if ($_POST['enable_custom_notification_configuration']) {
            $this->user->writePref('use_custom_notification_setting', 1);
        } else {
            $this->user->writePref('use_custom_notification_setting', 0);
        }

        $this->showSettingsObject();
    }

    public function showSettingsObject()
    {
        $userTypes = ilNotificationDatabaseHandler::loadUserConfig($this->user->getId());

        $this->language->loadLanguageModule('notification');

        $form = new ilPropertyFormGUI();
        $chk = new ilCheckboxInputGUI($this->language->txt('enable_custom_notification_configuration'), 'enable_custom_notification_configuration');
        $chk->setValue('1');
        $chk->setChecked($this->user->getPref('use_custom_notification_setting') == 1);
        $form->addItem($chk);

        $form->setFormAction($this->controller->getFormAction($this, 'showSettingsObject'));
        $form->addCommandButton('saveCustomizingOption', $this->language->txt('save'));
        $form->addCommandButton('showSettings', $this->language->txt('cancel'));

        $table = new ilNotificationSettingsTable($this, 'a title', $this->getAvailableChannels(array('set_by_user')), $userTypes);

        $table->setFormAction($this->controller->getFormAction($this, 'saveSettings'));
        $table->setData($this->getAvailableTypes(array('set_by_user')));

        if ($this->user->getPref('use_custom_notification_setting') == 1) {
            $table->addCommandButton('saveSettings', $this->language->txt('save'));
            $table->addCommandButton('showSettings', $this->language->txt('cancel'));
            $table->setEditable(true);
        } else {
            $table->setEditable(false);
        }

        $this->template->setContent($form->getHtml() . $table->getHTML());
    }

    public function addLocatorItems()
    {
        if (is_object($this->object)) {
            $this->locatorGUI->addItem(
                $this->object->getTitle(),
                $this->controller->getLinkTarget($this, ''),
                '',
                $_GET["ref_id"]
            );
        }
    }

    private function saveSettingsObject()
    {
        ilNotificationDatabaseHandler::setUserConfig(
            $this->user->getId(),
            $_REQUEST['notification'] ? $_REQUEST['notification'] : array()
        );

        $this->showSettingsObject();
    }
}
