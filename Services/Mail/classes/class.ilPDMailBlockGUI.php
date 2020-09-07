<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Block/classes/class.ilBlockGUI.php';
include_once 'Services/Mail/classes/class.ilMailUserCache.php';

/**
 * BlockGUI class for Personal Desktop Mail block
 * @author			Alex Killing <alex.killing@gmx.de>
 * @version		   $Id$
 * @ilCtrl_IsCalledBy ilPDMailBlockGUI: ilColumnGUI
 */
class ilPDMailBlockGUI extends ilBlockGUI
{
    public static $block_type = 'pdmail';

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var \ilSetting
     */
    protected $setting;

    /**
     * @var array
     */
    protected $mails = array();

    /**
     * @var int
     */
    protected $inbox;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->setting = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();

        include_once 'Services/User/classes/class.ilObjUser.php';
        include_once 'Services/Mail/classes/class.ilMailbox.php';
        include_once 'Services/Mail/classes/class.ilMail.php';

        parent::__construct();

        $this->setLimit(5);
        $this->setImage(ilUtil::getImagePath('icon_mail.svg'));
        $this->setTitle($this->lng->txt('mail'));
        $this->setAvailableDetailLevels(3);
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode()
    {
        switch ($_GET['cmd']) {
            case 'showMail':
                return IL_SCREEN_CENTER;
                break;

            default:
                return IL_SCREEN_SIDE;
                break;
        }
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('getHTML');

        return $this->$cmd();
    }

    public function getHTML()
    {
        $umail = new ilMail($this->user->getId());
        if (!$this->rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId())) {
            return '';
        }

        if ($this->getCurrentDetailLevel() == 0) {
            return '';
        } else {
            $html = parent::getHTML();
            return $html;
        }
    }

    /**
     * Get Mails
     */
    protected function getMails()
    {
        require_once 'Services/Mail/classes/class.ilObjMail.php';

        $umail = new ilMail($this->user->getId());
        $mbox = new ilMailbox($this->user->getId());
        $this->inbox = $mbox->getInboxFolder();

        $this->mails = $umail->getMailsOfFolder(
            $this->inbox,
            array(
                 'status' => 'unread',
                 'type' => ((int) $this->setting->get('pd_sys_msg_mode')) != ilObjMail::PD_SYS_MSG_MAIL_BLOCK ? 'normal' : ''
            )
        );
    }

    /**
     * Fill data section
     */
    public function fillDataSection()
    {
        $this->getMails();
        $this->setData($this->mails);

        if ($this->getCurrentDetailLevel() > 1 && count($this->mails) > 0) {
            $this->setRowTemplate("tpl.pd_mail_row.html", "Services/Mail");
            if ($this->getCurrentDetailLevel() > 2) {
                $this->setColSpan(2);
            }
            parent::fillDataSection();
        } else {
            $this->setEnableNumInfo(false);
            if (count($this->mails) == 0) {
                $this->setEnableDetailRow(false);
            }
            $this->setDataSection($this->getOverview());
        }
    }

    /**
     * get flat bookmark list for personal desktop
     */
    public function fillRow($mail)
    {
        $user = ilMailUserCache::getUserObjectById($mail['sender_id']);
        
        if ($this->getCurrentDetailLevel() > 2) {
            $this->tpl->touchBlock('usr_image_space');
            if ($user && $user->getId() != ANONYMOUS_USER_ID) {
                $this->tpl->setVariable('PUBLIC_NAME_LONG', $user->getPublicName());
                $this->tpl->setVariable('IMG_SENDER', $user->getPersonalPicturePath('xxsmall'));
                $this->tpl->setVariable('ALT_SENDER', htmlspecialchars($user->getPublicName()));
            } elseif (!$user) {
                $this->tpl->setVariable('PUBLIC_NAME_LONG', $mail['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')');
                
                $this->tpl->setCurrentBlock('image_container');
                $this->tpl->touchBlock('image_container');
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setVariable('PUBLIC_NAME_LONG', ilMail::_getIliasMailerName());
                $this->tpl->setVariable('IMG_SENDER', ilUtil::getImagePath('HeaderIconAvatar.svg'));
                $this->tpl->setVariable('ALT_SENDER', htmlspecialchars(ilMail::_getIliasMailerName()));
            }

            $this->tpl->setVariable('NEW_MAIL_DATE', ilDatePresentation::formatDate(new ilDate($mail['send_time'], IL_CAL_DATE)));
        } else {
            if ($user && $user->getId() != ANONYMOUS_USER_ID) {
                $this->tpl->setVariable('PUBLIC_NAME_SHORT', $user->getPublicName());
            } elseif (!$user) {
                $this->tpl->setVariable('PUBLIC_NAME_SHORT', $mail['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')');
            } else {
                $this->tpl->setVariable('PUBLIC_NAME_SHORT', ilMail::_getIliasMailerName());
            }
        }

        $this->tpl->setVariable('NEW_MAIL_SUBJ', htmlentities($mail['m_subject'], ENT_NOQUOTES, 'UTF-8'));
        $this->ctrl->setParameter($this, 'mobj_id', $this->inbox);
        $this->ctrl->setParameter($this, 'mail_id', $mail['mail_id']);
        $this->ctrl->setParameter($this, 'mail_mode', $this->mail_mode);
        $this->tpl->setVariable('NEW_MAIL_LINK_READ', $this->ctrl->getLinkTarget($this, 'showMail'));
        $this->ctrl->clearParameters($this);
    }

    /**
     * Get overview.
     */
    protected function getOverview()
    {
        return '<div class="small">' . ((int) count($this->mails)) . " " . $this->lng->txt("mails_pl") . "</div>";
    }

    /**
     * show mail
     */
    protected function showMail()
    {
        include_once("./Services/Mail/classes/class.ilPDMailGUI.php");
        $mail_gui = new ilPDMailGUI();

        include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
        $content_block = new ilPDContentBlockGUI();
        $content_block->setContent($mail_gui->getPDMailHTML(
            $_GET["mail_id"],
            $_GET["mobj_id"]
        ));
        $content_block->setTitle($this->lng->txt("message"));
        $content_block->setColSpan(2);
        $content_block->setImage(ilUtil::getImagePath("icon_mail.svg"));
        $content_block->addHeaderCommand(
            $this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui", "show"),
            $this->lng->txt("selected_items_back")
        );

        if ($_GET["mail_mode"] != "system") {
            $content_block->addBlockCommand(
                "ilias.php?baseClass=ilMailGUI&mail_id=" .
                    $_GET["mail_id"] . "&mobj_id=" . $_GET["mobj_id"] . "&type=reply",
                $this->lng->txt("reply")
            );
            $content_block->addBlockCommand(
                "ilias.php?baseClass=ilMailGUI&mail_id=" .
                    $_GET["mail_id"] . "&mobj_id=" . $_GET["mobj_id"] . "&type=read",
                $this->lng->txt("inbox")
            );

            $this->ctrl->setParameter($this, 'mail_id', (int) $_GET['mail_id']);
            $content_block->addBlockCommand($this->ctrl->getLinkTarget($this, 'deleteMail'), $this->lng->txt('delete'));
        } else {
            $this->ctrl->setParameter($this, "mail_id", $_GET["mail_id"]);
            $this->ctrl->setParameter($this, "mobj_id", $_GET["mobj_id"]);
            $content_block->addBlockCommand(
                $this->ctrl->getLinkTarget($this, "deleteMail"),
                $this->lng->txt("delete")
            );
            $this->ctrl->clearParameters($this);
        }

        return $content_block->getHTML();
    }

    /**
     * delete mail
     */
    public function deleteMail()
    {
        $this->lng->loadLanguageModule('mail');

        $umail = new ilMail($this->user->getId());
        $mbox = new ilMailbox($this->user->getId());

        if (!$_GET['mobj_id']) {
            $_GET['mobj_id'] = $mbox->getInboxFolder();
        }

        if ($umail->moveMailsToFolder(array((int) $_GET['mail_id']), (int) $mbox->getTrashFolder())) {
            \ilUtil::sendInfo($this->lng->txt('mail_moved_to_trash'), true);
        } else {
            \ilUtil::sendInfo($this->lng->txt('mail_move_error'), true);
        }
        $this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
    }

    /**
     * @param array $data
     */
    protected function preloadData(array $data)
    {
        $usr_ids = array();

        foreach ($data as $mail) {
            if ($mail['sender_id'] && $mail['sender_id'] != ANONYMOUS_USER_ID) {
                $usr_ids[$mail['sender_id']] = $mail['sender_id'];
            }
        }

        ilMailUserCache::preloadUserObjects($usr_ids);
    }
}
