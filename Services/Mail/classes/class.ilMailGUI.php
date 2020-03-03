<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Jens Conze
* @version $Id$
*
* @defgroup ServicesMail Services/Mail
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailGUI: ilMailFolderGUI, ilMailFormGUI, ilContactGUI, ilMailOptionsGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilObjUserGUI
*/
class ilMailGUI
{
    /** @var string */
    const VIEWMODE_SESSION_KEY = 'mail_viewmode';
    
    /** @var ilTemplate */
    private $tpl;

    /** @var \ilCtrl */
    private $ctrl;

    /** @var \ilLanguage */
    private $lng;

    /** @var string  */
    private $forwardClass = '';

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $httpRequest;

    /** @var int */
    private $currentFolderId = 0;
    
    /** @var \ilObjUser */
    private $user;

    /** @var \ilMail */
    public $umail;

    /** @var \ilMailBox */
    public $mbox;

    /**
     * ilMailGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->tpl  = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng  = $DIC->language();
        $this->user  = $DIC->user();
        $this->httpRequest = $DIC->http()->request();

        $this->lng->loadLanguageModule('mail');

        $this->mbox  = new ilMailbox($this->user->getId());
        $this->umail = new \ilMail($this->user->getId());
        if (!$DIC->rbac()->system()->checkAccess('internal_mail', $this->umail->getMailObjectReferenceId())) {
            $DIC['ilErr']->raiseError($this->lng->txt("permission_denied"), $DIC['ilErr']->WARNING);
        }

        $this->initFolder();
    }

    /**
     *
     */
    protected function initFolder()
    {
        $folderId = $this->httpRequest->getParsedBody()['mobj_id'] ?? 0;
        if (!is_numeric($folderId) || 0 == $folderId) {
            $folderId = $this->httpRequest->getQueryParams()['mobj_id'] ?? 0;
        }
        if (!is_numeric($folderId) || 0 == $folderId || !$this->mbox->isOwnedFolder($folderId)) {
            $folderId = $this->mbox->getInboxFolder();
        }
        $this->currentFolderId = (int) $folderId;
    }

    /**
     *
     */
    public function executeCommand()
    {
        $type = $this->httpRequest->getQueryParams()['type'] ?? '';
        $mailId = (int) ($this->httpRequest->getQueryParams()['mail_id'] ?? 0);

        $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->currentFolderId);
        $this->ctrl->setParameterByClass('ilmailfoldergui', 'mobj_id', $this->currentFolderId);

        if ('search_res' === $type) {
            \ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'searchResults');
        } elseif ('attach' === $type) {
            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'mailAttachment');
        } elseif ('new' === $type) {
            $to = $this->httpRequest->getQueryParams()['rcp_to'] ?? '';
            \ilSession::set('rcp_to', \ilUtil::stripSlashes($to));
            if (!strlen(\ilSession::get('rcp_to')) && ($recipients = \ilMailFormCall::getRecipients())) {
                \ilSession::set('rcp_to', implode(',', $recipients));
                \ilMailFormCall::setRecipients([]);
            }

            $cc = $this->httpRequest->getQueryParams()['rcp_cc'] ?? '';
            $bcc = $this->httpRequest->getQueryParams()['rcp_bcc'] ?? '';
            \ilSession::set('rcp_cc', \ilUtil::stripSlashes($cc));
            \ilSession::set('rcp_bcc', \ilUtil::stripSlashes($bcc));

            ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'mailUser');
        } elseif ('reply' === $type) {
            \ilSession::set('mail_id', $mailId);
            $this->ctrl->redirectByClass('ilmailformgui', 'replyMail');
        } elseif ('read' === $type) {
            \ilSession::set('mail_id', $mailId);
            $this->ctrl->redirectByClass('ilmailfoldergui', 'showMail');
        } elseif ('deliverFile' === $type) {
            \ilSession::set('mail_id', $mailId);

            $fileName = '';
            if (isset($this->httpRequest->getParsedBody()['filename'])) {
                $fileName = $this->httpRequest->getParsedBody()['filename'];
            } elseif (isset($this->httpRequest->getQueryParams()['filename'])) {
                $fileName = $this->httpRequest->getQueryParams()['filename'];
            }
            \ilSession::set('filename', \ilUtil::stripSlashes($fileName));
            ;
            $this->ctrl->redirectByClass('ilmailfoldergui', 'deliverFile');
        } elseif ('message_sent' === $type) {
            \ilUtil::sendSuccess($this->lng->txt('mail_message_send'), true);
            $this->ctrl->redirectByClass('ilmailfoldergui');
        } elseif ('role' === $type) {
            $roles = $this->httpRequest->getParsedBody()['roles'] ?? [];
            if (is_array($roles) && count($roles) > 0) {
                \ilSession::set('mail_roles', $roles);
            } elseif (isset($this->httpRequest->getQueryParams()['role'])) {
                \ilSession::set('mail_roles', [$this->httpRequest->getQueryParams()['role']]);
            }

            \ilMailFormCall::storeReferer($this->httpRequest->getQueryParams());
            $this->ctrl->redirectByClass('ilmailformgui', 'mailRole');
        }

        if ('my_courses' === $this->httpRequest->getQueryParams()['view']) {
            \ilSession::set('search_crs', \ilUtil::stripSlashes($this->httpRequest->getQueryParams()['search_crs']));
            $this->ctrl->redirectByClass('ilmailformgui', 'searchCoursesTo');
        }

        if (isset($this->httpRequest->getQueryParams()['viewmode'])) {
            \ilSession::set(self::VIEWMODE_SESSION_KEY, $this->httpRequest->getQueryParams()['viewmode']);
            $this->ctrl->setCmd('setViewMode');
        }

        $this->forwardClass = $this->ctrl->getNextClass($this);
        $this->showHeader();

        if ('tree' === ilSession::get(self::VIEWMODE_SESSION_KEY) && $this->ctrl->getCmd() !== 'showExplorer') {
            $this->showExplorer();
        }

        switch ($this->forwardClass) {
            case 'ilmailformgui':
                $this->ctrl->forwardCommand(new \ilMailFormGUI());
                break;

            case 'ilcontactgui':
                $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
                $this->ctrl->forwardCommand(new \ilContactGUI());
                break;

            case 'ilmailoptionsgui':
                $this->tpl->setTitle($this->lng->txt('mail'));
                $this->ctrl->forwardCommand(new \ilMailOptionsGUI());
                break;

            case 'ilmailfoldergui':
                $this->ctrl->forwardCommand(new \ilMailFolderGUI());
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
    private function setViewMode()
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
    private function showHeader()
    {
        global $DIC;
        
        $DIC['ilHelp']->setScreenIdComponent("mail");
        $DIC['ilMainMenu']->setActive("mail");

        $this->tpl->getStandardTemplate();
        $this->tpl->setTitleIcon(\ilUtil::getImagePath("icon_mail.svg"));

        \ilUtil::infoPanel();

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

        $folderTreeState = 'flat';
        if ('tree' !== ilSession::get(self::VIEWMODE_SESSION_KEY)) {
            $folderTreeState = 'tree';
        }

        if ($this->isMailDetailCommand($this->ctrl->getCmd())) {
            $this->ctrl->setParameter($this, 'mail_id', (int) $this->httpRequest->getQueryParams()['mail_id']);
            $this->ctrl->setParameter($this, 'type', 'redirect_to_read');
        }
        $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
        $this->ctrl->setParameter($this, 'viewmode', $folderTreeState);
        $this->tpl->setTreeFlatIcon($this->ctrl->getLinkTarget($this), $folderTreeState);
        $this->ctrl->clearParameters($this);

        $this->tpl->setCurrentBlock('tree_icons');
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @param string $cmd
     * @return bool
     */
    private function isMailDetailCommand(string $cmd) : bool
    {
        $mailId = $this->httpRequest->getQueryParams()['mail_id'] ?? 0;
        if (!is_numeric($mailId) || 0 == $mailId) {
            return false;
        }

        return in_array(strtolower($cmd), ['showmail']);
    }

    /**
     *
     */
    private function showExplorer()
    {
        $exp = new \ilMailExplorer($this, 'showExplorer', $this->user->getId());
        if (!$exp->handleCommand()) {
            $this->tpl->setLeftNavContent($exp->getHTML());
        }
    }
}
