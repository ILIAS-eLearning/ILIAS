<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    private ilGlobalTemplateInterface $tpl;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private string $forwardClass = '';
    private ServerRequestInterface $httpRequest;
    private int $currentFolderId = 0;
    private ilObjUser $user;
    public ilMail $umail;
    public ilMailBox $mbox;


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
        if (!$DIC->rbac()->system()->checkAccess(
            'internal_mail',
            $this->umail->getMailObjectReferenceId()
        )
        ) {
            $DIC['ilErr']->raiseError($this->lng->txt('permission_denied'), $DIC['ilErr']->WARNING);
        }

        $this->initFolder();


        $toolContext = $DIC->globalScreen()
                           ->tool()
                           ->context()
                           ->current();

        $additionalDataExists = $toolContext->getAdditionalData()->exists(
            MailGlobalScreenToolProvider::SHOW_MAIL_FOLDERS_TOOL
        );
        if (false === $additionalDataExists) {
            $toolContext->addAdditionalData(MailGlobalScreenToolProvider::SHOW_MAIL_FOLDERS_TOOL, true);
        }
    }

    
    protected function initFolder() : void
    {
        $folderId = (int) ($this->httpRequest->getParsedBody()['mobj_id'] ?? 0);
        if (0 === $folderId) {
            $folderId = (int) ($this->httpRequest->getQueryParams()['mobj_id'] ?? ilSession::get('mobj_id'));
        }
        if (0 === $folderId || !$this->mbox->isOwnedFolder($folderId)) {
            $folderId = $this->mbox->getInboxFolder();
        }
        $this->currentFolderId = $folderId;
    }

    
    public function executeCommand() : void
    {
        $type = $this->httpRequest->getQueryParams()['type'] ?? '';
        $mailId = (int) ($this->httpRequest->getQueryParams()['mail_id'] ?? 0);

        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'mobj_id', $this->currentFolderId);
        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mobj_id', $this->currentFolderId);

        if ('search_res' === $type) {
            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass(ilMailFormGUI::class, 'searchResults');
        } elseif ('attach' === $type) {
            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass(ilMailFormGUI::class, 'mailAttachment');
        } elseif ('new' === $type) {
            $to = $this->httpRequest->getQueryParams()['rcp_to'] ?? '';
            ilSession::set('rcp_to', ilUtil::stripSlashes($to));
            if (ilSession::get('rcp_to') === '' && ($recipients = ilMailFormCall::getRecipients())) {
                ilSession::set('rcp_to', implode(',', $recipients));
                ilMailFormCall::setRecipients([]);
            }

            $cc = $this->httpRequest->getQueryParams()['rcp_cc'] ?? '';
            $bcc = $this->httpRequest->getQueryParams()['rcp_bcc'] ?? '';
            ilSession::set('rcp_cc', ilUtil::stripSlashes($cc));
            ilSession::set('rcp_bcc', ilUtil::stripSlashes($bcc));

            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass(ilMailFormGUI::class, 'mailUser');
        } elseif ('reply' === $type) {
            ilSession::set('mail_id', $mailId);
            $this->ctrl->redirectByClass(ilMailFormGUI::class, 'replyMail');
        } elseif ('read' === $type) {
            ilSession::set('mail_id', $mailId);
            $this->ctrl->redirectByClass(ilMailFolderGUI::class, 'showMail');
        } elseif ('deliverFile' === $type) {
            ilSession::set('mail_id', $mailId);

            $fileName = '';
            if (isset($this->httpRequest->getParsedBody()['filename'])) {
                $fileName = $this->httpRequest->getParsedBody()['filename'];
            } elseif (isset($this->httpRequest->getQueryParams()['filename'])) {
                $fileName = $this->httpRequest->getQueryParams()['filename'];
            }
            ilSession::set('filename', ilUtil::stripSlashes($fileName));
            $this->ctrl->redirectByClass(ilMailFolderGUI::class, 'deliverFile');
        } elseif ('message_sent' === $type) {
            ilUtil::sendSuccess($this->lng->txt('mail_message_send'), true);
            $this->ctrl->redirectByClass(ilMailFolderGUI::class);
        } elseif ('role' === $type) {
            $roles = $this->httpRequest->getParsedBody()['roles'] ?? [];
            if (is_array($roles) && count($roles) > 0) {
                ilSession::set('mail_roles', $roles);
            } elseif (isset($this->httpRequest->getQueryParams()['role'])) {
                ilSession::set('mail_roles', [$this->httpRequest->getQueryParams()['role']]);
            }

            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass(ilMailFormGUI::class, 'mailRole');
        }

        $view = (string) ($this->httpRequest->getQueryParams()['view'] ?? '');
        if ('my_courses' === $view) {
            $search_crs = ilUtil::stripSlashes($this->httpRequest->getQueryParams()['search_crs']);
            $this->ctrl->setParameter($this, 'search_crs', $search_crs);
            $this->ctrl->redirectByClass(ilMailFormGUI::class, 'searchCoursesTo');
        }

        if (isset($this->httpRequest->getQueryParams()['viewmode'])) {
            $this->ctrl->setCmd('setViewMode');
        }

        $this->forwardClass = (string) $this->ctrl->getNextClass($this);
        $this->showHeader();

        switch (strtolower($this->forwardClass)) {
            case strtolower(ilMailFormGUI::class):
                $this->ctrl->forwardCommand(new ilMailFormGUI());
                break;

            case strtolower(ilContactGUI::class):
                $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            case strtolower(ilMailOptionsGUI::class):
                $this->tpl->setTitle($this->lng->txt('mail'));
                $this->ctrl->forwardCommand(new ilMailOptionsGUI());
                break;

            case strtolower(ilMailFolderGUI::class):
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

    
    private function setViewMode() : void
    {
        $targetClass = $this->httpRequest->getQueryParams()['target'] ?? ilMailFolderGUI::class;
        $type = $this->httpRequest->getQueryParams()['type'] ?? '';
        $mailId = (int) ($this->httpRequest->getQueryParams()['mail_id'] ?? 0);

        $this->ctrl->setParameterByClass($targetClass, 'mobj_id', $this->currentFolderId);

        if ('redirect_to_read' === $type) {
            $this->ctrl->setParameterByClass(
                ilMailFolderGUI::class,
                'mail_id',
                $mailId
            );
            $this->ctrl->setParameterByClass(
                ilMailFolderGUI::class,
                'mobj_id',
                $this->currentFolderId
            );
            $this->ctrl->redirectByClass(ilMailFolderGUI::class, 'showMail');
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

    
    private function showHeader() : void
    {
        global $DIC;

        $DIC['ilHelp']->setScreenIdComponent("mail");
        $DIC['ilMainMenu']->setActive("mail");

        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mail.svg"));

        ilUtil::infoPanel();

        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mobj_id', $this->currentFolderId);
        $DIC->tabs()->addTarget('fold', $this->ctrl->getLinkTargetByClass(ilMailFolderGUI::class));
        $this->ctrl->clearParametersByClass(ilMailFormGUI::class);

        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', 'new');
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'mobj_id', $this->currentFolderId);
        $DIC->tabs()->addTarget('compose', $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class));
        $this->ctrl->clearParametersByClass(ilMailFormGUI::class);

        $this->ctrl->setParameterByClass(ilContactGUI::class, 'mobj_id', $this->currentFolderId);
        $DIC->tabs()->addTarget(
            'mail_addressbook',
            $this->ctrl->getLinkTargetByClass(ilContactGUI::class)
        );
        $this->ctrl->clearParametersByClass(ilContactGUI::class);

        if ($DIC->settings()->get('show_mail_settings')) {
            $this->ctrl->setParameterByClass(
                ilMailOptionsGUI::class,
                'mobj_id',
                $this->currentFolderId
            );
            $DIC->tabs()->addTarget(
                'options',
                $this->ctrl->getLinkTargetByClass(ilMailOptionsGUI::class)
            );
            $this->ctrl->clearParametersByClass(ilMailOptionsGUI::class);
        }

        switch ($this->forwardClass) {
            case strtolower(ilMailFormGUI::class):
                $DIC->tabs()->setTabActive('compose');
                break;

            case strtolower(ilContactGUI::class):
                $DIC->tabs()->setTabActive('mail_addressbook');
                break;

            case strtolower(ilMailOptionsGUI::class):
                $DIC->tabs()->setTabActive('options');
                break;

            case strtolower(ilMailFolderGUI::class):
            default:
                $DIC->tabs()->setTabActive('fold');
                break;
        }

        if (isset($this->httpRequest->getQueryParams()['message_sent'])) {
            $DIC->tabs()->setTabActive('fold');
        }
    }


    protected function toggleExplorerNodeState() : void
    {
        $exp = new ilMailExplorer($this, $this->user->getId());
        $exp->toggleExplorerNodeState();
    }
}
