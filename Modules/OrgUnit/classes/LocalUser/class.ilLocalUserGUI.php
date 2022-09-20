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
 ********************************************************************
 */
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLocalUserGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilLocalUserGUI
{
    private ilObjectGUI $parentGui;
    private ilTabsGUI $tabsGui;
    private ilPropertyFormGUI $form;
    private ilToolbarGUI $toolbar;
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    /** @var ilObjOrgUnit|ilObjCategory|ilObject */
    private $object;
    private ilLanguage $lng;
    private ilAccessHandler $access;
    private ilRbacSystem $rbacSystem;
    private ilRbacReview $rbacReview;
    private ilRbacAdmin $rbacAdmin;
    private ilObjUser $user;
    private \ILIAS\DI\LoggingServices $logger;

    public function __construct(ilObjectGUI $parentGui)
    {
        global $DIC;

        $this->parentGui = $parentGui;
        $this->object = $parentGui->getObject();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->rbacSystem = $DIC->rbac()->system();
        $this->rbacReview = $DIC->rbac()->review();
        $this->rbacAdmin = $DIC->rbac()->admin();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->tabsGui = $DIC->tabs();
        $this->logger = $DIC->logger();

        $this->lng->loadLanguageModule('user');
        if (!$this->rbacSystem->checkAccess("cat_administrate_users", $this->parentGui->getObject()->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_admin_users"), true);
        }
    }

    public function executeCommand(): bool
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case "assignRoles":
            case "assignSave":
                $this->tabsGui->clearTargets();
                $this->tabsGui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTargetByClass("illocalusergui", 'index')
                );
                $this->$cmd();
                break;
            default:
                $this->$cmd();
                break;
        }

        return true;
    }
    public function getObject(): ilObjOrgUnit
    {
        return $this->object;
    }

    protected function resetFilter(): void
    {
        $table = new ilUserTableGUI($this, "index", ilUserTableGUI::MODE_LOCAL_USER);
        $table->resetOffset();
        $table->resetFilter();
        $this->index();
    }

    protected function applyFilter(): void
    {
        $table = new ilUserTableGUI($this, "index", ilUserTableGUI::MODE_LOCAL_USER);
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->index();
    }

    public function index(bool $show_delete = false): bool
    {
        $this->tpl->addBlockfile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.cat_admin_users.html',
            "Modules/Category"
        );
        if (count($this->rbacReview->getGlobalAssignableRoles())
            or in_array(SYSTEM_ROLE_ID, $this->rbacReview->assignedRoles($this->user->getId()))
        ) {
            $this->toolbar->addButton(
                $this->lng->txt('add_user'),
                $this->ctrl->getLinkTargetByClass('ilobjusergui', 'create')
            );
            $this->toolbar->addButton(
                $this->lng->txt('import_users'),
                $this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'importUserForm')
            );
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_roles_user_can_be_assigned_to'));
        }
        if ($show_delete) {
            $this->tpl->setCurrentBlock("confirm_delete");
            $this->tpl->setVariable("CONFIRM_FORMACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt('cancel'));
            $this->tpl->setVariable("CONFIRM_CMD", 'performDeleteUsers');
            $this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt('delete'));
            $this->tpl->parseCurrentBlock();
        }
        $table = new ilUserTableGUI($this, 'index', ilUserTableGUI::MODE_LOCAL_USER);
        $this->tpl->setVariable('USERS_TABLE', $table->getHTML());

        return true;
    }

    protected function addUserAutoCompleteObject(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login', 'firstname', 'lastname', 'email'));
        $auto->enableFieldSearchableCheck(true);
        $auto->setMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($_REQUEST['term']);
        exit();
    }

    public function performDeleteUsers(): bool
    {
        $this->checkPermission("cat_administrate_users");
        foreach ($_POST['user_ids'] as $user_id) {
            if (!in_array($user_id, ilLocalUser::_getAllUserIds($_GET['ref_id']))) {
                $this->logger->write(__FILE__ . ":" . __LINE__ . " User with id $user_id could not be found.");
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('user_not_found_to_delete'));
            }
            if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                continue;
            }
            $tmp_obj->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('deleted_users'), true);
        $this->ctrl->redirect($this, 'index');

        return true;
    }

    public function deleteUsers(): void
    {
        $this->checkPermission("cat_administrate_users");
        if (!count($_POST['id'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_users_selected'));
            $this->index();
            return;
        }
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('sure_delete_selected_users'));
        $confirm->setConfirm($this->lng->txt('delete'), 'performDeleteUsers');
        $confirm->setCancel($this->lng->txt('cancel'), 'index');
        foreach ($_POST['id'] as $user) {
            $name = ilObjUser::_lookupName($user);
            $confirm->addItem(
                'user_ids[]',
                $user,
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']'
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }


    /**
     * @throws ilCtrlException
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     */
    public function assignRoles(): void
    {
        if (!$this->access->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "");
        }
        $offset = $_GET["offset"];
        // init sort_by (unfortunatly sort_by is preset with 'title'
        if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"])) {
            $order = "login";
        } else {
            $order = $_GET["sort_by"];
        }

        $direction = $_GET["sort_order"];
        if (!isset($_GET['obj_id'])) {
            $this->tpl->setOnScreenMessage('failure', 'no_user_selected');
            $this->index();
            return;
        }
        $roles = $this->getAssignableRoles();
        $this->tpl->addBlockfile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.cat_role_assignment.html',
            "Modules/Category"
        );
        $ass_roles = $this->rbacReview->assignedRoles($_GET['obj_id']);
        $counter = 0;
        foreach ($roles as $role) {
            $role_obj = ilObjectFactory::getInstanceByObjId($role['obj_id']);
            $disabled = false;
            $f_result[$counter][] = ilLegacyFormElementsUtil::formCheckbox(
                in_array($role['obj_id'], $ass_roles) ? 1 : 0,
                'role_ids[]',
                $role['obj_id'],
                $disabled
            );
            $f_result[$counter][] = $role_obj->getTitle();
            $f_result[$counter][] = $role_obj->getDescription() ? $role_obj->getDescription() : '';
            $f_result[$counter][] = $role['role_type'] == 'global'
                ?
                $this->lng->txt('global')
                :
                $this->lng->txt('local');
            unset($role_obj);
            ++$counter;
        }
        $this->showRolesTable($f_result, "assignRolesObject");
    }

    public function assignSave(): bool
    {
        if (!$this->access->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "");
        }
        // check hack
        if (!isset($_GET['obj_id']) or !in_array($_REQUEST['obj_id'], ilLocalUser::_getAllUserIds())) {
            $this->tpl->setOnScreenMessage('failure', 'no_user_selected');
            $this->index();

            return true;
        }
        $roles = $this->getAssignableRoles();
        // check minimum one global role
        if (!$this->checkGlobalRoles($_POST['role_ids'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_global_role_left'));
            $this->assignRolesObject();

            return false;
        }
        $new_role_ids = $_POST['role_ids'] ? $_POST['role_ids'] : array();
        $assigned_roles = $this->rbacReview->assignedRoles((int) $_REQUEST['obj_id']);
        foreach ($roles as $role) {
            if (in_array($role['obj_id'], $new_role_ids) and !in_array($role['obj_id'], $assigned_roles)) {
                $this->rbacAdmin->assignUser($role['obj_id'], (int) $_REQUEST['obj_id']);
            }
            if (in_array($role['obj_id'], $assigned_roles) and !in_array($role['obj_id'], $new_role_ids)) {
                $this->rbacAdmin->deassignUser($role['obj_id'], (int) $_REQUEST['obj_id']);
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('role_assignment_updated'));
        $this->assignRoles();

        return true;
    }

    public function checkGlobalRoles($new_assigned): bool
    {
        if (!$this->access->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "");
        }
        // return true if it's not a local user
        $tmp_obj = ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
        if ($tmp_obj->getTimeLimitOwner() != $this->object->getRefId() and
            !in_array(SYSTEM_ROLE_ID, $this->rbacReview->assignedRoles($this->user->getId()))
        ) {
            return true;
        }
        // new assignment by form
        $new_assigned = $new_assigned ? $new_assigned : array();
        $assigned = $this->rbacReview->assignedRoles((int) $_GET['obj_id']);
        // all assignable globals
        if (!in_array(SYSTEM_ROLE_ID, $this->rbacReview->assignedRoles($this->user->getId()))) {
            $ga = $this->rbacReview->getGlobalAssignableRoles();
        } else {
            $ga = $this->rbacReview->getGlobalRolesArray();
        }
        $global_assignable = array();
        foreach ($ga as $role) {
            $global_assignable[] = $role['obj_id'];
        }
        $new_visible_assigned_roles = array_intersect($new_assigned, $global_assignable);
        $all_assigned_roles = array_intersect($assigned, $this->rbacReview->getGlobalRoles());
        $main_assigned_roles = array_diff($all_assigned_roles, $global_assignable);
        if (!count($new_visible_assigned_roles) and !count($main_assigned_roles)) {
            return false;
        }

        return true;
    }


    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     */
    public function getAssignableRoles(): array
    {
        // check local user
        $tmp_obj = ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
        // Admin => all roles
        if (in_array(SYSTEM_ROLE_ID, $this->rbacReview->assignedRoles($this->user->getId())) === true) {
            $global_roles = $this->rbacReview->getGlobalRolesArray();
        } elseif ($tmp_obj->getTimeLimitOwner() == $this->object->getRefId()) {
            $global_roles = $this->rbacReview->getGlobalAssignableRoles();
        } else {
            $global_roles = array();
        }

        return array_merge($global_roles, $this->rbacReview->getAssignableChildRoles($this->object->getRefId()));
    }


    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     * @throws ilTemplateException
     * @throws ilCtrlException
     */
    public function showRolesTable($a_result_set, $a_from = ""): bool
    {
        if ($this->access->checkAccess("cat_administrate_users", "", $_GET["ref_id"]) === false) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "");
        }
        $tbl = $this->initTableGUI();
        $tpl = $tbl->getTemplateObject();
        // SET FORMAACTION
        $tpl->setCurrentBlock("tbl_form_header");
        $this->ctrl->setParameter($this, 'obj_id', $_GET['obj_id']);
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $tpl->parseCurrentBlock();
        // SET FOOTER BUTTONS
        $tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
        $tpl->setVariable("BTN_NAME", "assignSave");
        $tpl->setVariable("BTN_VALUE", $this->lng->txt("change_assignment"));
        $tpl->setCurrentBlock("tbl_action_row");
        $tpl->setVariable("TPLPATH", $this->tpl->getValue("TPLPATH"));
        $tpl->parseCurrentBlock();
        $tmp_obj = ilObjectFactory::getInstanceByObjId($_GET['obj_id']);
        $title = $this->lng->txt('role_assignment') . ' (' . $tmp_obj->getFullname() . ')';
        $tbl->setTitle($title, "icon_role.svg", $this->lng->txt("role_assignment"));
        $tbl->setHeaderNames(array(
            '',
            $this->lng->txt("title"),
            $this->lng->txt('description'),
            $this->lng->txt("type"),
        ));
        $tbl->setHeaderVars(array(
            "",
            "title",
            "description",
            "type",
        ), (get_class($this->parentGui) == 'ilObjOrgUnitGUI')
            ? array(
                "ref_id" => $this->object->getRefId(),
                "cmd" => "assignRoles",
                "obj_id" => $_GET['obj_id'],
                "cmdNode" => $_GET["cmdNode"],
                "baseClass" => 'ilAdministrationGUI',
                "admin_mode" => "settings",
            )
            : array(
                "ref_id" => $this->object->getRefId(),
                "cmd" => "assignRoles",
                "obj_id" => $_GET['obj_id'],
                "cmdClass" => "ilobjcategorygui",
                "baseClass" => 'ilRepositoryGUI',
                "cmdNode" => $_GET["cmdNode"],
            ));
        $tbl->setColumnWidth(array("4%", "35%", "45%", "16%"));
        $this->set_unlimited = true;
        $this->setTableGUIBasicData($tbl, $a_result_set, $a_from);
        $tbl->render();
        $this->tpl->setVariable('OBJECTS', $tbl->getTemplateObject()->get());

        return true;
    }

    protected function initTableGUI(): ilTableGUI
    {
        return new ilTableGUI([], false);
    }

    protected function setTableGUIBasicData($tbl, &$result_set, string $a_from = ""): void
    {
        switch ($a_from) {
            case "clipboardObject":
                $offset = $_GET["offset"];
                $order = $_GET["sort_by"];
                $direction = $_GET["sort_order"];
                $tbl->disable("footer");
                break;

            default:
                $offset = $_GET["offset"];
                $order = $_GET["sort_by"];
                $direction = $_GET["sort_order"];
                break;
        }

        $tbl->setOrderColumn($order);
        $tbl->setOrderDirection($direction);
        $tbl->setOffset($offset);
        $tbl->setLimit($_GET["limit"]);
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        $tbl->setData($result_set);
    }

    protected function checkPermission(string $permission): void
    {
        if (!$this->access->checkAccess($permission, "", $_GET["ref_id"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "");
        }
    }
}
