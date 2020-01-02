<?php
/* Copyright (c) 1998-20016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for LTI consumer listing
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserRoleStartingPointTableGUI extends ilTable2GUI
{
    protected $log;
    protected $parent_obj;

    const TABLE_POSITION_USER_CHOOSES = -1;
    const TABLE_POSITION_DEFAULT = 9999;

    public function __construct($a_parent_obj)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];

        $this->log = ilLoggerFactory::getLogger("user");

        $this->parent_obj = $a_parent_obj;

        $this->setId("usrrolesp");

        parent::__construct($a_parent_obj);

        $this->getItems();

        $this->setLimit(9999);
        $this->setTitle($lng->txt("user_role_starting_point"));

        $this->addColumn($lng->txt("user_order"));
        $this->addColumn($lng->txt("criteria"));
        $this->addColumn($lng->txt("starting_page"));
        $this->addColumn($lng->txt("actions"));
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.user_role_starting_point_row.html", "Services/User");
        $this->addCommandButton("saveOrder", $lng->txt("save_order"));

        $this->setExternalSorting(true);

        //require_once "./Services/AccessControl/classes/class.ilObjRole.php";
        //$roles_without_point = ilObjRole::getGlobalRolesWithoutStartingPoint();
    }

    /**
     * Get data
     */
    public function getItems()
    {
        global $DIC;

        $lng = $DIC['lng'];

        include_once "Services/User/classes/class.ilUserUtil.php";
        require_once "Services/Object/classes/class.ilObjectDataCache.php";
        require_once "Services/AccessControl/classes/class.ilObjRole.php";
        require_once "Services/AccessControl/classes/class.ilStartingPoint.php";
        $dc = new ilObjectDataCache();

        $valid_points = ilUserUtil::getPossibleStartingPoints();

        $status = (ilUserUtil::hasPersonalStartingPoint()? $lng->txt("yes") : $lng->txt("no"));

        $result = array();
        $result[] = array(
            "id" => "user",
            "criteria" => $lng->txt("user_chooses_starting_page"),
            "starting_page" => $status,
            "starting_position" => self::TABLE_POSITION_USER_CHOOSES
        );

        $points = ilStartingPoint::getStartingPoints();

        foreach ($points as $point) {
            $starting_point = $point['starting_point'];
            $position = $point['position'];
            $sp_text = $valid_points[$starting_point];

            if ($starting_point == ilUserUtil::START_REPOSITORY_OBJ && $point['starting_object']) {
                $object_id = ilObject::_lookupObjId($point['starting_object']);
                $type = $dc->lookupType($object_id);
                $title = $dc->lookupTitle($object_id);
                $sp_text = $this->lng->txt("obj_" . $type) . " <i>\"" . $title . "\"</i> [" . $point['starting_object'] . "]";
            }

            if ($point['rule_type'] == ilStartingPoint::ROLE_BASED) {
                $options = unserialize($point['rule_options']);

                $role_obj = new ilObjRole($options['role_id']);

                $result[] = array(
                    "id" => $point['id'],
                    "criteria" => $role_obj->getTitle(),
                    "starting_page" => $sp_text,
                    "starting_position" => (int) $position,
                    "role_id" => $role_obj->getId()
                );
            }
        }

        $default_sp = ilUserUtil::getStartingPoint();
        $starting_point = $valid_points[$default_sp];
        if ($default_sp == ilUserUtil::START_REPOSITORY_OBJ) {
            $reference_id = ilUserUtil::getStartingObject();

            $object_id = ilObject::_lookupObjId($reference_id);
            $type = $dc->lookupType($object_id);
            $title = $dc->lookupTitle($object_id);
            $starting_point = $this->lng->txt("obj_" . $type) . " " . "<i>\"" . $title . "\" ($reference_id)</i>";
        }

        $result[] = array(
            "id" => "default",
            "criteria" => $lng->txt("default"),
            "starting_page" => $starting_point,
            "starting_position" => self::TABLE_POSITION_DEFAULT
        );

        $result = ilUtil::sortArray($result, "starting_position", "asc", true);

        $result = ilStartingPoint::reArrangePositions($result);

        $this->setData($result);
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";

        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle($lng->txt("actions"));

        $ilCtrl->setParameter($this->getParentObject(), "spid", $a_set['id']);


        if ($a_set['id'] > 0 && $a_set['id'] != 'default' && $a_set['id'] != 'user') {
            if (ilStartingPoint::ROLE_BASED) {
                $ilCtrl->setParameter($this->getParentObject(), "rolid", $a_set["role_id"]);
            }

            $list->setId($a_set["id"]);

            $edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "initRoleStartingPointForm");
            $list->addItem($lng->txt("edit"), "", $edit_url);
            $delete_url = $ilCtrl->getLinkTarget($this->getParentObject(), "confirmDeleteStartingPoint");
            $list->addItem($lng->txt("delete"), "", $delete_url);
            $this->tpl->setVariable("VAL_ID", "position[" . $a_set['id'] . "]");
            $this->tpl->setVariable("VAL_POS", $a_set["starting_position"]);

            $this->tpl->setVariable("TXT_TITLE", $this->lng->txt("has_role") . ": " . $a_set["criteria"]);
        } else {
            if ($a_set['id'] == "default") {
                $ilCtrl->setParameter($this->getParentObject(), "rolid", "default");
                $edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "initRoleStartingPointForm");
            } else {
                $ilCtrl->setParameter($this->getParentObject(), "rolid", "user");
                $edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "initUserStartingPointForm");
            }

            $list->addItem($lng->txt("edit"), "", $edit_url);

            $this->tpl->setVariable("HIDDEN", "hidden");

            $this->tpl->setVariable("TXT_TITLE", $a_set["criteria"]);
        }

        $this->tpl->setVariable("TXT_PAGE", $a_set["starting_page"]);

        $this->tpl->setVariable("ACTION", $list->getHTML());
    }
}
