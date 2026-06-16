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
use ILIAS\Mail\RecipientSearch\LegacyAutocompleteSearchResult;
use ILIAS\Mail\RecipientSearch\SentMailsBasedProvider;
use ILIAS\Mail\RecipientSearch\Search;
use ILIAS\Contact\BuddySystem\MailRecipientSearch\MailRecipientSearchProvider;
use ILIAS\Mail\RecipientSearch\UserSearchEndpointConfigurator;
use ILIAS\Data\Clock\ClockFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Folder\MailScheduleData;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;

/**
 * @ilCtrl_Calls ilMailFormGUI: ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailingListsGUI, ilMailFormUploadHandlerGUI
 * @ilCtrl_Calls ilMailFormGUI: ILIAS\User\Search\EndpointGUI
 */
class ilMailFormGUI
{
    use FileDataRCHandling;

    final public const string MAIL_FORM_TYPE_ATTACH = 'attach';
    final public const string MAIL_FORM_TYPE_SEARCH_RESULT = 'search_res';
    final public const string MAIL_FORM_TYPE_NEW = 'new';
    final public const string MAIL_FORM_TYPE_ROLE = 'role';
    final public const string MAIL_FORM_TYPE_REPLY = 'reply';
    final public const string MAIL_FORM_TYPE_ADDRESS = 'address';
    final public const string MAIL_FORM_TYPE_FORWARD = 'forward';
    final public const string MAIL_FORM_TYPE_DRAFT = 'draft';
    final public const string MAIL_FORM_TYPE_OUTBOX = 'outbox';
    final public const string MAIL_FORM_MODE_REGULAR_MAIL = 'regular_mail';
    final public const string MAIL_FORM_MODE_SERIAL_LETTER = 'serial_letter';

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
    private ?array $request_attachments = null;
    protected ilMailTemplateService $template_service;
    private readonly ilMailBodyPurifier $purifier;
    private string $mail_form_type = '';
    private readonly Factory $ui_factory;
    private readonly Renderer $ui_renderer;
    private readonly \Psr\Http\Message\ServerRequestInterface $request;
    private readonly ArrayBasedRequestWrapper $post;
    private readonly ArrayBasedRequestWrapper $query;
    private readonly ilMailFormUploadHandlerGUI $upload_handler;
    private readonly ilFileDataMail $fdm;
    private readonly ILIAS\ResourceStorage\Services $storage;
    private readonly ilSetting $settings;
    private readonly \ILIAS\User\Search\Search $user_search;
    private readonly ClockFactory $clock;

    public function __construct(
        ?ilMailTemplateService $template_service = null,
        ?ilMailBodyPurifier $body_purifier = null
    ) {
        global $DIC;

        $this->template_service = $template_service ?? $DIC->mail()->textTemplates();
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
        $this->purifier = $body_purifier ?? new ilMailBodyPurifier();
        $this->ui_factory = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->post = new ArrayBasedRequestWrapper($this->request->getParsedBody());
        $this->query = new ArrayBasedRequestWrapper($this->request->getQueryParams());
        $this->upload_handler = new ilMailFormUploadHandlerGUI();
        $this->storage = $DIC->resourceStorage();
        $this->fdm = new ilFileDataMail($this->user->getId());
        $this->settings = $DIC->settings();
        /** @var \ILIAS\User\PublicInterface $user_api */
        $user_api = $DIC['user'];
        $this->user_search = $user_api->getSearch();
        $this->clock = (new DataFactory())->clock();

        $mail_obj_id = $this->getBodyParam(
            'mobj_id',
            $this->refinery->kindlyTo()->int(),
            $this->getQueryParam(
                'mobj_id',
                $this->refinery->kindlyTo()->int(),
                0
            )
        );

        if ($mail_obj_id === 0) {
            $mail_obj_id = $this->mbox->getInboxFolder();
        }

        $this->ctrl->setParameter($this, 'mobj_id', $mail_obj_id);
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
        $forward_class = $this->ctrl->getNextClass($this) ?? '';
        switch (strtolower($forward_class)) {
            case strtolower(ILIAS\User\Search\EndpointGUI::class):
                $gui = $this->user_search->getEndpointGUI(
                    $this->getUserSearchConfigurator()
                );
                $this->ctrl->forwardCommand($gui);
                break;

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
     * @param list<string> $files
     * @return list<string>
     */
    protected function decodeAttachmentFiles(array $files): array
    {
        $decoded_files = [];
        foreach ($files as $value) {
            if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . urldecode($value))) {
                $decoded_files[] = urldecode($value);
            }
        }

        return $decoded_files;
    }

    public function saveMessageToOutbox(array $form_values, Form $form): void
    {
        $files = [];
        if (count($form_values['attachments']) > 0) {
            $files = $this->handleAttachments($form_values['attachments']);
        }

        $rcp_to = '';
        $rcp_cc = '';
        $rcp_bcc = '';
        if ($form_values['rcp_to'] !== []) {
            $rcp_to = $form_values['rcp_to'][0];
        }
        if ($form_values['rcp_cc'] !== []) {
            $rcp_cc = $form_values['rcp_cc'][0];
        }
        if ($form_values['rcp_bcc'] !== []) {
            $rcp_bcc = $form_values['rcp_bcc'][0];
        }

        $errors = $this->umail->validateRecipients(
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
        );
        if ($errors) {
            $this->showSubmissionErrors($errors);
            $this->showForm();
            $this->http->close();
        }

        $sanitized_message = (new ilMailBody($form_values['m_message'], $this->purifier))->getContent();

        $outbox_folder_id = $this->mbox->getOutboxFolder();
        if (ilSession::get('outbox')) {
            $outbox_id = (int) ilSession::get('outbox');
            ilSession::clear('outbox');
        }

        $this->umail->scheduledMail(
            $outbox_folder_id,
            $this->user->getId(),
            new MailScheduleData(
                new MailDeliveryData(
                    $rcp_to,
                    $rcp_cc,
                    $rcp_bcc,
                    ilUtil::securePlainString($form_values['m_subject'] ?? $this->lng->txt('mail_no_subject')),
                    $sanitized_message,
                    $files,
                    $form_values['use_placeholders'],
                    $outbox_id ?? null
                ),
                $form_values['use_schedule']['m_schedule']
            ),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        if (ilSession::get('draft')) {
            $draft_id = (int) ilSession::get('draft');
            ilSession::clear('draft');
            $this->umail->deleteMails([$draft_id]);
        }

        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mobj_id', $outbox_folder_id);
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_scheduled'), true);

        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        } else {
            $this->ctrl->redirectByClass([ilMailGUI::class, ilMailFolderGUI::class]);
        }

        $this->showForm();
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

        $schedule_date = $value['use_schedule']['m_schedule'] ?? null;
        if ($schedule_date instanceof DateTimeImmutable &&
            $schedule_date > $this->clock->local(new DateTimeZone($this->user->getTimeZone()))->now()) {
            $this->saveMessageToOutbox($value, $form);
            return;
        }

        $files = [];
        if (count($value['attachments']) > 0) {
            $files = $this->handleAttachments($value['attachments']);
        }

        $mailer = $this->umail
            ->withContextId(ilMailFormCall::getContextId() ?: '')
            ->withContextParameters(ilMailFormCall::getContextParameters());

        $mailer->setSaveInSentbox(true);

        $mailer->autoresponder()->enableAutoresponder();

        $rcp_to = '';
        $rcp_cc = '';
        $rcp_bcc = '';
        if (!empty($value['rcp_to'])) {
            $rcp_to = implode(',', $value['rcp_to']);
        }
        if (!empty($value['rcp_cc'])) {
            $rcp_cc = implode(',', $value['rcp_cc']);
        }
        if (!empty($value['rcp_bcc'])) {
            $rcp_bcc = implode(',', $value['rcp_bcc']);
        }

        if ($errors = $mailer->enqueue(
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            ilUtil::securePlainString($value['m_subject']),
            (new ilMailBody($value['m_message'], $this->purifier))->getContent(),
            $files,
            $value['use_placeholders']
        )) {
            $mailer->autoresponder()->disableAutoresponder();

            $this->showSubmissionErrors($errors);
            $this->showForm($form);

            $this->http->close();
        } else {
            $mailer->autoresponder()->disableAutoresponder();

            $mailer->persistToStage(
                $this->user->getId(),
                '',
                '',
                '',
                '',
                '',
                null
            );

            $mail_id = null;
            if (ilSession::get('outbox')) {
                $mail_id = (int) ilSession::get('outbox');
                ilSession::clear('outbox');
            } elseif (ilSession::get('draft')) {
                $mail_id = (int) ilSession::get('draft');
                ilSession::clear('draft');
            }

            if ($mail_id) {
                $mailer->deleteMails([$mail_id]);
            }

            $this->ctrl->setParameterByClass(ilMailGUI::class, 'type', 'message_sent');

            if (ilMailFormCall::isRefererStored()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_message_send'), true);
                $this->ctrl->redirectToURL(ilMailFormCall::getRefererRedirectUrl());
            } else {
                $this->ctrl->redirectByClass(ilMailGUI::class);
            }
        }
        $mailer->autoresponder()->disableAutoresponder();

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
        if (count($value['attachments']) > 0) {
            $files = $this->handleAttachments($value['attachments']);
        }

        $draft_folder_id = $this->mbox->getDraftsFolder();

        $rcp_to = !empty($value['rcp_to']) ? implode(',', $value['rcp_to']) : '';
        $rcp_cc = !empty($value['rcp_cc']) ? implode(',', $value['rcp_cc']) : '';
        $rcp_bcc = !empty($value['rcp_bcc']) ? implode(',', $value['rcp_bcc']) : '';

        if ($errors = $this->umail->validateRecipients($rcp_to, $rcp_cc, $rcp_bcc)) {
            $this->request_attachments = $files;
            $this->showSubmissionErrors($errors);
            $this->showForm($form);
            return;
        }

        if (ilSession::get('draft')) {
            $draft_id = (int) ilSession::get('draft');
            ilSession::clear('draft');
        } else {
            $draft_id = $this->umail->getNewDraftId($draft_folder_id);
        }

        $this->umail->updateDraft(
            $draft_folder_id,
            $files,
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            ilUtil::securePlainString($value['m_subject']),
            $value['m_message'],
            $draft_id,
            $value['use_schedule']['m_schedule'] ?? null,
            $value['use_placeholders'],
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        if (ilSession::get('outbox')) {
            $outbox_id = (int) ilSession::get('outbox');
            ilSession::clear('outbox');
            $this->umail->deleteMails([$outbox_id]);
        }

        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mobj_id', $draft_folder_id);
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_saved'), true);

        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        } else {
            $this->ctrl->redirectByClass([ilMailGUI::class, ilMailFolderGUI::class]);
        }

        $this->showForm();
    }

    public function searchUsers(bool $save = true): void
    {
        $this->tpl->setTitle($this->lng->txt('mail_new'));

        if ($save) {
            $this->saveMailBeforeSearch();
        }

        $form = new ilPropertyFormGUI();
        $form->setId('search_rcp');
        $form->setTitle($this->lng->txt('search_recipients'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'search'));

        $inp = new ilTextInputGUI($this->lng->txt('search_for'), 'search');
        $inp->setSize(30);
        $data_source_url = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true);
        $inp->setDataSource($data_source_url);

        $search_query = trim((string) ilSession::get('mail_search_search'));
        if ($search_query !== '') {
            $inp->setValue(ilLegacyFormElementsUtil::prepareFormOutput($search_query, true));
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

        if (trim(ilSession::get('mail_search_search') ?? '') === '') {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_insert_query'));
            $this->searchUsers(false);
        } elseif (strlen(trim(ilSession::get('mail_search_search') ?? '')) < 3) {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_minimum_three'));
            $this->searchUsers(false);
        } else {
            $this->ctrl->setParameterByClass(
                ilMailSearchGUI::class,
                'search',
                urlencode(ilSession::get('mail_search_search') ?? '')
            );
            $this->ctrl->redirectByClass(ilMailSearchGUI::class);
        }
    }

    public function cancelSearch(): void
    {
        ilSession::clear('mail_search');
        $this->searchResults();
    }

    public function editAttachments(): void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass(ilMailAttachmentGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailAttachmentGUI::class);
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

    public function mailAttachment(): void
    {
        $this->mail_form_type = self::MAIL_FORM_TYPE_ATTACH;
        $this->showForm();
    }

    protected function getTemplateDataById(): void
    {
        if (!$this->http->wrapper()->query()->has('template_id')) {
            $this->http->close();
        }

        try {
            $template = $this->template_service->loadTemplateForId(
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
        $this->tpl->setTitle($this->lng->txt('mail_new'));

        $this->lng->loadLanguageModule('crs');

        if (ilMailFormCall::isRefererStored()) {
            $this->tabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTarget($this, 'cancelMail')
            );
        }

        $mail_data = [];
        $mail_data['rcp_to'] = '';
        $mail_data['rcp_cc'] = '';
        $mail_data['rcp_bcc'] = '';
        $mail_data['attachments'] = [];
        $mail_data['m_subject'] = '';
        $mail_data['m_message'] = '';

        $mail_id = $this->getQueryParam('mail_id', $this->refinery->kindlyTo()->int(), 0);
        $type = $this->getQueryParam('type', $this->refinery->kindlyTo()->string(), '');
        if ($this->mail_form_type !== '') {
            $type = $this->mail_form_type;
        }

        switch ($type) {
            case self::MAIL_FORM_TYPE_REPLY:
                $mail_data = $this->umail->getMail($mail_id);

                $mail_data['m_subject'] = $this->umail->formatReplySubject($mail_data['m_subject'] ?? '');
                $mail_data['m_message'] = $this->umail->prependSignature(
                    $this->umail->formatReplyMessage($mail_data['m_message'] ?? '')
                );
                $mail_data['attachments'] = [];
                $mail_data['rcp_cc'] = '';
                $mail_data['rcp_to'] = $this->umail->formatReplyRecipient();
                break;

            case self::MAIL_FORM_TYPE_SEARCH_RESULT:
                $mail_data = $this->umail->retrieveFromStage();
                if (ilSession::get('mail_search_results_to')) {
                    $mail_data = $this->umail->appendSearchResult(
                        $this->refinery->kindlyTo()->listOf(
                            $this->refinery->kindlyTo()->string()
                        )->transform(ilSession::get('mail_search_results_to')),
                        'to'
                    );
                }
                if (ilSession::get('mail_search_results_cc')) {
                    $mail_data = $this->umail->appendSearchResult(
                        $this->refinery->kindlyTo()->listOf(
                            $this->refinery->kindlyTo()->string()
                        )->transform(ilSession::get('mail_search_results_cc')),
                        'cc'
                    );
                }
                if (ilSession::get('mail_search_results_bcc')) {
                    $mail_data = $this->umail->appendSearchResult(
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
                ilSession::set('draft', $mail_id);
                $mail_data = $this->umail->getMail($mail_id);

                if (!is_null($mail_data['attachments']) || !empty($mail_data['attachments'])) {
                    $mail_data['attachments'] = $this->filesFromLegacyToIRSS($mail_data);
                }

                ilMailFormCall::setContextId($mail_data['tpl_ctx_id']);
                ilMailFormCall::setContextParameters($mail_data['tpl_ctx_params']);
                break;

            case self::MAIL_FORM_TYPE_OUTBOX:
                ilSession::set('outbox', $mail_id);
                $mail_data = $this->umail->getMail($mail_id);
                ilMailFormCall::setContextId($mail_data['tpl_ctx_id']);
                ilMailFormCall::setContextParameters($mail_data['tpl_ctx_params']);
                break;

            case self::MAIL_FORM_TYPE_FORWARD:
                $mail_data = $this->umail->getMail($mail_id);
                $mail_data['rcp_to'] = $mail_data['rcp_cc'] = $mail_data['rcp_bcc'] = '';
                $mail_data['m_subject'] = $this->umail->formatForwardSubject($mail_data['m_subject'] ?? '');
                $mail_data['m_message'] = $this->umail->prependSignature($mail_data['m_message'] ?? '');
                if (is_array($mail_data['attachments']) && count($mail_data['attachments']) && $error = $this->mfile->adoptAttachments(
                    $mail_data['attachments'],
                    $mail_id
                )) {
                    $this->tpl->setOnScreenMessage('info', $error);
                }

                if (!is_null($mail_data['attachments']) || ($mail_data['attachments'] != '')) {
                    $mail_data['attachments'] = $this->filesFromLegacyToIRSS($mail_data);
                }
                break;

            case self::MAIL_FORM_TYPE_NEW:
                ilSession::clear('draft');
                ilSession::clear('outbox');
                // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                $to = ilUtil::securePlainString($this->getQueryParam('rcp_to', $this->refinery->kindlyTo()->string(), ''));
                if ($to === '' && ilSession::get('rcp_to')) {
                    $to = ilSession::get('rcp_to');
                }
                $mail_data['rcp_to'] = $to;

                $cc = ilUtil::securePlainString($this->getQueryParam('rcp_cc', $this->refinery->kindlyTo()->string(), ''));
                if ($cc === '' && ilSession::get('rcp_cc')) {
                    $cc = ilSession::get('rcp_cc');
                }
                $mail_data['rcp_cc'] = $cc;

                $bcc = ilUtil::securePlainString($this->getQueryParam('rcp_bcc', $this->refinery->kindlyTo()->string(), ''));
                if ($bcc === '' && ilSession::get('rcp_bcc')) {
                    $bcc = ilSession::get('rcp_bcc');
                }
                $mail_data['rcp_bcc'] = $bcc;

                $mail_data['m_message'] = '';
                if (($sig = ilMailFormCall::getSignature()) !== '') {
                    $mail_data['m_message'] = $sig;
                    $mail_data['m_message'] .= chr(13)
                        . chr(10)
                        . chr(13)
                        . chr(10);
                }
                $mail_data['m_message'] .= $this->umail->appendSignature('');

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
                $mail_data['rcp_to'] = ilUtil::securePlainString(
                    implode(',', $roles)
                );

                $mail_data['m_message'] = '';
                if (($sig = ilMailFormCall::getSignature()) !== '') {
                    $mail_data['m_message'] = $sig;
                    $mail_data['m_message'] .= chr(13)
                        . chr(10)
                        . chr(13)
                        . chr(10);
                }

                $additional_msg_text = '';
                if ($this->http->wrapper()->post()->has('additional_message_text')) {
                    $additional_msg_text = ilUtil::securePlainString($this->http->wrapper()->post()->retrieve(
                        'additional_message_text',
                        $this->refinery->kindlyTo()->string()
                    ));
                }

                $mail_data['m_message'] .= $additional_msg_text
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
                $mail_data['rcp_to'] = urldecode((string) $rcp);
                break;
            case self::MAIL_FORM_TYPE_ATTACH:
                $mail_data = $this->umail->retrieveFromStage();
                break;
            default:
                $mail_data = $this->http->request()->getParsedBody();
                foreach ($mail_data as $key => $value) {
                    if (is_string($value)) {
                        // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                        $mail_data[$key] = ilUtil::securePlainString($value);
                    }
                }

                if ($this->request_attachments) {
                    $mail_data['attachments'] = $this->request_attachments;
                }
                break;
        }

        $this->tpl->parseCurrentBlock();

        $form ??= $this->buildForm($mail_data);
        $this->addToolbarButtons($form);

        $this->tpl->setVariable('FORM', $this->ui_renderer->render($form));
        $this->tpl->addJavaScript('assets/js/ilMailComposeFunctions.js');
        $this->tpl->printToStdout();
    }

    public function lookupRecipientAsync(): void
    {
        $search = trim((string) $this->getBodyParam(
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

        $form = new ilMailForm();
        $result = $form->getRecipientAsync('%' . $quoted . '%', ilUtil::stripSlashes($search));

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

    /**
     * @param array<string|\ILIAS\UI\Component\Input\Input>|null $input_results
     */
    private function saveMailBeforeSearch(?array $input_results = null): void
    {
        if (empty($input_results)) {
            $form = $this->buildForm()->withRequest($this->request);
            $result = $form->getInputGroup()->getInputs()[0]->getInputs();
        } else {
            $result = $input_results;
        }

        $resource_collection_id = null;
        if (!empty($result['attachments']->getValue())) {
            $files = $this->handleAttachments($result['attachments']->getValue());
            $resource_collection_id = $this->getIdforCollection($files);
        }

        $rcp_to = implode(',', $result['rcp_to']->getValue() ?? []);
        $rcp_cc = implode(',', $result['rcp_cc']->getValue() ?? []);
        $rcp_bcc = implode(',', $result['rcp_bcc']->getValue() ?? []);

        $this->umail->persistToStage(
            $this->user->getId(),
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            ilUtil::securePlainString($result['m_subject']->getValue()),
            ilUtil::securePlainString($result['m_message']->getValue()),
            $resource_collection_id,
            (bool) $result['use_placeholders']->getValue(),
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
     * @param list<ilMailError> $errors
     */
    protected function showSubmissionErrors(array $errors): void
    {
        $formatter = new ilMailErrorFormatter($this->lng);
        $formatted_errors = $formatter->format($errors);

        if ($formatted_errors !== '') {
            $this->tpl->setOnScreenMessage('failure', $formatted_errors);
        }
    }

    protected function buildForm(?array $mail_data = null): Form
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'sendMessage'),
            $this->buildFormElements($mail_data)
        )->withAdditionalFormAction(
            $this->ctrl->getFormAction($this, 'saveDraft'),
            $this->lng->txt('save_message')
        )->withSubmitLabel($this->lng->txt('send_mail'));
    }

    private function getUserSearchConfigurator(): \ILIAS\User\Search\EndpointConfigurator
    {
        return new UserSearchEndpointConfigurator();
    }

    protected function buildFormElements(?array $mail_data): array
    {
        $ff = $this->ui_factory->input()->field();

        $rcp_to = $this->user_search->getInput(
            $this->lng->txt('mail_to'),
            $this->getUserSearchConfigurator()
        )->withRequired(true, $this->refinery->logical()->sequential([
            $this->refinery->logical()->not($this->refinery->null()),
            $this->refinery->string()->hasMinLength(1)
        ])->withProblemBuilder(function ($txt) {
            return $txt('mail_add_recipient');
        }));
        $rcp_cc = $this->user_search->getInput(
            $this->lng->txt('mail_cc'),
            $this->getUserSearchConfigurator()
        );
        $rcp_bcc = $this->user_search->getInput(
            $this->lng->txt('mail_bcc'),
            $this->getUserSearchConfigurator()
        );

        if (!is_null($mail_data)) {
            if (isset($mail_data['rcp_to']) && $mail_data['rcp_to'] != '') {
                $rcp_to = $rcp_to->withValue(explode(',', $mail_data['rcp_to']) ?? (array) $mail_data['rcp_to']);
            }
            if (isset($mail_data['rcp_cc']) && $mail_data['rcp_cc'] != '') {
                $rcp_cc = $rcp_cc->withValue(explode(',', $mail_data['rcp_cc']) ?? (array) $mail_data['rcp_cc']);
            }
            if (isset($mail_data['rcp_bcc']) && $mail_data['rcp_bcc'] != '') {
                $rcp_bcc = $rcp_bcc->withValue(explode(',', $mail_data['rcp_bcc']) ?? (array) $mail_data['rcp_bcc']);
            }
        }

        $has_files = !empty($mail_data['attachments']);
        $attachments = $ff->file(
            $this->upload_handler,
            $this->lng->txt('attachments')
        )->withMaxFiles(10);

        if (isset($mail_data['attachments']) && $has_files) {
            if ($mail_data['attachments'] instanceof \ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification) {
                $mail_data['attachments'] = $this->FilesFromIRSSToLegacy($mail_data['attachments']);
            }
            $attachments = $attachments->withValue($mail_data['attachments'] ?? []);
        }

        $template_chb = null;
        $signal = null;
        $use_placeholder_value = false;
        $context = new ilMailTemplateGenericContext();
        if (ilMailFormCall::getContextId()) {
            $context_id = ilMailFormCall::getContextId();

            try {
                $context = ilMailTemplateContextService::getTemplateContextById($context_id);
                $use_placeholder_value = true;

                $templates = $this->template_service->loadTemplatesForContextId($context->getId());
                if (!empty($templates)) {
                    $options = [];

                    $tmpl_value = '';
                    $signal_generator = new ILIAS\UI\Implementation\Component\SignalGenerator();
                    $signal = $signal_generator->create();
                    foreach ($templates as $template) {
                        $options[$template->getTplId()] = $template->getTitle();
                        $signal->addOption($template->getTplId() . '_subject', urlencode($template->getSubject()));
                        $signal->addOption($template->getTplId() . '_message', urlencode($template->getMessage()));

                        if (!isset($mail_data['template_id']) && $template->isDefault()) {
                            $tmpl_value = $template->getTplId();
                            $mail_data['m_subject'] = $template->getSubject();
                            $mail_data['m_message'] = $this->umail->appendSignature($template->getMessage());
                        }
                    }
                    if (isset($mail_data['template_id'])) {
                        $tmpl_value = (int) $mail_data['template_id'];
                    }
                    asort($options);

                    $template_chb = $ff
                        ->select(
                            $this->lng->txt('mail_template_client'),
                            $options,
                            $this->lng->txt('mail_template_client_info')
                        )
                        ->withValue($tmpl_value)
                        ->withOnUpdate($signal);
                }
            } catch (Exception) {
                ilLoggerFactory::getLogger('mail')->error(sprintf(
                    '%s has been called with invalid context id: %s.',
                    __METHOD__,
                    $context_id
                ));
            }
        } else {
            $use_placeholder_value = $mail_data['use_placeholders'] ?? false;
        }

        $m_subject = $ff
            ->text($this->lng->txt('subject'))
            ->withRequired(
                true,
                $this->refinery->logical()->sequential([
                    $this->refinery->logical()->not($this->refinery->null()),
                    $this->refinery->string()->hasMinLength(1)
                ])->withProblemBuilder(function ($txt) {
                    return $txt('mail_add_subject');
                })
            )
            ->withMaxLength(200)
            ->withValue($mail_data['m_subject'] ?? '');

        $m_message = $ff->markdown(
            new ilUIMarkdownPreviewGUI(),
            $this->lng->txt('message_content')
        )->withValue($mail_data['m_message'] ?? '');

        $use_placeholders = $ff->hidden()->withValue($use_placeholder_value ? '1' : '0');

        $placeholders = [];
        $mode = $use_placeholder_value ? self::MAIL_FORM_MODE_SERIAL_LETTER : self::MAIL_FORM_MODE_REGULAR_MAIL;
        if ($mode === self::MAIL_FORM_MODE_SERIAL_LETTER && $context) {
            foreach ($context->getPlaceholders() as $value) {
                $placeholders[$value['placeholder']] = $value['label'];
            }
            if (!empty($placeholders)) {
                $m_message = $m_message
                    ->withMustacheVariables(
                        $placeholders,
                        $this->lng->txt('mail_nacc_use_placeholder') . '<br />'
                        . sprintf($this->lng->txt('placeholders_advise'), '<br />')
                    )
                ;
            }
        }

        $use_placeholders = $use_placeholders->withAdditionalTransformation(
            $this->refinery->kindlyTo()->bool()
        );

        if ($signal !== null) {
            $m_subject = $m_subject->withAdditionalOnLoadCode(
                function ($id) use ($signal) {
                    return "
                    $(document).on('{$signal}', function (event, signalData) {
                        let subject = document.getElementById('{$id}');
                        let child = subject.querySelector('.c-input__field input');
                        let triggerer = signalData.triggerer[0];
                        let tplId = triggerer.querySelector('select').value;
                        if (tplId != '') {
                            child.value = decodeURIComponent(signalData.options[tplId + '_subject'].replace(/\+/g, ' '));
                        }
                    });
                ";
                }
            );
            $m_message = $m_message->withAdditionalOnLoadCode(
                function ($id) use ($signal) {
                    return "
                    $(document).on('{$signal}', function (event, signalData) {
                        let message = document.getElementById('{$id}');
                        let child = message.querySelector('.c-input__field textarea');
                        let triggerer = signalData.triggerer[0];
                        let tplId = triggerer.querySelector('select').value;
                        if (tplId != '') {
                            child.value = decodeURIComponent(signalData.options[tplId + '_message'].replace(/\+/g, ' '));
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

        $schedule_date_time_value = null;
        $current_time = $this->clock->local(new DateTimeZone($this->user->getTimeZone()))->now();
        $schedule_date_time_input = $ff
            ->dateTime($this->lng->txt('mail_schedule_scheduled_datetime'))
            ->withUseTime(true)
            ->withTimezone($this->user->getTimezone())
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    function (DateTimeImmutable $v) use ($current_time) {
                        return $v > $current_time;
                    },
                    $this->lng->txt('mail_schedule_error_past_datetime')
                )
            );

        if (isset($mail_data['schedule_datetime'])) {
            $schedule_time = new DateTimeImmutable(
                (string) $mail_data['schedule_datetime'],
                new DateTimeZone($mail_data['schedule_timezone'] ?? '')
            );
            $schedule_time->setTimezone(new DateTimeZone($this->user->getTimeZone()));
            $schedule_date_time_value = $schedule_time > $current_time ? $schedule_time : null;
        }

        $use_schedule_input = $ff->optionalGroup(
            ['m_schedule' => $schedule_date_time_input],
            $this->lng->txt('mail_message_scheduled')
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                function (?array $v) {
                    return $v === null || (isset($v['m_schedule']) && $v['m_schedule'] instanceof DateTimeImmutable);
                },
                $this->lng->txt('mail_schedule_error_no_datetime')
            )
        );
        if ($schedule_date_time_value !== null) {
            $use_schedule_input = $use_schedule_input->withValue(['m_schedule' => $schedule_date_time_value]);
        } else {
            $use_schedule_input = $use_schedule_input->withValue(null);
        }

        $elements['use_schedule'] = $use_schedule_input;
        $elements['use_placeholders'] = $use_placeholders;

        $section = $ff->section(
            $elements,
            $this->lng->txt('compose')
        );

        return [
            $section
        ];
    }

    protected function addToolbarButtons(Form $form): void
    {
        $bf = $this->ui_factory->button();

        $result = $form->getInputGroup()->getInputs()[0]->getInputs();
        $use_placeholders = (bool) $result['use_placeholders']->getValue();
        $action = $this->ctrl->getFormAction($this, 'toggleMailMode');
        $url_builder = new UrlBuilder(new URI(ILIAS_HTTP_PATH . '/' . $action));
        [$url_builder, $mail_mode_parameter] = $url_builder->acquireParameter(['mail', 'form'], 'mail_mode');

        $btn = $this->ui_factory->viewControl()->mode(
            [
                $this->lng->txt(self::MAIL_FORM_MODE_REGULAR_MAIL) => (string) $url_builder->withParameter(
                    $mail_mode_parameter,
                    self::MAIL_FORM_MODE_REGULAR_MAIL
                )->buildURI(),
                $this->lng->txt(self::MAIL_FORM_MODE_SERIAL_LETTER) => (string) $url_builder->withParameter(
                    $mail_mode_parameter,
                    self::MAIL_FORM_MODE_SERIAL_LETTER
                )->buildURI(),
            ],
            'mail_mode_switch_label'
        )->withActive(
            $this->lng->txt($use_placeholders ? self::MAIL_FORM_MODE_SERIAL_LETTER : self::MAIL_FORM_MODE_REGULAR_MAIL)
        );

        $this->toolbar->addComponent($btn);
        $this->toolbar->addSeparator();

        $this->tpl->addOnLoadCode(
            "document.getElementById('{$this->toolbar->getId()}')
            .querySelector('div[aria-label=\"" . $this->lng->txt('mail_mode_switch_label') . "\"]')
            .querySelectorAll('button[data-action]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    
                    let mailform = document.querySelector('form.c-form');
                    let action = button.getAttribute('data-action');
                    if (action && mailform) {
                        let submitBtn = mailform.querySelector('button[type=\"submit\"]');
                        if (submitBtn) {
                            submitBtn.formAction = action;
                            mailform.requestSubmit(submitBtn);
                        } else {
                            mailform.action = action;
                            mailform.submit();
                        }
                    }
                    return false;
                }, true);
            });"
        );

        $action = $this->ctrl->getFormAction($this, 'searchUsers');
        $btn = $bf->standard(
            $this->lng->txt('search_recipients'),
            ''
        )->withAdditionalOnLoadCode(
            fn(string $id): string => $this->mailFormToolbarDelegatedSubmitJs($id, $action)
        );

        $this->toolbar->addComponent($btn);

        $action = $this->ctrl->getFormAction($this, 'searchCoursesTo');
        $btn = $bf->standard(
            $this->lng->txt('mail_my_courses'),
            ''
        )->withAdditionalOnLoadCode(
            fn(string $id): string => $this->mailFormToolbarDelegatedSubmitJs($id, $action)
        );
        $this->toolbar->addComponent($btn);

        $action = $this->ctrl->getFormAction($this, 'searchGroupsTo');
        $btn = $bf->standard(
            $this->lng->txt('mail_my_groups'),
            ''
        )->withAdditionalOnLoadCode(
            fn(string $id): string => $this->mailFormToolbarDelegatedSubmitJs($id, $action)
        );
        $this->toolbar->addComponent($btn);

        if (count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) > 0) {
            $action = $this->ctrl->getFormAction($this, 'searchMailingListsTo');
            $btn = $bf->standard(
                $this->lng->txt('mail_my_mailing_lists'),
                ''
            )->withAdditionalOnLoadCode(
                fn(string $id): string => $this->mailFormToolbarDelegatedSubmitJs($id, $action)
            );
            $this->toolbar->addComponent($btn);
        }

        $this->toolbar->addSeparator();

        $action = $this->ctrl->getFormAction($this, 'editAttachments');
        $btn = $bf->standard(
            $this->lng->txt('edit_attachments'),
            ''
        )->withAdditionalOnLoadCode(
            fn(string $id): string => $this->mailFormToolbarDelegatedSubmitJs($id, $action)
        );
        $this->toolbar->addComponent($btn);
    }

    private function mailFormToolbarDelegatedSubmitJs(string $toolbar_button_id, string $form_action): string
    {
        return "document.getElementById('{$toolbar_button_id}').addEventListener('click', function () {
            let mailform = document.querySelector('form.c-form');
            if (!mailform) {
                return;
            }
            let submitBtn = mailform.querySelector('button[type=\"submit\"]');
            if (submitBtn) {
                submitBtn.formAction = '$form_action';
                mailform.requestSubmit(submitBtn);
            } else {
                mailform.action = '$form_action';
                mailform.submit();
            }
        });";
    }

    private function toggleMailMode(): never
    {
        $form = $this->buildForm()->withRequest($this->request);

        $mode = $this->getQueryParam(
            'mail_form_mail_mode',
            $this->refinery->kindlyTo()->string(),
            self::MAIL_FORM_MODE_REGULAR_MAIL
        );

        $result = null;
        if (!ilMailFormCall::getContextId() && in_array(
            $mode,
            [self::MAIL_FORM_MODE_REGULAR_MAIL, self::MAIL_FORM_MODE_SERIAL_LETTER],
            true
        )) {
            $result = $form->getInputGroup()->getInputs()[0]->getInputs();
            $result['use_placeholders'] = $result['use_placeholders']->withValue(
                $mode === self::MAIL_FORM_MODE_SERIAL_LETTER ? '1' : '0'
            );
        } elseif ($mode === self::MAIL_FORM_MODE_REGULAR_MAIL && ilMailFormCall::getContextId()) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_INFO,
                sprintf(
                    $this->lng->txt('mail_mode_switch_locked'),
                    $this->lng->txt('regular_mail')
                ),
                true
            );
        }

        $this->saveMailBeforeSearch($result ?? null);

        $this->ctrl->redirect($this, 'searchResults');
    }
}
