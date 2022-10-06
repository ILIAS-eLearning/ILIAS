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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Jens Conze
 * @ingroup ServicesMail
 * @ilCtrl_Calls ilMailFormGUI: ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailingListsGUI
 */
class ilMailFormGUI
{
    public const MAIL_FORM_TYPE_ATTACH = 'attach';
    public const MAIL_FORM_TYPE_SEARCH_RESULT = 'search_res';
    public const MAIL_FORM_TYPE_NEW = 'new';
    public const MAIL_FORM_TYPE_ROLE = 'role';
    public const MAIL_FORM_TYPE_REPLY = 'reply';
    public const MAIL_FORM_TYPE_ADDRESS = 'address';
    public const MAIL_FORM_TYPE_FORWARD = 'forward';
    public const MAIL_FORM_TYPE_DRAFT = 'draft';

    private ilGlobalTemplateInterface $tpl;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilObjUser $user;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private ilFormatMail $umail;
    private ilMailbox $mbox;
    private ilFileDataMail $mfile;
    private GlobalHttpState $http;
    private Refinery $refinery;
    private ?array $requestAttachments = null;
    protected ilMailTemplateService $templateService;
    private ilMailBodyPurifier $purifier;
    private string $mail_form_type = '';

    public function __construct(
        ilMailTemplateService $templateService = null,
        ilMailBodyPurifier $bodyPurifier = null
    ) {
        global $DIC;
        $this->templateService = $templateService ?? $DIC['mail.texttemplates.service'];
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
                $this->ctrl->forwardCommand(new ilMailAttachmentGUI());
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
        $message = $this->getBodyParam('m_message', $this->refinery->kindlyTo()->string(), '');

        $mailBody = new ilMailBody($message, $this->purifier);

        $sanitizedMessage = $mailBody->getContent();

        $attachments = $this->getBodyParam(
            'attachments',
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
            []
        );
        $files = $this->decodeAttachmentFiles($attachments);

        $mailer = $this->umail
            ->withContextId(ilMailFormCall::getContextId() ?: '')
            ->withContextParameters(ilMailFormCall::getContextParameters());

        $mailer->setSaveInSentbox(true);

        if ($errors = $mailer->enqueue(
            ilUtil::securePlainString($this->getBodyParam('rcp_to', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('rcp_cc', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('rcp_bcc', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('m_subject', $this->refinery->kindlyTo()->string(), '')),
            $sanitizedMessage,
            $files,
            $this->getBodyParam('use_placeholders', $this->refinery->kindlyTo()->bool(), false)
        )) {
            $this->requestAttachments = $files;
            $this->showSubmissionErrors($errors);
        } else {
            $mailer->savePostData(
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
        $draftFolderId = $this->mbox->getDraftsFolder();

        $files = $this->decodeAttachmentFiles($this->getBodyParam(
            'attachments',
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->custom()->transformation($this->refinery->kindlyTo()->string())
            ),
            []
        ));

        $rcp_to = ilUtil::securePlainString($this->getBodyParam('rcp_to', $this->refinery->kindlyTo()->string(), ''));
        $rcp_cc = ilUtil::securePlainString($this->getBodyParam('rcp_bcc', $this->refinery->kindlyTo()->string(), ''));
        $rcp_bcc = ilUtil::securePlainString($this->getBodyParam('rcp_bcc', $this->refinery->kindlyTo()->string(), ''));

        if ($errors = $this->umail->validateRecipients(
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
        )) {
            $this->requestAttachments = $files;
            $this->showSubmissionErrors($errors);
            $this->showForm();
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
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            ilUtil::securePlainString(
                $this->getBodyParam('m_subject', $this->refinery->kindlyTo()->string(), '')
            ) ?: 'No Subject',
            ilUtil::securePlainString($this->getBodyParam('m_message', $this->refinery->kindlyTo()->string(), '')),
            $draftId,
            $this->getBodyParam('use_placeholders', $this->refinery->kindlyTo()->bool(), false),
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

    public function searchUsers(bool $save = true): void
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        if ($save) {
            $files = $this->getBodyParam(
                'attachments',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->custom()->transformation(function ($elm): string {
                        $attachment = $this->refinery->kindlyTo()->string()->transform($elm);

                        return urldecode($attachment);
                    })
                ),
                []
            );

            // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
            $this->umail->savePostData(
                $this->user->getId(),
                $files,
                ilUtil::securePlainString($this->getBodyParam('rcp_to', $this->refinery->kindlyTo()->string(), '')),
                ilUtil::securePlainString($this->getBodyParam('rcp_cc', $this->refinery->kindlyTo()->string(), '')),
                ilUtil::securePlainString($this->getBodyParam('rcp_bcc', $this->refinery->kindlyTo()->string(), '')),
                ilUtil::securePlainString($this->getBodyParam('m_subject', $this->refinery->kindlyTo()->string(), '')),
                ilUtil::securePlainString($this->getBodyParam('m_message', $this->refinery->kindlyTo()->string(), '')),
                $this->getBodyParam('use_placeholders', $this->refinery->kindlyTo()->bool(), false),
                ilMailFormCall::getContextId(),
                ilMailFormCall::getContextParameters()
            );
        }

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
            $this->searchUsers(false);
        } elseif (strlen(trim(ilSession::get('mail_search_search'))) < 3) {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('search_minimum_three'));
            $this->searchUsers(false);
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

    public function editAttachments(): void
    {
        $files = $this->getBodyParam(
            'attachments',
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->custom()->transformation(function ($elm): string {
                    $attachment = $this->refinery->kindlyTo()->string()->transform($elm);

                    return urldecode($attachment);
                })
            ),
            []
        );

        // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($this->getBodyParam('rcp_to', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('rcp_cc', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('rcp_bcc', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('m_subject', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('m_message', $this->refinery->kindlyTo()->string(), '')),
            $this->getBodyParam('use_placeholders', $this->refinery->kindlyTo()->bool(), false),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

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

    public function showForm(): void
    {
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.mail_new.html',
            'Services/Mail'
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
                $mailData = $this->umail->getSavedData();

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

            case self::MAIL_FORM_TYPE_ATTACH:
                $mailData = $this->umail->getSavedData();
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

        $form_gui = new ilPropertyFormGUI();
        $form_gui->setTitle($this->lng->txt('compose'));
        $form_gui->setId('mail_compose_form');
        $form_gui->setName('mail_compose_form');
        $form_gui->setFormAction($this->ctrl->getFormAction($this, 'sendMessage'));

        $this->tpl->setVariable('FORM_ID', $form_gui->getId());

        $btn = ilButton::getInstance();
        $btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT);
        $btn->setForm('form_' . $form_gui->getName())
            ->setName('searchUsers')
            ->setCaption('search_recipients');
        $this->toolbar->addStickyItem($btn);

        $btn = ilButton::getInstance();
        $btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT)
            ->setName('searchCoursesTo')
            ->setForm('form_' . $form_gui->getName())
            ->setCaption('mail_my_courses');
        $this->toolbar->addButtonInstance($btn);

        $btn = ilButton::getInstance();
        $btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT)
            ->setName('searchGroupsTo')
            ->setForm('form_' . $form_gui->getName())
            ->setCaption('mail_my_groups');
        $this->toolbar->addButtonInstance($btn);

        if (count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) > 0) {
            $btn = ilButton::getInstance();
            $btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT)
                ->setName('searchMailingListsTo')
                ->setForm('form_' . $form_gui->getName())
                ->setCaption('mail_my_mailing_lists');
            $this->toolbar->addButtonInstance($btn);
        }

        $dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true);

        $inp = new ilTextInputGUI($this->lng->txt('mail_to'), 'rcp_to');
        $inp->setRequired(true);
        $inp->setSize(50);
        $inp->setValue((string) ($mailData['rcp_to'] ?? ''));
        $inp->setDataSource($dsDataLink, ',');
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('cc'), 'rcp_cc');
        $inp->setSize(50);
        $inp->setValue((string) ($mailData['rcp_cc'] ?? ''));
        $inp->setDataSource($dsDataLink, ',');
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('bc'), 'rcp_bcc');
        $inp->setSize(50);
        $inp->setValue($mailData['rcp_bcc'] ?? '');
        $inp->setDataSource($dsDataLink, ',');
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
        $inp->setSize(50);
        $inp->setRequired(true);
        $inp->setValue((string) ($mailData['m_subject'] ?? ''));
        $form_gui->addItem($inp);

        $att = new ilMailFormAttachmentPropertyGUI(
            $this->lng->txt(
                isset($mailData['attachments']) && is_array($mailData['attachments']) ?
                'edit' :
                'add'
            )
        );
        if (isset($mailData['attachments']) && is_array($mailData['attachments'])) {
            foreach ($mailData['attachments'] as $data) {
                if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . $data)) {
                    $hidden = new ilHiddenInputGUI('attachments[]');
                    $form_gui->addItem($hidden);
                    $size = filesize($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . $data);
                    $label = $data . ' [' . ilUtil::formatSize($size) . ']';
                    $att->addItem($label);
                    $hidden->setValue(urlencode($data));
                }
            }
        }
        $form_gui->addItem($att);

        $context = new ilMailTemplateGenericContext();
        if (ilMailFormCall::getContextId()) {
            $context_id = ilMailFormCall::getContextId();

            $mailData['use_placeholders'] = true;

            try {
                $context = ilMailTemplateContextService::getTemplateContextById($context_id);

                $templates = $this->templateService->loadTemplatesForContextId($context->getId());
                if ($templates !== []) {
                    $options = [];

                    $template_chb = new ilMailTemplateSelectInputGUI(
                        $this->lng->txt('mail_template_client'),
                        'template_id',
                        $this->ctrl->getLinkTarget($this, 'getTemplateDataById', '', true),
                        ['m_subject' => false, 'm_message' => true]
                    );

                    foreach ($templates as $template) {
                        $options[$template->getTplId()] = $template->getTitle();

                        if (!isset($mailData['template_id']) && $template->isDefault()) {
                            $template_chb->setValue((string) $template->getTplId());
                            $form_gui->getItemByPostVar('m_subject')->setValue($template->getSubject());
                            $mailData['m_message'] = $template->getMessage() . $this->umail->appendSignature(
                                $mailData['m_message']
                            );
                        }
                    }
                    if (isset($mailData['template_id'])) {
                        $template_chb->setValue((string) ((int) $mailData['template_id']));
                    }
                    asort($options);

                    $template_chb->setInfo($this->lng->txt('mail_template_client_info'));
                    $template_chb->setOptions(['' => $this->lng->txt('please_choose')] + $options);
                    $form_gui->addItem($template_chb);
                }
            } catch (Exception) {
                ilLoggerFactory::getLogger('mail')->error(sprintf(
                    '%s has been called with invalid context id: %s.',
                    __METHOD__,
                    $context_id
                ));
            }
        }

        $inp = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
        $inp->setValue((string) ($mailData['m_message'] ?? ''));
        $inp->setRequired(false);
        $inp->setCols(60);
        $inp->setRows(10);
        $form_gui->addItem($inp);

        $chb = new ilCheckboxInputGUI(
            $this->lng->txt('mail_serial_letter_placeholders'),
            'use_placeholders'
        );
        $chb->setValue('1');
        $chb->setChecked(isset($mailData['use_placeholders']) && $mailData['use_placeholders']);

        $placeholders = new ilManualPlaceholderInputGUI($this->lng->txt('mail_form_placeholders_label'), 'm_message');
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        foreach ($context->getPlaceholders() as $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }
        $chb->addSubItem($placeholders);
        $form_gui->addItem($chb);

        $form_gui->addCommandButton('sendMessage', $this->lng->txt('send_mail'));
        $form_gui->addCommandButton('saveDraft', $this->lng->txt('save_message'));
        if (ilMailFormCall::isRefererStored()) {
            $form_gui->addCommandButton('cancelMail', $this->lng->txt('cancel'));
        }

        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable('FORM', $form_gui->getHTML());

        $this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
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
        $files = $this->getBodyParam(
            'attachments',
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->custom()->transformation(function ($elm): string {
                    $attachment = $this->refinery->kindlyTo()->string()->transform($elm);

                    return urldecode($attachment);
                })
            ),
            []
        );

        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($this->getBodyParam('rcp_to', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('rcp_cc', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('rcp_bcc', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('m_subject', $this->refinery->kindlyTo()->string(), '')),
            ilUtil::securePlainString($this->getBodyParam('m_message', $this->refinery->kindlyTo()->string(), '')),
            $this->getBodyParam('use_placeholders', $this->refinery->kindlyTo()->bool(), false),
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
}
