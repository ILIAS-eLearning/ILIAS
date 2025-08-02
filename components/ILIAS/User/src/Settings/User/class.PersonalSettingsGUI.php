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

namespace ILIAS\User\Settings\User;

use ILIAS\User\LocalDIC;
use ILIAS\User\Presentation\SettingsTabs;
use ILIAS\User\Account\DeleteAccountGUI;
use ILIAS\User\Settings\User\Repository;
use ILIAS\DI\LoggingServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @ilCtrl_Calls ILIAS\User\Settings\User\PersonalSettingsGUI: ILIAS\User\Account\DeleteAccountGUI
 * @ilCtrl_Calls ILIAS\User\Settings\User\PersonalSettingsGUI: ilLocalUserPasswordSettingsGUI
 */
class PersonalSettingsGUI
{
    private readonly \ilGlobalTemplateInterface $tpl;
    private readonly UIFactory $ui_factory;
    private readonly Renderer $ui_renderer;
    private readonly \ilLanguage $lng;
    private readonly \ilCtrl $ctrl;
    private readonly Refinery $refinery;
    private readonly LoggingServices $log;
    private readonly \ilMailMimeSenderFactory $mail_sender_factory;
    private readonly \ilHelpGUI $help;
    private readonly \ilToolbarGUI $toolbar;
    private readonly \ilObjUser $current_user;
    private readonly \ilSetting $settings;
    private readonly \ilAuthSession $auth_session;
    private readonly \ilRbacSystem $rbac_system;
    private readonly Repository $user_settings_repository;
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
        $this->refinery = $DIC['refinery'];
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

        $this->ctrl->saveParameter($this, 'user_page');

        $this->user_settings_repository = LocalDIC::dic()['settings.user.repository'];

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

    public function showCmd(?\ilPropertyFormGUI $form = null): void
    {
        if ($form === null) {
            $form = $this->initForm();
        }
        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    public function saveCmd(): void
    {
        $form = $this->initForm();
        if (!$form->checkInput()
            || $this->checkStartingPointValue($form)) {
            $form->setValuesByPost();
            $this->showCmd($form);
            return;
        }

        foreach ($this->getSettingsForPageBySections() as $section => $settings) {
            $set_settings_to_default = false;
            if ($section !== 0
                && (($input = $form->getInput($section)) === '' || $input === '0')) {
                $set_settings_to_default = true;
            }
            foreach ($settings as $setting) {
                $setting->storeUserChoice(
                    $this->current_user,
                    $set_settings_to_default ? null : $form->getInput($setting->getIdentifier()),
                    $form
                );
            }
        }

        $this->current_user->update();

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txtlng('common', 'msg_obj_modified', $this->current_user->getLanguage()),
            true
        );

        $this->ctrl->redirectByClass([\ilDashboardGUI::class, self::class], 'show');
    }

    private function initForm(): \ilPropertyFormGUI
    {
        $form = array_reduce(
            $this->getSettingsForPageBySections(),
            fn(\ilPropertyFormGUI $c, array $v): \ilPropertyFormGUI => $this->addSectionToForm(
                $c,
                array_filter(
                    $v,
                    static fn(Setting $v): bool => $v->isVisibleInPersonalData()
                )
            ),
            new \ilPropertyFormGUI()
        );

        $form->addCommandButton('save', $this->lng->txt('save'));
        $form->setTitle($this->lng->txt('general_settings'));
        $form->setFormAction($this->ctrl->getFormActionByClass(self::class));

        return $form;
    }

    private function getSettingsForPageBySections(): array
    {
        return $this->reorderSections(
            array_reduce(
                $this->user_settings_repository->get(),
                function (array $c, Setting $v): array {
                    if ($v->getSettingsPage() !== AvailablePages::MainSettings) {
                        return $c;
                    }

                    if (!array_key_exists($v->getSection()->value, $c)) {
                        $c[$v->getSection()->value] = [];
                    }

                    $c[$v->getSection()->value][] = $v;
                    return $c;
                },
                []
            )
        );
    }

    private function reorderSections(array $sections): array
    {
        $default_section = $sections[AvailableSections::Main->value];
        $additional_section = $sections[AvailableSections::Additional->value];
        unset($sections[AvailableSections::Main->value]);
        unset($sections[AvailableSections::Additional->value]);
        array_unshift($sections, $default_section);
        $sections[AvailableSections::Additional->value] = $additional_section;
        return $sections;
    }

    private function addSectionToForm(
        \ilPropertyFormGUI $form,
        array $section
    ): \ilPropertyFormGUI {
        if ($section === []) {
            return $form;
        }

        if ($section[0]->getSection() === AvailableSections::Main) {
            return $this->addDefaultInputsToForm($form, $section);
        }

        return $this->addAdditionalInputsToForm($form, $section);
    }

    private function addDefaultInputsToForm(
        \ilPropertyFormGUI $form,
        array $section
    ): \ilPropertyFormGUI {
        return array_reduce(
            $section,
            function (\ilPropertyFormGUI $c, Setting $v): \ilPropertyFormGUI {
                $input = $v->getInput($this->lng, $this->current_user);
                $input->setPostVar($v->getIdentifier());
                $input->setDisabled(!$v->isVisibleInPersonalData());
                $c->addItem($input);
                return $c;
            },
            $form
        );
    }

    private function addAdditionalInputsToForm(
        \ilPropertyFormGUI $form,
        array $section
    ): \ilPropertyFormGUI {
        $values = array_reduce(
            $section,
            function (array $c, Setting $v): array {
                $input = $v->getInput($this->lng, $this->current_user);
                $input->setPostVar($v->getIdentifier());
                $input->setDisabled(!$v->isVisibleInPersonalData());
                $c['checkbox']->addSubItem($input);
                $c['defaults'] .= "{$this->lng->txt($v->getLanguageVariable())}: "
                    . "{$v->getDefaultValueForDisplay($this->lng, $this->refinery, $this->settings)}; ";
                if ($v->hasUserPersonalizedSetting($this->settings, $this->current_user)) {
                    $c['has_personalization'] = true;
                }
                return $c;
            },
            [
                'checkbox' => new \ilCheckboxInputGUI(
                    $this->lng->txt("personalise_{$section[0]->getSection()->value}"),
                    $section[0]->getSection()->value
                ),
                'defaults' => "{$this->lng->txt('default')}: ",
                'has_personalization' => false
            ]
        );
        $values['checkbox']->setInfo(trim($values['defaults']));
        $values['checkbox']->setChecked($values['has_personalization']);

        $form->addItem($values['checkbox']);

        return $form;
    }

    private function checkStartingPointValue(\ilPropertyFormGUI $form): bool
    {
        return $form->getItemByPostVar('starting_point') === null
            || $this->user_settings_repository->getByIdentifier('starting_point')->validateUserChoice($this->tpl, $this->lng, $form);
    }
}
