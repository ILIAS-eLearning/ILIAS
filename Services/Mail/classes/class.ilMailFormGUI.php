<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\RequestInterface;

/**
* @author Jens Conze
* @version $Id$
*
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
    private RequestInterface $httpRequest;
    private int $requestMailObjId = 0;
    private ?array $requestAttachments = null;
    private string $requestMailSubject = "";
    protected ?ilMailTemplateService $templateService;
    private ?ilMailBodyPurifier $purifier;

    /**
     * ilMailFormGUI constructor.
     * @param ilMailTemplateService|null $templateService
     * @param ilMailBodyPurifier|null $bodyPurifier
     */
    public function __construct(
        ilMailTemplateService $templateService = null,
        ilMailBodyPurifier $bodyPurifier = null
    ) {
        global $DIC;

        if (null === $templateService) {
            $templateService = $DIC['mail.texttemplates.service'];
        }
        $this->templateService = $templateService;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->httpRequest = $DIC->http()->request();

        $this->umail = new ilFormatMail($this->user->getId());
        $this->mfile = new ilFileDataMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());

        if (null === $bodyPurifier) {
            $bodyPurifier = new ilMailBodyPurifier();
        }
        $this->purifier = $bodyPurifier;

        if (isset($this->httpRequest->getParsedBody()['mobj_id']) && (int) $this->httpRequest->getParsedBody()['mobj_id']) {
            $this->requestMailObjId = $DIC->refinery()->kindlyTo()->int()->transform(
                $this->httpRequest->getParsedBody()['mobj_id']
            );
        }

        if (0 === $this->requestMailObjId && isset($this->httpRequest->getQueryParams()['mobj_id'])) {
            $this->requestMailObjId = $DIC->refinery()->kindlyTo()->int()->transform(
                $this->httpRequest->getQueryParams()['mobj_id']
            );
        }

        $this->ctrl->setParameter($this, 'mobj_id', $this->requestMailObjId);
    }

    public function executeCommand() : void
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            case 'ilmailfoldergui':
                $this->ctrl->forwardCommand(new ilMailFolderGUI());
                break;

            case 'ilmailattachmentgui':
                $this->ctrl->setReturn($this, "returnFromAttachments");
                $this->ctrl->forwardCommand(new ilMailAttachmentGUI());
                break;

            case 'ilmailsearchgui':
                $this->ctrl->setReturn($this, "searchResults");
                $this->ctrl->forwardCommand(new ilMailSearchGUI());
                break;

            case 'ilmailsearchcoursesgui':
                $this->ctrl->setReturn($this, "searchResults");
                $this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
                break;
            
            case 'ilmailinglistsgui':
                $this->ctrl->setReturn($this, 'searchResults');
                $this->ctrl->forwardCommand(new ilMailingListsGUI());
                break;

            case 'ilmailsearchgroupsgui':
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
     * @param array $files
     * @return array
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
        $message = (string) $this->httpRequest->getParsedBody()['m_message'] ?? "";

        $mailBody = new ilMailBody($message, $this->purifier);

        $sanitizedMessage = $mailBody->getContent();

        $attachments = $this->httpRequest->getParsedBody()['attachments'] ?? [];

        $files = $this->decodeAttachmentFiles($this->requestAttachments ? (array) $this->requestAttachments : $attachments);

        $mailer = $this->umail
            ->withContextId(ilMailFormCall::getContextId() ?: '')
            ->withContextParameters(is_array(ilMailFormCall::getContextParameters()) ? ilMailFormCall::getContextParameters() : []);

        $mailer->setSaveInSentbox(true);

        if ($errors = $mailer->enqueue(
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_to'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_cc'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_bcc'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_subject'] ?? ""),
            $sanitizedMessage,
            $files,
            isset($this->httpRequest->getParsedBody()['use_placeholders']) ? (bool) $this->httpRequest->getParsedBody()['use_placeholders'] : false
        )
        ) {
            $this->requestAttachments = $files;
            $this->showSubmissionErrors($errors);
        } else {
            $mailer->savePostData($this->user->getId(), [], "", "", "", "", "", "", "", "");

            $this->ctrl->setParameterByClass('ilmailgui', 'type', 'message_sent');

            if (ilMailFormCall::isRefererStored()) {
                ilUtil::sendSuccess($this->lng->txt('mail_message_send'), true);
                $this->ctrl->redirectToURL(ilMailFormCall::getRefererRedirectUrl());
            } else {
                $this->ctrl->redirectByClass('ilmailgui');
            }
        }

        $this->showForm();
    }

    public function saveDraft() : void
    {
        if (!$this->httpRequest->getParsedBody()['m_subject']) {
            $this->requestMailSubject = 'No title';
        }

        $draftFolderId = $this->mbox->getDraftsFolder();
        $attachments = $this->httpRequest->getParsedBody()['attachments'] ?? [];

        $files = $this->decodeAttachmentFiles($this->requestAttachments ? (array) $this->requestAttachments : $attachments);

        if ($errors = $this->umail->validateRecipients(
            (string) ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_to'] ?? ""),
            (string) ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_cc'] ?? ""),
            (string) ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_bcc'] ?? "")
        )) {
            $this->httpRequest->getParsedBody()['attachments'] = $files;
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
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_to'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_cc'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_bcc'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_email'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_subject']) ?? $this->requestMailSubject,
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_message']) ?? "",
            $draftId,
            isset($this->httpRequest->getParsedBody()['use_placeholders']) ? (int) $this->httpRequest->getParsedBody()['use_placeholders'] : 0,
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        ilUtil::sendInfo($this->lng->txt('mail_saved'), true);

        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        } else {
            $this->ctrl->redirectByClass(['ilmailgui', 'ilmailfoldergui']);
        }

        $this->showForm();
    }

    public function searchUsers(bool $save = true) : void
    {
        $this->tpl->setTitle($this->lng->txt("mail"));

        if ($save) {
            $files = [];
            $attachments = $this->httpRequest->getParsedBody()['attachments'] ?? $this->requestAttachments;

            if (is_array($attachments)) {
                foreach ($attachments as $value) {
                    $files[] = urldecode($value);
                }
            }

            // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
            $this->umail->savePostData(
                $this->user->getId(),
                $files,
                ilUtil::securePlainString($this->httpRequest->getParsedBody()["rcp_to"] ?? ''),
                ilUtil::securePlainString($this->httpRequest->getParsedBody()["rcp_cc"] ?? ''),
                ilUtil::securePlainString($this->httpRequest->getParsedBody()["rcp_bcc"] ?? ''),
                ilUtil::securePlainString($this->httpRequest->getParsedBody()["m_email"] ?? ''),
                ilUtil::securePlainString($this->httpRequest->getParsedBody()["m_subject"] ?? $this->requestMailSubject),
                ilUtil::securePlainString($this->httpRequest->getParsedBody()["m_message"] ?? ''),
                (bool) ($this->httpRequest->getParsedBody()['use_placeholders'] ?? false),
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

    /**
     *
     */
    public function searchCoursesTo() : void
    {
        $this->saveMailBeforeSearch();

        if (isset($this->httpRequest->getParsedBody()['search_crs'])) {
            $this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'cmd', 'showMembers');
        }
        
        $this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'ref', 'mail');
        $this->ctrl->redirectByClass('ilmailsearchcoursesgui');
    }

    /**
     *
     */
    public function searchGroupsTo() : void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass('ilmailsearchgroupsgui', 'ref', 'mail');
        $this->ctrl->redirectByClass('ilmailsearchgroupsgui');
    }

    public function search() : void
    {
        ilSession::set("mail_search_search", $this->httpRequest->getParsedBody()["search"]);
        if (trim(ilSession::get("mail_search_search")) === '') {
            ilUtil::sendInfo($this->lng->txt("mail_insert_query"));
            $this->searchUsers(false);
        } elseif (strlen(trim(ilSession::get("mail_search_search"))) < 3) {
            $this->lng->loadLanguageModule('search');
            ilUtil::sendInfo($this->lng->txt('search_minimum_three'));
            $this->searchUsers(false);
        } else {
            $this->ctrl->setParameterByClass("ilmailsearchgui", "search", urlencode(ilSession::get("mail_search_search")));
            $this->ctrl->redirectByClass("ilmailsearchgui");
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
        $attachments = $this->httpRequest->getParsedBody()['attachments'] ?? $this->requestAttachments;
        if (is_array($attachments)) {
            foreach ($attachments as $value) {
                $files[] = urldecode($value);
            }
        }

        // Note: For security reasons, ILIAS only allows Plain text messages.
        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($this->httpRequest->getParsedBody()["rcp_to"] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()["rcp_cc"] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()["rcp_bcc"] ?? ""),
            (bool) ($this->httpRequest->getParsedBody()["m_email"] ?? false),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()["m_subject"] ?? $this->requestMailSubject),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()["m_message"] ?? ""),
            (bool) ($this->httpRequest->getParsedBody()["use_placeholders"] ?? false),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        $this->ctrl->redirectByClass("ilmailattachmentgui");
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

    /**
     * Called asynchronously when changing the template
     */
    protected function getTemplateDataById() : void
    {
        if (!isset($this->httpRequest->getQueryParams()['template_id'])) {
            exit();
        }

        try {
            $template = $this->templateService->loadTemplateForId((int) ($this->httpRequest->getQueryParams()['template_id'] ?? 0));
            $context = ilMailTemplateContextService::getTemplateContextById((string) $template->getContext());

            echo json_encode([
                'm_subject' => $template->getSubject(),
                'm_message' => $template->getMessage(),
            ]);
        } catch (Exception $e) {
        }
        exit();
    }

    public function showForm() : void
    {
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_new.html", "Services/Mail");
        $this->tpl->setTitle($this->lng->txt("mail"));
        
        $this->lng->loadLanguageModule("crs");

        if (ilMailFormCall::isRefererStored()) {
            $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'cancelMail'));
        }

        $mailData = [];
        $mailData["rcp_to"] = '';
        $mailData["rcp_cc"] = '';
        $mailData["rcp_cc"] = '';
        $mailData["rcp_bcc"] = '';
        $mailData["attachments"] = [];

        $type = $this->httpRequest->getQueryParams()["type"] ?? ilSession::get("type");
        switch ($type) {
            case 'reply':
                if (ilSession::get('mail_id')) {
                    $this->httpRequest->getQueryParams()['mail_id'] = ilSession::get('mail_id');
                }
                $mailData = $this->umail->getMail((int) ilSession::get('mail_id'));
                $mailData["m_subject"] = $this->umail->formatReplySubject();
                $mailData["m_message"] = $this->umail->formatReplyMessage();
                $mailData["m_message"] = $this->umail->prependSignature();
                // NO ATTACHMENTS FOR REPLIES
                $mailData["attachments"] = [];
                //$mailData["rcp_cc"] = $this->umail->formatReplyRecipientsForCC();
                $mailData["rcp_cc"] = '';
                $mailData["rcp_to"] = $this->umail->formatReplyRecipient();
                ilSession::set("mail_id", "");
                break;
        
            case 'search_res':
                $mailData = $this->umail->getSavedData();

                /*if(ilSession("mail_search_results"))
                {
                    $mailData = $this->umail->appendSearchResult(ilSession::get("mail_search_results"),ilSession::get("mail_search"));
                }
                ilSession::clear("mail_search");
                ilSession::clear("mail_search_results");*/

                if (ilSession::get('mail_search_results_to')) {
                    $mailData = $this->umail->appendSearchResult(ilSession::get("mail_search_results_to"), 'to');
                }
                if (ilSession::get('mail_search_results_cc')) {
                    $mailData = $this->umail->appendSearchResult(ilSession::get("mail_search_results_cc"), 'cc');
                }
                if (ilSession::get('mail_search_results_bcc')) {
                    $mailData = $this->umail->appendSearchResult(ilSession::get("mail_search_results_bcc"), 'bc');
                }

                ilSession::clear("mail_search_results_to");
                ilSession::clear("mail_search_results_cc");
                ilSession::clear("mail_search_results_bcc");

                break;
        
            case 'attach':
                $mailData = $this->umail->getSavedData();
                break;
        
            case 'draft':
                ilSession::set("draft", $this->httpRequest->getQueryParams()["mail_id"] ?? null);
                $mailData = $this->umail->getMail((int) ($this->httpRequest->getQueryParams()["mail_id"] ?? 0));
                ilMailFormCall::setContextId($mailData['tpl_ctx_id']);
                ilMailFormCall::setContextParameters($mailData['tpl_ctx_params']);
                break;
        
            case 'forward':
                $mailData = $this->umail->getMail((int) ($this->httpRequest->getQueryParams()["mail_id"] ?? 0));
                $mailData["rcp_to"] = $mailData["rcp_cc"] = $mailData["rcp_bcc"] = '';
                $mailData["m_subject"] = $this->umail->formatForwardSubject();
                $mailData["m_message"] = $this->umail->prependSignature();
                if (is_array($mailData["attachments"]) && count($mailData["attachments"])) {
                    if ($error = $this->mfile->adoptAttachments($mailData["attachments"], $this->httpRequest->getQueryParams()["mail_id"] ? (int) $this->httpRequest->getQueryParams()["mail_id"] : 0)) {
                        ilUtil::sendInfo($error);
                    }
                }
                break;
        
            case 'new':
                if (isset($this->httpRequest->getQueryParams()['rcp_to'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData["rcp_to"] = ilUtil::securePlainString($this->httpRequest->getQueryParams()['rcp_to']);
                } elseif (ilSession::get('rcp_to')) {
                    $mailData["rcp_to"] = ilSession::get('rcp_to');
                }
                if (isset($this->httpRequest->getQueryParams()['rcp_cc'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData["rcp_cc"] = ilUtil::securePlainString($this->httpRequest->getQueryParams()['rcp_cc']);
                } elseif (ilSession::get('rcp_cc')) {
                    $mailData["rcp_cc"] = ilSession::get('rcp_cc');
                }
                if (isset($this->httpRequest->getQueryParams()['rcp_bcc'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData["rcp_bcc"] = ilUtil::securePlainString($this->httpRequest->getQueryParams()['rcp_bcc']);
                } elseif (ilSession::get('rcp_bcc')) {
                    $mailData["rcp_bcc"] = ilSession::get('rcp_bcc');
                }
                $mailData['m_message'] = '';
                if (strlen($sig = ilMailFormCall::getSignature())) {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13) . chr(10) . chr(13) . chr(10);
                }
                $mailData['m_message'] .= $this->umail->appendSignature();

                ilSession::set('rcp_to', '');
                ilSession::set('rcp_cc', '');
                ilSession::set('rcp_bcc', '');
                break;
        
            case 'role':
        
                if (is_array($this->httpRequest->getParsedBody()['roles'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData['rcp_to'] = ilUtil::securePlainString(implode(',', $this->httpRequest->getParsedBody()['roles'] ?? null));
                } elseif (is_array(ilSession::get('mail_roles'))) {
                    $mailData['rcp_to'] = ilUtil::securePlainString(implode(',', ilSession::get('mail_roles')));
                }

                $mailData['m_message'] = '';
                if (strlen($sig = ilMailFormCall::getSignature())) {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13) . chr(10) . chr(13) . chr(10);
                }
        
                $mailData['m_message'] .= $this->httpRequest->getParsedBody()["additional_message_text"] ?? "" . chr(13) . chr(10) . $this->umail->appendSignature();
                $this->httpRequest->getParsedBody()["additional_message_text"] = "";
                ilSession::set('mail_roles', []);
                break;
        
            case 'address':
                $mailData["rcp_to"] = urldecode($this->httpRequest->getQueryParams()["rcp"]);
                break;
        
            default:
                // GET DATA FROM POST
                $mailData = $this->httpRequest->getParsedBody();

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
            $att = new ilMailFormAttachmentPropertyGUI($this->lng->txt(($mailData["attachments"]) ? 'edit' : 'add'));
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

        $chb = new ilCheckboxInputGUI($this->lng->txt('mail_serial_letter_placeholders'), 'use_placeholders');
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
        if (isset($this->httpRequest->getQueryParams()["term"]) && is_string($this->httpRequest->getQueryParams()["term"])) {
            $search = $this->httpRequest->getQueryParams()["term"];
        }
        if (isset($this->httpRequest->getParsedBody()["term"]) && is_string($this->httpRequest->getParsedBody()["term"])) {
            $search = $this->httpRequest->getParsedBody()["term"];
        }

        $search = trim($search);

        $result = [];

        require_once 'Services/Utilities/classes/class.ilStr.php';
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

    /**
     *
     */
    protected function saveMailBeforeSearch() : void
    {
        $files = [];
        $attachments = $this->httpRequest->getParsedBody()['attachments'] ?? $this->requestAttachments;
        if (is_array($attachments)) {
            foreach ($attachments as $value) {
                $files[] = urldecode($value);
            }
        }

        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_to'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_cc'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['rcp_bcc'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_email'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_subject'] ?? ""),
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['m_message']) ?? "",
            ilUtil::securePlainString($this->httpRequest->getParsedBody()['use_placeholders'] ?? ""),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );
    }

    /**
     *
     */
    public function searchMailingListsTo() : void
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass('ilmailinglistsgui', 'ref', 'mail');
        $this->ctrl->redirectByClass('ilmailinglistsgui');
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
