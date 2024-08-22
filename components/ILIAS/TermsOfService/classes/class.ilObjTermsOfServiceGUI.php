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

use ILIAS\UI\Component\Component;
use ILIAS\TermsOfService\Consumer;
use ILIAS\TermsOfService\Settings;
use ILIAS\LegalDocuments\Config;
use ILIAS\LegalDocuments\Legacy\Confirmation;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\Blocks;
use ILIAS\Data\Factory as DataFactory;

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilTermsOfServiceDocumentGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilTermsOfServiceAcceptanceHistoryGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilLegalDocumentsAdministrationGUI
 * @ilCtrl_isCalledBy ilObjTermsOfServiceGUI: ilAdministrationGUI
 */
class ilObjTermsOfServiceGUI extends ilObject2GUI
{
    protected ILIAS\DI\Container $dic;
    protected ilErrorHandling $error;
    private readonly ilLegalDocumentsAdministrationGUI $legal_documents;
    private readonly Config $config;
    private readonly UI $ui;
    private readonly Settings $tos_settings;

    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->error = $DIC['ilErr'];

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('tos');
        $this->lng->loadLanguageModule('meta');
        $config = new Config($this->dic['legalDocuments']->provide(Consumer::ID));
        if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $config = $config->allowEditing();
        }
        $this->config = $config;
        $this->legal_documents = new ilLegalDocumentsAdministrationGUI(self::class, $this->config, $this->afterDocumentDeletion(...));
        $this->ui = new UI(Consumer::ID, $this->dic->ui()->factory(), $this->dic->ui()->mainTemplate(), $this->dic->language());
        $this->tos_settings = $this->createSettings();
    }

    public function getType(): string
    {
        return 'tos';
    }

    public function executeCommand(): void
    {
        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        $this->prepareOutput();
        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd() ?? '';

        switch (strtolower($next_class)) {
            case strtolower(ilLegalDocumentsAdministrationGUI::class):
                switch ($cmd) {
                    case 'documents': $this->documents();
                        // no break.
                    default: $this->ctrl->forwardCommand($this->legal_documents);
                }
                return;
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab('permissions');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                return;
            default:
                switch ($cmd) {
                    case 'confirmReset': $this->confirmReset();
                        return;
                    case 'resetNow': $this->resetNow();
                        return;
                    default: $this->settings();
                        return;
                }
        }
    }

    public function getAdminTabs(): void
    {
        $can_edit_permissions = $this->rbac_system->checkAccess('edit_permission', $this->object->getRefId());

        // Read right is already required by self::executeCommand.
        $this->legal_documents->tabs([
            'documents' => fn() => $this->tabs_gui->addTab('settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'settings')),
        ]);

        if ($can_edit_permissions) {
            $this->tabs_gui->addTab(
                'permissions',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm')
            );
        }
    }

    public function form(): ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $read_only = !$this->rbac_system->checkAccess('write', $this->object->getRefId());

        $enabled = $this->dic->ui()->factory()->input()->field()->optionalGroup(
            [
                'reeval_on_login' => $this->dic->ui()->factory()->input()->field()->checkbox(
                    $this->ui->txt('reevaluate_on_login'),
                    $this->ui->txt('reevaluate_on_login_desc')
                )
            ],
            $this->lng->txt('tos_status_enable'),
            $this->lng->txt('tos_status_desc')
        );
        $enabled = $enabled->withValue($this->tos_settings->enabled()->value() ? ['reeval_on_login' => $this->tos_settings->validateOnLogin()->value()] : null);
        $enabled = $enabled->withDisabled($read_only);

        $form = $this->dic->ui()->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveSettings'),
            ['enabled' => $enabled]
        );

        if ($read_only) {
            return $form;
        }

        return $this->legal_documents->admin()->withFormData($form, function (array $data): void {
            $no_documents = $this->config->legalDocuments()->document()->repository()->countAll() === 0;
            if ($no_documents && $data['enabled']) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tos_no_documents_exist_cant_save'), true);
                $this->ctrl->redirect($this, 'settings');
            }
            $this->tos_settings->enabled()->update(isset($data['enabled']));
            $this->tos_settings->validateOnLogin()->update($data['enabled']['reeval_on_login'] ?? false);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'settings');
        });
    }

    public function afterDocumentDeletion(): void
    {
        if ($this->config->legalDocuments()->document()->repository()->countAll() === 0) {
            $this->tos_settings->enabled()->update(false);
        }
    }

    protected function settings(): void
    {
        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateTab('settings');

        $components = [];

        if ($this->tos_settings->enabled()->value() && $this->config->legalDocuments()->document()->repository()->countAll() === 0) {
            $components[] = $this->ui->create()->messageBox()->info(
                $this->ui->txt('no_documents_exist')
            );
        }

        $components[] = $this->legal_documents->admin()->externalSettingsMessage($this->tos_settings->deleteUserOnWithdrawal()->value());

        $components[] = $this->form();
        $this->tpl->setContent($this->dic->ui()->renderer()->render($components));
    }

    private function documents(): void
    {
        $buttons = $this->config->editable() ?
                 [$this->legal_documents->admin()->resetButton($this->dic->ctrl()->getLinkTarget($this, 'confirmReset'))] :
                 [];

        $reset_date = new DateTimeImmutable('@' . $this->dic->settings()->get('tos_last_reset', '0'));

        $this->tpl->setCurrentBlock('mess');
        $this->legal_documents->admin()->setVariable('MESSAGE', $this->legal_documents->admin()->resetBox($reset_date, $buttons));
        $this->tpl->parseCurrentBlock('mess');
    }

    private function confirmReset(): void
    {
        $this->legal_documents->admin()->requireEditable();
        $this->legal_documents->admin()->setContent((new Confirmation($this->dic->language()))->render(
            $this->dic->ctrl()->getFormAction($this, 'resetNow'),
            'resetNow',
            'documents',
            $this->dic->language()->txt('tos_sure_reset_tos')
        ));
    }

    private function resetNow(): void
    {
        $this->legal_documents->admin()->requireEditable();
        $in = $this->dic->database()->in('usr_id', [ANONYMOUS_USER_ID, SYSTEM_USER_ID], true, 'integer');
        $this->dic->database()->manipulate("UPDATE usr_data SET agree_date = NULL WHERE $in");
        $this->tos_settings->lastResetDate()->update((new DataFactory())->clock()->system()->now());
        $this->dic->ctrl()->redirectByClass([self::class, $this->legal_documents::class], 'documents');
    }

    private function createSettings(): Settings
    {
        $blocks = new Blocks($this->config->legalDocuments()->id(), $this->dic, $this->config->legalDocuments());
        return new Settings($blocks->selectSettingsFrom($blocks->globalStore()));
    }
}
