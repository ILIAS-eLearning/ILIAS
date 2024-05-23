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
use ILIAS\UI\Component\Input\Container\Form\Standard;

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
    protected \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface $request;

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
        $this->service = $templateService ?? $DIC->mail()->textTemplates();
        $this->request = $DIC->http()->request();

        $this->lng->loadLanguageModule('meta');
    }

    private function isEditingAllowed(): bool
    {
        return $this->rbacsystem->checkAccess('write', $this->parentObject->getRefId());
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd || !method_exists($this, $cmd)) {
                    $cmd = 'showTemplates';
                }

                $this->$cmd();
                break;
        }
    }

    protected function showContextForm(): void
    {
        $form = $this->buildContextForm();
        $this->tpl->setContent($this->uiRenderer->render($form));
    }

    protected function showTemplates(): void
    {
        $contexts = ilMailTemplateContextService::getTemplateContexts();
        if (count($contexts) <= 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'));
        } elseif ($this->isEditingAllowed()) {
            $this->toolbar->addComponent($this->uiFactory->button()->standard(
                $this->lng->txt('mail_new_template'),
                $this->ctrl->getLinkTarget($this, 'showContextForm')
            ));
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

        $context_id = null;
        $ctx_id = $this->request->getQueryParams()['ctx_id'] ?? '';
        if ($ctx_id !== '') {
            $context_id = $ctx_id;
        }
        if ($context_id === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'));
            $this->showTemplates();
            return;
        }
        $context = ilMailTemplateContextService::getTemplateContextById($context_id);

        $form = $this->getTemplateForm(null, null, $context)->withRequest($this->request);
        $result = $form->getInputGroup()->getContent();
        if (!$result->isOK()) {
            $this->tpl->setContent($this->uiRenderer->render($form));
            return;
        }
        $value = $result->value();

        $generic_context = new ilMailTemplateGenericContext();
        if ($value["context"] === $generic_context->getId()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_valid_context'));
            $this->tpl->setContent($this->uiRenderer->render($form));
            return;
        }

        $template = $this->service->createNewTemplate(
            ilMailTemplateContextService::getTemplateContextById($value["context"])->getId(),
            $value["title"],
            $value["m_subject"],
            $value["m_message"],
            $value["lang"]
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showTemplates');
    }

    /**
     * @throws ilMailException
     */
    protected function showInsertTemplateForm(): void
    {
        $context_id = '';
        $result = $this->buildContextForm()
                       ->withRequest($this->request)
                       ->getInputGroup()
                       ->getContent();
        if ($result->isOK()) {
            $values = $result->value();
            $context_id = $values["context"];
        }
        if ($context_id === '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'));
            $this->showContextForm();
            return;
        }
        $tpl_context = ilMailTemplateContextService::getTemplateContextById($context_id);

        $form = $this->getTemplateForm(
            null,
            null,
            $tpl_context,
            'insertTemplate'
        );
        $this->tpl->setContent($this->uiRenderer->render($form));
    }

    protected function updateTemplate(): void
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

        $context_id = null;
        $ctx_id = $this->request->getQueryParams()['ctx_id'] ?? '';
        if ($ctx_id !== '') {
            $context_id = $ctx_id;
        }

        if ($context_id === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'));
            $this->showTemplates();
            return;
        }
        $context = ilMailTemplateContextService::getTemplateContextById($context_id);
        $form = $this->getTemplateForm(null, null, $context)->withRequest($this->request);
        $result = $form->getInputGroup()->getContent();

        if (!$result->isOK()) {
            $this->showEditTemplateForm($form);
            return;
        }

        $value = $result->value();
        $genericContext = new ilMailTemplateGenericContext();
        if ($value["context"] === $genericContext->getId()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_valid_context'));
            $this->showEditTemplateForm($form);
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showTemplates');
        $this->showEditTemplateForm($form);
    }

    protected function showEditTemplateForm(?Standard $form = null): void
    {
        if ($form === null) {
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

            $template = $this->service->loadTemplateForId((int) $templateId);
            $global_language = $this->lng->getDefaultLanguage();

            if ($template->getTplId() === 0) {
                $template = new ilMailTemplate(
                    [
                        "tpl_id" => $templateId,
                        "lang" => $global_language,
                        "title" => "",
                        "context" => "",
                        "m_subject" => "",
                        "m_message" => "",
                        "is_default" => 0
                    ]
                );
            }

            $original_template = new ilMailTemplate();
            if ($global_language != $global_language) {
                $original_template = $this->service->loadTemplateForId((int) $templateId);
            }

            $form = $this->getTemplateForm(
                $template,
                $original_template,
                null,
                'updateTemplate'
            );
        }

        $this->tpl->setContent($this->uiRenderer->render($form));
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
        $this->ctrl->setParameter($this, 'mtlanguage', $this->lng->getDefaultLanguage());
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'deleteTemplate'));

        $confirm->setHeaderText($this->lng->txt('mail_tpl_sure_delete_entries'));
        if (1 === count($templateIds)) {
            $confirm->setHeaderText($this->lng->txt('mail_tpl_sure_delete_entry'));
        }

        $confirm->setConfirm($this->lng->txt('confirm'), 'deleteTemplate');
        $confirm->setCancel($this->lng->txt('cancel'), 'showTemplates');

        foreach ($templateIds as $templateId) {
            $template = $this->service->loadTemplateForId((int) $templateId);
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

        if (1 === count($templateIds)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_tpl_deleted_s'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_tpl_deleted_p'), true);
        }
        $this->ctrl->redirect($this, 'showTemplates');
    }

    protected function buildContextForm(): Standard
    {
        $contexts = ilMailTemplateContextService::getTemplateContexts();
        if (count($contexts) <= 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'), true);
            $this->ctrl->redirect($this, 'showTemplates');
        }
        $context_sort = [];
        $generic_context = new ilMailTemplateGenericContext();
        foreach ($contexts as $ctx) {
            if ($ctx->getId() != $generic_context->getId()) {
                $context_sort[$ctx->getId()] = $ctx;
            }
        }
        usort(
            $context_sort,
            fn($a, $b) => strcmp($a->getTitle(), $b->getTitle())
        );

        $context = $this->uiFactory->input()->field()->radio($this->lng->txt('mail_template_context'))
                                   ->withRequired(true)
                                   ->withDisabled(!$this->isEditingAllowed());
        $first = null;
        foreach ($context_sort as $ctx) {
            $context = $context->withOption(
                $ctx->getId(),
                $ctx->getTitle(),
                $ctx->getDescription()
            );
            if ($first === null) {
                $first = $ctx->getId();
            }
        }
        $context = $context->withValue($first ?? '');
        $form = $this->uiFactory->input()->container()->form()->standard(
            $this->ctrl->getFormaction($this, 'showInsertTemplateForm'),
            [
                'context' => $context
            ]
        )->withSubmitLabel($this->lng->txt('btn_next'));
        return $form;
    }

    /**
     * @throws ilMailException
     */
    protected function getTemplateForm(
        ?ilMailTemplate $template = null,
        ?ilMailTemplate $original_template = null,
        ?ilMailTemplateContext $context = null,
        string $action = 'insertTemplate'
    ): Standard {
        if ($template === null && $context === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_no_context_available'));
            $this->showContextForm();
            exit;
        }

        if ($template !== null) {
            $context = ilMailTemplateContextService::getTemplateContextById($template->getContext());
        }

        $this->ctrl->setParameter($this, 'ctx_id', $context->getId());
        if ($template !== null) {
            $this->ctrl->setParameter($this, 'tpl_id', $template->getTplId());
        }
        $form = $this->uiFactory->input()->container()->form()->standard(
            $this->ctrl->getFormaction($this, $action),
            $this->buildFormElements(
                $context,
                $template,
                $original_template
            )
        );
        return $form;
    }

    protected function buildFormElements(
        ilMailTemplateContext $tpl_context,
        ilMailTemplate $template = null,
        ilMailTemplate $original_template = null
    ): array {
        $cmd = $this->request->getQueryParams()['cmd'] ?? '';
        $fallbackCmd = $this->request->getQueryParams()['fallbackCmd'] ?? '';
        if ($cmd === 'post' && $fallbackCmd !== '') {
            $cmd = $fallbackCmd;
        }

        $global_language = $this->lng->getDefaultLanguage();

        // Title
        $title = $this->uiFactory->input()->field()->text($this->lng->txt('mail_template_title'))
                                 ->withRequired(true)
                                 ->withDisabled(!$this->isEditingAllowed());

        $title = $title->withMaxLength(1024);

        if ($this->checkParameters($global_language, $cmd)) {
            $title = $title->withByline($this->lng->txt('translation') . ': ' . $original_template->getTitle());
        }

        if ($template !== null) {
            $title = $title->withValue($template->getTitle());
        }

        // Mail context
        $context = $this->uiFactory->input()->field()->radio($this->lng->txt('mail_template_context'));
        $context = $context->withOption(
            $tpl_context->getId(),
            $tpl_context->getTitle(),
            $tpl_context->getDescription()
        );
        $context = $context->withValue($tpl_context->getId());

        // Subject
        $subject = $this->uiFactory->input()->field()->text($this->lng->txt('subject'))
                                   ->withDisabled(!$this->isEditingAllowed());

        if ($template !== null) {
            $subject = $subject->withValue($template->getSubject());
        }

        // Message
        $md_renderer = new ilUIMarkdownPreviewGUI();
        $message = $this->uiFactory->input()->field()->markdown(
            $md_renderer,
            $this->lng->txt('message')
        )
                                   ->withDisabled(!$this->isEditingAllowed())
                                   ->withRequired(true);

        if ($this->checkParameters($global_language, $cmd)) {
            $message = $message->withByline(
                $this->lng->txt('translation') . ': '
                . str_replace(
                    '{{',
                    '&lcub;&lcub;',
                    str_replace('}}', '&rcub;&rcub;', $original_template->getMessage())
                )
            );
        }

        $entries = [];
        foreach ($tpl_context->getPlaceholders() as $value) {
            $entries[$value['placeholder']] = $value['label'];
        }
        $message = $message
            ->withMustachable($entries)
            ->withPlaceholderAdvice(
                $this->lng->txt('mail_nacc_use_placeholder') . '<br />'
                . sprintf($this->lng->txt('placeholders_advise'), '<br />')
            );

        if ($template !== null) {
            $message = $message->withValue($template->getMessage());
        }

        return [
            'title' => $title,
            'context' => $context,
            'lang' => $global_language,
            'm_subject' => $subject,
            'm_message' => $message
        ];
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
            'm_placeholders',
            'm_message'
        );
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        try {
            $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        } catch (Throwable $e) {
            $placeholders->setAdviseText($this->lng->txt('placeholders_advise'));
        }

        $context = ilMailTemplateContextService::getTemplateContextById($contextId);
        foreach ($context->getPlaceholders() as $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }

        $placeholders->render(true);
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

        $template = $this->service->loadTemplateForId((int) $templateId);
        $this->service->unsetAsContextDefault($template);
        $this->tpl->setOnScreenMessage($this->lng->txt('saved_successfully'), true);
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

        $template = $this->service->loadTemplateForId((int) $templateId);
        $this->service->setAsContextDefault($template);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showTemplates');
    }

    /**
     * Show a preview of the mail template
     *
     * @return void
     */
    protected function showPreview()
    {
        $get = $_GET;

        if (!isset($get['tpl_id']) || !strlen($get['tpl_id'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_template_missing_id'));
            $this->showTemplates();
            return;
        }

        $template = $this->service->loadTemplateForId((int) $get['tpl_id']);
        $gui = new ilMailPreviewGUI($template, new ilPreviewFactory());

        $this->tpl->setContent($gui->getHTML());
    }

    protected function getLanguages(): array
    {
        $installed_languages = ilLanguage::_getInstalledLanguages();
        $languages = [];
        foreach ($installed_languages as $language) {
            $languages[] = [
                "language" => $this->lng->txt('meta_l_' . $language),
                "short_lang" => $language
            ];
        }

        return $languages;
    }

    protected function getLanguageParameter(): string
    {
        return $this->request->getParsedBody()['lang'];
    }

    protected function checkParameters(string $global_language, string $cmd): bool
    {
        return $global_language && $cmd === 'showEditTemplateForm';
    }
}
