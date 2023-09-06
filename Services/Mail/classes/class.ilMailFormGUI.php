<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jens Conze
 * @version $Id$
 *
 * @ingroup ServicesMail
 * @ilCtrl_Calls ilMailFormGUI: ilMailFolderGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailingListsGUI
 */
class ilMailFormGUI
{
    /** @var ilTemplate */
    private $tpl;

    /** @var ilCtrl */
    private $ctrl;

    /** @var ilLanguage */
    private $lng;

    /** @var ilObjUser */
    private $user;

    /** @var ilTabsGUI */
    private $tabs;

    /** @var ilToolbarGUI */
    private $toolbar;

    /** @var ilRbacSystem */
    private $rbacsystem;

    /** @var ilFormatMail */
    private $umail;

    /** @var ilMailBox */
    private $mbox;

    /** @var ilFileDataMail */
    private $mfile;

    /** @var ilMailTemplateService */
    protected $templateService;

    /** @var ilMailBodyPurifier */
    private $purifier;

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

        $this->umail = new ilFormatMail($this->user->getId());
        $this->mfile = new ilFileDataMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());

        if (null === $bodyPurifier) {
            $bodyPurifier = new ilMailBodyPurifier();
        }
        $this->purifier = $bodyPurifier;

        if (isset($_POST['mobj_id']) && (int) $_POST['mobj_id']) {
            $_GET['mobj_id'] = $_POST['mobj_id'];
        }

        if (!(int) $_GET['mobj_id']) {
            $_GET['mobj_id'] = $this->mbox->getInboxFolder();
        }
        $_GET['mobj_id'] = (int) $_GET['mobj_id'];

        $this->ctrl->saveParameter($this, 'mobj_id');
    }

    public function executeCommand()
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
    protected function decodeAttachmentFiles(array $files)
    {
        $decodedFiles = array();

        foreach ($files as $value) {
            if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . urldecode($value))) {
                $decodedFiles[] = urldecode($value);
            }
        }

        return $decodedFiles;
    }

    public function sendMessage()
    {
        $message = (string) $_POST['m_message'];

        $mailBody = new ilMailBody($message, $this->purifier);

        $sanitizedMessage = $mailBody->getContent();

        $files = $this->decodeAttachmentFiles(isset($_POST['attachments']) ? (array) $_POST['attachments'] : array());

        $mailer = $this->umail
            ->withContextId(ilMailFormCall::getContextId() ?: '')
            ->withContextParameters(is_array(ilMailFormCall::getContextParameters()) ? ilMailFormCall::getContextParameters() : []);

        $mailer->setSaveInSentbox(true);

        if ($errors = $mailer->enqueue(
            ilUtil::securePlainString($_POST['rcp_to']),
            ilUtil::securePlainString($_POST['rcp_cc']),
            ilUtil::securePlainString($_POST['rcp_bcc']),
            ilUtil::securePlainString($_POST['m_subject']),
            $sanitizedMessage,
            $files,
            (int) $_POST['use_placeholders']
        )
        ) {
            $_POST['attachments'] = $files;
            $this->showSubmissionErrors($errors);
        } else {
            $mailer->savePostData($this->user->getId(), array(), "", "", "", "", "", "", "", "");

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

    public function saveDraft()
    {
        if (!$_POST['m_subject']) {
            $_POST['m_subject'] = $this->lng->txt('mail_no_subject');
        }

        $draftFolderId = $this->mbox->getDraftsFolder();
        $files = $this->decodeAttachmentFiles(isset($_POST['attachments']) ? (array) $_POST['attachments'] : array());

        if ($errors = $this->umail->validateRecipients(
            (string) ilUtil::securePlainString($_POST['rcp_to']),
            (string) ilUtil::securePlainString($_POST['rcp_cc']),
            (string) ilUtil::securePlainString($_POST['rcp_bcc'])
        )) {
            $_POST['attachments'] = $files;
            $this->showSubmissionErrors($errors);
            $this->showForm();
            return;
        }

        if (isset($_SESSION["draft"])) {
            $draftId = (int) $_SESSION['draft'];
            unset($_SESSION['draft']);
        } else {
            $draftId = $this->umail->getNewDraftId($this->user->getId(), $draftFolderId);
        }

        $this->umail->updateDraft(
            $draftFolderId,
            $files,
            ilUtil::securePlainString($_POST['rcp_to']),
            ilUtil::securePlainString($_POST['rcp_cc']),
            ilUtil::securePlainString($_POST['rcp_bcc']),
            ilUtil::securePlainString($_POST['m_email']),
            ilUtil::securePlainString($_POST['m_subject']),
            ilUtil::securePlainString($_POST['m_message']),
            $draftId,
            (int) $_POST['use_placeholders'],
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
            if (isset($_POST['attachments']) && is_array($_POST['attachments'])) {
                foreach ($_POST['attachments'] as $value) {
                    $files[] = urldecode($value);
                }
            }

            // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
            $this->umail->savePostData(
                $this->user->getId(),
                $files,
                ilUtil::securePlainString($_POST["rcp_to"] ?? ''),
                ilUtil::securePlainString($_POST["rcp_cc"] ?? ''),
                ilUtil::securePlainString($_POST["rcp_bcc"] ?? ''),
                ilUtil::securePlainString($_POST["m_email"] ?? ''),
                ilUtil::securePlainString($_POST["m_subject"] ?? ''),
                ilUtil::securePlainString($_POST["m_message"] ?? ''),
                (bool) ($_POST['use_placeholders'] ?? false),
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
            $inp->setValue(ilUtil::prepareFormOutput($searchQuery), true);
        }
        $form->addItem($inp);

        $form->addCommandButton('search', $this->lng->txt("search"));
        $form->addCommandButton('cancelSearch', $this->lng->txt("cancel"));

        $this->tpl->setContent($form->getHtml());
        $this->tpl->printToStdout();
    }

    /**
     *
     */
    public function searchCoursesTo()
    {
        $this->saveMailBeforeSearch();

        if ($_SESSION['search_crs']) {
            $this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'cmd', 'showMembers');
        }

        $this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'ref', 'mail');
        $this->ctrl->redirectByClass('ilmailsearchcoursesgui');
    }

    /**
     *
     */
    public function searchGroupsTo()
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass('ilmailsearchgroupsgui', 'ref', 'mail');
        $this->ctrl->redirectByClass('ilmailsearchgroupsgui');
    }

    public function search()
    {
        $_SESSION["mail_search_search"] = $_POST["search"];
        if (strlen(trim($_SESSION["mail_search_search"])) == 0) {
            ilUtil::sendInfo($this->lng->txt("mail_insert_query"));
            $this->searchUsers(false);
        } else {
            if (strlen(trim($_SESSION["mail_search_search"])) < 3) {
                $this->lng->loadLanguageModule('search');
                ilUtil::sendInfo($this->lng->txt('search_minimum_three'));
                $this->searchUsers(false);
            } else {
                $this->ctrl->setParameterByClass(
                    "ilmailsearchgui",
                    "search",
                    urlencode($_SESSION["mail_search_search"])
                );
                $this->ctrl->redirectByClass("ilmailsearchgui");
            }
        }
    }

    public function cancelSearch()
    {
        unset($_SESSION["mail_search"]);
        $this->searchResults();
    }

    public function editAttachments() : void
    {
        // decode post values
        $files = [];
        if (isset($_POST['attachments']) && is_array($_POST['attachments'])) {
            foreach ($_POST['attachments'] as $value) {
                $files[] = urldecode($value);
            }
        }

        // Note: For security reasons, ILIAS only allows Plain text messages.
        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($_POST["rcp_to"]),
            ilUtil::securePlainString($_POST["rcp_cc"]),
            ilUtil::securePlainString($_POST["rcp_bcc"]),
            (bool) ($_POST["m_email"] ?? false),
            ilUtil::securePlainString($_POST["m_subject"]),
            ilUtil::securePlainString($_POST["m_message"]),
            (bool) ($_POST["use_placeholders"] ?? false),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        $this->ctrl->redirectByClass("ilmailattachmentgui");
    }

    public function returnFromAttachments()
    {
        $_GET["type"] = "attach";
        $this->showForm();
    }

    public function searchResults()
    {
        $_GET["type"] = "search_res";
        $this->showForm();
    }

    public function mailUser()
    {
        $_GET["type"] = "new";
        $this->showForm();
    }

    public function mailRole()
    {
        $_GET["type"] = "role";
        $this->showForm();
    }

    public function replyMail()
    {
        $_GET["type"] = "reply";
        $this->showForm();
    }

    public function mailAttachment()
    {
        $_GET["type"] = "attach";
        $this->showForm();
    }

    /**
     * Called asynchronously when changing the template
     */
    protected function getTemplateDataById()
    {
        if (!isset($_GET['template_id'])) {
            exit();
        }

        try {
            $template = $this->templateService->loadTemplateForId((int) $_GET['template_id']);
            $context = ilMailTemplateContextService::getTemplateContextById((string) $template->getContext());

            echo json_encode([
                'm_subject' => $template->getSubject(),
                'm_message' => $template->getMessage() . $this->umail->appendSignature(),
            ]);
        } catch (Exception $e) {
        }
        exit();
    }

    public function showForm()
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

        switch ($_GET["type"]) {
            case 'reply':
                if ($_SESSION['mail_id']) {
                    $_GET['mail_id'] = $_SESSION['mail_id'];
                }
                $mailData = $this->umail->getMail($_GET["mail_id"]);
                $mailData["m_subject"] = $this->umail->formatReplySubject();
                $mailData["m_message"] = $this->umail->formatReplyMessage();
                $mailData["m_message"] = $this->umail->prependSignature();
                // NO ATTACHMENTS FOR REPLIES
                $mailData["attachments"] = array();
                //$mailData["rcp_cc"] = $this->umail->formatReplyRecipientsForCC();
                $mailData["rcp_cc"] = '';
                $mailData["rcp_to"] = $this->umail->formatReplyRecipient();
                $_SESSION["mail_id"] = "";
                break;

            case 'search_res':
                $mailData = $this->umail->getSavedData();

                /*if($_SESSION["mail_search_results"])
                {
                    $mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results"],$_SESSION["mail_search"]);
                }
                unset($_SESSION["mail_search"]);
                unset($_SESSION["mail_search_results"]);*/

                if ($_SESSION["mail_search_results_to"]) {
                    $mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results_to"], 'to');
                }
                if ($_SESSION["mail_search_results_cc"]) {
                    $mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results_cc"], 'cc');
                }
                if ($_SESSION["mail_search_results_bcc"]) {
                    $mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results_bcc"], 'bc');
                }

                unset($_SESSION["mail_search_results_to"]);
                unset($_SESSION["mail_search_results_cc"]);
                unset($_SESSION["mail_search_results_bcc"]);

                break;

            case 'attach':
                $mailData = $this->umail->getSavedData();
                break;

            case 'draft':
                $_SESSION["draft"] = $_GET["mail_id"];
                $mailData = $this->umail->getMail($_GET["mail_id"]);
                if (isset($mailData['m_subject']) && $mailData['m_subject'] === $this->lng->txt('mail_no_subject')) {
                    $mailData['m_subject'] = '';
                }
                ilMailFormCall::setContextId($mailData['tpl_ctx_id']);
                ilMailFormCall::setContextParameters($mailData['tpl_ctx_params']);
                break;

            case 'forward':
                $mailData = $this->umail->getMail($_GET["mail_id"]);
                $mailData["rcp_to"] = $mailData["rcp_cc"] = $mailData["rcp_bcc"] = '';
                $mailData["m_subject"] = $this->umail->formatForwardSubject();
                $mailData["m_message"] = $this->umail->prependSignature();
                if (is_array($mailData["attachments"]) && count($mailData["attachments"])) {
                    if ($error = $this->mfile->adoptAttachments($mailData["attachments"], $_GET["mail_id"])) {
                        ilUtil::sendInfo($error);
                    }
                }
                break;

            case 'new':
                if (isset($_GET['rcp_to'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData["rcp_to"] = ilUtil::securePlainString($_GET['rcp_to']);
                } elseif (isset($_SESSION['rcp_to'])) {
                    $mailData["rcp_to"] = $_SESSION['rcp_to'];
                }
                if (isset($_GET['rcp_cc'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData["rcp_cc"] = ilUtil::securePlainString($_GET['rcp_cc']);
                } elseif (isset($_SESSION['rcp_cc'])) {
                    $mailData["rcp_cc"] = $_SESSION['rcp_cc'];
                }
                if (isset($_GET['rcp_bcc'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData["rcp_bcc"] = ilUtil::securePlainString($_GET['rcp_bcc']);
                } elseif (isset($_SESSION['rcp_bcc'])) {
                    $mailData["rcp_bcc"] = $_SESSION['rcp_bcc'];
                }
                $mailData['m_message'] = '';
                if (strlen($sig = ilMailFormCall::getSignature())) {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13) . chr(10) . chr(13) . chr(10);
                }
                $mailData['m_message'] .= $this->umail->appendSignature();

                $_SESSION['rcp_to'] = '';
                $_SESSION['rcp_cc'] = '';
                $_SESSION['rcp_bcc'] = '';
                break;

            case 'role':

                if (is_array($_POST['roles'])) {
                    // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
                    $mailData['rcp_to'] = ilUtil::securePlainString(implode(',', $_POST['roles']));
                } elseif (is_array($_SESSION['mail_roles'])) {
                    $mailData['rcp_to'] = ilUtil::securePlainString(implode(',', $_SESSION['mail_roles']));
                }

                $mailData['m_message'] = '';
                if (strlen($sig = ilMailFormCall::getSignature())) {
                    $mailData['m_message'] = $sig;
                    $mailData['m_message'] .= chr(13) . chr(10) . chr(13) . chr(10);
                }

                $mailData['m_message'] .= $_POST["additional_message_text"] . chr(13) . chr(10) . $this->umail->appendSignature();
                $_POST["additional_message_text"] = "";
                $_SESSION['mail_roles'] = [];
                break;

            case 'address':
                $mailData["rcp_to"] = urldecode($_GET["rcp"]);
                break;

            default:
                // GET DATA FROM POST
                $mailData = $_POST;

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
        $inp->setValue($mailData["rcp_to"]);
        $inp->setDataSource($dsDataLink, ",");
        $inp->setMaxLength(null);
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('mail_cc'), 'rcp_cc');
        $inp->setSize(50);
        $inp->setValue($mailData["rcp_cc"]);
        $inp->setDataSource($dsDataLink, ",");
        $inp->setMaxLength(null);
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('mail_bcc'), 'rcp_bcc');
        $inp->setSize(50);
        $inp->setValue($mailData["rcp_bcc"]);
        $inp->setDataSource($dsDataLink, ",");
        $inp->setMaxLength(null);
        $form_gui->addItem($inp);

        $inp = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
        $inp->setSize(50);
        $inp->setRequired(true);
        $inp->setValue($mailData["m_subject"]);
        $form_gui->addItem($inp);

        $att = new ilMailFormAttachmentPropertyGUI($this->lng->txt(($mailData["attachments"]) ? 'edit' : 'add'));

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
        $form_gui->addItem($att);

        if (ilMailFormCall::getContextId()) {
            $context_id = ilMailFormCall::getContextId();

            $mailData['use_placeholders'] = true;

            try {
                $context = ilMailTemplateContextService::getTemplateContextById($context_id);

                $templates = $this->templateService->loadTemplatesForContextId($context->getId());
                if (count($templates) > 0) {
                    $options = array();

                    $template_chb = new ilMailTemplateSelectInputGUI(
                        $this->lng->txt('mail_template_client'),
                        'template_id',
                        $this->ctrl->getLinkTarget($this, 'getTemplateDataById', '', true, false),
                        array('m_subject' => false, 'm_message' => true)
                    );

                    foreach ($templates as $template) {
                        $options[$template->getTplId()] = $template->getTitle();

                        if (!isset($mailData['template_id']) && $template->isDefault()) {
                            $template_chb->setValue($template->getTplId());
                            $form_gui->getItemByPostVar('m_subject')->setValue($template->getSubject());
                            $mailData["m_message"] = $template->getMessage() . $this->umail->appendSignature();
                        }
                    }
                    if (isset($mailData['template_id'])) {
                        $template_chb->setValue((int) $mailData['template_id']);
                    }
                    asort($options);

                    $template_chb->setInfo($this->lng->txt('mail_template_client_info'));
                    $template_chb->setOptions(array('' => $this->lng->txt('please_choose')) + $options);
                    $form_gui->addItem($template_chb);
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

        $inp = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
        //$inp->setValue(htmlspecialchars($mailData["m_message"], false));
        $inp->setValue($mailData["m_message"]);
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
        try {
            $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        } catch (Throwable $e) {
            $placeholders->setAdviseText($this->lng->txt('placeholders_advise'));
        }
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

    public function lookupRecipientAsync()
    {
        $search = '';
        if (isset($_GET["term"]) && is_string($_GET["term"])) {
            $search = $_GET["term"];
        }
        if (isset($_POST["term"]) && is_string($_POST["term"])) {
            $search = $_POST["term"];
        }

        $search = trim($search);

        $result = array();

        require_once 'Services/Utilities/classes/class.ilStr.php';
        if (ilStr::strLen($search) < 3) {
            echo json_encode($result);
            exit;
        }

        // #14768
        $quoted = ilUtil::stripSlashes($search);
        $quoted = str_replace('%', '\%', $quoted);
        $quoted = str_replace('_', '\_', $quoted);

        $mailFormObj = new ilMailForm;
        $result = $mailFormObj->getRecipientAsync("%" . $quoted . "%", ilUtil::stripSlashes($search));

        echo json_encode($result);
        exit;
    }

    public function cancelMail()
    {
        if (ilMailFormCall::isRefererStored()) {
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        }

        $this->showForm();
    }

    /**
     *
     */
    protected function saveMailBeforeSearch()
    {
        $files = array();
        if (is_array($_POST['attachments'])) {
            foreach ($_POST['attachments'] as $value) {
                $files[] = urldecode($value);
            }
        }

        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            ilUtil::securePlainString($_POST['rcp_to']),
            ilUtil::securePlainString($_POST['rcp_cc']),
            ilUtil::securePlainString($_POST['rcp_bcc']),
            ilUtil::securePlainString($_POST['m_email']),
            ilUtil::securePlainString($_POST['m_subject']),
            ilUtil::securePlainString($_POST['m_message']),
            ilUtil::securePlainString($_POST['use_placeholders']),
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );
    }

    /**
     *
     */
    public function searchMailingListsTo()
    {
        $this->saveMailBeforeSearch();

        $this->ctrl->setParameterByClass('ilmailinglistsgui', 'ref', 'mail');
        $this->ctrl->redirectByClass('ilmailinglistsgui');
    }

    /**
     * @param $errors ilMailError[]
     */
    protected function showSubmissionErrors(array $errors)
    {
        $formatter = new ilMailErrorFormatter($this->lng);
        $formattedErrors = $formatter->format($errors);

        if (strlen($formattedErrors) > 0) {
            ilUtil::sendFailure($formattedErrors);
        }
    }
}
