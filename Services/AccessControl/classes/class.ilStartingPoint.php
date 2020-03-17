<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStartingPoint
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 * @ilCtrl_Calls ilStartingPoint:
 * @ingroup	ServicesAccessControl
 */

class ilStartingPoint
{
    //list view: first and last items in the table are fixed.
    const ORDER_POSITION_MIN = 0;
    const ORDER_POSITION_MAX = 9999;

    //rule options.
    const FALLBACK_RULE = 1;
    const ROLE_BASED = 2;
    const USER_SELECTION_RULE = 3;

    protected $starting_point;
    protected $starting_object;
    protected $starting_position;
    protected $rule_type;
    protected $rule_options; // array serialized in db
    protected $id;

    /**
     * Constructor
     * @param a_id
     * @access public
     */
    public function __construct($a_id = 0)
    {
        if ($a_id > 0) {
            $this->id = $a_id;
            $this->setData($a_id);
        }
    }

    /**
     * Set data for the starting point
     * @param $a_id integer starting point id
     *
     */
    private function setData($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM usr_starting_point WHERE id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->query($query);

        while ($point = $ilDB->fetchAssoc($res)) {
            $this->setStartingPoint($point['starting_point']);
            $this->setRuleOptions($point['rule_options']);
            $this->setPosition($point['position']);
            $this->setStartingObject($point['starting_object']);
            $this->setRuleType($point['rule_type']);
        }
    }

    /**
     * Sets the starting point
     *
     * @access	public
     * @param	int
     */
    public function setStartingPoint($a_starting_point)
    {
        $this->starting_point = $a_starting_point;
    }

    /**
     * Gets the starting point
     *
     * @access	public
     * @return	int
     */
    public function getStartingPoint()
    {
        return $this->starting_point;
    }

    /**
     * Sets the starting object
     *
     * @access	public
     * @param	int
     */
    public function setStartingObject($a_starting_object)
    {
        $this->starting_object = $a_starting_object;
    }

    /**
     * Gets the starting object
     *
     * @access	public
     * @return	int
     */
    public function getStartingObject()
    {
        return $this->starting_object;
    }

    /**
     * Sets the starting position
     *
     * @access	public
     * @param	int
     */
    public function setPosition($a_starting_position)
    {
        $this->starting_position = $a_starting_position;
    }

    /**
     * Gets the starting point position
     *
     * @access	public
     * @return int
     */
    public function getPosition()
    {
        return $this->starting_position;
    }

    /**
     * Sets rule type
     *
     * @access	public
     * @param	int
     */
    public function setRuleType($a_rule_type)
    {
        $this->rule_type = $a_rule_type;
    }

    /**
     * Gets the rule type
     *
     * @access	public
     * @return int
     */
    public function getRuleType()
    {
        return $this->rule_type;
    }

    /**
     * Sets rule type options
     *
     * @access	public
     * @param	int
     */
    public function setRuleOptions($a_rule_options)
    {
        $this->rule_options = $a_rule_options;
    }


    /**
     * Gets the rule options
     *
     * @access	public
     * @return int
     */
    public function getRuleOptions()
    {
        return $this->rule_options;
    }

    /**
     * Get all the starting points in database
     * @return array
     */
    public static function getStartingPoints()
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
                "rule_type" => $point['rule_type'],
                "rule_options" => $point['rule_options']
            );
        }

        return $points;
    }

    /**
     * @param ilObjRole $role
     * @return void
     */
    public static function onRoleDeleted(ilObjRole $role)
    {
        foreach (self::getRolesWithStartingPoint() as $roleId => $data) {
            if ((int) $roleId === (int) $role->getId()) {
                $sp = new self((int) $data['id']);
                $sp->delete();
            } elseif (
                false === ($maybeDeletedRole = ilObjectFactory::getInstanceByObjId((int) $roleId, false)) ||
                !($maybeDeletedRole instanceof ilObjRole)
            ) {
                $sp = new self((int) $data['id']);
                $sp->delete();
            }
        }
    }

    /**
     * get array with all roles which have starting point defined.
     * @return array
     */
    public static function getRolesWithStartingPoint()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM usr_starting_point WHERE rule_options LIKE %s";
        $res = $ilDB->queryF($query, array('text'), array("%role_id%"));

        $roles = array();
        while ($sp = $ilDB->fetchAssoc($res)) {
            $options = unserialize($sp['rule_options']);

            $roles[$options['role_id']] = array(
                "id" => $sp['id'],
                "starting_point" => $sp['starting_point'],
                "starting_object" => $sp['starting_object'],
                "position" => $sp['position'],
                "role_id" => $options['role_id'],

            );
        }
        return $roles;
    }

    /**
     * Get id and title of the roles without starting points
     * @return array
     */
    public static function getGlobalRolesWithoutStartingPoint()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        require_once "./Services/AccessControl/classes/class.ilObjRole.php";

        $global_roles = $rbacreview->getGlobalRoles();

        $roles_with_starting_point = self::getRolesWithStartingPoint();

        $ids_roles_with_sp = array();
        foreach ($roles_with_starting_point as $role) {
            array_push($ids_roles_with_sp, $role['role_id']);
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
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        //get position
        $max_position = $this->getMaxPosition();
        $position = $max_position + 10;

        $next_id = $ilDB->nextId('usr_starting_point');
        $values = array(
                    $next_id,
                    $this->getStartingPoint(),
                    $this->getStartingObject(),
                    $position,
                    $this->getRuleType(),
                    $this->getRuleOptions()
                );

        $ilDB->manipulateF(
            "INSERT INTO usr_starting_point (id, starting_point, starting_object, position, rule_type, rule_options) VALUES (%s, %s, %s, %s, %s, %s)",
            array('integer', 'integer', 'integer', 'integer', 'integer', 'text'),
            $values
        );
    }

    /**
     * update starting point
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            'UPDATE usr_starting_point
			SET starting_point = %s,
				starting_object = %s,
				position = %s,
				rule_type = %s,
				rule_options = %s
			WHERE id = %s',
            array('integer', 'integer', 'integer', 'integer', 'text', 'integer'),
            array($this->getStartingPoint(), $this->getStartingObject(), $this->getPosition(),
                    $this->getRuleType(), $this->getRuleOptions(), $this->id)
        );
    }

    /**
     * delete starting point
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM usr_starting_point WHERE id = " . $ilDB->quote($this->id, "integer");
        $ilDB->manipulate($query);
    }

    //Order methods
    /**
     * @param int $a_ass_id assignment id
     * @return int
     */
    public function getMaxPosition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        //get max order number
        $result = $ilDB->query("SELECT max(position) as max_order FROM usr_starting_point");

        while ($row = $ilDB->fetchAssoc($result)) {
            $order_val = (int) $row['max_order'];
        }
        return $order_val;
    }

    /**
     * @param $a_items
     * @return mixed
     */
    public static function reArrangePositions($a_items)
    {
        $ord_const = 0;
        $rearranged = [];
        foreach ($a_items as $k => $v) {
            $v['starting_position'] = $ord_const;
            $rearranged[$ord_const] = $v;
            $ord_const = $ord_const + 10;
        }
        return $rearranged;
    }

    /**
     * Save all starting point positions. Ordering values with increment +10
     * @param $a_items
     */
    public function saveOrder($a_items)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        asort($a_items);
        $nr = 10;
        foreach ($a_items as $id => $position) {
            if ($position > self::ORDER_POSITION_MIN && $position < self::ORDER_POSITION_MAX) {
                $ilDB->manipulate(
                    "UPDATE usr_starting_point SET" .
                    " position = " . $ilDB->quote($nr, 'integer') .
                    " WHERE id = " . $ilDB->quote($id, 'integer')
                );
                $nr += 10;
            }
        }
    }
}
