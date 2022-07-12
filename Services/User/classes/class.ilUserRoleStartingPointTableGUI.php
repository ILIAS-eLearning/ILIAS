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

/**
 * TableGUI class for LTI consumer listing
 * @author Jesús López <lopez@leifos.com>
 */
class ilUserRoleStartingPointTableGUI extends ilTable2GUI
{
    public const TABLE_POSITION_USER_CHOOSES = -1;
    public const TABLE_POSITION_DEFAULT = 9999;

    protected ilLogger $log;
    protected ilRbacReview $rbacreview;

    public function __construct(object $a_parent_obj)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->rbacreview = $DIC->rbac()->review();

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
    }

    /**
     * Get data
     */
    public function getItems() : void
    {
        global $DIC;

        $lng = $DIC['lng'];

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
            $sp_text = $valid_points[$starting_point] ?? "";

            if ($starting_point == ilUserUtil::START_REPOSITORY_OBJ && $point['starting_object']) {
                $object_id = ilObject::_lookupObjId($point['starting_object']);
                $type = $dc->lookupType($object_id);
                $title = $dc->lookupTitle($object_id);
                $sp_text = $this->lng->txt("obj_" . $type) . " <i>\"" . $title . "\"</i> [" . $point['starting_object'] . "]";
            }

            if ($point['rule_type'] == ilStartingPoint::ROLE_BASED) {
                $options = unserialize($point['rule_options'], ['allowed_classes' => false]);

                $role_obj = ilObjectFactory::getInstanceByObjId($options['role_id'], false);
                if (!($role_obj instanceof \ilObjRole)) {
                    continue;
                }

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

        $result = ilArrayUtil::sortArray($result, "starting_position", "asc", true);

        $result = ilStartingPoint::reArrangePositions($result);

        $this->setData($result);
    }

    protected function fillRow(array $a_set) : void // Missing array type.
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

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

            $parent_title = "";
            if (ilObject::_lookupType($a_set["role_id"]) == "role") {
                $ref_id = $this->rbacreview->getObjectReferenceOfRole($a_set["role_id"]);
                if ($ref_id != ROLE_FOLDER_ID) {
                    $parent_title = " (" . ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)) . ")";
                }
            }
            $this->tpl->setVariable("TXT_TITLE", $this->lng->txt("has_role") . ": " .
                ilObjRole::_getTranslation($a_set["criteria"]) . $parent_title);
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
