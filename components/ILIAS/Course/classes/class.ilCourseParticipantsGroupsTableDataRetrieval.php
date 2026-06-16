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

use ILIAS\UI\Component\Table\DataRetrieval as ilTableDataRetrievalInterface;
use ILIAS\DI\UIServices as ilUIServices;

class ilCourseParticipantsGroupsTableDataRetrieval implements ilTableDataRetrievalInterface
{
    protected const string TABLE_ACTION_CONFIRM_UNSUBSCRIBE_SUFFIX = '_confirm_unsubscribe';
    protected const string TABLE_ACTION_UNSUBSCRIBE_SUFFIX = '_unsubscribe';

    protected array $data;
    protected int $obj_id;

    public function __construct(
        protected ilCourseParticipantsGroupsGUI $parent_obj,
        protected ilTree $tree,
        protected ilAccess $access,
        protected ilUIServices $ui_services,
        protected int $ref_id
    ) {
        $this->data = [];
        $this->obj_id = ilObject::_lookupObjId($ref_id);
    }

    protected function applyFilter(
        ?array $filter_data
    ): array {
        if (is_null($filter_data)) {
            return $this->data['rows'] ?? [];
        }
        /**
         * @var \ILIAS\UI\Component\Input\Field\Select $select
         * @var \ILIAS\UI\Component\Input\Field\Text $text
         */
        $select = $filter_data[ilCourseParticipantsGroupsTableGUI::TABLE_FILTER_GROUPS];
        $text = $filter_data[ilCourseParticipantsGroupsTableGUI::TABLE_FILTER_NAME];
        $group_ids = is_null($select->getValue()) ? [] : [(int) $select->getValue()];
        $group_ids = array_diff($group_ids, [0]);
        $name = $text->getValue() ?? '';
        $rows = [];
        foreach ($this->data['rows'] ?? [] as $row) {
            $row_group_ids = [];
            foreach ($row['groups'] as $group) {
                $row_group_ids[] = $group['group_id'];
            }
            if (
                !$select->isDisabled() &&
                count($group_ids) > 0 &&
                count(array_diff($group_ids, $row_group_ids)) === count($group_ids)
            ) {
                continue;
            }
            if (
                !$text->isDisabled() &&
                $name !== '' &&
                !str_contains($row['name'], $name)
            ) {
                continue;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public function buildConfirmUnsubscribeActionId(
        int $group_ref_id
    ): string {
        return $group_ref_id . self::TABLE_ACTION_CONFIRM_UNSUBSCRIBE_SUFFIX;
    }

    public function buildUnsubscribeActionId(
        int $group_ref_id
    ): string {
        return $group_ref_id . self::TABLE_ACTION_UNSUBSCRIBE_SUFFIX;
    }

    public function getAllConfirmUnsubscribeActionIds(): array
    {
        $ids = [];
        foreach ($this->getSelectableGroups() as $ref_id => $group) {
            $ids[] = $this->buildConfirmUnsubscribeActionId($ref_id);
        }
        return $ids;
    }

    public function getAllUserIds(): array
    {
        # TODO: Filter
        $ids = [];
        foreach ($this->data['rows'] ?? [] as $row) {
            $ids[] = (int) $row['usr_id'];
        }
        return $ids;
    }

    public function getSelectableGroups(): array
    {
        $selectable_group_ids = [];
        foreach ($this->data['groups'] ?? [] as $ref_id => $something) {
            if ($this->data['groups_rights'][$ref_id]['manage_members']) {
                $selectable_group_ids[$ref_id] = $this->data['groups'][$ref_id];
            }
        }
        return $selectable_group_ids;
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $rows = $this->applyFilter($filter_data);
        $groups_rights = $this->data['groups_rights'] ?? [];
        $comparator = function (array $f1, array $f2) {
            return 0;
        };
        switch ($column_name) {
            case ilCourseParticipantsGroupsTableGUI::TABLE_COL_NAME:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['name'], $f2['name']);
                };
                break;
            case ilCourseParticipantsGroupsTableGUI::TABLE_COL_LOGIN:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['login'], $f2['login']);
                };
                break;
            case ilCourseParticipantsGroupsTableGUI::TABLE_COL_GROUP_NUMBER:
                $comparator = function (array $f1, array $f2) {
                    if ($f1['groups_number'] === $f2['groups_number']) {
                        return 0;
                    }
                    return $f1['groups_number'] > $f2['groups_number'] ? 1 : -1;
                };
                break;
            case ilCourseParticipantsGroupsTableGUI::TABLE_COL_GROUPS:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['group_info']['title'], $f2['group_info']['title']);
                };
                break;
        }
        uasort($rows, $comparator);
        if ($direction === "DESC") {
            $rows = array_reverse($rows, true);
        }
        $rows = array_slice($rows, $range->getStart(), $range->getLength(), true);
        foreach ($rows as $row) {
            $groups_str = '';
            $enabled_actions = [];
            foreach ($row['groups'] as $group_info) {
                $grp_id = $group_info['group_id'];
                $role = $group_info['role'];
                $title = $group_info['title'];
                if (
                    !($role == 'admin' && $groups_rights[$grp_id]['edit_permission']) &&
                    !($role == 'member' && $groups_rights[$grp_id]['manage_members'])
                ) {
                    continue;
                }
                $groups_str .= ($groups_str === '' ? $title : '<br>' . $title);
                $enabled_actions[] = $this->buildConfirmUnsubscribeActionId($grp_id);
            }
            $data_row = $row_builder->buildDataRow(
                $row['usr_id'] . '',
                [
                    ilCourseParticipantsGroupsTableGUI::TABLE_COL_NAME => $row['name'],
                    ilCourseParticipantsGroupsTableGUI::TABLE_COL_LOGIN => $row['login'],
                    ilCourseParticipantsGroupsTableGUI::TABLE_COL_GROUP_NUMBER => (int) $row['groups_number'],
                    ilCourseParticipantsGroupsTableGUI::TABLE_COL_GROUPS => $groups_str,
                ]
            );
            $disabled_actions = array_diff($this->getAllConfirmUnsubscribeActionIds(), $enabled_actions);
            foreach ($disabled_actions as $disabled_action) {
                $data_row = $data_row->withDisabledAction($disabled_action, true);
            }
            yield $data_row;
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->applyFilter($filter_data));
    }

    public function init(): void
    {
        $parent_node = $this->tree->getNodeData($this->ref_id);
        $groups = $this->tree->getSubTree($parent_node, true, ['grp']);
        if (
            !is_array($groups) ||
            !count($groups)
        ) {
            return;
        }
        $results_participants = [];
        $results_groups = [];
        $results_groups_rights = [];
        foreach ($groups as $idx => $group_data) {
            # check for group in group
            if (
                $group_data['parent'] != $this->ref_id &&
                $this->tree->checkForParentType(
                    $group_data['ref_id'],
                    'grp',
                    true
                )
            ) {
                unset($groups[$idx]);
                continue;
            }
            $results_groups[$group_data['ref_id']] = $group_data['title'];
            $results_groups_rights[$group_data['ref_id']]['manage_members'] = $this->access->checkRbacOrPositionPermissionAccess(
                'manage_members',
                'manage_members',
                $group_data['ref_id']
            );
            $results_groups_rights[$group_data['ref_id']]['edit_permission'] = $this->access->checkAccess(
                'edit_permission',
                '',
                $group_data['ref_id']
            );
            $gobj = ilGroupParticipants::_getInstanceByObjId($group_data['obj_id']);
            $members = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'manage_members',
                'manage_members',
                $group_data['ref_id'],
                $gobj->getMembers()
            );
            $admins = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'manage_members',
                'manage_members',
                $group_data['ref_id'],
                $gobj->getAdmins()
            );
            $results_participants[$group_data['ref_id']]['members'] = $members;
            $results_participants[$group_data['ref_id']]['admins'] = $admins;
        }
        $part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
        $members = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->ref_id,
            $part->getMembers()
        );
        if ($members === []) {
            return;
        }
        $usr_data = [];
        foreach ($members as $usr_id) {
            $name = ilObjUser::_lookupName($usr_id);
            $membership_count = 0;
            $new_entry = [
                'usr_id' => $usr_id,
                'name' => $name['lastname'] . ', ' . $name['firstname'],
                'groups' => [],
                'login' => $name['login']
            ];
            foreach (array_keys($results_participants) as $group_id) {
                $group_info = [
                    'group_id' => $group_id,
                    'title' => $results_groups[$group_id],
                    'role' => ''
                ];
                if (in_array($usr_id, $results_participants[$group_id]['members'])) {
                    $group_info['role'] = 'member';
                }
                if (in_array($usr_id, $results_participants[$group_id]['admins'])) {
                    $group_info['role'] = 'admin';
                }
                if ($group_info['role'] === '') {
                    continue;
                }
                $new_entry['groups'][] = $group_info;
                $membership_count++;
            }
            $new_entry['groups_number'] = $membership_count;
            $usr_data[] = $new_entry;
        }
        $this->data['rows'] = $usr_data;
        $this->data['groups'] = $results_groups;
        $this->data['groups_rights'] = $results_groups_rights;
    }
}
