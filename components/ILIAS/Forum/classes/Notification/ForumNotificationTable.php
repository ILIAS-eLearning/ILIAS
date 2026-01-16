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

namespace ILIAS\Forum\Notification;

use ilStr;
use ilForum;
use Generator;
use ilObjUser;
use ilLanguage;
use ilUIService;
use ilParticipants;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ilForumNotification;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Input\Field\Factory;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\Implementation\Component\Table\Action\Action;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ilUtil;
use ILIAS\UI\Component\Table\DataRowBuilder;

class ForumNotificationTable implements DataRetrieval
{
    /** @var list<array{
     * 'user_id': int,
     * 'login': string,
     * 'firstname': string,
     * 'lastname': string,
     * 'user_toggle_noti': Icon,
     * 'role': string,
     * }>|null */
    private ?array $records = null;
    private FilterComponent $filter_component;
    private DataTable $table_component;

    public function __construct(
        private readonly ServerRequestInterface $http_request,
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly DataFactory $data_factory,
        private readonly int $ref_id,
        private readonly ilParticipants $participants,
        private readonly ilForumNotification $forumNotificationObj,
        private readonly ilUIService $ui_service,
        private readonly string $action
    ) {
    }

    /**
     * @param array{role: string}|null $filter_data
     */
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->getRecords($range, $order, $filter_data);
        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['user_id'], $record);
        }
    }

    /**
     * @param array{role: string}|null $filter_data
     */
    public function initRecords(?array $filter_data): void
    {
        if ($this->records === null) {
            $this->records = $this->getUserNotificationTableData($this->getFilteredUserIds($filter_data));
        }
    }

    /**
     * @return array{
     *     0: FilterComponent,
     *     1: DataTable
     *  }
     */
    public function getComponents(): array
    {
        return [$this->getFilterComponent(), $this->getTableComponent()];
    }

    public function getTableComponent(): DataTable
    {
        if (!isset($this->table_component)) {
            $query_params_namespace = ['frm', 'notifications', 'table'];
            $table_uri = $this->data_factory->uri(ilUtil::_getHttpPath() . '/' . $this->action);
            $url_builder = new URLBuilder($table_uri);
            [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
                $query_params_namespace,
                'action',
                'usr_ids'
            );

            $this->table_component = $this->ui_factory->table()
                ->data(
                    $this,
                    $this->lng->txt(''),
                    $this->getColumns()
                )
                ->withFilter(
                    $this->ui_service->filter()->getData(
                        $this->getFilterComponent()
                    )
                )
                ->withActions(
                    $this->getActions(
                        $url_builder,
                        $action_parameter_token,
                        $row_id_token
                    )
                )
                ->withId('forum_notification_table')
                ->withRange(new Range(0, 50))
                ->withRequest($this->http_request);
        }

        return $this->table_component;
    }

    public function getFilterComponent(): FilterComponent
    {
        if (!isset($this->filter_component)) {
            $filter_inputs = [];
            $is_input_initially_rendered = [];
            $field_factory = $this->ui_factory->input()->field();

            foreach ($this->getFilterFields($field_factory) as $filter_id => $filter) {
                [$filter_inputs[$filter_id], $is_input_initially_rendered[$filter_id]] = $filter;
            }

            $this->filter_component = $this->ui_service->filter()->standard(
                'forum_notification_filter',
                $this->action,
                $filter_inputs,
                $is_input_initially_rendered,
                true,
                true
            );
        }

        return $this->filter_component;
    }

    /**
     * @return array<string, array{0: FilterInput, 1: bool}>
     */
    public function getFilterFields(Factory $field_factory): array
    {
        $options = [
            'member' => $this->lng->txt('il_' . $this->participants->getType() . '_member'),
            'tutor' => $this->lng->txt('il_' . $this->participants->getType() . '_tutor'),
            'admin' => $this->lng->txt('il_' . $this->participants->getType() . '_admin'),
            'moderators' => $this->lng->txt('frm_moderators'),
        ];

        return [
            'role' => [
                $field_factory->select($this->lng->txt('roles'), $options),
                true
            ]
        ];
    }

    /**
     * @param array{role: string}|null $filter_data
     */
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $this->initRecords($filter_data);

        return \count((array) $this->records);
    }

    /**
     * @param  array{role: string}|null $filter_data
     * @return list<int>
     */
    public function getFilteredUserIds(?array $filter_data): array
    {
        $moderator_ids = ilForum::_getModerators($this->ref_id);

        $admin_ids = $this->participants->getAdmins();
        $member_ids = $this->participants->getMembers();
        $tutor_ids = $this->participants->getTutors();

        $filter = (string) ($filter_data['role'] ?? '');
        switch ($filter) {
            case 'member':
                $user_ids = $member_ids;
                break;

            case 'tutor':
                $user_ids = $tutor_ids;
                break;

            case 'admin':
                $user_ids = $admin_ids;
                break;

            case 'moderators':
                $user_ids = $moderator_ids;
                break;

            default:
                $user_ids = array_merge($admin_ids, $member_ids, $tutor_ids, $moderator_ids);
                break;
        }

        return array_unique($user_ids);
    }

    /**
     * @param array{role: string}|null $filter_data
     * @return list<array{
     *     'user_id': int,
     *     'login': string,
     *     'firstname': string,
     *     'lastname': string,
     *     'user_toggle_noti': Icon,
     *     'role': string,
     * }>
     */
    private function getRecords(Range $range, Order $order, ?array $filter_data): array
    {
        $this->initRecords($filter_data);
        $records = $this->records;

        if ($order) {
            $records = $this->orderRecords($records, $order);
        }

        if ($range) {
            $records = $this->limitRecords($records, $range);
        }

        return $records;
    }

    /**
     * @param int[] $user_ids
     * @return list<array{
     *      'user_id': int,
     *      'login': string,
     *      'firstname': string,
     *      'lastname': string,
     *      'user_toggle_noti': Icon,
     *      'role': string,
     *  }>
     */
    private function getUserNotificationTableData(array $user_ids): array
    {
        $icons = [
            $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small'),
            $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small'),
        ];

        $counter = 0;
        $users = [];
        $moderator_ids = ilForum::_getModerators($this->ref_id);
        foreach ($user_ids as $user_id) {
            $forced_events = $this->forumNotificationObj->getForcedEventsObjectByUserId($user_id);
            $member_const = 'IL_' . strtoupper($this->participants->getType()) . '_MEMBER';
            $tutor_const = 'IL_' . strtoupper($this->participants->getType()) . '_TUTOR';
            $admin_const = 'IL_' . strtoupper($this->participants->getType()) . '_ADMIN';
            $member_id = $this->participants->getAutoGeneratedRoleId(ilParticipants::{$member_const});
            $tutor_id = $this->participants->getAutoGeneratedRoleId(ilParticipants::{$tutor_const});
            $admin_id = $this->participants->getAutoGeneratedRoleId(ilParticipants::{$admin_const});

            $types = implode(', ', array_map(function (int $role_id) use ($admin_id, $tutor_id, $member_id) {
                return match ($role_id) {
                    $member_id => $this->lng->txt('il_' . $this->participants->getType() . '_member'),
                    $tutor_id => $this->lng->txt('il_' . $this->participants->getType() . '_tutor'),
                    $admin_id => $this->lng->txt('il_' . $this->participants->getType() . '_admin'),
                    default => ''
                };
            }, $this->participants->getAssignedRoles($user_id)));
            if (\in_array($user_id, $moderator_ids, true)) {
                $types .= ', ' . $this->lng->txt('frm_moderators');
            }

            $users[$counter]['user_id'] = $user_id;
            $users[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
            $name = ilObjUser::_lookupName($user_id);
            $users[$counter]['firstname'] = $name['firstname'];
            $users[$counter]['lastname'] = $name['lastname'];
            $users[$counter]['user_toggle_noti'] = $icons[(int) $forced_events->getUserToggle()];
            $users[$counter]['role'] = $types;

            $counter++;
        }

        return $users;
    }

    /**
     * @return array<string, Action>
     */
    public function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        return [
            'enableHideUserToggleNoti' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('enable_hide_user_toggle'),
                $url_builder->withParameter($action_parameter_token, 'enableHideUserToggleNoti'),
                $row_id_token
            ),
            'disableHideUserToggleNoti' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('disable_hide_user_toggle'),
                $url_builder->withParameter($action_parameter_token, 'disableHideUserToggleNoti'),
                $row_id_token
            ),
            'notificationSettings' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('notification_settings'),
                $url_builder->withParameter($action_parameter_token, 'notificationSettings'),
                $row_id_token
            )->withAsync(true),
        ];
    }

    /**
     * @param list<array{
     *      'user_id': int,
     *      'login': string,
     *      'firstname': string,
     *      'lastname': string,
     *      'user_toggle_noti': Icon,
     *      'role': string,
     *  }> $records
     * @return list<array{
     *      'user_id': int,
     *      'login': string,
     *      'firstname': string,
     *      'lastname': string,
     *      'user_toggle_noti': Icon,
     *      'role': string,
     *  }>>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return \array_slice($records, $range->getStart(), $range->getLength());
    }

    /**
     * @param list<array{
     *      'user_id': int,
     *      'login': string,
     *      'firstname': string,
     *      'lastname': string,
     *      'user_toggle_noti': Icon,
     *      'role': string,
     *  }> $records
     * @return list<array{
     *      'user_id': int,
     *      'login': string,
     *      'firstname': string,
     *      'lastname': string,
     *      'user_toggle_noti': Icon,
     *      'role': string,
     *  }>
     */
    private function orderRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );
        usort($records, static fn(array $left, array $right): int => ilStr::strCmp(
            $left[$order_field] ?? '',
            $right[$order_field] ?? ''
        ));

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        return $records;
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        return [
            'login' => $this->ui_factory->table()->column()->text($this->lng->txt('login')),
            'firstname' => $this->ui_factory->table()->column()->text($this->lng->txt('firstname')),
            'lastname' => $this->ui_factory->table()->column()->text($this->lng->txt('lastname')),
            'user_toggle_noti' => $this->ui_factory->table()->column()->statusIcon(
                $this->lng->txt('allow_user_toggle_noti')
            )
                ->withIsSortable(false),
            'role' => $this->ui_factory->table()->column()->text($this->lng->txt('role')),
        ];
    }
}
