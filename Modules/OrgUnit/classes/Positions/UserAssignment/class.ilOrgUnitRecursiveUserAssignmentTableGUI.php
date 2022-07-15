<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitRecursiveUserAssignmentTableGUI
 *
 * @author dkloepfer
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitRecursiveUserAssignmentTableGUI extends ilTable2GUI
{
    private static $permission_access_staff_recursive = [];
    private static $permission_view_lp_recursive = [];

    /**
     * @var ilOrgUnitPosition
     */
    protected $ilOrgUnitPosition;
    /**
     * @var \ilCtrl
     */
    protected $ctrl;
    /**
     * @var \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands
     */
    protected $parent_obj;


    /**
     * ilOrgUnitUserAssignmentTableGUI constructor.
     *
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_obj
     * @param string                                       $parent_cmd
     * @param \ilOrgUnitPosition                           $position
     */
    public function __construct(BaseCommands $parent_obj, string $parent_cmd, ilOrgUnitPosition $position)
    {
        global $DIC;

        $this->parent_obj = $parent_obj;
        $this->ilOrgUnitPosition = $position;
        $this->ctrl = $DIC->ctrl();
        $this->setPrefix("il_orgu_" . $position->getId());
        $this->setFormName('il_orgu_' . $position->getId());
        $this->setId("il_orgu_" . $position->getId());
        $this->orgu_ref_id = filter_input(INPUT_GET, "ref_id", FILTER_SANITIZE_NUMBER_INT);
        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setTableHeaders();
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(true);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setTitle($position->getTitle());
        $this->setRowTemplate("tpl.staff_row.html", "Modules/OrgUnit");
        $this->setData($this->loadData());
    }


    protected function setTableHeaders()
    {
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        $this->addColumn($this->lng->txt("obj_orgu"), "orgus");
        $this->addColumn($this->lng->txt("action"));
    }

    /**
     * @return array
     */
    public function loadData()
    {
        global $DIC;
        $access = $DIC['ilAccess'];
        $orgu_tree = ilObjOrgUnitTree::_getInstance();
        $data = [];
        // maybe any parent gives us recursive permission
        (int) $root = (int) ilObjOrgUnit::getRootOrgRefId();
        $parent = (int) $orgu_tree->getParent($this->orgu_ref_id);
        
        while ($parent !== $root) {
            if (ilObjOrgUnitAccess::_checkAccessStaffRec($parent)) {
                self::$permission_access_staff_recursive = array_merge(
                    self::$permission_access_staff_recursive,
                    $orgu_tree->getAllChildren($parent)
                );
            }
            $parent = (int) $orgu_tree->getParent($parent);
        }

        foreach ($orgu_tree->getAllChildren($this->orgu_ref_id) as $ref_id) {
            $recursive = in_array($ref_id, self::$permission_access_staff_recursive);
            if (!$recursive) {
                // ok, so no permission from above, lets check local permissions
                if (ilObjOrgUnitAccess::_checkAccessStaffRec($ref_id)) {
                    // update recursive permissions

                    self::$permission_access_staff_recursive = array_merge(
                        self::$permission_access_staff_recursive,
                        $orgu_tree->getAllChildren($ref_id)
                    );
                } elseif (!ilObjOrgUnitAccess::_checkAccessStaff($ref_id)) {
                    // skip orgus in which one may not view the staff
                    continue;
                }
            }
            $permission_view_lp = $this->mayViewLPIn($ref_id, $access, $orgu_tree);
            foreach ($orgu_tree->getAssignements($ref_id, $this->ilOrgUnitPosition) as $usr_id) {
                $usr_id = (int) $usr_id;
                if (!array_key_exists($usr_id, $data)) {
                    $user = new ilObjUser($usr_id);
                    $set["login"] = $user->getLogin();
                    $set["first_name"] = $user->getFirstname();
                    $set["last_name"] = $user->getLastname();
                    $set["user_id"] = $user_id;
                    $set["orgu_assignments"] = [];
                    $set['view_lp'] = false;
                    $set['user_id'] = $usr_id;
                    $data[$usr_id] = $set;
                }
                $data[$usr_id]['orgu_assignments'][] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
                $data[$usr_id]['view_lp'] = $permission_view_lp || $data[$usr_id]['view_lp'];
            }
        }

        return $data;
    }




    /**
     * @return bool
     */
    private function mayViewLPIn($ref_id, ilAccess $access, ilObjOrgUnitTree $orgu_tree)
    {
        if ($access->checkAccess("view_learning_progress", "", $ref_id)) { // admission by local
            return true;
        }
        $current = (int) $ref_id;
        $root = (int) ilObjOrgUnit::getRootOrgRefId();
        $checked_children = [];
        while ($current !== $root) {
            if (!array_key_exists($current, self::$permission_view_lp_recursive)) {
                self::$permission_view_lp_recursive[$current]
                    = $access->checkAccess("view_learning_progress_rec", "", $current);
            }
            if (self::$permission_view_lp_recursive[$current]) {
                // if an orgu may be viewed recursively, same holds for all of its children. lets cache this.
                foreach ($checked_children as $child) {
                    self::$permission_view_lp_recursive[$child] = true;
                }

                return true;
            }
            $checked_children[] = $current;
            $current = (int) $orgu_tree->getParent($current);
        }

        return false;
    }


    /**
     * @param array $set
     */
    public function fillRow($set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl->setVariable("LOGIN", $set["login"]);
        $this->tpl->setVariable("FIRST_NAME", $set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $set["last_name"]);
        $orgus = $set['orgu_assignments'];
        sort($orgus);
        $this->tpl->setVariable("ORG_UNITS", implode(',', $orgus));
        $this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'usr_id', $set["user_id"]);
        $this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'position_id', $this->ilOrgUnitPosition->getId());
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($lng->txt("Actions"));
        $selection->setId("selection_list_user_lp_" . $set["user_id"]);
        if ($set['view_lp']
            && ilObjUserTracking::_enabledLearningProgress()
            && ilObjUserTracking::_enabledUserRelatedData()
        ) {
            $selection->addItem($lng->txt("show_learning_progress"), "show_learning_progress", $this->ctrl->getLinkTargetByClass(array(
                ilAdministrationGUI::class,
                ilObjOrgUnitGUI::class,
                ilLearningProgressGUI::class,
            ), ""));
        }
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $this->addActions($selection);
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }


    /**
     * @param $selection ilAdvancedSelectionListGUI
     */
    protected function addActions(&$selection)
    {
        $selection->addItem(
            $this->lng->txt("remove"),
            "delete_from_employees",
            $this->ctrl->getLinkTargetByClass(ilOrgUnitUserAssignmentGUI::class, ilOrgUnitUserAssignmentGUI::CMD_CONFIRM)
        );
    }
}
