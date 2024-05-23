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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/**
 * @ilCtrl_Calls ilMailFormGUI: ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailingListsGUI, ilMailFormUploadHandlerGUI
 */
class ilMailFormGUI
{
    final public const MAIL_FORM_TYPE_ATTACH = 'attach';
    final public const MAIL_FORM_TYPE_SEARCH_RESULT = 'search_res';
    final public const MAIL_FORM_TYPE_NEW = 'new';
    final public const MAIL_FORM_TYPE_ROLE = 'role';
    final public const MAIL_FORM_TYPE_REPLY = 'reply';
    final public const MAIL_FORM_TYPE_ADDRESS = 'address';
    final public const MAIL_FORM_TYPE_FORWARD = 'forward';
    final public const MAIL_FORM_TYPE_DRAFT = 'draft';

    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private readonly ilTabsGUI $tabs;
    private readonly ilToolbarGUI $toolbar;
    private readonly ilFormatMail $umail;
    private readonly ilMailbox $mbox;
    private readonly ilFileDataMail $mfile;
    private readonly GlobalHttpState $http;
    private readonly Refinery $refinery;
    private ?array $requestAttachments = null;
    protected ilMailTemplateService $templateService;
    private readonly ilMailBodyPurifier $purifier;
    private string $mail_form_type = '';
    private readonly Factory $ui_factory;
    protected Renderer $ui_renderer;
    /**
     * @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface $request
     */
    protected $request;
    protected ArrayBasedRequestWrapper $post;
    protected ArrayBasedRequestWrapper $query;
    protected ilMailFormUploadHandlerGUI $upload_handler;
    protected ilFileDataMail $fdm;
    protected ILIAS\ResourceStorage\Services $storage;

    public function __construct(
        ilMailTemplateService $templateService = null,
        ilMailBodyPurifier $bodyPurifier = null
    ) {
        global $DIC;

        $this->templateService = $templateService ?? $DIC->mail()->textTemplates();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->umail = new ilFormatMail($this->user->getId());
        $this->mfile = new ilFileDataMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());
        $this->purifier = $bodyPurifier ?? new ilMailBodyPurifier();
        $this->ui_factory = $ui_factory ?? $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->ui_renderer = $ui_renderer ?? $DIC->ui()->renderer();
        $this->post = new ArrayBasedRequestWrapper($this->request->getParsedBody());
        $this->query = new ArrayBasedRequestWrapper($this->request->getQueryParams());
        $this->upload_handler = new ilMailFormUploadHandlerGUI();
        $this->storage = $DIC->resourceStorage();
        $this->fdm = new ilFileDataMail($this->user->getId());

        $requestMailObjId = $this->getBodyParam(
            'mobj_id',
            $this->refinery->kindlyTo()->int(),
            $this->getQueryParam(
                'mobj_id',
                $this->refinery->kindlyTo()->int(),
                0
            )
        );

        if (0 === $requestMailObjId) {
            $requestMailObjId = $this->mbox->getInboxFolder();
        }

        $this->ctrl->setParameter($this, 'mobj_id', $requestMailObjId);
    }

    private function getQueryParam(string $name, Transformation $trafo, $default = null)
    {
        if ($this->http->wrapper()->query()->has($name)) {
            return $this->http->wrapper()->query()->retrieve(
                $name,
                $trafo
            );
        }

        return $default;
    }

    private function getBodyParam(string $name, Transformation $trafo, $default = null)
    {
        if ($this->http->wrapper()->post()->has($name)) {
            return $this->http->wrapper()->post()->retrieve(
                $name,
                $trafo
            );
        }

        return $default;
    }

    public function executeCommand(): void
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch (strtolower($forward_class)) {
            case strtolower(ilMailAttachmentGUI::class):
                $this->ctrl->setReturn($this, 'returnFromAttachments');
                $gui = new ilMailAttachmentGUI();
                $gui->consume();
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilMailSearchGUI::class):
                $this->ctrl->setReturn($this, 'searchResults');
                $this->ctrl->forwardCommand(new ilMailSearchGUI());
                break;

            case strtolower(ilMailSearchCoursesGUI::class):
                $this->ctrl->setReturn($this, 'searchResults');
                $this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
                break;

            case strtolower(ilMailingListsGUI::class):
                $this->ctrl->setReturn($this, 'searchResults');
                $this->ctrl->forwardCommand(new ilMailingListsGUI());
                break;

            case strtolower(ilMailSearchGroupsGUI::class):
                $this->ctrl->setReturn($this, 'searchResults');
                $this->ctrl->forwardCommand(new ilMailSearchGroupsGUI());
                break;

            case strtolower(ilMailFormUploadHandlerGUI::class):
                $this->ctrl->forwardCommand($this->upload_handler);
                break;

            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = 'showForm';
                }

                $this->$cmd();
                break;
        }
    }

    /**
     * @param string[] $files
     * @return string[]
     */
    protected function decodeAttachmentFiles(array $files): array
    {
        $decodedFiles = [];

        foreach ($files as $value) {
            if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . urldecode($value))) {
                $decodedFiles[] = urldecode($value);
            }
        }

        return $decodedFiles;
    }

    public function sendMessage(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $result = $form->getInputGroup()->getContent();

        if (!$result->isOK()) {
            $this->showForm($form);
            return;
        }

        $value = $result->value()[0];

        $files = [];
        if (count($value["attachments"]) > 0) {
            $files = $this->handleAttachments($value["attachments"]);
        }

        $mailer = $this->umail
            ->withContextId(ilMailFormCall::getContextId() ?: '')
            ->withContextParameters(ilMailFormCall::getContextParameters());

        $mailer->setSaveInSentbox(true);

        if ($errors = $mailer->enqueue(
            ilUtil::securePlainString($value['rcp_to']),
            ilUtil::securePlainString($value['rcp_cc']),
            ilUtil::securePlainString($value['rcp_bcc']),
            ilUtil::securePlainString($value['m_subject']),
            $value['m_message'],
            $files,
            $value['use_placeholders']
        )
        ) {
            $_POST['attachments'] = $files;
            $this->showSubmissionErrors($errors);
        } else {
            $mailer->persistToStage(
                $this->user->getId(),
                [],
                '',
                '',
                '',
                '',
                ''
            );

            $this->ctrl->setParameterByClass(ilMailGUI::class, 'type', 'message_sent');

            if (ilMailFormCall::isRefererStored()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_message_send'), true);
                $this->ctrl->redirectToURL(ilMailFormCall::getRefererRedirectUrl());
            } else {
                $this->ctrl->redirectByClass(ilMailGUI::class);
            }
        }

        $this->showForm();
    }

    public function saveDraft(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $result = $form->getInputGroup()->getContent();

        if (!$result->isOK()) {
            $this->showForm($form);
            return;
        }

        $value = $result->value()[0];

        if ($value['m_subject'] === '') {
            $value['m_subject'] = $this->lng->txt('mail_no_subject');
        }

        $files = [];
        if (count($value["attachments"]) > 0) {
            $files = $this->handleAttachments($value["attachments"]);
        }

        $draftFolderId = $this->mbox->getDraftsFolder();

        if ($errors = $this->umail->validateRecipients(
            ilUtil::securePlainString($value['rcp_to']),
            ilUtil::securePlainString($value['rcp_cc']),
            ilUtil::securePlainString($value['rcp_bcc'])
        )) {
            $this->showSubmissionErrors($errors);
            $this->showForm($form);
            return;
        }

        if (ilSession::get('draft')) {
            $draftId = (int) ilSession::get('draft');
            ilSession::clear('draft');
        } else {
            $draftId = $this->umail->getNewDraftId($draftFolderId);
        }

        $this->umail->updateDraft(
            $draftFolderId,
            $files,
            ilUtil::securePlainString($value['rcp_to']),
            ilUtil::securePlainString($value['rcp_cc']),
            ilUtil::securePlainString($value['rcp_bcc']),
            ilUtil::securePlainString($value['m_subject']),
            $value['m_message'],
            $draftId,
            $value['use_placeholders'],
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_saved'), true);

        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        } else {
            $this->ctrl->redirectByClass([ilMailGUI::class, ilMailFolderGUI::class]);
        }

        $this->showForm();
    }

    public function searchUsers(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        $form = new ilPropertyFormGUI();
        $form->setId('search_rcp');
        $form->setTitle($this->lng->txt('search_recipients'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'search'));

        $inp = new ilTextInputGUI($this->lng->txt('search_for'), 'search');
        $inp->setSize(30);
        $dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true);
        $inp->setDataSource($dsDataLink);

        $searchQuery = trim((string) ilSession::get('mail_search_search'));
        if ($searchQuery !== '') {
            $inp->setValue(ilLegacyFormElementsUtil::prepareFormOutput($searchQuery, true));
        }
        $form->addItem($inp);

        $form->addCommandButton('search', $this->lng->txt('search'));
        $form->addCommandButton('cancelSearch', $this->lng->txt('cancel'));

        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    public function searchCoursesTo(): void
    {
        $this->saveMailBeforeSearch();

        if (ilSession::get('search_crs')) {
            $this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'cmd', 'showMembers');
        }

        $this->ctrl->setParameterByClass(ilMailSearchCoursesGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailSearchCoursesGUI::class);
    }

    public function searchGroupsTo(): void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass(ilMailSearchGroupsGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailSearchGroupsGUI::class);
    }

    public function search(): void
    {
        ilSession::set(
            'mail_search_search',
            ilUtil::securePlainString($this->getBodyParam('search', $this->refinery->kindlyTo()->string(), ''))
        );

        if (trim(ilSession::get('mail_search_search')) === '') {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("mail_insert_query"));
            $this->searchUsers();
        } elseif (strlen(trim(ilSession::get('mail_search_search'))) < 3) {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_minimum_three'));
            $this->searchUsers();
        } else {
            $this->ctrl->setParameterByClass(
                ilMailSearchGUI::class,
                'search',
                urlencode(ilSession::get('mail_search_search'))
            );
            $this->ctrl->redirectByClass(ilMailSearchGUI::class);
        }
    }

    public function cancelSearch(): void
    {
        ilSession::clear('mail_search');
        $this->searchResults();
    }

    public function returnFromAttachments(): void
    {
        $this->mail_form_type = self::MAIL_FORM_TYPE_ATTACH;
        $this->showForm();
    }

    public function searchResults(): void
    {
        $this->mail_form_type = self::MAIL_FORM_TYPE_SEARCH_RESULT;
        $this->showForm();
    }

    public function mailUser(): void
    {
        $this->mail_form_type = self::MAIL_FORM_TYPE_NEW;
        $this->showForm();
    }

    public function mailRole(): void
    {
        $this->mail_form_type = self::MAIL_FORM_TYPE_ROLE;
        $this->showForm();
    }

    public function replyMail(): void
    {
        $this->mail_form_type = self::MAIL_FORM_TYPE_REPLY;
        $this->showForm();
    }

    protected function getTemplateDataById(): void
    {
        if (!$this->http->wrapper()->query()->has('template_id')) {
            $this->http->close();
        }

        try {
            $template = $this->templateService->loadTemplateForId(
                $this->http->wrapper()->query()->retrieve('template_id', $this->refinery->kindlyTo()->int())
            );
            ilMailTemplateContextService::getTemplateContextById($template->getContext());

            $this->http->saveResponse(
                $this->http->response()
                    ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                    ->withBody(Streams::ofString(json_encode([
                        'm_subject' => $template->getSubject(),
                        'm_message' => $this->umail->appendSignature($template->getMessage()),
                    ], JSON_THROW_ON_ERROR)))
            );
        } catch (Exception) {
        }

        $this->http->sendResponse();
        $this->http->close();
    }

    public function showForm(?Form $form = null): void
    {
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.mail_new.html',
            'components/ILIAS/Mail'
        );
        $this->tpl->setTitle($this->lng->txt('mail'));

        $this->lng->loadLanguageModule('crs');

        if (ilMailFormCall::isRefererStored()) {
            $this->tabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTarget($this, 'cancelMail')
            );
        }

        $mailData = [];
        $mailData['rcp_to'] = '';
        $mailData['rcp_cc'] = '';
        $mailData['rcp_bcc'] = '';
        $mailData['attachments'] = [];
        $mailData["m_subject"] = '';
        $mailData["m_message"] = '';

        $mailId = $this->getQueryParam('mail_id', $this->refinery->kindlyTo()->int(), 0);
        $type = $this->getQueryParam('type', $this->refinery->kindlyTo()->string(), '');
        if ($this->mail_form_type !== '') {
            $type = $this->mail_form_type;
        }

        switch ($type) {
            case self::MAIL_FORM_TYPE_REPLY:
                $mailData = $this->umail->getMail($mailId);

                $mailData['m_subject'] = $this->umail->formatReplySubject($mailData['m_subject'] ?? '');
                $mailData['m_message'] = $this->umail->prependSignature(
                    $this->umail->formatReplyMessage($mailData['m_message'] ?? '')
                );
                $mailData['attachments'] = [];
                $mailData['rcp_cc'] = '';
                $mailData['rcp_to'] = $this->umail->formatReplyRecipient();
                break;

            case self::MAIL_FORM_TYPE_SEARCH_RESULT:
                $mailData = $this->umail->retrieveFromStage();

                if (ilSession::get('mail_search_results_to')) {
                    $mailData = $this->umail->appendSearchResult(
                        $this->refinery->kindlyTo()->listOf(
                            $this->refinery->kindlyTo()->string()
                        )->transform(ilSession::get('mail_search_results_to')),
                        'to'
                    );
                }
                if (ilSession::get('mail_search_results_cc')) {
                    $mailData = $this->umail->appendSearchResult(
                        $this->refinery->kindlyTo()->listOf(
                            $this->refinery->kindlyTo()->string()
                        )->transform(ilSession::get('mail_search_results_cc')),
                        'cc'
                    );
                }
                if (ilSession::get('mail_search_results_bcc')) {
                    $mailData = $this->umail->appendSearchResult(
                        $this->refinery->kindlyTo()->listOf(
                            $this->refinery->kindlyTo()->string()
                        )->transform(ilSession::get('mail_search_results_bcc')),
                        'bc'
                    );
                }

                ilSession::clear('mail_search_results_to');
                ilSession::clear('mail_search_results_cc');
                ilSession::clear('mail_search_results_bcc');
                break;

            case self::MAIL_FORM_TYPE_DRAFT:
                ilSession::set('draft', $mailId);
                $mailData = $this->umail->getMail($mailId);
                ilMailFormCall::setContextId($mailData['tpl_ctx_id']);
                ilMailFormCall::setContextParameters($mailData['tpl_ctx_params']);
                break;

            case self::MAIL_FORM_TYPE_FORWARD:
                $mailData = $this->umail->getMail($mailId);
                $mailData['rcp_to'] = $mailData['rcp_cc'] = $mailData['rcp_bcc'] = '';
                $mailData['m_subject'] = $this->umail->formatForwardSubject($mailData['m_subject'] ?? '');
                $mailData['m_message'] = $this->umail->prependSignature($mailData['m_message'] ?? '');
                if (is_array($mailData['attachments']) && count($mailData['attachments']) && $error = $this->mfile->adoptAttachments(
                    $mailData['attachments'],
                    $mailId
                )) {
                    $this->tpl->setOnScreenMessage('info', $error);
                }
                break;

            case self::MAIL_FORM_TYPE_NEW:
                // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                $to = ilUtil::securePlainString($this->getQueryParam('rcp_to', $this->refinery->kindlyTo()->string(), ''));
                if ($to === '' && ilSession::get('rcp_to')) {
                    $to = ilSession::get('rcp_to');
                }
                $mailData['rcp_to'] = $to;

                $cc = ilUtil::securePlainString($this->getQueryParam('rcp_cc', $this->refinery->kindlyTo()->string(), ''));
                if ($cc === '' && ilSession::get('rcp_cc')) {
                    $cc = ilSession::get('rcp_cc');
                }
                $mailData['rcp_cc'] = $cc;

                $bcc = ilUtil::securePlainString($this->getQueryParam('rcp_bcc', $this->refinery->kindlyTo()->string(), ''));
                if ($bcc === '' && ilSession::get('rcp_bcc')) {
                    $bcc = ilSession::get('rcp_bcc');
                }
                $mailData['rcp_bcc'] = $bcc;

                $mailData['m_message'] = '';
                if (($sig = ilMailFormCall::getSignature()) !== '') {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13)
                        . chr(10)
                        . chr(13)
                        . chr(10);
                }
                $mailData['m_message'] .= $this->umail->appendSignature('');

                ilSession::set('rcp_to', '');
                ilSession::set('rcp_cc', '');
                ilSession::set('rcp_bcc', '');
                break;

            case self::MAIL_FORM_TYPE_ROLE:
                $roles = [];
                if ($this->http->wrapper()->post()->has('roles')) {
                    $roles = $this->http->wrapper()->post()->retrieve(
                        'roles',
                        $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
                    );
                } elseif (is_array(ilSession::get('mail_roles'))) {
                    $roles = $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->string()
                    )->transform(ilSession::get('mail_roles'));
                }

                // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                $mailData['rcp_to'] = ilUtil::securePlainString(
                    implode(',', $roles)
                );

                $mailData['m_message'] = '';
                if (($sig = ilMailFormCall::getSignature()) !== '') {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13)
                        . chr(10)
                        . chr(13)
                        . chr(10);
                }

                $additionalMessageText = '';
                if ($this->http->wrapper()->post()->has('additional_message_text')) {
                    $additionalMessageText = ilUtil::securePlainString($this->http->wrapper()->post()->retrieve(
                        'additional_message_text',
                        $this->refinery->kindlyTo()->string()
                    ));
                }

                $mailData['m_message'] .= $additionalMessageText
                    . chr(13)
                    . chr(10)
                    . $this->umail->appendSignature('');
                ilSession::set('mail_roles', []);
                break;

            case self::MAIL_FORM_TYPE_ADDRESS:
                $rcp = '';
                if ($this->http->wrapper()->query()->has('rcp')) {
                    $rcp = $this->http->wrapper()->query()->retrieve('rcp', $this->refinery->kindlyTo()->string());
                }
                $mailData['rcp_to'] = urldecode($rcp);
                break;

            default:
                $mailData = $this->http->request()->getParsedBody();
                foreach ($mailData as $key => $value) {
                    if (is_string($value)) {
                        // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                        $mailData[$key] = ilUtil::securePlainString($value);
                    }
                }

                if ($this->requestAttachments) {
                    $mailData['attachments'] = $this->requestAttachments;
                }
                break;
        }

        $this->tpl->parseCurrentBlock();
        $this->addToolbarButtons();
        if ($form === null) {
            $form = $this->buildForm($mailData);
        }
        $this->tpl->setVariable('FORM', $this->ui_renderer->render($form));
        $this->tpl->addJavaScript('assets/js/ilMailComposeFunctions.js');
        $this->tpl->printToStdout();
    }

    public function lookupRecipientAsync(): void
    {
        $search = trim($this->getBodyParam(
            'term',
            $this->refinery->kindlyTo()->string(),
            $this->getQueryParam(
                'term',
                $this->refinery->kindlyTo()->string(),
                ''
            )
        ));

        $result = [];

        if (ilStr::strLen($search) < 3) {
            $this->http->saveResponse(
                $this->http->response()
                    ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                    ->withBody(Streams::ofString(json_encode($result, JSON_THROW_ON_ERROR)))
            );

            $this->http->sendResponse();
            $this->http->close();
        }

        // #14768
        $quoted = ilUtil::stripSlashes($search);
        $quoted = str_replace(['%', '_'], ['\%', '\_'], $quoted);

        $mailFormObj = new ilMailForm();
        $result = $mailFormObj->getRecipientAsync("%" . $quoted . "%", ilUtil::stripSlashes($search));

        $this->http->saveResponse(
            $this->http->response()
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                ->withBody(Streams::ofString(json_encode($result, JSON_THROW_ON_ERROR)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    public function cancelMail(): void
    {
        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        }

        $this->showForm();
    }

    protected function saveMailBeforeSearch(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $result = $form->getInputGroup()->getInputs()[0]->getInputs();

        $files = [];
        $attachments = $result['attachments']->getValue();
        if (count($attachments) > 0) {
            $files = $this->handleAttachments($attachments);
        }

        $this->umail->persistToStage(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($result['rcp_to']->getValue()),
            ilUtil::securePlainString($result['rcp_cc']->getValue()),
            ilUtil::securePlainString($result['rcp_bcc']->getValue()),
            ilUtil::securePlainString($result['m_subject']->getValue()),
            ilUtil::securePlainString($result['m_message']->getValue()),
            $result['use_placeholders']->getValue(),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );
    }

    public function searchMailingListsTo(): void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass(ilMailingListsGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailingListsGUI::class);
    }

    /**
     * @param ilMailError[] $errors
     */
    protected function showSubmissionErrors(array $errors): void
    {
        $formatter = new ilMailErrorFormatter($this->lng);
        $formattedErrors = $formatter->format($errors);

        if ($formattedErrors !== '') {
            $this->tpl->setOnScreenMessage('failure', $formattedErrors);
        }
    }

    protected function buildForm(?array $mailData = null): Form
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'sendMessage'),
            $this->buildFormElements($mailData)
        )
                               ->withSubmitLabel($this->lng->txt('send_mail'))
                               ->withAdditionalSubmitButton(
                                   $this->lng->txt('save_message'),
                                   $this->ctrl->getFormAction($this, 'saveDraft')
                               );
    }

    protected function buildFormElements(?array $mailData): array
    {
        $ff = $this->ui_factory->input()->field();

        $rcp_to = $ff->text($this->lng->txt('mail_to'))
                     ->withRequired(true)
                     ->withValue($mailData["rcp_to"] ?? '');
        $rcp_cc = $ff->text($this->lng->txt('mail_cc'))
                     ->withValue($mailData["rcp_cc"] ?? '');
        $rcp_bcc = $ff->text($this->lng->txt('mail_bcc'))
                      ->withValue($mailData["rcp_bcc"] ?? '');

        $attachments = $ff->file(
            $this->upload_handler,
            $this->lng->txt('attachments')
        )->withMaxFiles(10);

        $template_chb = null;
        $signal = null;
        if (ilMailFormCall::getContextId()) {
            $context_id = ilMailFormCall::getContextId();

            try {
                $context = ilMailTemplateContextService::getTemplateContextById($context_id);

                $templates = $this->templateService->loadTemplatesForContextId($context->getId());
                if (count($templates) > 0) {
                    $options = array();

                    $tmpl_value = '';
                    $signal_generator = new ILIAS\UI\Implementation\Component\SignalGenerator();
                    $signal = $signal_generator->create();
                    foreach ($templates as $template) {
                        if (
                            isset(ilMailFormCall::getContextParameters()["template_lng"]) &&
                            $template->getLang() != ilMailFormCall::getContextParameters()["template_lng"]
                        ) {
                            continue;
                        }

                        $options[$template->getTplId()] = $template->getTitle();
                        $signal->addOption($template->getTplId() . '_subject', urlencode($template->getSubject()));
                        $signal->addOption($template->getTplId() . '_message', urlencode($template->getMessage()));

                        if (!isset($mailData['template_id']) && $template->isDefault()) {
                            $tmpl_value = $template->getTplId();
                            $mailData["m_subject"] = $template->getSubject();
                            $mailData["m_message"] = $this->umail->appendSignature($template->getMessage());
                        }
                    }
                    if (isset($mailData['template_id'])) {
                        $tmpl_value = (int) $mailData['template_id'];
                    }
                    asort($options);

                    $template_chb = $ff->select(
                        $this->lng->txt('mail_template_client'),
                        $options,
                        $this->lng->txt('mail_template_client_info')
                    )
                                       ->withValue($tmpl_value)
                                       ->withOnUpdate($signal);
                }
            } catch (Exception $e) {
                ilLoggerFactory::getLogger('mail')->error(sprintf(
                    '%s has been called with invalid context id: %s.',
                    __METHOD__,
                    $context_id
                ));
            }
        } else {
            $context = new ilMailTemplateGenericContext();
        }

        $m_subject = $ff->text($this->lng->txt('subject'))
                        ->withRequired(true)
                        ->withMaxLength(200)
                        ->withValue($mailData["m_subject"] ?? '');

        $m_message = $ff->markdown(
            new ilUIMarkdownPreviewGUI(),
            $this->lng->txt('message_content')
        )
                        ->withValue($mailData["m_message"] ?? '')
                        ->withRequired(true);

        $use_placeholders = $ff->hidden()->withValue('0');
        $placeholders = [];
        foreach ($context->getPlaceholders() as $key => $value) {
            $placeholders[$value['placeholder']] = $value['label'];
        }
        if (count($placeholders) > 0) {
            $m_message = $m_message
                ->withMustachable($placeholders)
                ->withPlaceholderAdvice(
                    $this->lng->txt('mail_nacc_use_placeholder') . '<br />'
                    . sprintf($this->lng->txt('placeholders_advise'), '<br />')
                );
            $use_placeholders = $use_placeholders->withValue('1');
        }
        $use_placeholders = $use_placeholders->withAdditionalTransformation(
            $this->refinery->kindlyTo()->bool()
        );

        if ($signal !== null) {
            $m_subject = $m_subject->withAdditionalOnLoadCode(
                function ($id) use ($signal) {
                    return "
                    $(document).on('{$signal}', function(event, signalData) {
                        let subject = document.getElementById('{$id}');
                        let tplId = signalData.triggerer.val();
                        if (tplId != '') {
                            subject.value = decodeURIComponent(signalData.options[tplId + '_subject'].replace(/\+/g, ' '));
                        }
                    });
                ";
                }
            );
            $m_message = $m_message->withAdditionalOnLoadCode(
                function ($id) use ($signal) {
                    return "
                    $(document).on('{$signal}', function(event, signalData) {
                        let message = document.getElementById('{$id}');
                        let tplId = signalData.triggerer.val();
                        if (tplId != '') {
                            message.value = decodeURIComponent(signalData.options[tplId + '_message'].replace(/\+/g, ' '));
                        }
                    });
                ";
                }
            );
        }

        $elements = [
            'rcp_to' => $rcp_to,
            'rcp_cc' => $rcp_cc,
            'rcp_bcc' => $rcp_bcc,
            'm_subject' => $m_subject,
            'attachments' => $attachments
        ];
        if ($template_chb !== null) {
            $elements[] = $template_chb;
        }
        $elements['m_message'] = $m_message;
        $elements['use_placeholders'] = $use_placeholders;
        $section = $ff->section(
            $elements,
            $this->lng->txt('compose')
        );

        return [
            $section
        ];
    }

    protected function handleAttachments(array $attachments): array
    {
        $files = [];
        foreach ($attachments as $attachment) {
            $info = $this->upload_handler->getInfoResult($attachment);
            if ($info->getFileIdentifier() !== 'unknown') {
                $src = $this->upload_handler->getStreamConsumer($attachment);
                $stored = $this->fdm->storeAsAttachment(
                    $info->getName(),
                    (string) $src->getStream()
                );
                if ($stored === false) {
                    throw new Exception("File '" . $info->getName() . "' could not be stored");
                }
                $files[] = ilFileUtils::_sanitizeFilemame($info->getName());
                $this->upload_handler->removeFileForIdentifier($attachment);
            }
        }

        return $files;
    }

    protected function addToolbarButtons()
    {
        $bf = $this->ui_factory->button();

        $action = $this->ctrl->getFormAction($this, 'searchUsers');
        $btn = $bf->standard(
            $this->lng->txt('search_recipients'),
            ''
        )->withAdditionalOnLoadCode(
            function ($id) use ($action) {
                return "document.getElementById('{$id}').addEventListener('click', function (event) {
                    let mailform = document.querySelector('form.il-standard-form');
                    mailform.action = '{$action}';
                    mailform.submit();                    
                });";
            }
        );
        $this->toolbar->addComponent($btn);

        $action = $this->ctrl->getFormAction($this, 'searchCoursesTo');
        $btn = $bf->standard(
            $this->lng->txt('mail_my_courses'),
            ''
        )->withAdditionalOnLoadCode(
            function ($id) use ($action) {
                return "document.getElementById('{$id}').addEventListener('click', function (event) {
                    let mailform = document.querySelector('form.il-standard-form');
                    mailform.action = '{$action}';
                    mailform.submit();                    
                });";
            }
        );
        $this->toolbar->addComponent($btn);

        $action = $this->ctrl->getFormAction($this, 'searchGroupsTo');
        $btn = $bf->standard(
            $this->lng->txt('mail_my_groups'),
            ''
        )->withAdditionalOnLoadCode(
            function ($id) use ($action) {
                return "document.getElementById('{$id}').addEventListener('click', function (event) {
                    let mailform = document.querySelector('form.il-standard-form');
                    mailform.action = '{$action}';
                    mailform.submit();                    
                });";
            }
        );
        $this->toolbar->addComponent($btn);

        if (count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) > 0) {
            $action = $this->ctrl->getFormAction($this, 'searchMailingListsTo');
            $btn = $bf->standard(
                $this->lng->txt('mail_my_mailing_lists'),
                ''
            )->withAdditionalOnLoadCode(
                function ($id) use ($action) {
                    return "document.getElementById('{$id}').addEventListener('click', function (event) {
                    let mailform = document.querySelector('form.il-standard-form');
                    mailform.action = '{$action}';
                    mailform.submit();                    
                });";
                }
            );
            $this->toolbar->addComponent($btn);
        }
    }
}
