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

use ILIAS\MyStaff\ilMyStaffCachedAccessDecorator;
use ILIAS\MyStaff\ilMyStaffAccess;

class ilUserStartingPointRepository
{
    private const START_PD_OVERVIEW = 1;
    private const START_PD_SUBSCRIPTION = 2;
    private const START_PD_NOTES = 4;
    private const START_PD_NEWS = 5;
    private const START_PD_WORKSPACE = 6;
    private const START_PD_PORTFOLIO = 7;
    private const START_PD_SKILLS = 8;
    private const START_PD_LP = 9;
    public const START_PD_CALENDAR = 10;
    private const START_PD_MAIL = 11;
    private const START_PD_CONTACTS = 12;
    private const START_PD_PROFILE = 13;
    private const START_PD_SETTINGS = 14;
    private const START_REPOSITORY = 15;
    public const START_REPOSITORY_OBJ = 16;
    private const START_PD_MYSTAFF = 17;

    private const ORDER_POSITION_MIN = 0;
    private const ORDER_POSITION_MAX = 9999;

    private const USER_STARTING_POINT_ID = -1;
    private const DEFAULT_STARTING_POINT_ID = 0;

    private const URL_LINKS_BY_TYPE = [
        self::START_PD_OVERVIEW => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToSelectedItems',
        self::START_PD_SUBSCRIPTION => 'ilias.php?baseClass=ilMembershipOverviewGUI',
        self::START_PD_WORKSPACE => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace',
        self::START_PD_CALENDAR => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToCalendar',
        self::START_PD_MYSTAFF => 'ilias.php?baseClass=' . ilDashboardGUI::class . '&cmd=' . ilDashboardGUI::CMD_JUMP_TO_MY_STAFF
    ];

    private bool $current_user_has_access_to_my_staff;

    public function __construct(
        private ilObjUser $user,
        private ilDBInterface $db,
        private ilTree $tree,
        private ilRbacReview $rbac_review,
        private ilSetting $settings
    ) {
        global $DIC;
        $this->current_user_has_access_to_my_staff = (new ilMyStaffCachedAccessDecorator(
            $DIC,
            ilMyStaffAccess::getInstance()
        ))->hasCurrentUserAccessToMyStaff();
    }

    public function getStartingPointById(?int $id): ilUserStartingPoint
    {
        if ($id === null) {
            return new ilUserStartingPoint($id);
        }

        $query = 'SELECT * FROM usr_starting_point WHERE id = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($query);
        $starting_point_array = $this->db->fetchAssoc($res);

        if ($starting_point_array === null) {
            $default_starting_point = new ilUserStartingPoint(self::DEFAULT_STARTING_POINT_ID);
            $default_starting_point->setStartingPointType($this->getSystemDefaultStartingPointType());
            $default_starting_point->setStartingObject($this->getSystemDefaultStartingObject());
            $default_starting_point->setCalendarView($this->getSystemDefaultCalendarView());
            $default_starting_point->setCalendarPeriod($this->getSystemDefaultCalendarPeriod());
            return $default_starting_point;
        }

        return new ilUserStartingPoint(
            $id,
            $starting_point_array['starting_point'],
            $starting_point_array['starting_object'],
            $starting_point_array['position'],
            $starting_point_array['rule_type'],
            $starting_point_array['rule_options'],
            $starting_point_array['calendar_view'],
            $starting_point_array['calendar_period']
        );
    }

    /**
     * @return array<ilUserStartingPoint>
     */
    public function getStartingPoints(): array
    {
        $query = 'SELECT * FROM usr_starting_point';
        $res = $this->db->query($query);
        $starting_points = [];
        while ($starting_point_array = $this->db->fetchAssoc($res)) {
            $starting_point = new ilUserStartingPoint(
                $starting_point_array['id'],
                $starting_point_array['starting_point'],
                $starting_point_array['starting_object'],
                $starting_point_array['position'],
                $starting_point_array['rule_type'],
                $starting_point_array['rule_options'],
                $starting_point_array['calendar_view'],
                $starting_point_array['calendar_period']
            );

            $starting_points[] = $starting_point;
        }

        return $starting_points;
    }

    public function getDefaultStartingPointID(): int
    {
        return self::DEFAULT_STARTING_POINT_ID;
    }

    public function getUserStartingPointID(): int
    {
        return self::USER_STARTING_POINT_ID;
    }

    public function onRoleDeleted(ilObjRole $role): void
    {
        foreach ($this->getRolesWithStartingPoint() as $roleId => $data) {
            if ((int) $roleId === $role->getId()) {
                $sp = new self((int) $data['id']);
                $sp->delete();
            } elseif (
                is_null($maybeDeletedRole = ilObjectFactory::getInstanceByObjId((int) $roleId, false)) ||
                !($maybeDeletedRole instanceof ilObjRole)
            ) {
                $sp = new self((int) $data['id']);
                $sp->delete();
            }
        }
    }

    private function getRolesWithStartingPoint(): array
    {
        $query = 'SELECT * FROM usr_starting_point WHERE rule_options LIKE %s ORDER BY position ASC';
        $res = $this->db->queryF($query, ['text'], ['%role_id%']);

        $roles = [];
        while ($sp = $this->db->fetchAssoc($res)) {
            $options = unserialize($sp['rule_options']);

            $roles[$options['role_id']] = [
                'id' => (int) $sp['id'],
                'starting_point' => (int) $sp['starting_point'],
                'starting_object' => (int) $sp['starting_object'],
                'calendar_view' => (int) $sp['calendar_view'],
                'calendar_period' => (int) $sp['calendar_period'],
                'position' => (int) $sp['position'],
                'role_id' => (int) $options['role_id'],

            ];
        }
        return $roles;
    }

    public function getGlobalRolesWithoutStartingPoint(): array
    {
        $global_roles = $this->rbac_review->getGlobalRoles();
        $roles_with_starting_point = $this->getRolesWithStartingPoint();

        $ids_roles_with_sp = [];
        foreach ($roles_with_starting_point as $role) {
            $ids_roles_with_sp[] = $role['role_id'];
        }

        $ids_roles_without_sp = array_diff($global_roles, $ids_roles_with_sp);

        $roles = [];
        foreach ($ids_roles_without_sp as $roleid) {
            if ($roleid === ANONYMOUS_ROLE_ID) {
                continue;
            }
            $role_obj = new ilObjRole($roleid);
            $roles[] = [
                'id' => $role_obj->getId(),
                'title' => $role_obj->getTitle(),
            ];
        }

        return $roles;
    }

    public function save(ilUserStartingPoint $starting_point): void
    {
        if ($starting_point->getId() === $this->getDefaultStartingPointID()) {
            $this->setSystemDefaultStartingPoint($starting_point);
            return;
        }
        //get position
        $max_position = $this->getMaxPosition();
        $position = $max_position + 10;

        $next_id = $this->db->nextId('usr_starting_point');
        $values = [
            $next_id,
            $starting_point->getStartingPointType(),
            $starting_point->getStartingObject(),
            $position,
            $starting_point->getRuleType(),
            $starting_point->getRuleOptions(),
            $starting_point->getCalendarView(),
            $starting_point->getCalendarPeriod()
        ];

        $this->db->manipulateF(
            'INSERT INTO usr_starting_point (id, starting_point, starting_object, position, rule_type, rule_options, calendar_view, calendar_period) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)',
            [
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_TEXT,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER
            ],
            $values
        );
    }

    /**
     * update starting point
     */
    public function update(ilUserStartingPoint $starting_point): void
    {
        if ($starting_point->getId() === $this->getDefaultStartingPointID()) {
            $this->setSystemDefaultStartingPoint($starting_point);
            return;
        }

        $this->db->manipulateF(
            'UPDATE usr_starting_point
			SET starting_point = %s,
				starting_object = %s,
				position = %s,
				rule_type = %s,
				rule_options = %s,
				calendar_view = %s,
				calendar_period = %s
			WHERE id = %s',
            [
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_TEXT,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER
            ],
            [
                $starting_point->getStartingPointType(),
                $starting_point->getStartingObject(),
                $starting_point->getPosition(),
                $starting_point->getRuleType(),
                $starting_point->getRuleOptions(),
                $starting_point->getCalendarView(),
                $starting_point->getCalendarPeriod(),
                $starting_point->getId()
            ]
        );
    }

    public function delete(int $starting_point_id): void
    {
        $query = 'DELETE FROM usr_starting_point WHERE id = ' . $this->db->quote($starting_point_id, 'integer');
        $this->db->manipulate($query);
    }

    public function getMaxPosition(): int
    {
        //get max order number
        $result = $this->db->query('SELECT max(position) as max_order FROM usr_starting_point');

        $order_val = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            $order_val = (int) $row['max_order'];
        }
        return $order_val;
    }

    public function reArrangePositions(array $a_items): array
    {
        $ord_const = 0;
        $rearranged = [];
        foreach ($a_items as $v) {
            $v['starting_position'] = $ord_const;
            $rearranged[$ord_const] = $v;
            $ord_const += 10;
        }
        return $rearranged;
    }

    public function saveOrder(array $a_items): void
    {
        asort($a_items);
        $nr = 10;
        foreach ($a_items as $id => $position) {
            if ($position > self::ORDER_POSITION_MIN && $position < self::ORDER_POSITION_MAX) {
                $this->db->manipulate(
                    'UPDATE usr_starting_point SET' .
                    ' position = ' . $this->db->quote($nr, 'integer') .
                    ' WHERE id = ' . $this->db->quote($id, 'integer')
                );
                $nr += 10;
            }
        }
    }

    public function getPossibleStartingPoints(bool $force_all = false): array //checked
    {
        $all = [];

        $all[self::START_PD_OVERVIEW] = 'mm_dashboard';
        $all[self::START_PD_SUBSCRIPTION] = 'my_courses_groups';

        if ($this->current_user_has_access_to_my_staff) {
            $all[self::START_PD_MYSTAFF] = 'my_staff';
        }

        if ($force_all || !$this->settings->get('disable_personal_workspace')) {
            $all[self::START_PD_WORKSPACE] = 'mm_personal_and_shared_r';
        }
        $calendar_settings = ilCalendarSettings::_getInstance();
        if ($force_all || $calendar_settings->isEnabled()) {
            $all[self::START_PD_CALENDAR] = 'calendar';
        }

        $all[self::START_REPOSITORY] = 'obj_root';
        $all[self::START_REPOSITORY_OBJ] = 'adm_user_starting_point_object';

        return $all;
    }

    public function getSystemDefaultStartingPointType(): int
    {
        $valid = array_keys($this->getPossibleStartingPoints());
        $current = (int) $this->settings->get('usr_starting_point');
        if (!$current || !in_array($current, $valid)) {
            $current = self::START_PD_OVERVIEW;

            if ($this->settings->get('disable_my_offers') === '0' &&
                $this->settings->get('disable_my_memberships') === '0' &&
                $this->settings->get('personal_items_default_view') === '1') {
                $current = self::START_PD_SUBSCRIPTION;
            }

            $this->setSystemDefaultStartingPoint($current);
        }
        if ($this->user->getId() === ANONYMOUS_USER_ID ||
            !$this->user->getId()) {
            $current = self::START_REPOSITORY;
        }
        return $current;
    }

    private function setSystemDefaultStartingPoint(
        ilUserStartingPoint $starting_point
    ): void {
        $starting_point_type = $starting_point->getStartingPointType();

        $valid_starting_points = array_keys($this->getPossibleStartingPoints());
        if (in_array($starting_point_type, $valid_starting_points)) {
            $this->settings->set('usr_starting_point', (string) $starting_point_type);
        }

        if ($starting_point->getStartingPointType() === self::START_REPOSITORY_OBJ) {
            $this->settings->set('usr_starting_point_ref_id', (string) $starting_point->getStartingObject());
        }

        if ($starting_point->getStartingPointType() === self::START_PD_CALENDAR) {
            $this->settings->set('user_calendar_view', (string) $starting_point->getCalendarView());
            $this->settings->set('user_calendar_period', (string) $starting_point->getCalendarPeriod());
        }
    }

    public function getStartingPointAsUrl(): string
    {
        $starting_point = $this->getApplicableStartingPointTypeInfo();

        if ($starting_point['type'] === self::START_REPOSITORY) {
            $starting_point['object'] = $this->tree->getRootId();
        }

        if ($starting_point['type'] === self::START_REPOSITORY_OBJ
            && ($starting_point['object'] === null
                || !ilObject::_exists($starting_point['object'], true)
                || $this->tree->isDeleted($starting_point['object']))
        ) {
            $starting_point['type'] = self::START_PD_OVERVIEW;
        }

        return $this->getLinkUrlByStartingPointTypeInfo($starting_point);
    }

    private function getApplicableStartingPointTypeInfo(): array
    {
        if ($this->isPersonalStartingPointEnabled()
            && $this->getCurrentUserPersonalStartingPoint() !== 0) {
            return [
                'type' => $this->getCurrentUserPersonalStartingPoint(),
                'object' => $this->getCurrentUserPersonalStartingObject()
            ];
        }

        $role = $this->getFirstRoleWithStartingPointForUserId($this->user->getId());
        if ($role !== []) {
            return [
                'type' => $role['starting_point'],
                'object' => $role['starting_object'],
                'cal_view' => $role['calendar_view'],
                'cal_period' => $role['calendar_period']
            ];
        }

        return [
                'type' => $this->getSystemDefaultStartingPointType(),
                'object' => $this->getSystemDefaultStartingObject(),
                'cal_view' => $this->getSystemDefaultCalendarView(),
                'cal_period' => $this->getSystemDefaultCalendarPeriod()
            ];
    }

    private function getFirstRoleWithStartingPointForUserId(int $user_id): array
    {
        $roles = $this->getRolesWithStartingPoint();
        $role_ids = array_keys($roles);
        foreach ($role_ids as $role_id) {
            if ($this->rbac_review->isAssigned($user_id, $role_id)) {
                return $roles[$role_id];
            }
        }
        return [];
    }

    private function getLinkUrlByStartingPointTypeInfo(array $starting_point): string
    {
        $type = $starting_point['type'];
        if ($type === self::START_REPOSITORY
            || $type === self::START_REPOSITORY_OBJ) {
            return ilLink::_getStaticLink($starting_point['object'], '', true);
        }

        $url = self::URL_LINKS_BY_TYPE[$type];
        if ($type == self::START_PD_CALENDAR) {
            $cal_view = $starting_point['cal_view'] ?? '';
            $cal_period = $starting_point['cal_period'] ?? '';
            $calendar_string = '';
            if (!empty($cal_view) && !empty($cal_period)) {
                $calendar_string = '&cal_view=' . $cal_view . '&cal_agenda_per=' . $cal_period;
            }
            $url .= $calendar_string;
        }

        return $url;
    }

    public function getSystemDefaultStartingObject(): int
    {
        return (int) $this->settings->get('usr_starting_point_ref_id');
    }

    /**
     * Get specific view of calendar starting point
     */
    public function getSystemDefaultCalendarView(): int
    {
        return (int) $this->settings->get('user_calendar_view');
    }

    /**
     * Get time frame of calendar view
     */
    public function getSystemDefaultCalendarPeriod(): int
    {
        return (int) $this->settings->get('user_cal_period');
    }

    /**
     * Toggle personal starting point setting
     */
    public function togglePersonalStartingPointActivation(bool $value): void
    {
        $this->settings->set('usr_starting_point_personal', $value ? '1' : '0');
    }

    public function isPersonalStartingPointEnabled(): bool //checked
    {
        return $this->settings->get('usr_starting_point_personal') === '1' ? true : false;
    }

    /**
     * Did user set any personal starting point (yet)?
     */
    public function isCurrentUserPersonalStartingPointEnabled(): bool
    {
        return (bool) $this->user->getPref('usr_starting_point');
    }

    /**
     * Get current personal starting point
     */
    public function getCurrentUserPersonalStartingPoint(): int
    {
        $valid = array_keys($this->getPossibleStartingPoints());
        $current = $this->user->getPref('usr_starting_point');
        if ($current !== null
            && in_array((int) $current, $valid)) {
            return (int) $current;
        }

        return 0;
    }

    /**
     * Set personal starting point setting
     */
    public function setCurrentUserPersonalStartingPoint(
        int $starting_point_type,
        int $ref_id = null
    ): bool {
        if ($starting_point_type === 0) {
            $this->user->setPref('usr_starting_point', null);
            $this->user->setPref('usr_starting_point_ref_id', null);
            return false;
        }

        if ($starting_point_type === self::START_REPOSITORY_OBJ) {
            if (ilObject::_lookupObjId($ref_id) &&
                !$this->tree->isDeleted($ref_id)) {
                $this->user->setPref('usr_starting_point', (string) $starting_point_type);
                $this->user->setPref('usr_starting_point_ref_id', (string) $ref_id);
                return true;
            }
        }
        $valid = array_keys($this->getPossibleStartingPoints());
        if (in_array($starting_point_type, $valid)) {
            $this->user->setPref('usr_starting_point', (string) $starting_point_type);
            return true;
        }
        return false;
    }

    /**
     * Get ref id of personal starting object
     */
    public function getCurrentUserPersonalStartingObject(): ?int
    {
        $personal_starting_object = $this->user->getPref('usr_starting_point_ref_id');
        if ($personal_starting_object !== null) {
            return (int) $personal_starting_object;
        }

        return null;
    }
}
