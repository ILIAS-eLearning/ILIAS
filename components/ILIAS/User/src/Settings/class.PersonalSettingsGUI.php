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

namespace ILIAS\User\Settings;

use ILIAS\User\LocalDIC;
use ILIAS\User\Context;
use ILIAS\User\Presentation\SettingsTabs;
use ILIAS\User\Account\DeleteAccountGUI;
use ILIAS\DI\LoggingServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @ilCtrl_Calls ILIAS\User\Settings\PersonalSettingsGUI: ILIAS\User\Account\DeleteAccountGUI
 * @ilCtrl_Calls ILIAS\User\Settings\PersonalSettingsGUI: ilLocalUserPasswordSettingsGUI
 */
class PersonalSettingsGUI
{
    private readonly \ilGlobalTemplateInterface $tpl;
    private readonly UIFactory $ui_factory;
    private readonly Renderer $ui_renderer;
    private readonly \ilLanguage $lng;
    private readonly \ilCtrl $ctrl;
    private readonly ServerRequestInterface $request;
    private readonly LoggingServices $log;
    private readonly \ilMailMimeSenderFactory $mail_sender_factory;
    private readonly \ilHelpGUI $help;
    private readonly \ilToolbarGUI $toolbar;
    private \ilObjUser $current_user;
    private readonly \ilSetting $settings;
    private readonly \ilAuthSession $auth_session;
    private readonly \ilRbacSystem $rbac_system;
    private readonly SettingsImplementation $user_settings;
    private readonly SettingsTabs $tabs;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->request = $DIC['http']->request();
        $this->log = $DIC->logger();
        $this->mail_sender_factory = $DIC->mail()->mime()->senderFactory();
        $this->help = $DIC['ilHelp'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->current_user = $DIC['ilUser'];
        $this->settings = $DIC['ilSetting'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->rbac_system = $DIC['rbacsystem'];

        $this->lng->loadLanguageModule('user');
        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('mail');

        $this->ctrl->saveParameter($this, 'user_page');

        $this->user_settings = LocalDIC::dic()[Settings::class];

        $this->tabs = new SettingsTabs(
            $DIC['ilTabs'],
            $this->lng,
            $this->ctrl,
            $this->settings,
            $this->current_user
        );
    }

    public function executeCommand(): void
    {
        $this->help->setScreenIdComponent('user');
        $this->tabs->initializeTabs();

        switch ($this->ctrl->getNextClass()) {
            case strtolower(\ilLocalUserPasswordSettingsGUI::class):
                $this->ctrl->forwardCommand(
                    new \ilLocalUserPasswordSettingsGUI()
                );
                break;
            case strtolower(DeleteAccountGUI::class):
                $this->ctrl->forwardCommand(
                    new DeleteAccountGUI(
                        $this->ctrl,
                        $this->lng,
                        $this->tpl,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->toolbar,
                        $this->log,
                        $this->mail_sender_factory,
                        $this->settings,
                        $this->auth_session,
                        $this->current_user
                    )
                );
                break;
            default:
                $this->tpl->setTitle($this->lng->txt('personal_settings'));
                $cmd = $this->ctrl->getCmd('show') . 'Cmd';
                $this->$cmd();
        }
    }

    public function showCmd(?StandardForm $form = null): void
    {
        if ($form === null) {
            $form = $this->initForm();
        }
        $this->tpl->setContent($this->ui_renderer->render($form));
        $this->tpl->printToStdout();
    }

    public function saveCmd(): void
    {
        $form = $this->initForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showCmd($form);
            return;
        }

        $this->current_user = $this->user_settings->saveForm(
            $data['settings'],
            [AvailablePages::MainSettings],
            Context::User,
            $this->current_user
        );

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txtlng('common', 'msg_obj_modified', $this->current_user->getLanguage()),
            true
        );

        $this->ctrl->redirectByClass([\ilDashboardGUI::class, self::class], 'show');
    }

    private function initForm(): StandardForm
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, 'save'),
            [
                'settings' => $this->ui_factory->input()->field()->section(
                    $this->user_settings->buildFormInputs(
                        [AvailablePages::MainSettings],
                        Context::User,
                        $this->current_user
                    ),
                    $this->lng->txt('settings')
                )
            ]
        );
    }
}
