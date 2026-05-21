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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Item\Item;

/**
 * @ilCtrl_IsCalledBy ilPDMailBlockGUI: ilColumnGUI
 */
class ilPDMailBlockGUI extends ilBlockGUI
{
    public static string $block_type = 'pdmail';

    private readonly GlobalHttpState $http;
    private readonly Refinery $refinery;
    protected ilRbacSystem $rbacsystem;
    protected ilSetting $setting;
    /** @var string[] */
    protected array $mails = [];
    protected int $inbox;
    private bool $has_access = false;

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

        $umail = new ilMail($this->user->getId());
        if ($this->rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId())) {
            $this->has_access = true;
            $this->getMails();
            $this->setData($this->mails);
        }
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
        $cmd = '';
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
        if (!$this->has_access) {
            return '';
        }
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
        $this->max_count = count($this->mails);
    }

    protected function getOverview(): string
    {
        return '<div class="small">' . (count($this->mails)) . ' ' . $this->lng->txt('mails_pl') . '</div>';
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

    protected function getListItemForData(array $data): ?Item
    {
        $f = $this->ui->factory();

        $user = ilMailUserCache::getUserObjectById($data['sender_id']);

        $avatar = null;
        if ($user && $user->getId() !== ANONYMOUS_USER_ID) {
            $avatar = $user->getAvatar();
            $public_name_long = $user->getPublicName();
        } elseif ($user) {
            $avatar = $this->ui->factory()
                ->symbol()
                ->avatar()
                ->picture(ilUtil::getImagePath('logo/HeaderIconAvatar.svg'), ilMail::_getIliasMailerName());
            $public_name_long = ilMail::_getIliasMailerName();
        } else {
            $public_name_long = trim(($data['import_name'] ?? '') . ' (' . $this->lng->txt('user_deleted') . ')');
        }

        $new_mail_subj = htmlentities($data['m_subject'], ENT_NOQUOTES, 'UTF-8');
        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mobj_id', $this->inbox);
        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mail_id', $data['mail_id']);
        $new_mail_link = $this->ctrl->getLinkTargetByClass([ilMailGUI::class, ilMailFolderGUI::class], 'showMail');
        $this->ctrl->clearParametersByClass(ilMailFolderGUI::class);

        $button = $f->link()->standard($new_mail_subj, $new_mail_link);

        $item = $f->item()->standard($button);
        if ($avatar !== null) {
            $item = $item->withLeadAvatar($avatar);
        }

        $item = $item->withProperties([
            $this->lng->txt('from') => $public_name_long,
            $this->lng->txt('mail_sent_datetime') => ilDatePresentation::formatDate(new ilDateTime($data['send_time'], IL_CAL_DATETIME)),
        ]);

        return $item;
    }

    public function getNoItemFoundContent(): string
    {
        return $this->lng->txt('mail_no_mail_items');
    }
}
