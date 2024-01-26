<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author       Jens Conze
 * @version      $Id$
 * @defgroup     ServicesMail Services/Mail
 * @ingroup      ServicesMail
 * @ilCtrl_Calls ilMailGUI: ilMailFolderGUI, ilMailFormGUI, ilContactGUI, ilMailOptionsGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilObjUserGUI
 */
class ilMailGUI
{
    /** @var ilGlobalPageTemplate */
    private $tpl;

    /** @var ilCtrl */
    private $ctrl;

    /** @var ilLanguage */
    private $lng;

    /** @var string */
    private $forwardClass = '';

    /** @var ServerRequestInterface */
    private $httpRequest;

    /** @var int */
    private $currentFolderId = 0;

    /** @var ilObjUser */
    private $user;

    /** @var ilMail */
    public $umail;

    /** @var ilMailBox */
    public $mbox;

    /**
     * ilMailGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->httpRequest = $DIC->http()->request();

        $this->lng->loadLanguageModule('mail');

        $this->mbox = new ilMailbox($this->user->getId());
        $this->umail = new ilMail($this->user->getId());
        if (!$DIC->rbac()->system()->checkAccess('internal_mail', $this->umail->getMailObjectReferenceId())) {
            $DIC['ilErr']->raiseError($this->lng->txt('permission_denied'), $DIC['ilErr']->WARNING);
        }

        $this->initFolder();


        $toolContext = $DIC->globalScreen()
                           ->tool()
                           ->context()
                           ->current();

        $additionalDataExists = $toolContext->getAdditionalData()->exists(MailGlobalScreenToolProvider::SHOW_MAIL_FOLDERS_TOOL);
        if (false === $additionalDataExists) {
            $toolContext->addAdditionalData(MailGlobalScreenToolProvider::SHOW_MAIL_FOLDERS_TOOL, true);
        }
    }

    /**
     *
     */
    protected function initFolder() : void
    {
        $folderId = (int) ($this->httpRequest->getParsedBody()['mobj_id'] ?? 0);
        if (0 === $folderId) {
            $folderId = (int) ($this->httpRequest->getQueryParams()['mobj_id'] ?? 0);
        }
        if (0 === $folderId || !$this->mbox->isOwnedFolder($folderId)) {
            $folderId = $this->mbox->getInboxFolder();
        }
        $this->currentFolderId = (int) $folderId;
    }

    /**
     *
     */
    public function executeCommand() : void
    {
        $type = $this->httpRequest->getQueryParams()['type'] ?? '';
        $mailId = (int) ($this->httpRequest->getQueryParams()['mail_id'] ?? 0);

        $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->currentFolderId);
        $this->ctrl->setParameterByClass('ilmailfoldergui', 'mobj_id', $this->currentFolderId);

        if ('search_res' === $type) {
            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'searchResults');
        } elseif ('attach' === $type) {
            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'mailAttachment');
        } elseif ('new' === $type) {
            foreach (['to', 'cc', 'bcc'] as $reciepient_type) {
                $key = 'rcp_' . $reciepient_type;

                $recipients = $this->httpRequest->getQueryParams()[$key] ?? '';

                ilSession::set($key, ilUtil::stripSlashes($recipients));

                if (ilSession::get($key) === '' &&
                    ($recipients = ilMailFormCall::getRecipients($reciepient_type))) {
                    ilSession::set($key, implode(',', $recipients));
                    ilMailFormCall::setRecipients([], $reciepient_type);
                }
            }

            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'mailUser');
        } elseif ('reply' === $type) {
            ilSession::set('mail_id', $mailId);
            $this->ctrl->redirectByClass('ilmailformgui', 'replyMail');
        } elseif ('read' === $type) {
            ilSession::set('mail_id', $mailId);
            $this->ctrl->redirectByClass('ilmailfoldergui', 'showMail');
        } elseif ('deliverFile' === $type) {
            ilSession::set('mail_id', $mailId);

            $fileName = '';
            if (isset($this->httpRequest->getParsedBody()['filename'])) {
                $fileName = $this->httpRequest->getParsedBody()['filename'];
            } elseif (isset($this->httpRequest->getQueryParams()['filename'])) {
                $fileName = $this->httpRequest->getQueryParams()['filename'];
            }
            ilSession::set('filename', ilUtil::stripSlashes($fileName));
            $this->ctrl->redirectByClass('ilmailfoldergui', 'deliverFile');
        } elseif ('message_sent' === $type) {
            ilUtil::sendSuccess($this->lng->txt('mail_message_send'), true);
            $this->ctrl->redirectByClass('ilmailfoldergui');
        } elseif ('role' === $type) {
            $roles = $this->httpRequest->getParsedBody()['roles'] ?? [];
            if (is_array($roles) && count($roles) > 0) {
                ilSession::set('mail_roles', $roles);
            } elseif (isset($this->httpRequest->getQueryParams()['role'])) {
                ilSession::set('mail_roles', [$this->httpRequest->getQueryParams()['role']]);
            }

            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'mailRole');
        }

        $view = (string) ($this->httpRequest->getQueryParams()['view'] ?? '');
        if ('my_courses' === $view) {
            ilSession::set('search_crs', ilUtil::stripSlashes($this->httpRequest->getQueryParams()['search_crs']));
            $this->ctrl->redirectByClass('ilmailformgui', 'searchCoursesTo');
        }

        if (isset($this->httpRequest->getQueryParams()['viewmode'])) {
            $this->ctrl->setCmd('setViewMode');
        }

        $this->forwardClass = (string) $this->ctrl->getNextClass($this);
        $this->showHeader();

        switch (strtolower($this->forwardClass)) {
            case 'ilmailformgui':
                $this->ctrl->forwardCommand(new ilMailFormGUI());
                break;

            case 'ilcontactgui':
                $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            case 'ilmailoptionsgui':
                $this->tpl->setTitle($this->lng->txt('mail'));
                $this->ctrl->forwardCommand(new ilMailOptionsGUI());
                break;

            case 'ilmailfoldergui':
                $this->ctrl->forwardCommand(new ilMailFolderGUI());
                break;

            default:
                if (!($cmd = $this->ctrl->getCmd()) || !method_exists($this, $cmd)) {
                    $cmd = 'setViewMode';
                }

                $this->{$cmd}();
                break;
        }
    }

    /**
     *
     */
    private function setViewMode() : void
    {
        $targetClass = $this->httpRequest->getQueryParams()['target'] ?? 'ilmailfoldergui';
        $type = $this->httpRequest->getQueryParams()['type'] ?? '';
        $mailId = (int) ($this->httpRequest->getQueryParams()['mail_id'] ?? 0);

        $this->ctrl->setParameterByClass($targetClass, 'mobj_id', $this->currentFolderId);

        if ('redirect_to_read' === $type) {
            $this->ctrl->setParameterByClass(
                'ilMailFolderGUI',
                'mail_id',
                $mailId
            );
            $this->ctrl->setParameterByClass('ilmailfoldergui', 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirectByClass('ilMailFolderGUI', 'showMail');
        } elseif ('add_subfolder' === $type) {
            $this->ctrl->redirectByClass($targetClass, 'addSubFolder');
        } elseif ('enter_folderdata' === $type) {
            $this->ctrl->redirectByClass($targetClass, 'enterFolderData');
        } elseif ('confirmdelete_folderdata' === $type) {
            $this->ctrl->redirectByClass($targetClass, 'confirmDeleteFolder');
        } else {
            $this->ctrl->redirectByClass($targetClass);
        }
    }

    /**
     *
     */
    private function showHeader() : void
    {
        global $DIC;

        $DIC['ilHelp']->setScreenIdComponent("mail");
        $DIC['ilMainMenu']->setActive("mail");

        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mail.svg"));

        ilUtil::infoPanel();

        $this->ctrl->setParameterByClass('ilmailfoldergui', 'mobj_id', $this->currentFolderId);
        $DIC->tabs()->addTarget('fold', $this->ctrl->getLinkTargetByClass('ilmailfoldergui'));
        $this->ctrl->clearParametersByClass('ilmailformgui');

        $this->ctrl->setParameterByClass('ilmailformgui', 'type', 'new');
        $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->currentFolderId);
        $DIC->tabs()->addTarget('compose', $this->ctrl->getLinkTargetByClass('ilmailformgui'));
        $this->ctrl->clearParametersByClass('ilmailformgui');

        $this->ctrl->setParameterByClass('ilcontactgui', 'mobj_id', $this->currentFolderId);
        $DIC->tabs()->addTarget('mail_addressbook', $this->ctrl->getLinkTargetByClass('ilcontactgui'));
        $this->ctrl->clearParametersByClass('ilcontactgui');

        if ($DIC->settings()->get('show_mail_settings')) {
            $this->ctrl->setParameterByClass('ilmailoptionsgui', 'mobj_id', $this->currentFolderId);
            $DIC->tabs()->addTarget('options', $this->ctrl->getLinkTargetByClass('ilmailoptionsgui'));
            $this->ctrl->clearParametersByClass('ilmailoptionsgui');
        }

        switch ($this->forwardClass) {
            case 'ilmailformgui':
                $DIC->tabs()->setTabActive('compose');
                break;

            case 'ilcontactgui':
                $DIC->tabs()->setTabActive('mail_addressbook');
                break;

            case 'ilmailoptionsgui':
                $DIC->tabs()->setTabActive('options');
                break;

            case 'ilmailfoldergui':
            default:
                $DIC->tabs()->setTabActive('fold');
                break;
        }

        if (isset($this->httpRequest->getQueryParams()['message_sent'])) {
            $DIC->tabs()->setTabActive('fold');
        }
    }

    /**
     * Toggle explorer tree node
     */
    protected function toggleExplorerNodeState() : void
    {
        $exp = new ilMailExplorer($this, $this->user->getId());
        $exp->toggleExplorerNodeState();
    }
}
