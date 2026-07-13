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

use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as PublishingSettings;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\Cron\Job\JobManager as CronJobManager;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @ilCtrl_Calls ilMDPublishingSettingsGUI: ilPropertyFormGUI
 */
class ilMDPublishingSettingsGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected CronJobManager $cron_manager;
    protected ilObjMDSettingsGUI $parent_obj_gui;
    protected ilMDSettingsAccessService $access_service;

    protected ?ilMDSettings $md_settings = null;
    protected PublishingSettings $publishing_settings;
    protected CopyrightRepository $copyright_repo;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    public function __construct(ilObjMDSettingsGUI $parent_obj_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->cron_manager = $DIC->cron()->manager();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->parent_obj_gui = $parent_obj_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_obj_gui->getRefId(),
            $DIC->access()
        );

        // TODO service structure for md admin!
        $internal = new InternalServices($DIC);
        $this->publishing_settings = $internal->OERHarvester()->settings();
        $this->copyright_repo = $internal->copyright()->repository();

        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilPropertyFormGUI::class):
                $form = $this->initSettingsForm();
                $this->ctrl->forwardCommand($form);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'showPublishingSettings';
                }
                switch ($cmd) {
                    case 'showPublishingSettings':
                        $this->showPublishingSettings();
                        break;

                    case 'savePublishingSettings':
                        $this->savePublishingSettings();
                        break;

                    default:
                        throw new ilPermissionException($this->lng->txt('no_permission'));
                }
                break;
        }
    }

    public function showPublishingSettings(?ilPropertyFormGUI $form = null): void
    {
        $message_box = $this->getConfigStatusMessageBox();
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setContent(
            $this->ui_renderer->render($message_box) .
            $form->getHTML()
        );
    }

    public function savePublishingSettings(): void
    {
        if (!$this->access_service->hasCurrentUserWriteAccess()) {
            $this->ctrl->redirect($this, 'showPublishingSettings');
        }
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $copyrights = [];
            foreach ($form->getInput('copyright') as $id) {
                $copyrights[] = (int) $id;
            }

            $modes = $form->getInput('mode');
            $this->publishing_settings->saveManualPublishingEnabled(in_array('manual', $modes));
            $this->publishing_settings->saveAutomaticPublishingEnabled(in_array('automatic', $modes));

            $editorial_step_enabled = (bool) $form->getInput('editorial_step');
            $this->publishing_settings->saveEditorialStepEnabled($editorial_step_enabled);
            if ($editorial_step_enabled) {
                $this->publishing_settings->saveContainerRefIDForEditorialStep((int) $form->getInput('target'));
            }

            $this->publishing_settings->saveContainerRefIDForPublishing((int) $form->getInput('exposed_source'));
            $this->publishing_settings->saveCopyrightEntryIDsSelectedForPublishing(...$copyrights);
            $this->publishing_settings->saveObjectTypesSelectedForPublishing(...$form->getInput('object_type'));
            $this->MDSettings()->activateOAIPMH((bool) $form->getInput('oai_active'));
            $this->MDSettings()->saveOAIRepositoryName((string) $form->getInput('oai_repository_name'));
            $this->MDSettings()->saveOAIIdentifierPrefix((string) $form->getInput('oai_identifier_prefix'));
            $this->MDSettings()->saveOAIContactMail((string) $form->getInput('oai_contact_mail'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showPublishingSettings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $form->setValuesByPost();
        $this->showPublishingSettings($form);
    }

    /**
     * TODO move to KS when repository picker is available there
     */
    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        if ($this->access_service->hasCurrentUserWriteAccess()) {
            $form->addCommandButton('savePublishingSettings', $this->lng->txt('save'));
        }

        $this->addPublishingWorkflowSection($form);
        $this->addCopyrightSection($form);
        $this->addObjectTypeSection($form);
        $this->addOAIPMHSection($form);

        return $form;
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return void
     */
    protected function addPublishingWorkflowSection(ilPropertyFormGUI $form): void
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('md_publishing_workflow'));
        $form->addItem($header);

        // publishing mode
        $mode = new ilCheckboxGroupInputGUI(
            $this->lng->txt('md_publishing_workflow_mode'),
            'mode'
        );
        $mode->setInfo($this->lng->txt('md_publishing_workflow_mode_info'));
        $checked = [];
        $manual_checkbox = new ilCheckboxOption(
            $this->lng->txt('md_publishing_workflow_manual'),
            'manual',
            $this->lng->txt('md_publishing_workflow_manual_info')
        );
        if ($this->publishing_settings->isManualPublishingEnabled()) {
            $checked[] = 'manual';
        }
        $mode->addOption($manual_checkbox);
        $automatic_checkbox = new ilCheckboxOption(
            $this->lng->txt('md_publishing_workflow_automatic'),
            'automatic',
            $this->lng->txt('md_publishing_workflow_automatic_info')
        );
        if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
            $checked[] = 'automatic';
        }
        $mode->addOption($automatic_checkbox);
        $mode->setValue($checked);
        $form->addItem($mode);

        // source for exposing
        $ex_target = new ilRepositorySelector2InputGUI(
            $this->lng->txt('meta_oer_exposed_source'),
            'exposed_source',
            false,
            $form
        );

        $ex_explorer = $ex_target->getExplorerGUI();
        $ex_explorer->setRootId(ROOT_FOLDER_ID);
        $ex_explorer->setTypeWhiteList(['cat']);

        $ex_target_ref_id = $this->publishing_settings->getContainerRefIDForPublishing();
        if ($ex_target_ref_id) {
            $ex_explorer->setPathOpen($ex_target_ref_id);
            $ex_target->setValue($ex_target_ref_id);
        }

        $ex_target->setRequired(true);
        $form->addItem($ex_target);

        // target selection
        $checkbox = new ilCheckboxInputGUI(
            $this->lng->txt('meta_oer_editorial_step_toggle'),
            'editorial_step'
        );
        $checkbox->setInfo($this->lng->txt('meta_oer_editorial_step_info'));
        $checkbox->setChecked($this->publishing_settings->isEditorialStepEnabled());
        $form->addItem($checkbox);

        $target = new ilRepositorySelector2InputGUI(
            $this->lng->txt('meta_oer_editorial_category'),
            'target',
            false,
            $form
        );

        $explorer = $target->getExplorerGUI();
        $explorer->setRootId(ROOT_FOLDER_ID);
        $explorer->setTypeWhiteList(['cat']);

        $target_ref_id = $this->publishing_settings->getContainerRefIDForEditorialStep();
        if ($target_ref_id) {
            $explorer->setPathOpen($target_ref_id);
            $target->setValue($target_ref_id);
        }

        $target->setRequired(true);
        $checkbox->addSubItem($target);
    }

    protected function addCopyrightSection(ilPropertyFormGUI $form): void
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('meta_oer_harvested_licences'));
        $form->addItem($header);

        $checkbox_group = new ilCheckboxGroupInputGUI(
            $this->lng->txt('meta_oer_copyright_selection'),
            'copyright'
        );
        $checkbox_group->setValue($this->publishing_settings->getCopyrightEntryIDsSelectedForPublishing());
        $checkbox_group->setInfo(
            $this->lng->txt('meta_oer_copyright_selection_info')
        );

        foreach ($this->copyright_repo->getAllEntries() as $copyright_entry) {
            if ($copyright_entry->isDefault()) {
                continue;
            }
            $copyright_checkbox = new ilCheckboxOption(
                $copyright_entry->title(),
                (string) $copyright_entry->id(),
                $copyright_entry->description()
            );
            $checkbox_group->addOption($copyright_checkbox);
        }
        $checkbox_group->setRequired(true);
        $form->addItem($checkbox_group);
    }

    protected function addObjectTypeSection(ilPropertyFormGUI $form): void
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('meta_oer_harvested_types'));
        $form->addItem($header);

        $checkbox_group = new ilCheckboxGroupInputGUI(
            $this->lng->txt('meta_oer_object_type_selection'),
            'object_type'
        );
        $checkbox_group->setRequired(true);
        $checkbox_group->setValue($this->publishing_settings->getObjectTypesSelectedForPublishing());

        foreach ($this->publishing_settings->getObjectTypesEligibleForPublishing() as $type) {
            $type_checkbox = new ilCheckboxOption(
                $this->lng->txt('objs_' . $type),
                $type
            );
            $checkbox_group->addOption($type_checkbox);
        }
        $form->addItem($checkbox_group);
    }

    protected function addOAIPMHSection(ilPropertyFormGUI $form): void
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('md_settings_publishing'));
        $form->addItem($header);

        $oai_check = new ilCheckboxInputGUI($this->lng->txt('md_oai_pmh_enabled'), 'oai_active');
        $oai_check->setChecked($this->MDSettings()->isOAIPMHActive());
        $oai_check->setValue('1');
        $oai_check->setInfo($this->lng->txt('md_oai_pmh_enabled_info'));
        $form->addItem($oai_check);

        $oai_repo_name = new ilTextInputGUI($this->lng->txt('md_oai_repository_name'), 'oai_repository_name');
        $oai_repo_name->setValue($this->MDSettings()->getOAIRepositoryName());
        $oai_repo_name->setInfo($this->lng->txt('md_oai_repository_name_info'));
        $oai_repo_name->setRequired(true);
        $oai_check->addSubItem($oai_repo_name);

        $oai_id_prefix = new ilTextInputGUI($this->lng->txt('md_oai_identifier_prefix'), 'oai_identifier_prefix');
        $oai_id_prefix->setValue($this->MDSettings()->getOAIIdentifierPrefix());
        $oai_id_prefix->setInfo($this->lng->txt('md_oai_identifier_prefix_info'));
        $oai_id_prefix->setRequired(true);
        $oai_check->addSubItem($oai_id_prefix);

        $oai_contact_mail = new ilTextInputGUI($this->lng->txt('md_oai_contact_mail'), 'oai_contact_mail');
        $oai_contact_mail->setValue($this->MDSettings()->getOAIContactMail());
        $oai_contact_mail->setRequired(true);
        $oai_check->addSubItem($oai_contact_mail);
    }

    protected function getConfigStatusMessageBox(): MessageBox
    {
        $enabled = $this->lng->txt('meta_publishing_config_enabled');
        $disabled = $this->lng->txt('meta_publishing_config_disabled');

        $text = $this->lng->txt('meta_publishing_config_status') . '<br/>' .
            sprintf(
                $this->lng->txt('meta_publishing_config_cp'),
                $this->MDSettings()->isCopyrightSelectionActive() ? $enabled : $disabled
            ) . '<br/>' .
            sprintf(
                $this->lng->txt('meta_publishing_config_harvester'),
                $this->isOERHarvesterActive() ? $enabled : $disabled
            );

        $url = $this->ctrl->getLinkTargetByClass(
            [ilAdministrationGUI::class, ilObjCronGUI::class],
        );
        $link = $this->ui_factory->link()->standard(
            $this->lng->txt('meta_publishing_config_cron_job_admin'),
            $url,
        );

        return $this->ui_factory->messageBox()->info($text)->withLinks([$link]);
    }

    protected function isOERHarvesterActive(): bool
    {
        return $this->cron_manager->isJobActive(ilCronOerHarvester::CRON_JOB_IDENTIFIER);
    }

    protected function MDSettings(): ilMDSettings
    {
        if (!isset($this->md_settings)) {
            $this->md_settings = ilMDSettings::_getInstance();
        }
        return $this->md_settings;
    }
}
