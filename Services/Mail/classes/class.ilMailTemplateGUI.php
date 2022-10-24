<?php

declare(strict_types=1);

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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilMailTemplateGUI
 * @author            Nadia Ahmad <nahmad@databay.de>
 * @author            Michael Jansen <mjansen@databay.de>
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
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;

    public function __construct(
        protected ilObject $parentObject,
        ilGlobalTemplateInterface $tpl = null,
        ilCtrlInterface $ctrl = null,
        ilLanguage $lng = null,
        ilToolbarGUI $toolbar = null,
        ilRbacSystem $rbacsystem = null,
        ilErrorHandling $error = null,
        GlobalHttpState $http = null,
        Factory $uiFactory = null,
        Renderer $uiRenderer = null,
        ilMailTemplateService $templateService = null
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
        $this->uiFactory = $uiFactory ?? $DIC->ui()->factory();
        $this->uiRenderer = $uiRenderer ?? $DIC->ui()->renderer();
        $this->service = $templateService ?? $DIC['mail.texttemplates.service'];

        $this->lng->loadLanguageModule('meta');
    }

    private function isEditingAllowed(): bool
    {
        return $this->rbacsystem->checkAccess('write', $this->parentObject->getRefId());
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
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
            $create_tpl_button = ilLinkButton::getInstance();
            $create_tpl_button->setCaption('mail_new_template');
            $create_tpl_button->setUrl($this->ctrl->getLinkTarget($this, 'showInsertTemplateForm'));
            $this->toolbar->addButtonInstance($create_tpl_button);
        }

        $tbl = new ilMailTemplateTableGUI(
            $this,
            'showTemplates',
            $this->uiFactory,
            $this->uiRenderer,
            !$this->isEditingAllowed()
        );
        $tbl->setData($this->service->listAllTemplatesAsArray());

        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * @throws ilMailException
     */
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
        } catch (Exception) {
            $form->getItemByPostVar('context')->setAlert(
                $this->lng->txt('mail_template_no_valid_context')
            );
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        }

        $form->setValuesByPost();
        $this->showInsertTemplateForm($form);
    }

    /**
     * @param ilPropertyFormGUI|null $form
     * @throws ilMailException
     */
    protected function showInsertTemplateForm(ilPropertyFormGUI $form = null): void
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

        $templateId = 0;
        if ($this->http->wrapper()->post()->has('tpl_id')) {
            $templateId = $this->http->wrapper()->post()->retrieve('tpl_id', $this->refinery->kindlyTo()->int());
        }

        if (!is_numeric($templateId) || $templateId < 1) {
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

            $genericContext = new ilMailTemplateGenericContext();
            if ($form->getInput('context') === $genericContext->getId()) {
                $form->getItemByPostVar('context')->setAlert(
                    $this->lng->txt('mail_template_no_valid_context')
                );
                $form->setValuesByPost();
                $this->showEditTemplateForm($form);
                return;
            }

            try {
                $this->service->modifyExistingTemplate(
                    (int) $templateId,
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

    protected function showEditTemplateForm(ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $templateId = 0;
            if ($this->http->wrapper()->query()->has('tpl_id')) {
                $templateId = $this->http->wrapper()->query()->retrieve(
                    'tpl_id',
                    $this->refinery->kindlyTo()->int()
                );
            }

            if (!is_numeric($templateId) || $templateId < 1) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
                $this->showTemplates();
                return;
            }

            try {
                $template = $this->service->loadTemplateForId((int) $templateId);
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

        $templateIds = [];
        if ($this->http->wrapper()->post()->has('tpl_id')) {
            $templateIds = $this->http->wrapper()->post()->retrieve(
                'tpl_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        if (count($templateIds) === 0 && $this->http->wrapper()->query()->has('tpl_id')) {
            $templateIds = [$this->http->wrapper()->query()->retrieve(
                'tpl_id',
                $this->refinery->kindlyTo()->int()
            )];
        }

        if (0 === count($templateIds)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showTemplates();
            return;
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'deleteTemplate'));

        $confirm->setHeaderText($this->lng->txt('mail_tpl_sure_delete_entry'));
        if (1 === count($templateIds)) {
            $confirm->setHeaderText($this->lng->txt('mail_tpl_sure_delete_entries'));
        }

        $confirm->setConfirm($this->lng->txt('confirm'), 'deleteTemplate');
        $confirm->setCancel($this->lng->txt('cancel'), 'showTemplates');

        foreach ($templateIds as $templateId) {
            $template = $this->service->loadTemplateForId($templateId);
            $confirm->addItem('tpl_id[]', (string) $templateId, $template->getTitle());
        }

        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteTemplate(): void
    {
        if (!$this->isEditingAllowed()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $templateIds = [];
        if ($this->http->wrapper()->post()->has('tpl_id')) {
            $templateIds = $this->http->wrapper()->post()->retrieve(
                'tpl_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        if (count($templateIds) === 0) {
            $templateId = 0;
            if ($this->http->wrapper()->query()->has('tpl_id')) {
                $templateId = $this->http->wrapper()->query()->retrieve('tpl_id', $this->refinery->kindlyTo()->int());
            }
            $templateIds = [$templateId];
        }

        if (0 === count($templateIds)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showTemplates();
            return;
        }

        $this->service->deleteTemplatesByIds($templateIds);

        if (1 === count($templateIds)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_tpl_deleted_s'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_tpl_deleted_p'), true);
        }
        $this->ctrl->redirect($this, 'showTemplates');
    }

    /**
     * @throws ilMailException
     */
    public function getAjaxPlaceholdersById(): void
    {
        $triggerValue = '';
        if ($this->http->wrapper()->query()->has('triggerValue')) {
            $triggerValue = $this->http->wrapper()->query()->retrieve(
                'triggerValue',
                $this->refinery->kindlyTo()->string()
            );
        }
        $contextId = ilUtil::stripSlashes($triggerValue);

        $placeholders = new ilManualPlaceholderInputGUI(
            $this->lng->txt('mail_form_placeholders_label'),
            'm_message'
        );
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));

        $context = ilMailTemplateContextService::getTemplateContextById($contextId);
        foreach ($context->getPlaceholders() as $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }

        $placeholders->render(true);
    }

    /**
     * @param ilMailTemplate|null $template
     * @throws ilMailException
     */
    protected function getTemplateForm(ilMailTemplate $template = null): ilPropertyFormGUI
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
            'm_message'
        );
        $placeholders->setDisabled(!$this->isEditingAllowed());
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
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

        $templateId = 0;
        if ($this->http->wrapper()->query()->has('tpl_id')) {
            $templateId = $this->http->wrapper()->query()->retrieve('tpl_id', $this->refinery->kindlyTo()->int());
        }

        if (!is_numeric($templateId) || $templateId < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        try {
            $template = $this->service->loadTemplateForId((int) $templateId);
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

        $templateId = 0;
        if ($this->http->wrapper()->query()->has('tpl_id')) {
            $templateId = $this->http->wrapper()->query()->retrieve('tpl_id', $this->refinery->kindlyTo()->int());
        }

        if (!is_numeric($templateId) || $templateId < 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        try {
            $template = $this->service->loadTemplateForId((int) $templateId);
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
