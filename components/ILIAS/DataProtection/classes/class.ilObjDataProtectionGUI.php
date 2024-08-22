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

use ILIAS\DataProtection\Consumer;
use ILIAS\DataProtection\Settings;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings as SettingsInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\ReadOnlyStore;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\ILIASSettingStore;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\Config;
use ILIAS\LegalDocuments\Legacy\Confirmation;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Component\Component;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;

/**
 * @ilCtrl_isCalledBy ilObjDataProtectionGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjDataProtectionGUI: ilLegalDocumentsAdministrationGUI
 * @ilCtrl_Calls      ilObjDataProtectionGUI: ilPermissionGUI
 */
final class ilObjDataProtectionGUI extends ilObject2GUI
{
    private readonly ilLegalDocumentsAdministrationGUI $legal_documents;
    private readonly Container $container;
    private readonly Config $config;
    private readonly SettingsInterface $data_protection_settings;
    private readonly UI $ui;

    public function __construct()
    {
        parent::__construct(...func_get_args());
        global $DIC;
        $this->container = $DIC;
        $config = new Config($DIC['legalDocuments']->provide(Consumer::ID));
        if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $config = $config->allowEditing();
        }
        $this->config = $config;
        $this->legal_documents = new ilLegalDocumentsAdministrationGUI(self::class, $config, $this->afterDocumentDeletion(...));

        $this->data_protection_settings = $this->createDataProtectionSettings();
        $this->ui = new UI($this->getType(), $this->container->ui()->factory(), $this->container->ui()->mainTemplate(), $this->container->language());
    }

    public function getType(): string
    {
        return 'dpro';
    }

    public function executeCommand(): void
    {
        $this->requireReadable();
        $this->prepareOutput();

        $this->container->language()->loadLanguageModule('dpro');
        $this->container->language()->loadLanguageModule('ldoc');

        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd('settings');

        switch (strtolower($next_class)) {
            case strtolower(ilLegalDocumentsAdministrationGUI::class):
                if ($cmd === 'documents') {
                    $this->documents();
                }
                $this->ctrl->forwardCommand($this->legal_documents);
                return;
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab('permissions');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                return;
            default:
                switch ($cmd) {
                    case 'documents':
                        $this->ctrl->redirectByClass(ilLegalDocumentsAdministrationGUI::class, 'documents');
                        break;

                    default:
                        $reflection = new ReflectionClass(self::class);
                        if (!$reflection->hasMethod($cmd) || !$reflection->getMethod($cmd)->isPublic()) {
                            throw new Exception('Undefined command.');
                        }
                        $this->$cmd();
                }
        }
    }

    public function view(): void
    {
        $this->settings();
    }

    public function settings(): void
    {
        $this->container->tabs()->activateTab('settings');

        $components = [];

        if ($this->data_protection_settings->enabled()->value() && $this->config->legalDocuments()->document()->repository()->countAll() === 0) {
            $components[] = $this->ui->create()->messageBox()->info(
                $this->ui->txt('no_documents_exist')
            );
        }

        $components[] = $this->legal_documents->admin()->externalSettingsMessage($this->data_protection_settings->deleteUserOnWithdrawal()->value());

        $components[] = $this->form();
        $this->tpl->setContent($this->container->ui()->renderer()->render($components));
    }

    public function confirmReset(): void
    {
        $this->container->tabs()->activateTab('documents');
        $this->legal_documents->admin()->requireEditable();
        $this->legal_documents->admin()->setContent((new Confirmation($this->lng))->render(
            $this->ctrl->getFormAction($this, 'resetNow'),
            'resetNow',
            'documents',
            $this->ui->txt('sure_reset_tos')
        ));
    }

    public function resetNow(): void
    {
        $this->legal_documents->admin()->requireEditable();

        $in = $this->container->database()->in('usr_id', [ANONYMOUS_USER_ID, SYSTEM_USER_ID], true, 'integer');
        $this->container->database()->manipulate("delete from usr_pref WHERE keyword = 'dpro_agree_date' AND $in");
        $this->data_protection_settings->lastResetDate()->update((new DataFactory())->clock()->system()->now());
        $this->ctrl->redirect($this->legal_documents, 'documents');
    }

    public function getAdminTabs(): void
    {
        $this->container->language()->loadLanguageModule('tos');
        $this->legal_documents->tabs([
            'documents' => fn() => $this->tabs_gui->addTab('settings', $this->ui->txt('settings'), $this->ctrl->getLinkTarget($this, 'settings')),
        ]);
        $this->tabs_gui->addTab('permissions', $this->ui->txt('perm_settings'), $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm'));
    }

    public function afterDocumentDeletion(): void
    {
        if ($this->config->legalDocuments()->document()->repository()->countAll() === 0) {
            $this->data_protection_settings->enabled()->update(false);
        }
    }

    private function requireReadable(): void
    {
        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->container->language()->txt('msg_no_perm_read'), $this->error->WARNING);
        }
    }

    private function form(): Component
    {
        $enabled = $this->optionalGroup('status_enable', 'status_enable_desc', [
            'type' => $this->radio('mode', [
                'once' => 'once',
                'eval_on_login' => 'reevaluate_on_login',
                'no_acceptance' => 'no_acceptance',
            ])->withValue('once')->withRequired(true),
        ]);

        $enabled = $enabled->withValue($this->data_protection_settings->enabled()->value() ? [
            'type' => $this->data_protection_settings->validateOnLogin()->value() ?
                      'eval_on_login' :
                      ($this->data_protection_settings->noAcceptance()->value() ? 'no_acceptance' : 'once'),
        ] : null);

        $enabled = $enabled->withDisabled(!$this->config->editable())->withRequired(true);

        $form = $this->ui->create()->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'settings'),
            ['enabled' => $enabled]
        );

        return $this->legal_documents->admin()->withFormData($form, function (array $data): void {
            $no_documents = $this->config->legalDocuments()->document()->repository()->countAll() === 0;
            if ($no_documents && isset($data['enabled'])) {
                $this->tpl->setOnScreenMessage('failure', $this->ui->txt('no_documents_exist_cant_save'), true);
                $this->ctrl->redirect($this, 'settings');
            }
            $type = $data['enabled']['type'] ?? false;
            $this->data_protection_settings->enabled()->update(isset($data['enabled']));
            $this->data_protection_settings->validateOnLogin()->update($type === 'eval_on_login');
            $this->data_protection_settings->noAcceptance()->update($type === 'no_acceptance');

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'settings');
        });
    }

    private function optionalGroup(string $label, string $description, array $fields): OptionalGroup
    {
        return $this->ui->create()->input()->field()->optionalGroup(
            $fields,
            $this->ui->txt($label),
            $this->ui->txt($description)
        );
    }

    private function radio(string $prefix, array $options): Component
    {
        $field = $this->ui->create()->input()->field()->radio($this->ui->txt($prefix), $this->ui->txt($prefix . '_desc'));
        foreach ($options as $key => $label) {
            $field = $field->withOption((string) $key, $this->ui->txt($label));
        }
        return $field;
    }

    private function createDataProtectionSettings(): Settings
    {
        $store = new ILIASSettingStore($this->container->settings());
        return new Settings(new SelectSetting(
            $this->config->editable() ? $store : new ReadOnlyStore($store),
            new Marshal($this->container->refinery())
        ));
    }

    private function documents(): void
    {
        $buttons = $this->config->editable() ?
                 [$this->legal_documents->admin()->resetButton($this->ctrl->getLinkTarget($this, 'confirmReset'))] :
                 [];

        $reset_date = $this->data_protection_settings->lastResetDate()->value();
        $this->tpl->setCurrentBlock('mess');
        $this->legal_documents->admin()->setVariable('MESSAGE', $this->legal_documents->admin()->resetBox($reset_date, $buttons));
        $this->tpl->parseCurrentBlock('mess');
    }
}
