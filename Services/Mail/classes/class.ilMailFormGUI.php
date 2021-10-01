<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
* @author Jens Conze
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailFormGUI: ilMailFolderGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailingListsGUI
*/
class ilMailFormGUI
{
    private ilGlobalTemplateInterface $tpl;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilObjUser $user;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private ilRbacSystem $rbacsystem;
    private ilFormatMail $umail;
    private ilMailBox $mbox;
    private ilFileDataMail $mfile;
    private GlobalHttpState $http;
    private Refinery $refinery;
    private int $requestMailObjId = 0;
    private ?array $requestAttachments = null;
    private string $requestMailSubject = "";
    protected ilMailTemplateService $templateService;
    private ilMailBodyPurifier $purifier;

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
        $this->rbacsystem = $DIC->rbac()->system();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->umail = new ilFormatMail($this->user->getId());
        $this->mfile = new ilFileDataMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());
        $this->purifier = $bodyPurifier ?? new ilMailBodyPurifier();

        if ($this->http->wrapper()->post()->has('mobj_id')) {
            $this->requestMailObjId = $this->http->wrapper()->post()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        }

        $this->ctrl->setParameter($this, 'mobj_id', $this->requestMailObjId);
    }

    public function executeCommand() : void
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch (strtolower($forward_class)) {
            case ilMailFolderGUI::class:
                $this->ctrl->forwardCommand(new ilMailFolderGUI());
                break;

            case strtolower(ilMailAttachmentGUI::class):
                $this->ctrl->setReturn($this, "returnFromAttachments");
                $this->ctrl->forwardCommand(new ilMailAttachmentGUI());
                break;

            case strtolower(ilMailSearchGUI::class):
                $this->ctrl->setReturn($this, "searchResults");
                $this->ctrl->forwardCommand(new ilMailSearchGUI());
                break;

            case strtolower(ilMailSearchCoursesGUI::class):
                $this->ctrl->setReturn($this, "searchResults");
                $this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
                break;
            
            case strtolower(ilMailingListsGUI::class):
                $this->ctrl->setReturn($this, 'searchResults');
                $this->ctrl->forwardCommand(new ilMailingListsGUI());
                break;

            case strtolower(ilMailSearchGroupsGUI::class):
                $this->ctrl->setReturn($this, "searchResults");
                $this->ctrl->forwardCommand(new ilMailSearchGroupsGUI());
                break;

            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = "showForm";
                }

                $this->$cmd();
                break;
        }
    }

    /**
     * @param string[] $files
     * @return string[]
     */
    protected function decodeAttachmentFiles(array $files) : array
    {
        $decodedFiles = [];

        foreach ($files as $value) {
            if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . urldecode($value))) {
                $decodedFiles[] = urldecode($value);
            }
        }

        return $decodedFiles;
    }

    public function sendMessage() : void
    {
        $message = '';
        if ($this->http->wrapper()->post()->has('m_message')) {
            $message = $this->http->wrapper()->post()->retrieve('m_message', $this->refinery->kindlyTo()->string());
        }

        $mailBody = new ilMailBody($message, $this->purifier);

        $sanitizedMessage = $mailBody->getContent();

        $attachments = [];
        if ($this->http->wrapper()->post()->has('attachments')) {
            $attachments = $this->http->wrapper()->post()->retrieve(
                'attachments',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        $files = $this->decodeAttachmentFiles(
            $this->requestAttachments ? (array) $this->requestAttachments : $attachments
        );

        $mailer = $this->umail
            ->withContextId(ilMailFormCall::getContextId() ?: '')
            ->withContextParameters(
                is_array(ilMailFormCall::getContextParameters()) ?
                    ilMailFormCall::getContextParameters() :
                    []
            );

        $mailer->setSaveInSentbox(true);

        $rcpTo = "";
        if ($this->http->wrapper()->post()->has('rcp_to')) {
            $rcpTo = $this->http->wrapper()->post()->retrieve('rcp_to', $this->refinery->kindlyTo()->string());
        }
        $rcpCc = "";
        if ($this->http->wrapper()->post()->has('rcp_cc')) {
            $rcpCc = $this->http->wrapper()->post()->retrieve('rcp_cc', $this->refinery->kindlyTo()->string());
        }
        $rcpBcc = "";
        if ($this->http->wrapper()->post()->has('rcp_bcc')) {
            $rcpBcc = $this->http->wrapper()->post()->retrieve('rcp_bcc', $this->refinery->kindlyTo()->string());
        }
        $mSubject = "";
        if ($this->http->wrapper()->post()->has('m_subject')) {
            $mSubject = $this->http->wrapper()->post()->retrieve(
                'm_subject',
                $this->refinery->kindlyTo()->string()
            );
        }

        $usePlaceholders = false;
        if ($this->http->wrapper()->post()->has('use_placholder')) {
            $usePlaceholders = $this->http->wrapper()->post()->retrieve(
                'use_placeholder',
                $this->refinery->kindlyTo()->bool()
            );
        }

        if ($errors = $mailer->enqueue(
            ilUtil::securePlainString($rcpTo),
            ilUtil::securePlainString($rcpCc),
            ilUtil::securePlainString($rcpBcc),
            ilUtil::securePlainString($mSubject),
            $sanitizedMessage,
            $files,
            $usePlaceholders
        )
        ) {
            $this->requestAttachments = $files;
            $this->showSubmissionErrors($errors);
        } else {
            $mailer->savePostData(
                $this->user->getId(),
                [],
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                ""
            );

            $this->ctrl->setParameterByClass(ilMailGUI::class, 'type', 'message_sent');

            if (ilMailFormCall::isRefererStored()) {
                ilUtil::sendSuccess($this->lng->txt('mail_message_send'), true);
                $this->ctrl->redirectToURL(ilMailFormCall::getRefererRedirectUrl());
            } else {
                $this->ctrl->redirectByClass(ilMailGUI::class);
            }
        }

        $this->showForm();
    }

    public function saveDraft() : void
    {
        $mSubject = '';
        if ($this->http->wrapper()->post()->has('m_subject')) {
            $mSubject = $this->http->wrapper()->post()->retrieve('m_subject', $this->refinery->kindlyTo()->string());
        }
        if (!$mSubject) {
            $this->requestMailSubject = 'No title';
        }

        $draftFolderId = $this->mbox->getDraftsFolder();
        $attachments = [];
        if ($this->http->wrapper()->post()->has('attachements')) {
            $userId = $this->http->wrapper()->post()->retrieve(
                'attachements',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        $files = $this->decodeAttachmentFiles(
            $this->requestAttachments ?
            (array) $this->requestAttachments :
            $attachments
        );

        $rcpTo = "";
        if ($this->http->wrapper()->post()->has('rcp_to')) {
            $rcpTo = $this->http->wrapper()->post()->retrieve('rcp_to', $this->refinery->kindlyTo()->string());
        }
        $rcpCc = "";
        if ($this->http->wrapper()->post()->has('rcp_cc')) {
            $rcpCc = $this->http->wrapper()->post()->retrieve('rcp_cc', $this->refinery->kindlyTo()->string());
        }
        $rcpBcc = "";
        if ($this->http->wrapper()->post()->has('rcp_bcc')) {
            $rcpBcc = $this->http->wrapper()->post()->retrieve('rcp_bcc', $this->refinery->kindlyTo()->string());
        }

        $mEmail = "";
        if ($this->http->wrapper()->post()->has('m_email')) {
            $mEmail = $this->http->wrapper()->post()->retrieve('m_email', $this->refinery->kindlyTo()->string());
        }
        $mMessage = "";
        if ($this->http->wrapper()->post()->has('m_message')) {
            $mMessage = $this->http->wrapper()->post()->retrieve(
                'm_message',
                $this->refinery->kindlyTo()->string()
            );
        }
        $usePlaceholder = false;
        if ($this->http->wrapper()->post()->has('use_placeholders')) {
            $usePlaceholder = $this->http->wrapper()->post()->retrieve(
                'use_placeholders',
                $this->refinery->kindlyTo()->bool()
            );
        }

        if ($errors = $this->umail->validateRecipients(
            (string) ilUtil::securePlainString($rcpTo),
            (string) ilUtil::securePlainString($rcpCc),
            (string) ilUtil::securePlainString($rcpBcc)
        )) {
            $this->showSubmissionErrors($errors);
            $this->showForm();
            return;
        }

        if (ilSession::get("draft")) {
            $draftId = (int) ilSession::get('draft');
            ilSession::clear('draft');
        } else {
            $draftId = $this->umail->getNewDraftId($this->user->getId(), $draftFolderId);
        }

        $this->umail->updateDraft(
            $draftFolderId,
            $files,
            ilUtil::securePlainString($rcpTo),
            ilUtil::securePlainString($rcpCc),
            ilUtil::securePlainString($rcpBcc),
            ilUtil::securePlainString($mEmail),
            ilUtil::securePlainString(
                $mSubject
            ) ?? $this->requestMailSubject,
            ilUtil::securePlainString($mMessage),
            $draftId,
            $usePlaceholder,
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        ilUtil::sendInfo($this->lng->txt('mail_saved'), true);

        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        } else {
            $this->ctrl->redirectByClass([ilMailGUI::class, ilMailFolderGUI::class]);
        }

        $this->showForm();
    }

    public function searchUsers(bool $save = true) : void
    {
        $this->tpl->setTitle($this->lng->txt("mail"));



        if ($save) {
            $files = [];
            $attachments = $this->requestAttachments;
            if ($this->http->wrapper()->post()->has('attachements')) {
                $userId = $this->http->wrapper()->post()->retrieve(
                    'attachements',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
                );
            }

            if (is_array($attachments)) {
                foreach ($attachments as $value) {
                    $files[] = urldecode($value);
                }
            }


            $rcpTo = "";
            if ($this->http->wrapper()->post()->has('rcp_to')) {
                $rcpTo = $this->http->wrapper()->post()->retrieve('rcp_to', $this->refinery->kindlyTo()->string());
            }
            $rcpCc = "";
            if ($this->http->wrapper()->post()->has('rcp_cc')) {
                $rcpCc = $this->http->wrapper()->post()->retrieve('rcp_cc', $this->refinery->kindlyTo()->string());
            }
            $rcpBcc = "";
            if ($this->http->wrapper()->post()->has('rcp_bcc')) {
                $rcpBcc = $this->http->wrapper()->post()->retrieve(
                    'rcp_bcc',
                    $this->refinery->kindlyTo()->string()
                );
            }

            $mEmail = "";
            if ($this->http->wrapper()->post()->has('m_email')) {
                $mEmail = $this->http->wrapper()->post()->retrieve(
                    'm_email',
                    $this->refinery->kindlyTo()->string()
                );
            }
            $mSubject = $this->requestMailSubject;
            if ($this->http->wrapper()->post()->has('m_subject')) {
                $mSubject = $this->http->wrapper()->post()->retrieve(
                    'm_subject',
                    $this->refinery->kindlyTo()->string()
                );
            }
            $mMessage = "";
            if ($this->http->wrapper()->post()->has('m_message')) {
                $mMessage = $this->http->wrapper()->post()->retrieve(
                    'm_message',
                    $this->refinery->kindlyTo()->string()
                );
            }
            $usePlaceholder = false;
            if ($this->http->wrapper()->post()->has('use_placeholders')) {
                $usePlaceholder = $this->http->wrapper()->post()->retrieve(
                    'use_placeholders',
                    $this->refinery->kindlyTo()->bool()
                );
            }

            // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
            $this->umail->savePostData(
                $this->user->getId(),
                $files,
                ilUtil::securePlainString($rcpTo),
                ilUtil::securePlainString($rcpCc),
                ilUtil::securePlainString($rcpBcc),
                ilUtil::securePlainString($mEmail),
                ilUtil::securePlainString(
                    $mSubject
                ),
                ilUtil::securePlainString($mMessage),
                $usePlaceholder,
                ilMailFormCall::getContextId(),
                ilMailFormCall::getContextParameters()
            );
        }

        $form = new ilPropertyFormGUI();
        $form->setId('search_rcp');
        $form->setTitle($this->lng->txt('search_recipients'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'search'));

        $inp = new ilTextInputGUI($this->lng->txt("search_for"), 'search');
        $inp->setSize(30);
        $dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true);
        $inp->setDataSource($dsDataLink);

        $searchQuery = trim((string) ilSession::get('mail_search_search'));
        if ($searchQuery !== '') {
            $inp->setValue(ilUtil::prepareFormOutput($searchQuery, true));
        }
        $form->addItem($inp);

        $form->addCommandButton('search', $this->lng->txt("search"));
        $form->addCommandButton('cancelSearch', $this->lng->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    public function searchCoursesTo() : void
    {
        $this->saveMailBeforeSearch();

        if ($this->http->wrapper()->post()->has('search_crs')) {
            $this->ctrl->setParameterByClass(
                ilMailSearchCoursesGUI::class,
                'cmd',
                'showMembers'
            );
        }
        
        $this->ctrl->setParameterByClass(ilMailSearchCoursesGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailSearchCoursesGUI::class);
    }

    public function searchGroupsTo() : void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass(ilMailSearchGroupsGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailSearchGroupsGUI::class);
    }

    public function search() : void
    {
        $mailSearch = '';
        if ($this->http->wrapper()->post()->has('search')) {
            $mailSearch = $this->http->wrapper()->post()->retrieve('search', $this->refinery->kindlyTo()->string());
        }
        ilSession::set("mail_search_search", $mailSearch);
        if (trim(ilSession::get("mail_search_search")) === '') {
            ilUtil::sendInfo($this->lng->txt("mail_insert_query"));
            $this->searchUsers(false);
        } elseif (strlen(trim(ilSession::get("mail_search_search"))) < 3) {
            $this->lng->loadLanguageModule('search');
            ilUtil::sendInfo($this->lng->txt('search_minimum_three'));
            $this->searchUsers(false);
        } else {
            $this->ctrl->setParameterByClass(
                ilMailSearchGUI::class,
                "search",
                urlencode(ilSession::get("mail_search_search"))
            );
            $this->ctrl->redirectByClass(ilMailSearchGUI::class);
        }
    }

    public function cancelSearch() : void
    {
        ilSession::clear("mail_search");
        $this->searchResults();
    }

    public function editAttachments() : void
    {
        // decode post values
        $files = [];
        $attachments = $this->requestAttachments;
        if ($this->http->wrapper()->post()->has('attachements')) {
            $attachments = $this->http->wrapper()->post()->retrieve(
                'attachements',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }
        if (is_array($attachments)) {
            foreach ($attachments as $value) {
                $files[] = urldecode($value);
            }
        }

        $rcpTo = "";
        if ($this->http->wrapper()->post()->has('rcp_to')) {
            $rcpTo = $this->http->wrapper()->post()->retrieve('rcp_to', $this->refinery->kindlyTo()->string());
        }
        $rcpCc = "";
        if ($this->http->wrapper()->post()->has('rcp_cc')) {
            $rcpCc = $this->http->wrapper()->post()->retrieve('rcp_cc', $this->refinery->kindlyTo()->string());
        }
        $rcpBcc = "";
        if ($this->http->wrapper()->post()->has('rcp_bcc')) {
            $rcpBcc = $this->http->wrapper()->post()->retrieve('rcp_bcc', $this->refinery->kindlyTo()->string());
        }
        $mEmail = false;
        if ($this->http->wrapper()->post()->has('m_email')) {
            $mEmail = $this->http->wrapper()->post()->retrieve('m_email', $this->refinery->kindlyTo()->string());
        }
        $mSubject = $this->requestMailSubject;
        if ($this->http->wrapper()->post()->has('m_subject')) {
            $mSubject = $this->http->wrapper()->post()->retrieve(
                'm_subject',
                $this->refinery->kindlyTo()->string()
            );
        }
        $mMessage = "";
        if ($this->http->wrapper()->post()->has('m_message')) {
            $mMessage = $this->http->wrapper()->post()->retrieve(
                'm_message',
                $this->refinery->kindlyTo()->string()
            );
        }
        $usePlaceholder = false;
        if ($this->http->wrapper()->post()->has('use_placeholders')) {
            $usePlaceholder = $this->http->wrapper()->post()->retrieve(
                'use_placeholders',
                $this->refinery->kindlyTo()->bool()
            );
        }

        // Note: For security reasons, ILIAS only allows Plain text messages.
        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($rcpTo),
            ilUtil::securePlainString($rcpCc),
            ilUtil::securePlainString($rcpBcc),
            (bool) $mEmail,
            ilUtil::securePlainString(
                $mSubject
            ),
            ilUtil::securePlainString($mMessage),
            $usePlaceholder,
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        $this->ctrl->redirectByClass(ilMailAttachmentGUI::class);
    }

    public function returnFromAttachments() : void
    {
        ilSession::set("type", "attach");
        $this->showForm();
    }
    
    public function searchResults() : void
    {
        ilSession::set("type", "search_res");
        $this->showForm();
    }

    public function mailUser() : void
    {
        ilSession::set("type", "new");
        $this->showForm();
    }

    public function mailRole() : void
    {
        ilSession::set("type", "role");
        $this->showForm();
    }

    public function replyMail() : void
    {
        ilSession::set("type", "reply");
        $this->showForm();
    }

    public function mailAttachment() : void
    {
        ilSession::set("type", "attach");
        $this->showForm();
    }

    protected function getTemplateDataById() : void
    {
        if (!$this->http->wrapper()->query()->has('template_id')) {
            exit();
        }

        try {
            $template = $this->templateService->loadTemplateForId(
                $this->http->wrapper()->query()->retrieve('template_id', $this->refinery->kindlyTo()->int())
            );
            $context = ilMailTemplateContextService::getTemplateContextById((string) $template->getContext());

            echo json_encode([
                'm_subject' => $template->getSubject(),
                'm_message' => $template->getMessage(),
            ], JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
        }
        exit();
    }

    public function showForm() : void
    {
        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.mail_new.html",
            "Services/Mail"
        );
        $this->tpl->setTitle($this->lng->txt("mail"));
        
        $this->lng->loadLanguageModule("crs");

        if (ilMailFormCall::isRefererStored()) {
            $this->tabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTarget($this, 'cancelMail')
            );
        }

        $mailData = [];
        $mailData["rcp_to"] = '';
        $mailData["rcp_cc"] = '';
        $mailData["rcp_bcc"] = '';
        $mailData["attachments"] = [];

        $rcpTo = ilSession::get('rcp_to');
        if ($this->http->wrapper()->query()->has('rcp_to')) {
            $rcpTo = $this->http->wrapper()->query()->retrieve('rcp_to', $this->refinery->kindlyTo()->string());
        }
        $rcpCc = ilSession::get('rcp_cc');
        if ($this->http->wrapper()->query()->has('rcp_cc')) {
            $rcpCc = $this->http->wrapper()->query()->retrieve('rcp_cc', $this->refinery->kindlyTo()->string());
        }
        $rcpBcc = ilSession::get('rcp_bcc');
        if ($this->http->wrapper()->query()->has('rcp_bcc')) {
            $rcpBcc = $this->http->wrapper()->query()->retrieve('rcp_bcc', $this->refinery->kindlyTo()->string());
        }
        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }


        $type = ilSession::get("type");
        if ($this->http->wrapper()->query()->has('type')) {
            $type = $this->http->wrapper()->query()->retrieve('type', $this->refinery->kindlyTo()->string());
        }

        switch ($type) {
            case 'reply':
//                if (ilSession::get('mail_id')) {
//                    $this->httpRequest->getQueryParams()['mail_id'] = ilSession::get('mail_id');
//                }
                $mailData = $this->umail->getMail((int) ilSession::get('mail_id'));
                $mailData["m_subject"] = $this->umail->formatReplySubject();
                $mailData["m_message"] = $this->umail->formatReplyMessage();
                $mailData["m_message"] = $this->umail->prependSignature();
                // NO ATTACHMENTS FOR REPLIES
                $mailData["attachments"] = [];
                //$mailData["rcp_cc"] = $this->umail->formatReplyRecipientsForCC();g
                $mailData["rcp_cc"] = '';
                $mailData["rcp_to"] = $this->umail->formatReplyRecipient();
                ilSession::set("mail_id", "");
                break;
        
            case 'search_res':
                $mailData = $this->umail->getSavedData();

                /*if(ilSession("mail_search_results"))
                {
                    $mailData = $this->umail->appendSearchResult(
                        ilSession::get("mail_search_results"),
                        ilSession::get("mail_search")
                    );
                }
                ilSession::clear("mail_search");
                ilSession::clear("mail_search_results");*/

                if (ilSession::get('mail_search_results_to')) {
                    $mailData = $this->umail->appendSearchResult(
                        ilSession::get("mail_search_results_to"),
                        'to'
                    );
                }
                if (ilSession::get('mail_search_results_cc')) {
                    $mailData = $this->umail->appendSearchResult(
                        ilSession::get("mail_search_results_cc"),
                        'cc'
                    );
                }
                if (ilSession::get('mail_search_results_bcc')) {
                    $mailData = $this->umail->appendSearchResult(
                        ilSession::get("mail_search_results_bcc"),
                        'bc'
                    );
                }

                ilSession::clear("mail_search_results_to");
                ilSession::clear("mail_search_results_cc");
                ilSession::clear("mail_search_results_bcc");

                break;
        
            case 'attach':
                $mailData = $this->umail->getSavedData();
                break;
        
            case 'draft':
                ilSession::set("draft", $mailId);
                $mailData = $this->umail->getMail($mailId);
                ilMailFormCall::setContextId($mailData['tpl_ctx_id']);
                ilMailFormCall::setContextParameters($mailData['tpl_ctx_params']);
                break;
        
            case 'forward':
                $mailData = $this->umail->getMail($mailId);
                $mailData["rcp_to"] = $mailData["rcp_cc"] = $mailData["rcp_bcc"] = '';
                $mailData["m_subject"] = $this->umail->formatForwardSubject();
                $mailData["m_message"] = $this->umail->prependSignature();
                if (is_array($mailData["attachments"]) && count($mailData["attachments"])) {
                    if ($error = $this->mfile->adoptAttachments(
                        $mailData["attachments"],
                        $mailId
                    )
                    ) {
                        ilUtil::sendInfo($error);
                    }
                }
                break;
        
            case 'new':

                // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                $mailData["rcp_to"] = ilUtil::securePlainString($rcpTo);
                $mailData["rcp_cc"] = ilUtil::securePlainString($rcpCc);
                $mailData["rcp_bcc"] = ilUtil::securePlainString($rcpBcc);

                $mailData['m_message'] = '';
                if (($sig = ilMailFormCall::getSignature()) !== '') {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13)
                        . chr(10)
                        . chr(13)
                        . chr(10);
                }
                $mailData['m_message'] .= $this->umail->appendSignature();

                ilSession::set('rcp_to', '');
                ilSession::set('rcp_cc', '');
                ilSession::set('rcp_bcc', '');
                break;
        
            case 'role':
                $roles = ilSession::get('mail_roles');
                if ($this->http->wrapper()->post()->has('roles')) {
                    $roles = $this->http->wrapper()->post()->retrieve(
                        'roles',
                        $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
                    );
                }

                // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                $mailData['rcp_to'] = ilUtil::securePlainString(
                    implode(',', $roles)
                );

                $mailData['m_message'] = '';
                if (strlen($sig = ilMailFormCall::getSignature())) {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13)
                        . chr(10)
                        . chr(13)
                        . chr(10);
                }

                $additionalMessageText = "";
                if ($this->http->wrapper()->post()->has('additional_message_text')) {
                    $additionalMessageText = $this->http->wrapper()->post()->retrieve(
                        'additional_message_text',
                        $this->refinery->kindlyTo()->string()
                    );
                }

                $mailData['m_message'] .= $additionalMessageText
                    . chr(13)
                    . chr(10)
                    . $this->umail->appendSignature();
//                $this->httpRequest->getParsedBody()["additional_message_text"] = "";
                ilSession::set('mail_roles', []);
                break;
        
            case 'address':
                $rcp = "";
                if ($this->http->wrapper()->query()->has('rcp')) {
                    $rcp = $this->http->wrapper()->query()->retrieve('rcp', $this->refinery->kindlyTo()->string());
                }
                $mailData["rcp_to"] = urldecode($rcp);
                break;
        
            default:
                // GET DATA FROM POST
                $mailData = $this->http->request()->getParsedBody();

                // strip slashes
                foreach ($mailData as $key => $value) {
                    if (is_string($value)) {
                        // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                        $mailData[$key] = ilUtil::securePlainString($value);
                    }
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
        if (isset($mailData["rcp_to"])) {
            $inp->setValue($mailData["rcp_to"]);
        }
        $inp->setDataSource($dsDataLink, ",");
        $inp->setMaxLength(null);
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('cc'), 'rcp_cc');
        $inp->setSize(50);
        if (isset($mailData["rcp_cc"])) {
            $inp->setValue($mailData["rcp_cc"]);
        }
        $inp->setDataSource($dsDataLink, ",");
        $inp->setMaxLength(null);
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('bc'), 'rcp_bcc');
        $inp->setSize(50);
        if (isset($mailData["rcp_bcc"])) {
            $inp->setValue($mailData["rcp_bcc"]);
        }
        $inp->setDataSource($dsDataLink, ",");
        $inp->setMaxLength(null);
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
        $inp->setSize(50);
        $inp->setRequired(true);
        $inp->setValue($mailData["m_subject"] ?? "");
        $form_gui->addItem($inp);

        $att = null;
        if (isset($mailData["attachments"])) {
            $att = new ilMailFormAttachmentPropertyGUI(
                $this->lng->txt(($mailData["attachments"]) ?
                    'edit' :
                    'add')
            );
        }

        if (isset($mailData["attachments"]) && is_array($mailData["attachments"])) {
            foreach ($mailData["attachments"] as $data) {
                if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . "_" . $data)) {
                    $hidden = new ilHiddenInputGUI('attachments[]');
                    $form_gui->addItem($hidden);
                    $size = filesize($this->mfile->getMailPath() . '/' . $this->user->getId() . "_" . $data);
                    $label = $data . " [" . ilUtil::formatSize($size) . "]";
                    $att->addItem($label);
                    $hidden->setValue(urlencode($data));
                }
            }
        }
        if ($att) {
            $form_gui->addItem($att);
        }

        $context = new ilMailTemplateGenericContext();
        if (ilMailFormCall::getContextId()) {
            $context_id = ilMailFormCall::getContextId();

            $mailData['use_placeholders'] = true;

            try {
                $context = ilMailTemplateContextService::getTemplateContextById($context_id);

                $templates = $this->templateService->loadTemplatesForContextId($context->getId());
                if (count($templates) > 0) {
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
                            $template_chb->setValue($template->getTplId());
                            $form_gui->getItemByPostVar('m_subject')->setValue($template->getSubject());
                            $mailData["m_message"] = $template->getMessage();
                        }
                    }
                    if (isset($mailData['template_id'])) {
                        $template_chb->setValue((int) $mailData['template_id']);
                    }
                    asort($options);

                    $template_chb->setInfo($this->lng->txt('mail_template_client_info'));
                    $template_chb->setOptions(['' => $this->lng->txt('please_choose')] + $options);
                    $form_gui->addItem($template_chb);
                }
            } catch (Exception $e) {
                ilLoggerFactory::getLogger('mail')->error(sprintf(
                    '%s has been called with invalid context id: %s.',
                    __METHOD__,
                    $context_id
                ));
            }
        }

        $inp = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
        //$inp->setValue(htmlspecialchars($mailData["m_message"], false));
        if (isset($mailData["m_message"])) {
            $inp->setValue($mailData["m_message"]);
        }
        $inp->setRequired(false);
        $inp->setCols(60);
        $inp->setRows(10);
        $form_gui->addItem($inp);

        $chb = new ilCheckboxInputGUI(
            $this->lng->txt('mail_serial_letter_placeholders'),
            'use_placeholders'
        );
        $chb->setValue('1');
        if (isset($mailData['use_placeholders']) && $mailData['use_placeholders']) {
            $chb->setChecked(true);
        }

        $placeholders = new ilManualPlaceholderInputGUI('m_message');
        $placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
        $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        foreach ($context->getPlaceholders() as $key => $value) {
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

    public function lookupRecipientAsync() : void
    {
        $search = '';
        if ($this->http->wrapper()->query()->has('term')) {
            $search = $this->http->wrapper()->query()->retrieve('term', $this->refinery->kindlyTo()->string());
        }
        if ($this->http->wrapper()->post()->has('term')) {
            $search = $this->http->wrapper()->post()->retrieve('term', $this->refinery->kindlyTo()->string());
        }

        $search = trim($search);

        $result = [];

        if (ilStr::strLen($search) < 3) {
            echo json_encode($result);
            exit;
        }

        // #14768
        $quoted = ilUtil::stripSlashes($search);
        $quoted = str_replace(['%', '_'], ['\%', '\_'], $quoted);

        $mailFormObj = new ilMailForm();
        $result = $mailFormObj->getRecipientAsync("%" . $quoted . "%", ilUtil::stripSlashes($search));

        echo json_encode($result);
        exit;
    }

    public function cancelMail() : void
    {
        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        }

        $this->showForm();
    }
    
    protected function saveMailBeforeSearch() : void
    {
        $files = [];

        $rcpTo = "";
        if ($this->http->wrapper()->post()->has('rcp_to')) {
            $rcpTo = $this->http->wrapper()->post()->retrieve('rcp_to', $this->refinery->kindlyTo()->string());
        }
        $rcpCc = "";
        if ($this->http->wrapper()->post()->has('rcp_cc')) {
            $rcpCc = $this->http->wrapper()->post()->retrieve('rcp_cc', $this->refinery->kindlyTo()->string());
        }
        $rcpBcc = "";
        if ($this->http->wrapper()->post()->has('rcp_bcc')) {
            $rcpBcc = $this->http->wrapper()->post()->retrieve('rcp_bcc', $this->refinery->kindlyTo()->string());
        }
        $mEmail = false;
        if ($this->http->wrapper()->post()->has('m_email')) {
            $mEmail = $this->http->wrapper()->post()->retrieve('m_email', $this->refinery->kindlyTo()->string());
        }
        $mMessage = "";
        if ($this->http->wrapper()->post()->has('m_message')) {
            $mMessage = $this->http->wrapper()->post()->retrieve(
                'm_message',
                $this->refinery->kindlyTo()->string()
            );
        }
        $mSubject = "";
        if ($this->http->wrapper()->post()->has('m_subject')) {
            $mSubject = $this->http->wrapper()->post()->retrieve(
                'm_subject',
                $this->refinery->kindlyTo()->string()
            );
        }
        $usePlaceholder = false;
        if ($this->http->wrapper()->post()->has('use_placeholders')) {
            $usePlaceholder = $this->http->wrapper()->post()->retrieve(
                'use_placeholders',
                $this->refinery->kindlyTo()->bool()
            );
        }
        $attachments = $this->requestAttachments;
        if ($this->http->wrapper()->post()->has('attachments')) {
            $usePlaceholder = $this->http->wrapper()->post()->retrieve(
                'attachments',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        if (is_array($attachments)) {
            foreach ($attachments as $value) {
                $files[] = urldecode($value);
            }
        }

        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($rcpTo),
            ilUtil::securePlainString($rcpCc),
            ilUtil::securePlainString($rcpBcc),
            ilUtil::securePlainString($mEmail),
            ilUtil::securePlainString($mSubject),
            ilUtil::securePlainString($mMessage),
            ilUtil::securePlainString($usePlaceholder),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );
    }

    public function searchMailingListsTo() : void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass(ilMailingListsGUI::class, 'ref', 'mail');
        $this->ctrl->redirectByClass(ilMailingListsGUI::class);
    }

    /**
     * @param $errors ilMailError[]
     */
    protected function showSubmissionErrors(array $errors) : void
    {
        $formatter = new ilMailErrorFormatter($this->lng);
        $formattedErrors = $formatter->format($errors);

        if ($formattedErrors !== '') {
            ilUtil::sendFailure($formattedErrors);
        }
    }
}
