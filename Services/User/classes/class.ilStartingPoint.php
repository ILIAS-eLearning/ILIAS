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

/**
 * Class ilStartingPoint
 * @author Jesús López <lopez@leifos.com>
 */
class ilStartingPoint
{
    private const ORDER_POSITION_MIN = 0;
    private const ORDER_POSITION_MAX = 9999;

    private const DEFAULT_STARTING_POINT_ID = 0;

    private const ROLE_BASED = 2;

    private ?int $starting_point = null;
    private ?int $starting_object = null;
    private ?int $starting_position = null;
    private ?int $rule_type = null;
    private ?string $rule_options = null; // array serialized in db
    private ?int $calendar_view = null;
    private ?int $calendar_period = null;

    public function __construct(
        private ilDBInterface $db,
        private ilRbacReview $rbac_review,
        private ?int $id
    ) {
        if ($this->id === null) {
            $this->id = self::DEFAULT_STARTING_POINT_ID;
        }
        $this->loadData();
    }

    private function loadData(): void
    {
        $query = 'SELECT * FROM usr_starting_point WHERE id = ' . $this->db->quote($this->id, 'integer');
        $res = $this->db->query($query);

        while ($point = $this->db->fetchAssoc($res)) {
            $this->setStartingPoint((int) $point['starting_point']);
            $this->setRuleOptions((string) $point['rule_options']);
            $this->setPosition((int) $point['position']);
            $this->setStartingObject((int) $point['starting_object']);
            if ($point['rule_type'] === self::ROLE_BASED) {
                $this->setRuleTypeRoleBased();
            }
            $this->setCalendarView((int) $point['calendar_view']);
            $this->setCalendarPeriod((int) $point['calendar_period']);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setStartingPoint(int $a_starting_point): void
    {
        $this->starting_point = $a_starting_point;
    }

    public function getStartingPoint(): int
    {
        return $this->starting_point;
    }

    public function setStartingObject(int $a_starting_object): void
    {
        $this->starting_object = $a_starting_object;
    }

    public function getStartingObject(): int
    {
        return $this->starting_object;
    }

    public function setPosition(int $a_starting_position): void
    {
        $this->starting_position = $a_starting_position;
    }

    public function getPosition(): int
    {
        return $this->starting_position;
    }

   public function isRoleBasedStartingPoint(): bool
   {
       return $this->rule_type === self::ROLE_BASED;
   }

    public function setRuleTypeRoleBased(): void
    {
        $this->rule_type = self::ROLE_BASED;
    }

    /**
     * Gets calendar view
     */
    public function getCalendarView(): int
    {
        return $this->calendar_view;
    }

    /**
     * Sets calendar view
     */
    public function setCalendarView(int $calendar_view): void
    {
        $this->calendar_view = $calendar_view;
    }

    public function getCalendarPeriod(): int
    {
        return $this->calendar_period;
    }

    public function setCalendarPeriod(int $calendar_period): void
    {
        $this->calendar_period = $calendar_period;
    }

    public function getRuleOptions(): ?string
    {
        return $this->rule_options;
    }

    public function setRuleOptions(string $a_rule_options): void
    {
        $this->rule_options = $a_rule_options;
    }

    /**
     * Get all the starting points in database
     *
     * @return array<ilStartingPoint>
     */
    public function getStartingPoints(): array
    {
        $query = 'SELECT * FROM usr_starting_point';
        $res = $this->db->query($query);
        $starting_points = [];
        while ($point = $this->db->fetchAssoc($res)) {
            $starting_point = new static(
                $this->db,
                $this->rbac_review,
                $point['id']
            );

            $starting_point->setPosition((int) $point['position']);
            $starting_point->setStartingPoint((int) $point['starting_point']);
            $starting_point->setStartingObject((int)$point['starting_object']);
            $starting_point->setCalendarView((int) $point['calendar_view']);
            $starting_point->setCalendarPeriod((int) $point['calendar_period']);
            $starting_point->setRuleOptions($point['rule_options']);
            if ($point['rule_type'] === self::ROLE_BASED) {
                $starting_point->setRuleTypeRoleBased();
            }

            $starting_points[] = $starting_point;
        }

        return $starting_points;
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

    public function getRolesWithStartingPoint(): array
    {
        $query = 'SELECT * FROM usr_starting_point WHERE rule_options LIKE %s';
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
            $role_obj = new ilObjRole($roleid);
            $roles[] = [
                'id' => $role_obj->getId(),
                'title' => $role_obj->getTitle(),
            ];
        }

        return $roles;
    }

    public function save(): void
    {
        //get position
        $max_position = $this->getMaxPosition();
        $position = $max_position + 10;

        $next_id = $this->db->nextId('usr_starting_point');
        $values = [
            $next_id,
            $this->starting_point,
            $this->starting_object,
            $position,
            $this->rule_type,
            $this->rule_options,
            $this->calendar_view,
            $this->calendar_period
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
    public function update(): void
    {
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
                $this->starting_point,
                $this->starting_object,
                $this->starting_position,
                $this->rule_type,
                $this->rule_options,
                $this->calendar_view,
                $this->calendar_period,
                $this->id
            ]
        );
    }

    public function delete(): void
    {
        $query = 'DELETE FROM usr_starting_point WHERE id = ' . $this->db->quote($this->id, 'integer');
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
}
