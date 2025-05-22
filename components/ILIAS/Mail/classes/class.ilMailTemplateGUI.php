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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

/**
 * @ilCtrl_isCalledBy ilMailTemplateGUI: ilObjMailGUI
 */
class ilMailTemplateGUI
{
    protected ilPropertyFormGUI $form;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilRbacSystem $rbacsystem;
    protected ilErrorHandling $error;
    protected ilMailTemplateService $service;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;

    public function __construct(
        protected ilObject $parent_object,
        ?ilGlobalTemplateInterface $tpl = null,
        ?ilCtrlInterface $ctrl = null,
        ?ilLanguage $lng = null,
        ?ilToolbarGUI $toolbar = null,
        ?ilRbacSystem $rbacsystem = null,
        ?ilErrorHandling $error = null,
        ?GlobalHttpState $http = null,
        ?Factory $ui_factory = null,
        ?Renderer $ui_renderer = null,
        ?ilMailTemplateService $template_service = null
    ) {
        global $DIC;
        $this->tpl = $tpl ?? $DIC->ui()->mainTemplate();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
        $this->lng = $lng ?? $DIC->language();
        $this->toolbar = $toolbar ?? $DIC->toolbar();
        $this->rbacsystem = $rbacsystem ?? $DIC->rbac()->system();
        $this->error = $error ?? $DIC['ilErr'];
        $this->http = $http ?? $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $ui_factory ?? $DIC->ui()->factory();
        $this->ui_renderer = $ui_renderer ?? $DIC->ui()->renderer();
        $this->service = $template_service ?? $DIC->mail()->textTemplates();

        $this->lng->loadLanguageModule('meta');
    }

    private function isEditingAllowed(): bool
    {
        return $this->rbacsystem->checkAccess('write', $this->parent_object->getRefId());
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if ($this->http->wrapper()->query()->has('mail_template_table_action')) {
            $cmd = $this->http->wrapper()->query()->retrieve(
                'mail_template_table_action',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (!$cmd || !method_exists($this, $cmd)) {
            $cmd = 'showTemplates';
        }
        $this->$cmd();
    }

    protected function showTemplates(): void
    {
        $contexts = ilMailTemplateContextService::getTemplateContexts();
        if (count($contexts) <= 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'));
        } elseif ($this->isEditingAllowed()) {
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('mail_new_template'),
                $this->ctrl->getLinkTarget($this, 'showInsertTemplateForm')
            ));
        }

        $tbl = new ilMailTemplateTable(
            $this->http->request(),
            $this->lng,
            $this->ui_factory,
            new DataFactory(),
            $this->service,
            !$this->isEditingAllowed()
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
    }

    protected function insertTemplate(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $form = $this->getTemplateForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showInsertTemplateForm($form);
            return;
        }

        $generic_context = new ilMailTemplateGenericContext();
        if ($form->getInput('context') === $generic_context->getId()) {
            $form->getItemByPostVar('context')->setAlert(
                $this->lng->txt('mail_template_no_valid_context')
            );
            $form->setValuesByPost();
            $this->showInsertTemplateForm($form);
            return;
        }

        try {
            $this->service->createNewTemplate(
                ilMailTemplateContextService::getTemplateContextById($form->getInput('context'))->getId(),
                $form->getInput('title'),
                $form->getInput('m_subject'),
                $form->getInput('m_message'),
                $form->getInput('lang')
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, 'showTemplates');
        } catch (\ILIAS\Mail\Templates\TemplateSubjectSyntaxException) {
            $form->getItemByPostVar('m_subject')->setAlert($this->lng->txt('mail_template_invalid_tpl_syntax'));
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        } catch (\ILIAS\Mail\Templates\TemplateMessageSyntaxException) {
            $form->getItemByPostVar('m_message')->setAlert($this->lng->txt('mail_template_invalid_tpl_syntax'));
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        } catch (Exception) {
            $form->getItemByPostVar('context')->setAlert(
                $this->lng->txt('mail_template_no_valid_context')
            );
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        }

        $form->setValuesByPost();
        $this->showInsertTemplateForm($form);
    }

    protected function showInsertTemplateForm(?ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getTemplateForm();
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function updateTemplate(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $template_id = 0;
        if ($this->http->wrapper()->post()->has('tpl_id')) {
            $template_id = $this->http->wrapper()->post()->retrieve('tpl_id', $this->refinery->kindlyTo()->int());
        }

        if (!is_numeric($template_id) || $template_id < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        try {
            $form = $this->getTemplateForm();
            if (!$form->checkInput()) {
                $form->setValuesByPost();
                $this->showEditTemplateForm($form);
                return;
            }

            $generic_context = new ilMailTemplateGenericContext();
            if ($form->getInput('context') === $generic_context->getId()) {
                $form->getItemByPostVar('context')->setAlert(
                    $this->lng->txt('mail_template_no_valid_context')
                );
                $form->setValuesByPost();
                $this->showEditTemplateForm($form);
                return;
            }

            try {
                $this->service->modifyExistingTemplate(
                    (int) $template_id,
                    ilMailTemplateContextService::getTemplateContextById($form->getInput('context'))->getId(),
                    $form->getInput('title'),
                    $form->getInput('m_subject'),
                    $form->getInput('m_message'),
                    $form->getInput('lang')
                );

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                $this->ctrl->redirect($this, 'showTemplates');
            } catch (OutOfBoundsException) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            } catch (\ILIAS\Mail\Templates\TemplateSubjectSyntaxException) {
                $form->getItemByPostVar('m_subject')->setAlert($this->lng->txt('mail_template_invalid_tpl_syntax'));
            } catch (\ILIAS\Mail\Templates\TemplateMessageSyntaxException) {
                $form->getItemByPostVar('m_message')->setAlert($this->lng->txt('mail_template_invalid_tpl_syntax'));
            } catch (Exception) {
                $form->getItemByPostVar('context')->setAlert(
                    $this->lng->txt('mail_template_no_valid_context')
                );
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            }

            $form->setValuesByPost();
            $this->showEditTemplateForm($form);
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
        }
    }

    protected function showEditTemplateForm(?ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $template_id = 0;
            if ($this->http->wrapper()->query()->has('mail_template_tpl_ids')) {
                $template_id = $this->http->wrapper()->query()->retrieve(
                    'mail_template_tpl_ids',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                )[0];
            }

            if (!is_numeric($template_id) || $template_id < 1) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
                $this->showTemplates();
                return;
            }

            try {
                $template = $this->service->loadTemplateForId((int) $template_id);
                $form = $this->getTemplateForm($template);
                $this->populateFormWithTemplate($form, $template);
            } catch (Exception) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
                $this->showTemplates();
                return;
            }
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function populateFormWithTemplate(ilPropertyFormGUI $form, ilMailTemplate $template): void
    {
        $form->setValuesByArray([
            'tpl_id' => $template->getTplId(),
            'title' => $template->getTitle(),
            'context' => $template->getContext(),
            'lang' => $template->getLang(),
            'm_subject' => $template->getSubject(),
            'm_message' => $template->getMessage(),
        ]);
    }

    protected function confirmDeleteTemplate(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $template_ids = [];
        if ($this->http->wrapper()->query()->has('mail_template_tpl_ids')) {
            $template_ids = $this->http->wrapper()->query()->retrieve(
                'mail_template_tpl_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
            if ($template_ids === ['ALL_OBJECTS']) {
                $template_ids = array_map(
                    static fn(array $template): int => (int) ($template['tpl_id'] ?? 0),
                    $this->service->listAllTemplatesAsArray()
                );
            } else {
                $template_ids = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                    ->transform($template_ids);
            }
        }

        if (count($template_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showTemplates();
            return;
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'deleteTemplate'));

        $confirm->setHeaderText($this->lng->txt('mail_tpl_sure_delete_entries'));
        if (count($template_ids) === 1) {
            $confirm->setHeaderText($this->lng->txt('mail_tpl_sure_delete_entry'));
        }

        $confirm->setConfirm($this->lng->txt('confirm'), 'deleteTemplate');
        $confirm->setCancel($this->lng->txt('cancel'), 'showTemplates');

        foreach ($template_ids as $template_id) {
            $template = $this->service->loadTemplateForId($template_id);
            $confirm->addItem('tpl_id[]', (string) $template_id, $template->getTitle());
        }

        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteTemplate(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $template_ids = [];
        if ($this->http->wrapper()->post()->has('tpl_id')) {
            $template_ids = $this->http->wrapper()->post()->retrieve(
                'tpl_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        if (count($template_ids) === 0) {
            $template_id = 0;
            if ($this->http->wrapper()->query()->has('tpl_id')) {
                $template_id = $this->http->wrapper()->query()->retrieve('tpl_id', $this->refinery->kindlyTo()->int());
            }
            $template_ids = [$template_id];
        }

        if (count($template_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showTemplates();
            return;
        }

        $this->service->deleteTemplatesByIds($template_ids);

        if (count($template_ids) === 1) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_tpl_deleted_s'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_tpl_deleted_p'), true);
        }
        $this->ctrl->redirect($this, 'showTemplates');
    }

    public function getAjaxPlaceholdersById(): void
    {
        $trigger_value = '';
        if ($this->http->wrapper()->query()->has('triggerValue')) {
            $trigger_value = $this->http->wrapper()->query()->retrieve(
                'triggerValue',
                $this->refinery->kindlyTo()->string()
            );
        }
        $context_id = ilUtil::stripSlashes($trigger_value);

        $placeholders = new ilManualPlaceholderInputGUI(
            $this->lng->txt('mail_form_placeholders_label'),
            'm_placeholders',
            'm_message'
        );
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        try {
            $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        } catch (Throwable) {
            $placeholders->setAdviseText($this->lng->txt('placeholders_advise'));
        }

        $context = ilMailTemplateContextService::getTemplateContextById($context_id);
        foreach ($context->getPlaceholders() as $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }

        $placeholders->render(true);
    }

    protected function getTemplateForm(?ilMailTemplate $template = null): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($this->lng->txt('mail_template_title'), 'title');
        $title->setRequired(true);
        $title->setDisabled(!$this->isEditingAllowed());
        $form->addItem($title);

        $context = new ilRadioGroupInputGUI($this->lng->txt('mail_template_context'), 'context');
        $context->setDisabled(!$this->isEditingAllowed());
        $contexts = ilMailTemplateContextService::getTemplateContexts();

        if (count($contexts) <= 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'), true);
            $this->ctrl->redirect($this, 'showTemplates');
        }

        $context_sort = [];
        $context_options = [];
        $generic_context = new ilMailTemplateGenericContext();
        foreach ($contexts as $ctx) {
            if ($ctx->getId() !== $generic_context->getId()) {
                $context_options[$ctx->getId()] = $ctx;
                $context_sort[$ctx->getId()] = $ctx->getTitle();
            }
        }
        asort($context_sort);
        $first = null;
        foreach (array_keys($context_sort) as $id) {
            $ctx = $context_options[$id];
            $option = new ilRadioOption($ctx->getTitle(), $ctx->getId());
            $option->setInfo($ctx->getDescription());
            $context->addOption($option);

            if (!$first) {
                $first = $id;
            }
        }
        $context->setValue($first);
        $context->setRequired(true);
        $form->addItem($context);

        $hidden = new ilHiddenInputGUI('lang');
        $hidden->setValue($this->lng->getLangKey());
        $form->addItem($hidden);

        $subject = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
        $subject->setDisabled(!$this->isEditingAllowed());
        $subject->setSize(50);
        $form->addItem($subject);

        $message = new ilTextAreaInputGUI($this->lng->txt('message'), 'm_message');
        $message->setDisabled(!$this->isEditingAllowed());
        $message->setRequired(true);
        $message->setCols(60);
        $message->setRows(10);
        $form->addItem($message);

        $placeholders = new ilManualPlaceholderInputGUI(
            $this->lng->txt('mail_form_placeholders_label'),
            'm_placeholders',
            'm_message'
        );
        $placeholders->setDisabled(!$this->isEditingAllowed());
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        try {
            $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        } catch (Throwable) {
            $placeholders->setAdviseText($this->lng->txt('placeholders_advise'));
        }
        $placeholders->supportsRerenderSignal(
            'context',
            $this->ctrl->getLinkTarget($this, 'getAjaxPlaceholdersById', '', true)
        );
        if ($template === null) {
            $context_id = $generic_context->getId();
        } else {
            $context_id = $template->getContext();
        }
        $context = ilMailTemplateContextService::getTemplateContextById($context_id);
        foreach ($context->getPlaceholders() as $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }
        $form->addItem($placeholders);
        if ($template instanceof ilMailTemplate && $template->getTplId() > 0) {
            $id = new ilHiddenInputGUI('tpl_id');
            $form->addItem($id);

            $form->setTitle($this->lng->txt('mail_edit_tpl'));
            $form->setFormAction($this->ctrl->getFormAction($this, 'updateTemplate'));

            if ($this->isEditingAllowed()) {
                $form->addCommandButton('updateTemplate', $this->lng->txt('save'));
            }
        } else {
            $form->setTitle($this->lng->txt('mail_create_tpl'));
            $form->setFormAction($this->ctrl->getFormAction($this, 'insertTemplate'));

            if ($this->isEditingAllowed()) {
                $form->addCommandButton('insertTemplate', $this->lng->txt('save'));
            }
        }

        if ($this->isEditingAllowed()) {
            $form->addCommandButton('showTemplates', $this->lng->txt('cancel'));
        } else {
            $form->addCommandButton('showTemplates', $this->lng->txt('back'));
        }

        return $form;
    }

    public function unsetAsContextDefault(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $template_id = 0;
        if ($this->http->wrapper()->query()->has('mail_template_tpl_ids')) {
            $template_id = $this->http->wrapper()->query()->retrieve(
                'mail_template_tpl_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            )[0];
        }

        if (!is_numeric($template_id) || $template_id < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        try {
            $template = $this->service->loadTemplateForId((int) $template_id);
            $this->service->unsetAsContextDefault($template);
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showTemplates');
    }

    public function setAsContextDefault(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $template_id = 0;
        if ($this->http->wrapper()->query()->has('mail_template_tpl_ids')) {
            $template_id = $this->http->wrapper()->query()->retrieve(
                'mail_template_tpl_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            )[0];
        }

        if (!is_numeric($template_id) || $template_id < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        try {
            $template = $this->service->loadTemplateForId((int) $template_id);
            $this->service->setAsContextDefault($template);
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showTemplates');
    }
}
