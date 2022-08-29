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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Item\Item;

/**
 * BlockGUI class for Personal Desktop Mail block
 * @author			Alex Killing <alex.killing@gmx.de>
 * @assertSame_IsCalledBy ilPDMailBlockGUI: ilColumnGUI
 */
class ilPDMailBlockGUI extends ilBlockGUI
{
    public static string $block_type = 'pdmail';
    private GlobalHttpState $http;
    private Refinery $refinery;
    private int $requestMailObjId = 0;
    protected ilRbacSystem $rbacsystem;
    protected ilSetting $setting;
    /** @var string[] */
    protected array $mails = [];
    protected int $inbox;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->setting = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        parent::__construct();

        $this->setLimit(5);
        $this->setTitle($this->lng->txt('mail'));
        $this->setPresentation(self::PRES_SEC_LIST);
    }

    public function getBlockType(): string
    {
        return self::$block_type;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    public static function getScreenMode(): string
    {
        global $DIC;
        $cmd = "";
        if ($DIC->http()->wrapper()->query()->has('cmd')) {
            $cmd = $DIC->http()->wrapper()->query()->retrieve('cmd', $DIC->refinery()->kindlyTo()->string());
        }
        if ($cmd === 'showMail') {
            return IL_SCREEN_CENTER;
        }

        return IL_SCREEN_SIDE;
    }

    public function executeCommand(): string
    {
        $cmd = $this->ctrl->getCmd('getHTML');

        return $this->$cmd();
    }

    public function getHTML(): string
    {
        $umail = new ilMail($this->user->getId());
        if (!$this->rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId())) {
            return '';
        }

        $this->getMails();
        $this->setData($this->mails);

        return parent::getHTML();
    }

    protected function getMails(): void
    {
        $umail = new ilMail($this->user->getId());
        $mbox = new ilMailbox($this->user->getId());
        $this->inbox = $mbox->getInboxFolder();

        $this->mails = $umail->getMailsOfFolder(
            $this->inbox,
            [
                 'status' => 'unread',
            ]
        );
    }

    public function fillDataSection(): void
    {
        $this->getMails();
        $this->setData($this->mails);

        if ($this->mails !== []) {
            $this->setRowTemplate("tpl.pd_mail_row.html", "Services/Mail");
            parent::fillDataSection();
        } else {
            $this->setEnableNumInfo(false);
            $this->setDataSection($this->getOverview());
        }
    }

    public function fillRow(array $a_set): void
    {
        $user = ilMailUserCache::getUserObjectById((int) $a_set['sender_id']);

        $this->tpl->touchBlock('usr_image_space');
        if ($user && $user->getId() !== ANONYMOUS_USER_ID) {
            $this->tpl->setVariable('PUBLIC_NAME_LONG', $user->getPublicName());
            $this->tpl->setVariable('IMG_SENDER', $user->getPersonalPicturePath('xxsmall'));
            $this->tpl->setVariable('ALT_SENDER', htmlspecialchars($user->getPublicName()));
        } elseif (!$user instanceof ilObjUser) {
            $this->tpl->setVariable(
                'PUBLIC_NAME_LONG',
                $a_set['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')'
            );

            $this->tpl->setCurrentBlock('image_container');
            $this->tpl->touchBlock('image_container');
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable('PUBLIC_NAME_LONG', ilMail::_getIliasMailerName());
            $this->tpl->setVariable('IMG_SENDER', ilUtil::getImagePath('HeaderIconAvatar.svg'));
            $this->tpl->setVariable('ALT_SENDER', htmlspecialchars(ilMail::_getIliasMailerName()));
        }

        $this->tpl->setVariable(
            'NEW_MAIL_DATE',
            ilDatePresentation::formatDate(new ilDate($a_set['send_time'], IL_CAL_DATE))
        );

        $this->tpl->setVariable(
            'NEW_MAIL_SUBJ',
            htmlentities($a_set['m_subject'], ENT_NOQUOTES, 'UTF-8')
        );
        $this->ctrl->setParameter($this, 'mobj_id', $this->inbox);
        $this->ctrl->setParameter($this, 'mail_id', $a_set['mail_id']);
        $this->tpl->setVariable('NEW_MAIL_LINK_READ', $this->ctrl->getLinkTarget($this, 'showMail'));
        $this->ctrl->clearParameters($this);
    }

    protected function getOverview(): string
    {
        return '<div class="small">' . (count($this->mails)) . " " . $this->lng->txt("mails_pl") . "</div>";
    }

    protected function showMail(): string
    {
        $mail_gui = new ilPDMailGUI();

        $content_block = new ilDashboardContentBlockGUI();
        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }
        $mobjId = $this->requestMailObjId;
        if ($this->http->wrapper()->query()->has('mobj_id')) {
            $mobjId = $this->http->wrapper()->query()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        }
        $content_block->setContent($mail_gui->getPDMailHTML(
            $mailId,
            $mobjId
        ));
        $content_block->setTitle($this->lng->txt("message"));

        $content_block->addBlockCommand(
            "ilias.php?baseClass=ilMailGUI&mail_id=" .
            $mailId . "&mobj_id="
            . $mobjId . "&type=reply",
            $this->lng->txt("reply")
        );
        $content_block->addBlockCommand(
            "ilias.php?baseClass=ilMailGUI&mail_id=" .
            $mailId . "&mobj_id="
            . $mobjId . "&type=read",
            $this->lng->txt("inbox")
        );

        $this->ctrl->setParameter($this, 'mail_id', $mailId);
        $content_block->addBlockCommand(
            $this->ctrl->getLinkTarget($this, 'deleteMail'),
            $this->lng->txt('delete')
        );

        return $content_block->getHTML();
    }

    public function deleteMail(): void
    {
        $this->lng->loadLanguageModule('mail');

        $umail = new ilMail($this->user->getId());
        $mbox = new ilMailbox($this->user->getId());

        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }
        $mobjId = 0;
        if ($this->http->wrapper()->query()->has('mobj_id')) {
            $mobjId = $this->http->wrapper()->query()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        }

        if ($mobjId) {
            $this->requestMailObjId = $mbox->getInboxFolder();
        }

        if ($umail->moveMailsToFolder(
            [$mailId],
            $mbox->getTrashFolder()
        )) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('mail_moved_to_trash'), true);
        } else {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('mail_move_error'), true);
        }
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'show');
    }

    protected function preloadData(array $data): void
    {
        $usr_ids = [];

        foreach ($data as $mail) {
            if ($mail['sender_id'] && $mail['sender_id'] !== ANONYMOUS_USER_ID) {
                $usr_ids[$mail['sender_id']] = $mail['sender_id'];
            }
        }

        ilMailUserCache::preloadUserObjects($usr_ids);
    }

    //
    // New rendering
    //

    protected bool $new_rendering = true;

    protected function getListItemForData(array $data): ?Item
    {
        $f = $this->ui->factory();

        $user = ilMailUserCache::getUserObjectById($data['sender_id']);

        if ($user && $user->getId() !== ANONYMOUS_USER_ID) {
            $public_name_long = $user->getPublicName();
            $img_sender = $user->getPersonalPicturePath('xxsmall');
            $alt_sender = htmlspecialchars($user->getPublicName());
        } elseif (!$user instanceof ilObjUser) {
            $public_name_long = $data['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')';
            $img_sender = "";
            $alt_sender = "";
        } else {
            $public_name_long = ilMail::_getIliasMailerName();
            $img_sender = ilUtil::getImagePath('HeaderIconAvatar.svg');
            $alt_sender = htmlspecialchars(ilMail::_getIliasMailerName());
        }

        $new_mail_date = ilDatePresentation::formatDate(new ilDate($data['send_time'], IL_CAL_DATE));
        $new_mail_subj = htmlentities($data['m_subject'], ENT_NOQUOTES, 'UTF-8');
        $this->ctrl->setParameter($this, 'mobj_id', $this->inbox);
        $this->ctrl->setParameter($this, 'mail_id', $data['mail_id']);
        $new_mail_link = $this->ctrl->getLinkTarget($this, 'showMail');
        $this->ctrl->clearParameters($this);


        $button = $f->button()->shy($new_mail_subj, $new_mail_link);

        $item = $f->item()->standard($button)->withDescription($new_mail_date);
        if ($img_sender !== "") {
            $item = $item->withLeadImage($f->image()->standard($img_sender, $alt_sender));
        }

        return $item;
    }
}
