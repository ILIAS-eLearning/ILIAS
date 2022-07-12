<?php declare(strict_types=1);
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
 
/**
 * Class ilStartingPoint
 * @author Jesús López <lopez@leifos.com>
 */
class ilStartingPoint
{
    //list view: first and last items in the table are fixed.
    protected const ORDER_POSITION_MIN = 0;
    protected const ORDER_POSITION_MAX = 9999;

    //rule options.
    public const FALLBACK_RULE = 1;
    public const ROLE_BASED = 2;
    public const USER_SELECTION_RULE = 3;
    
    protected ?int $starting_point = null;
    protected ?int $starting_object = null;
    protected ?int $starting_position = null;
    protected ?int $rule_type = null;
    protected ?string $rule_options = null; // array serialized in db
    protected int $id;
    protected ?int $calendar_view = null;
    protected ?int $calendar_period = null;

    protected ilDBInterface $db;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;
        $this->setData();
    }

    private function setData() : void
    {
        $query = "SELECT * FROM usr_starting_point WHERE id = " . $this->db->quote($this->id, 'integer');
        $res = $this->db->query($query);

        while ($point = $this->db->fetchAssoc($res)) {
            $this->setStartingPoint((int) $point['starting_point']);
            $this->setRuleOptions((string) $point['rule_options']);
            $this->setPosition((int) $point['position']);
            $this->setStartingObject((int) $point['starting_object']);
            $this->setRuleType((int) $point['rule_type']);
            $this->setCalendarView((int) $point['calendar_view']);
            $this->setCalendarPeriod((int) $point['calendar_period']);
        }
    }

    public function setStartingPoint(int $a_starting_point) : void
    {
        $this->starting_point = $a_starting_point;
    }

    public function getStartingPoint() : int
    {
        return $this->starting_point;
    }

    public function setStartingObject(int $a_starting_object) : void
    {
        $this->starting_object = $a_starting_object;
    }

    public function getStartingObject() : int
    {
        return $this->starting_object;
    }

    public function setPosition(int $a_starting_position) : void
    {
        $this->starting_position = $a_starting_position;
    }

    public function getPosition() : int
    {
        return $this->starting_position;
    }

    public function setRuleType(int $a_rule_type) : void
    {
        $this->rule_type = $a_rule_type;
    }

    public function getRuleType() : int
    {
        return $this->rule_type;
    }

    /**
     * serialized string
     */
    public function setRuleOptions(string $a_rule_options) : void
    {
        $this->rule_options = $a_rule_options;
    }

    /**
     * Gets calendar view
     */
    public function getCalendarView() : int
    {
        return $this->calendar_view;
    }

    /**
     * Sets calendar view
     */
    public function setCalendarView(int $calendar_view) : void
    {
        $this->calendar_view = $calendar_view;
    }

    public function getCalendarPeriod() : int
    {
        return $this->calendar_period;
    }

    public function setCalendarPeriod(int $calendar_period) : void
    {
        $this->calendar_period = $calendar_period;
    }

    public function getRuleOptions() : ?string
    {
        return $this->rule_options;
    }

    /**
     * Get all the starting points in database
     */
    public static function getStartingPoints() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM usr_starting_point";
        $res = $ilDB->query($query);
        $points = array();
        while ($point = $ilDB->fetchAssoc($res)) {
            $points[] = array(
                "id" => $point["id"],
                "position" => $point["position"],
                "starting_point" => $point['starting_point'],
                "starting_object" => $point['starting_object'],
                "calendar_view" => $point['calendar_view'],
                "calendar_period" => $point['calendar_period'],
                "rule_type" => $point['rule_type'],
                "rule_options" => $point['rule_options']
            );
        }

        return $points;
    }

    public static function onRoleDeleted(ilObjRole $role) : void
    {
        foreach (self::getRolesWithStartingPoint() as $roleId => $data) {
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

    /**
     * get array with all roles which have starting point defined.
     */
    public static function getRolesWithStartingPoint() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM usr_starting_point WHERE rule_options LIKE %s";
        $res = $ilDB->queryF($query, ['text'], ["%role_id%"]);

        $roles = [];
        while ($sp = $ilDB->fetchAssoc($res)) {
            $options = unserialize($sp['rule_options']);

            $roles[$options['role_id']] = [
                "id" => (int) $sp['id'],
                "starting_point" => (int) $sp['starting_point'],
                "starting_object" => (int) $sp['starting_object'],
                "calendar_view" => (int) $sp['calendar_view'],
                "calendar_period" => (int) $sp['calendar_period'],
                "position" => (int) $sp['position'],
                "role_id" => (int) $options['role_id'],

            ];
        }
        return $roles;
    }

    /**
     * Get id and title of the roles without starting points
     */
    public static function getGlobalRolesWithoutStartingPoint() : array
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $global_roles = $rbacreview->getGlobalRoles();
        $roles_with_starting_point = self::getRolesWithStartingPoint();

        $ids_roles_with_sp = array();
        foreach ($roles_with_starting_point as $role) {
            $ids_roles_with_sp[] = $role['role_id'];
        }

        $ids_roles_without_sp = array_diff($global_roles, $ids_roles_with_sp);

        $roles = array();
        foreach ($ids_roles_without_sp as $roleid) {
            $role_obj = new ilObjRole($roleid);
            $roles[] = array(
                "id" => $role_obj->getId(),
                "title" => $role_obj->getTitle(),
            );
        }

        return $roles;
    }

    /**
     * insert starting point into database
     */
    public function save() : void
    {
        //get position
        $max_position = $this->getMaxPosition();
        $position = $max_position + 10;

        $next_id = $this->db->nextId('usr_starting_point');
        $values = array(
            $next_id,
            $this->getStartingPoint(),
            $this->getStartingObject(),
            $position,
            $this->getRuleType(),
            $this->getRuleOptions(),
            $this->getCalendarView(),
            $this->getCalendarPeriod()
        );

        $this->db->manipulateF(
            "INSERT INTO usr_starting_point (id, starting_point, starting_object, position, rule_type, rule_options, calendar_view, calendar_period) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
            array(ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_TEXT,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER
            ),
            $values
        );
    }

    /**
     * update starting point
     */
    public function update() : void
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
            array(ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_TEXT,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER,
                  ilDBConstants::T_INTEGER
            ),
            array($this->getStartingPoint(),
                  $this->getStartingObject(),
                  $this->getPosition(),
                  $this->getRuleType(),
                  $this->getRuleOptions(),
                  $this->getCalendarView(),
                  $this->getCalendarPeriod(),
                  $this->id
            )
        );
    }

    /**
     * delete starting point
     */
    public function delete() : void
    {
        $query = "DELETE FROM usr_starting_point WHERE id = " . $this->db->quote($this->id, "integer");
        $this->db->manipulate($query);
    }

    public function getMaxPosition() : int
    {
        //get max order number
        $result = $this->db->query("SELECT max(position) as max_order FROM usr_starting_point");

        $order_val = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            $order_val = (int) $row['max_order'];
        }
        return $order_val;
    }

    public static function reArrangePositions(array $a_items) : array
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

    /**
     * Save all starting point positions. Ordering values with increment +10
     */
    public function saveOrder(array $a_items) : void
    {
        asort($a_items);
        $nr = 10;
        foreach ($a_items as $id => $position) {
            if ($position > self::ORDER_POSITION_MIN && $position < self::ORDER_POSITION_MAX) {
                $this->db->manipulate(
                    "UPDATE usr_starting_point SET" .
                    " position = " . $this->db->quote($nr, 'integer') .
                    " WHERE id = " . $this->db->quote($id, 'integer')
                );
                $nr += 10;
            }
        }
    }
}
