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

namespace ILIAS\Mail\Folder;

use ilLanguage;
use ilMail;
use ilMailUserCache;
use ILIAS\Mail\Message\MailBoxOrderColumn;
use ILIAS\Mail\Message\MailRecordData;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\Column\Column as TableColumn;
use ILIAS\UI\Component\Table\Action\Action as TableAction;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;
use DateTimeImmutable;
use DateTimeZone;

class MailFolderTableUI implements \ILIAS\UI\Component\Table\DataRetrieval
{
    // table actions
    public const ACTION_SHOW = 'show';
    public const ACTION_EDIT = 'edit';
    public const ACTION_REPLY = 'reply';
    public const ACTION_FORWARD = 'forward';
    public const ACTION_DOWNLOAD_ATTACHMENT = 'download';
    public const ACTION_PRINT = 'print';
    public const ACTION_PROFILE = 'profile';
    public const ACTION_MOVE_TO = 'moveTo';
    public const ACTION_DELETE = 'delete';
    public const ACTION_MARK_READ = 'markRead';
    public const ACTION_MARK_UNREAD = 'marUnread';

    /** @var string[] */
    private array $avatars = [];

    /**
     * @param MailFolderData[] $user_folders
     */
    public function __construct(
        private readonly URLBuilder $url_builder,
        private readonly URLBuilderToken $action_token,
        private readonly URLBuilderToken $row_id_token,
        private readonly URLBuilderToken $folder_token,
        private readonly array $user_folders,
        private readonly MailFolderData $current_folder,
        private readonly MailFolderSearch $search,
        private readonly ilMail $mail,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly ilLanguage $lng,
        private readonly ServerRequestInterface $http_request,
        private readonly DataFactory $data_factory,
        private readonly Refinery $refinery,
        private readonly DateFormat $date_format,
        private readonly string $time_format,
        private readonly DateTimeZone $user_time_zone
    ) {
    }

    public function getComponent(): DataTable
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this->getTableTitle(),
                $this->getColumnDefinition(),
                $this
            )
            ->withId(self::class)
            ->withOrder(new Order('date', Order::DESC))
            ->withActions($this->getActions())
            ->withRequest($this->http_request);
    }

    /**
     * @return array<string, TableColumn>
     */
    private function getColumnDefinition(): array
    {
        if ((int) $this->time_format === \ilCalendarSettings::TIME_FORMAT_12) {
            $date_format = $this->data_factory->dateFormat()->withTime12($this->date_format);
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime24($this->date_format);
        }

        $columns = [
            'status' => $this->ui_factory
                ->table()
                ->column()
                ->statusIcon($this->lng->txt('status'))
                ->withIsSortable(true),

            'avatar' => $this->ui_factory
                ->table()
                ->column()
                ->status($this->lng->txt('personal_picture'))
                ->withIsSortable(true),

            'sender' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('sender'))
                ->withIsSortable(true),

            'recipients' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('recipient'))
                ->withIsSortable(true),

            'subject' => $this->ui_factory
                ->table()
                ->column()
                ->link($this->lng->txt('subject'))
                ->withIsSortable(true),

            'attachments' => $this->ui_factory
                ->table()
                ->column()
                ->status($this->lng->txt('attachments'))
                ->withIsSortable(true),

            'date' => $this->ui_factory
                ->table()
                ->column()
                ->date(
                    $this->lng->txt('date'),
                    $date_format
                )
                ->withIsSortable(true),
        ];

        if ($this->current_folder->hasOutgoingMails()) {
            unset($columns['status'], $columns['avatar'], $columns['sender']);
        } else {
            unset($columns['recipients']);
        }

        return $columns;
    }

    /**
     * @return array<string, TableAction>
     */
    private function getActions(): array
    {
        $actions = [
            self::ACTION_SHOW => $this->ui_factory->table()->action()->single(
                $this->lng->txt('view'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_SHOW),
                $this->row_id_token
            ),
            self::ACTION_EDIT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_EDIT),
                $this->row_id_token
            ),
            self::ACTION_REPLY => $this->ui_factory->table()->action()->single(
                $this->lng->txt('reply'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_REPLY),
                $this->row_id_token
            ),
            self::ACTION_FORWARD => $this->ui_factory->table()->action()->single(
                $this->lng->txt('forward'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_FORWARD),
                $this->row_id_token
            ),
            self::ACTION_DOWNLOAD_ATTACHMENT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('mail_download_attachment'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_DOWNLOAD_ATTACHMENT),
                $this->row_id_token
            ),
            self::ACTION_PRINT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('print'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_PRINT),
                $this->row_id_token
            ),
            self::ACTION_MARK_READ => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('mail_mark_read'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_MARK_READ),
                $this->row_id_token
            ),
            self::ACTION_MARK_UNREAD => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('mail_mark_unread'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_MARK_UNREAD),
                $this->row_id_token
            ),
            self::ACTION_DELETE => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_token, self::ACTION_DELETE),
                $this->row_id_token
            )->withAsync(), // for confirmation modal
        ];

        foreach ($this->user_folders as $target_folder) {
            if ($target_folder->getFolderId() !== $this->current_folder->getFolderId()) {
                $action_title = $this->lng->txt('mail_move_to') . ' ' . $target_folder->getTitle();
                if ($target_folder->isTrash()) {
                    $action_title .= ' (' . $this->lng->txt('delete') . ')';
                }

                $actions[self::ACTION_MOVE_TO . $target_folder->getFolderId()] = $this->ui_factory
                    ->table()
                    ->action()
                    ->multi(
                        $action_title,
                        $this->url_builder
                            ->withParameter($this->action_token, self::ACTION_MOVE_TO)
                            ->withParameter($this->folder_token, (string) $target_folder->getFolderId()),
                        $this->row_id_token
                    );
            }
        }

        if ($this->current_folder->isDrafts()) {
            unset($actions[self::ACTION_SHOW], $actions[self::ACTION_REPLY], $actions[self::ACTION_FORWARD]);
        } else {
            unset($actions[self::ACTION_EDIT]);
        }

        if ($this->current_folder->hasOutgoingMails()) {
            unset($actions[self::ACTION_MARK_READ], $actions[self::ACTION_MARK_UNREAD]);
        }

        if (!$this->current_folder->isTrash()) {
            unset($actions[self::ACTION_DELETE]);
        }

        return $actions;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,            // not used, because data is filtered by MailDataSearch
        ?array $additional_parameters   // not used
    ): \Generator {
        // mapping of table columns to allowed order columns of the mailbox query
        $order_columns = [
            'status' => MailBoxOrderColumn::STATUS,
            'subject' => MailBoxOrderColumn::SUBJECT,
            'sender' => MailBoxOrderColumn::FROM,
            'recipients' => MailBoxOrderColumn::RCP_TO,
            'date' => MailBoxOrderColumn::SEND_TIME,
            'attachments' => MailBoxOrderColumn::ATTACHMENTS
        ];

        [$order_column, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        $records = $this->search->getPagedRecords(
            $range->getLength(),
            $range->getStart(),
            $order_columns[$order_column] ?? null,
            $order_direction
        );

        // preload user objects for display of avatar and sender
        if ($this->current_folder->hasIncomingMails()) {
            $user_ids = [];
            foreach ($records as $record) {
                if ($record->hasPersonalSender()) {
                    $user_ids[$record->getSenderId()] = $record->getSenderId();
                }
            }
            ilMailUserCache::preloadUserObjects($user_ids);
        }

        foreach ($records as $record) {
            if ($this->current_folder->hasIncomingMails()) {
                $data = [
                    'status' => $this->getStatus($record),
                    'avatar' => $this->getAvatar($record),
                    'sender' => $this->getSender($record),
                    'subject' => $this->getSubject($record),
                    'attachments' => $this->getAttachments($record),
                    'date' => $this->getDate($record)
                ];
            } else {
                $data = [
                    'recipients' => $this->getRecipients($record),
                    'subject' => $this->getSubject($record),
                    'attachments' => $this->getAttachments($record),
                    'date' => $this->getDate($record)
                ];
            }

            yield $row_builder->buildDataRow(
                (string) $record->getMailId(),
                $data
            )->withDisabledAction(self::ACTION_REPLY, !$record->hasPersonalSender())->withDisabledAction(
                self::ACTION_DOWNLOAD_ATTACHMENT,
                !$record->hasAttachments()
            );
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->search->getCount();
    }

    private function getTableTitle(): string
    {
        if ($this->current_folder->hasIncomingMails() && $this->search->getUnread() > 0) {
            return \sprintf(
                '%s: %s (%s %s)',
                $this->current_folder->getTitle(),
                $this->search->getCount() === 1
                    ? $this->lng->txt('mail_1')
                    : \sprintf($this->lng->txt('mail_s'), $this->search->getCount()),
                $this->search->getUnread(),
                $this->lng->txt('unread')
            );
        }

        return \sprintf(
            '%s: %s',
            $this->current_folder->getTitle(),
            $this->search->getCount() === 1
                ? $this->lng->txt('mail_1')
                : \sprintf($this->lng->txt('mail_s'), $this->search->getCount()),
        );
    }

    private function getAvatar(MailRecordData $record): string
    {
        if (!\array_key_exists($record->getSenderId(), $this->avatars)) {
            if ($record->getSenderId() === ANONYMOUS_USER_ID) {
                $avatar = $this->ui_factory->symbol()->avatar()->picture(
                    \ilUtil::getImagePath('logo/HeaderIconAvatar.svg'),
                    $this->getSender($record)
                );
            } else {
                $user = ilMailUserCache::getUserObjectById($record->getSenderId());
                $avatar = $user?->getAvatar();
            }

            $this->avatars[$record->getSenderId()] = $avatar
                ? $this->ui_renderer->render($avatar)
                : '';
        }

        return $this->avatars[$record->getSenderId()];
    }

    private function getStatus(MailRecordData $record): Icon
    {
        return $record->isRead()
            ? $this->ui_factory->symbol()->icon()->standard('mailr', $this->lng->txt('mailr'))
            : $this->ui_factory->symbol()->icon()->standard('mailu', $this->lng->txt('mailu'));
    }

    private function getSender(MailRecordData $record): string
    {
        if ($record->getSenderId() === ANONYMOUS_USER_ID) {
            return ilMail::_getIliasMailerName();
        }

        $user = ilMailUserCache::getUserObjectById($record->getSenderId());
        if ($user !== null) {
            if ($user->hasPublicProfile()) {
                return $this->ui_renderer->render(
                    $this->ui_factory->link()->standard(
                        $user->getPublicName(),
                        (string) $this->url_builder
                            ->withParameter($this->action_token, self::ACTION_PROFILE)
                            ->withParameter($this->row_id_token, (string) $record->getMailId())
                            ->buildURI()
                    )
                );
            }

            return $user->getPublicName();
        }

        return trim(($record->getImportName() ?? '') . ' (' . $this->lng->txt('user_deleted') . ')');
    }

    private function getRecipients(MailRecordData $record): string
    {
        return $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
            $this->mail->formatNamesForOutput((string) $record->getRcpTo())
        );
    }

    private function getSubject(MailRecordData $record): Link
    {
        return $this->ui_factory->link()->standard(
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($record->getSubject()),
            (string) $this->url_builder
                ->withParameter(
                    $this->action_token,
                    $this->current_folder->isDrafts() ? self::ACTION_EDIT : self::ACTION_SHOW
                )
                ->withParameter($this->row_id_token, (string) $record->getMailId())
                ->buildURI()
        );
    }

    private function getDate(MailRecordData $record): ?DateTimeImmutable
    {
        return empty($record->getSendTime()) ? null : $record->getSendTime()->setTimezone($this->user_time_zone);
    }

    private function getAttachments(MailRecordData $record): string
    {
        return $record->hasAttachments()
            ? $this->ui_renderer->render($this->ui_factory->symbol()->glyph()->attachment())
            : '';
    }
}
