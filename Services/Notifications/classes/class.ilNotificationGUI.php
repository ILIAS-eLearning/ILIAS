<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Notifications\ilNotificationDatabaseHandler;
use ILIAS\Notifications\ilNotificationHandler;
use ILIAS\Notifications\ilNotificationOSDHandler;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\Notifications\ilNotificationSettingsTable;
use ILIAS\Services\Notifications\ToastsOfNotifications;

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class ilNotificationGUI implements ilCtrlBaseClassInterface
{
    private array $handler = [];
    private Container $dic;
    private ilObjUser $user;
    private ilGlobalTemplateInterface $template;
    private ilCtrlInterface $controller;
    private ilLanguage $language;

    public function __construct(
        ?ilObjUser $user = null,
        ?ilGlobalTemplateInterface $template = null,
        ?ilCtrlInterface $controller = null,
        ?ilLanguage $language = null,
        ?Container $dic = null
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
    }

    public static function _forwards(): array
    {
        return [];
    }

    public function executeCommand(): void
    {
        if (!$this->controller->getCmd()) {
            return;
        }

        $cmd = $this->controller->getCmd() . 'Object';
        $this->$cmd();
    }

    /**
     * @return mixed
     */
    public function getHandler(string $type)
    {
        return $this->handler[$type];
    }

    private function getAvailableTypes(array $types = []): array
    {
        return ilNotificationDatabaseHandler::getAvailableTypes($types);
    }

    private function getAvailableChannels(array $types = []): array
    {
        return ilNotificationDatabaseHandler::getAvailableChannels($types);
    }

    public function getOSDNotificationsObject(): void
    {
        $settings = new ilSetting('notifications');
        ilSession::enableWebAccessWithoutSession(true);
        $notifications = (new ilNotificationOSDHandler())->getNotificationsForUser(
            $this->user->getId(),
            true,
            $this->dic->http()->wrapper()->query()->retrieve('max_age', $this->dic->refinery()->kindlyTo()->int())
        );

        $toasts = (new ToastsOfNotifications(
            $this->dic->ui()->factory(),
            $settings
        ))->create($notifications);

        $this->dic->http()->saveResponse(
            $this->dic->http()->response()
                ->withBody(Streams::ofString(
                    $this->dic->ui()->renderer()->renderAsync($toasts)
                ))
        );
        $this->dic->http()->sendResponse();
        $this->dic->http()->close();
    }

    public function removeOSDNotificationsObject(): void
    {
        ilSession::enableWebAccessWithoutSession(true);
        (new ilNotificationOSDHandler())->removeNotification(
            $this->dic->http()->wrapper()->query()->retrieve('notification_id', $this->dic->refinery()->kindlyTo()->int())
        );
        exit;
    }

    public function addHandler(string $channel, ilNotificationHandler $handler): void
    {
        if (!array_key_exists($channel, $this->handler) || !is_array($this->handler[$channel])) {
            $this->handler[$channel] = [];
        }

        $this->handler[$channel][] = $handler;
    }

    private function saveCustomizingOptionObject(): void
    {
        if ($this->dic->http()->wrapper()->post()->has('enable_custom_notification_configuration')) {
            $this->user->writePref('use_custom_notification_setting', "1");
        } else {
            $this->user->writePref('use_custom_notification_setting', "0");
        }

        $this->showSettingsObject();
    }

    public function showSettingsObject(): void
    {
        $userTypes = ilNotificationDatabaseHandler::loadUserConfig($this->user->getId());

        $this->language->loadLanguageModule('notification');

        $form = new ilPropertyFormGUI();
        $chk = new ilCheckboxInputGUI($this->language->txt('enable_custom_notification_configuration'), 'enable_custom_notification_configuration');
        $chk->setValue('1');
        $chk->setChecked($this->dic->refinery()->kindlyTo()->int()->transform($this->user->getPref('use_custom_notification_setting')) === 1);
        $form->addItem($chk);

        $form->setFormAction($this->controller->getFormAction($this, 'showSettingsObject'));
        $form->addCommandButton('saveCustomizingOption', $this->language->txt('save'));
        $form->addCommandButton('showSettings', $this->language->txt('cancel'));

        $table = new ilNotificationSettingsTable($this, 'a title', $this->getAvailableChannels(array('set_by_user')), $userTypes);

        $table->setFormAction($this->controller->getFormAction($this, 'saveSettings'));
        $table->setData($this->getAvailableTypes(array('set_by_user')));

        if (
            $this->dic->refinery()->kindlyTo()->int()->transform(
                $this->user->getPref('use_custom_notification_setting')
            ) === 1
        ) {
            $table->addCommandButton('saveSettings', $this->language->txt('save'));
            $table->addCommandButton('showSettings', $this->language->txt('cancel'));
            $table->setEditable(true);
        } else {
            $table->setEditable(false);
        }

        $this->template->setContent($form->getHtml() . $table->getHTML());
    }
}
