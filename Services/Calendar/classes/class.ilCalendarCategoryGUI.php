<?php declare(strict_types=1);
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as RefineryFactory;

/**
 * Administration, Side-Block presentation of calendar categories
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilCalendarCategoryGUI: ilCalendarAppointmentGUI, ilCalendarSelectionBlockGUI
 * @ingroup      ServicesCalendar
 */
class ilCalendarCategoryGUI
{
    protected const SEARCH_USER = 1;
    protected const SEARCH_ROLE = 2;

    private int $user_id = 0;
    private int $ref_id = 0;
    private int $obj_id = 0;
    private bool $editable = false;
    private bool $visible = false;
    private bool $importable = false;
    private int $category_id = 0;

    protected ?ilPropertyFormGUI $form = null;

    protected ilDate $seed;
    protected ilCalendarActions $actions;

    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected ilToolbarGUI $toolbar;
    protected ilHelpGUI $help;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilTree $tree;
    protected HttpServices $http;
    protected RefineryFactory $refinery;


    /**
     * Constructor
     */
    public function __construct(int $a_user_id, ilDate $seed, int $a_ref_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->lng->loadLanguageModule('dash');
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC->help();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->tabs = $DIC->tabs();
        $this->user_id = $a_user_id;
        $this->seed = $seed;
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        if (
            in_array($this->ctrl->getNextClass(), array("", "ilcalendarcategorygui")) &&
            $this->ctrl->getCmd() == "manage") {
            if ($a_ref_id > 0) {        // no manage screen in repository
                $this->ctrl->returnToParent($this);
            }
            if ($this->initCategoryIdFromQuery() > 0) {
                // reset category id on manage screen (redirect needed to initialize categories correctly)
                $this->ctrl->setParameter($this, "category_id", "");
                $this->ctrl->setParameterByClass("ilcalendarpresentationgui", "category_id", "");
                $this->ctrl->redirect($this, "manage");
            }
        }
        $this->category_id = $this->initCategoryIdFromQuery();
        $this->actions = ilCalendarActions::getInstance();
    }

    protected function initCategoryIdFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('category_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'category_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->saveParameter($this, 'category_id');
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));

        if ($this->http->wrapper()->query()->has('backvm')) {
            $this->ctrl->setParameter($this, 'backvm', 1);
        }
        switch ($next_class) {
            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, 'details');

                $app_id = 0;
                if ($this->http->wrapper()->query()->has('app_id')) {
                    $app_id = $this->http->wrapper()->query()->retrieve(
                        'app_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, $app_id);
                $this->ctrl->forwardCommand($app);
                break;

            default:
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                if (!in_array($cmd, array("details", "askDeleteAppointments", "deleteAppointments"))) {
                    return;
                }
        }
    }

    protected function cancel() : void
    {
        if ($this->http->wrapper()->query()->has('backvm')) {
            $this->ctrl->redirect($this, 'manage');
        }
        $this->ctrl->returnToParent($this);
    }

    protected function add(ilPropertyFormGUI $form = null) : void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt("cal_back_to_list"), $this->ctrl->getLinkTarget($this, 'cancel'));

        $ed_tpl = new ilTemplate('tpl.edit_category.html', true, true, 'Services/Calendar');

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormCategory('create');
        }
        $ed_tpl->setVariable('EDIT_CAT', $form->getHTML());
        $this->tpl->setContent($ed_tpl->get());
    }

    protected function save() : void
    {
        $form = $this->initFormCategory('create');
        if ($form->checkInput()) {
            $category = new ilCalendarCategory(0);
            $category->setTitle($form->getInput('title'));
            $category->setColor('#' . $form->getInput('color'));
            $category->setLocationType($form->getInput('type_rl'));
            $category->setRemoteUrl($form->getInput('remote_url'));
            $category->setRemoteUser($form->getInput('remote_user'));
            $category->setRemotePass($form->getInput('remote_pass'));
            if ($form->getInput('type') == ilCalendarCategory::TYPE_GLOBAL) {
                $category->setType((int) $form->getInput('type'));
                $category->setObjId(0);
            } else {
                $category->setType(ilCalendarCategory::TYPE_USR);
                $category->setObjId($GLOBALS['DIC']->user()->getId());
            }
            $category->add();
        } else {
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->add($form);
            return;
        }

        // try sync
        try {
            if ($category->getLocationType() == ilCalendarCategory::LTYPE_REMOTE) {
                $this->doSynchronisation($category);
            }
        } catch (Exception $e) {
            // Delete calendar if creation failed
            $category->delete();
            ilUtil::sendFailure($e->getMessage());
            $form->setValuesByPost();
            $this->add($form);
            return;
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'manage');
    }

    protected function edit(ilPropertyFormGUI $form = null) : void
    {
        $this->tabs->activateTab("edit");
        $this->readPermissions();

        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        if (!$this->actions->checkSettingsCal($this->category_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
        }

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormCategory('edit');
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function details() : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->readPermissions();
        $this->checkVisible();

        // Non editable category
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));

        $info->addSection($this->lng->txt('cal_cal_details'));

        $this->tpl->setContent($info->getHTML() . $this->showAssignedAppointments());
    }

    protected function synchroniseCalendar() : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $category = new ilCalendarCategory($this->category_id);

        try {
            $this->doSynchronisation($category);
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'manage');
        }
        ilUtil::sendSuccess($this->lng->txt('cal_cal_sync_success'), true);
        $this->ctrl->redirect($this, 'manage');
    }

    protected function doSynchronisation(ilCalendarCategory $category) : void
    {
        $remote = new ilCalendarRemoteReader($category->getRemoteUrl());
        $remote->setUser($category->getRemoteUser());
        $remote->setPass($category->getRemotePass());
        $remote->read();
        $remote->import($category);
    }

    protected function update() : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->edit();
            return;
        }

        $form = $this->initFormCategory('edit');
        if ($form->checkInput()) {
            $category = new ilCalendarCategory($this->category_id);
            if ($category->getType() != ilCalendarCategory::TYPE_OBJ) {
                $category->setTitle($form->getInput('title'));
            }
            $category->setColor('#' . $form->getInput('color'));
            $category->update();
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            if ($this->ref_id > 0) {
                $this->ctrl->returnToParent($this);
            } else {
                $this->ctrl->redirect($this, "manage");
            }
        } else {
            $form->setValuesByPost();
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            $this->edit($form);
        }
    }

    protected function confirmDelete() : void
    {
        $cat_ids = (is_array($_POST['selected_cat_ids']) && count($_POST['selected_cat_ids']) > 0)
            ? $_POST['selected_cat_ids']
            : ($this->initCategoryIdFromQuery() > 0 ? array($this->initCategoryIdFromQuery()) : null);

        if (!is_array($cat_ids)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->manage();
        }

        $confirmation_gui = new ilConfirmationGUI();

        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('cal_del_cal_sure'));
        $confirmation_gui->setConfirm($this->lng->txt('delete'), 'delete');
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'manage');

        foreach ($cat_ids as $cat_id) {
            $category = new ilCalendarCategory((int) $cat_id);
            $confirmation_gui->addItem('category_id[]', $cat_id, $category->getTitle());
        }
        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    protected function delete() : void
    {
        if (!$_POST['category_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->manage();
        }

        foreach ($_POST['category_id'] as $cat_id) {
            $category = new ilCalendarCategory((int) $cat_id);
            $category->delete();
        }

        ilUtil::sendSuccess($this->lng->txt('cal_cal_deleted'), true);
        $this->ctrl->redirect($this, 'manage');
    }

    public function saveSelection() : void
    {
        $selected_cat_ids = $_POST['selected_cat_ids'] ?: array();
        $shown_cat_ids = $_POST['shown_cat_ids'] ?: array();

        $cats = ilCalendarCategories::_getInstance($this->user->getId());
        $cat_ids = $cats->getCategories();

        $cat_visibility = ilCalendarVisibility::_getInstanceByUserId($this->user->getId(), $this->ref_id);
        if ($this->obj_id > 0) {
            $old_selection = $cat_visibility->getVisible();
        } else {
            $old_selection = $cat_visibility->getHidden();
        }

        $new_selection = array();

        // put all entries from the old selection into the new one
        // that are not presented on the screen
        foreach ($old_selection as $cat_id) {
            if (!in_array($cat_id, $shown_cat_ids)) {
                $new_selection[] = $cat_id;
            }
        }

        foreach ($shown_cat_ids as $shown_cat_id) {
            $shown_cat_id = (int) $shown_cat_id;
            if ($this->obj_id > 0) {
                if (in_array($shown_cat_id, $selected_cat_ids)) {
                    $new_selection[] = $shown_cat_id;
                }
            } else {
                if (!in_array($shown_cat_id, $selected_cat_ids)) {
                    $new_selection[] = $shown_cat_id;
                }
            }
        }

        if ($this->obj_id > 0) {
            $cat_visibility->showSelected($new_selection);
        } else {
            $cat_visibility->hideSelected($new_selection);
        }

        $cat_visibility->save();

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->returnToParent($this);
    }

    public function shareSearch() : void
    {
        $this->tabs->activateTab("share");

        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->manage();
            return;
        }

        $_SESSION['cal_query'] = '';

        $this->ctrl->saveParameter($this, 'category_id');

        $table = new ilCalendarSharedListTableGUI($this, 'shareSearch');
        $table->setTitle($this->lng->txt('cal_cal_shared_with'));
        $table->setCalendarId($this->category_id);
        $table->parse();

        $this->getSearchToolbar();
        $this->tpl->setContent($table->getHTML());
    }

    public function sharePerformSearch() : void
    {
        $this->tabs->activateTab("share");
        $this->lng->loadLanguageModule('search');

        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        $this->ctrl->saveParameter($this, 'category_id');

        $query = '';
        $type = '';
        if (!isset($_POST['query'])) {
            $query = $_SESSION['cal_query'];
            $type = $_SESSION['cal_type'];
        } elseif ($_POST['query']) {
            $query = $_SESSION['cal_query'] = $_POST['query'];
            $type = $_SESSION['cal_type'] = $_POST['query_type'];
        }

        if (!$query) {
            ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
            $this->shareSearch();
            return;
        }

        $this->getSearchToolbar();
        $res_sum = new ilSearchResult();

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($query));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->setMinWordLength(3);
        $query_parser->parse();

        switch ($type) {
            case self::SEARCH_USER:
                $search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
                $search->enableActiveCheck(true);

                $search->setFields(array('login'));
                $res = $search->performSearch();
                $res_sum->mergeEntries($res);

                $search->setFields(array('firstname'));
                $res = $search->performSearch();
                $res_sum->mergeEntries($res);

                $search->setFields(array('lastname'));
                $res = $search->performSearch();
                $res_sum->mergeEntries($res);

                $res_sum->filter(ROOT_FOLDER_ID, false);
                break;

            case self::SEARCH_ROLE:

                $search = new ilLikeObjectSearch($query_parser);
                $search->setFilter(array('role'));

                $res = $search->performSearch();
                $res_sum->mergeEntries($res);

                $res_sum->filter(ROOT_FOLDER_ID, false);
                break;
        }

        if (!count($res_sum->getResults())) {
            ilUtil::sendFailure($this->lng->txt('search_no_match'));
            $this->shareSearch();
            return;
        }

        switch ($type) {
            case self::SEARCH_USER:
                $this->showUserList($res_sum->getResultIds());
                break;

            case self::SEARCH_ROLE:
                $this->showRoleList($res_sum->getResultIds());
                break;
        }
    }

    /**
     * Share with write access
     */
    public function shareAssignEditable() : void
    {
        $this->shareAssign(true);
    }

    public function shareAssign($a_editable = false) : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        if (!isset($_POST['user_ids']) || !count($_POST['user_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->sharePerformSearch();
            return;
        }

        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->shareSearch();
            return;
        }

        $shared = new ilCalendarShared($this->category_id);

        foreach ($_POST['user_ids'] as $user_id) {
            if ($this->user->getId() != $user_id) {
                $shared->share($user_id, ilCalendarShared::TYPE_USR, $a_editable);
            }
        }
        ilUtil::sendSuccess($this->lng->txt('cal_shared_selected_usr'));
        $this->shareSearch();
    }

    protected function shareAssignRolesEditable() : void
    {
        $this->shareAssignRoles(true);
    }

    public function shareAssignRoles(bool $a_editable = false) : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        if (!isset($_POST['role_ids']) || !count($_POST['role_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->sharePerformSearch();
            return;
        }

        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->shareSearch();
            return;
        }

        $shared = new ilCalendarShared($this->category_id);

        foreach ($_POST['role_ids'] as $role_id) {
            $shared->share($role_id, ilCalendarShared::TYPE_ROLE, $a_editable);
        }
        ilUtil::sendSuccess($this->lng->txt('cal_shared_selected_usr'));
        $this->shareSearch();
    }

    public function shareDeassign() : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        if (!isset($_POST['obj_ids']) || !count($_POST['obj_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->shareSearch();
            return;
        }

        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->shareSearch();
            return;
        }

        $shared = new ilCalendarShared($this->category_id);

        foreach ($_POST['obj_ids'] as $obj_id) {
            $shared->stopSharing($obj_id);
        }
        ilUtil::sendSuccess($this->lng->txt('cal_unshared_selected_usr'));
        $this->shareSearch();
    }

    protected function showUserList(array $a_ids = array()) : void
    {
        $table = new ilCalendarSharedUserListTableGUI($this, 'sharePerformSearch');
        $table->setTitle($this->lng->txt('cal_share_search_usr_header'));
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setUsers($a_ids);
        $table->parse();
        $this->tpl->setContent($table->getHTML());
    }

    protected function showRoleList(array $a_ids = array()) : void
    {
        $table = new ilCalendarSharedRoleListTableGUI($this, 'sharePerformSearch');
        $table->setTitle($this->lng->txt('cal_share_search_role_header'));
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setRoles($a_ids);
        $table->parse();

        $this->tpl->setContent($table->getHTML());
    }

    public function getSearchToolbar() : void
    {
        $this->lng->loadLanguageModule('search');
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));

        // search term
        $search = new ilTextInputGUI($this->lng->txt('cal_search'), 'query');
        $search->setValue($_POST['query']);
        $search->setSize(16);
        $search->setMaxLength(128);

        $this->toolbar->addInputItem($search, true);

        // search type
        $options = array(
            self::SEARCH_USER => $this->lng->txt('obj_user'),
            self::SEARCH_ROLE => $this->lng->txt('obj_role'),
        );
        $si = new ilSelectInputGUI($this->lng->txt('search_type'), "query_type");
        $si->setValue($_POST['query_type']);
        $si->setOptions($options);
        $si->setInfo($this->lng->txt(""));
        $this->toolbar->addInputItem($si);
        $this->toolbar->addFormButton($this->lng->txt('search'), "sharePerformSearch");
    }

    protected function initFormCategory(string $a_mode) : ilPropertyFormGUI
    {
        $this->help->setScreenIdComponent("cal");
        $this->help->setScreenId("cal");
        if ($a_mode == "edit") {
            $this->help->setSubScreenId("edit");
        } else {
            $this->help->setSubScreenId("create");
        }

        $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($this->category_id);

        $this->form = new ilPropertyFormGUI();
        $category = new ilCalendarCategory();
        switch ($a_mode) {
            case 'edit':
                $category = new ilCalendarCategory($this->category_id);
                $this->form->setTitle($this->lng->txt('cal_edit_category'));
                $this->ctrl->saveParameter($this, array('seed', 'category_id'));
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                if ($this->isEditable()) {
                    $this->form->addCommandButton('update', $this->lng->txt('save'));

                    /*
                    if($cat_info['type'] == ilCalendarCategory::TYPE_USR)
                    {
                        $this->form->addCommandButton('shareSearch',$this->lng->txt('cal_share'));
                    }
                    $this->form->addCommandButton('confirmDelete',$this->lng->txt('delete'));
                     */
                }
                break;
            case 'create':
                $this->editable = true;
                $category = new ilCalendarCategory(0);
                $this->ctrl->saveParameter($this, array('category_id'));
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                $this->form->setTitle($this->lng->txt('cal_add_category'));
                $this->form->addCommandButton('save', $this->lng->txt('save'));
                break;
        }

        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        // Calendar name
        $title = new ilTextInputGUI($this->lng->txt('cal_calendar_name'), 'title');
        if ($a_mode == 'edit') {
            if (!$this->isEditable() || $category->getType() == ilCalendarCategory::TYPE_OBJ) {
                $title->setDisabled(true);
            }
        }
        $title->setRequired(true);
        $title->setMaxLength(64);
        $title->setSize(32);
        $title->setValue($category->getTitle());
        $this->form->addItem($title);

        if ($a_mode == 'create' and $this->rbacsystem->checkAccess('edit_event',
                ilCalendarSettings::_getInstance()->getCalendarSettingsId())) {
            $type = new ilRadioGroupInputGUI($this->lng->txt('cal_cal_type'), 'type');
            $type->setValue((string) $category->getType());
            $type->setRequired(true);

            $opt = new ilRadioOption($this->lng->txt('cal_type_personal'), (string) ilCalendarCategory::TYPE_USR);
            $type->addOption($opt);

            $opt = new ilRadioOption($this->lng->txt('cal_type_system'), (string) ilCalendarCategory::TYPE_GLOBAL);
            $type->addOption($opt);
            $type->setInfo($this->lng->txt('cal_type_info'));
            $this->form->addItem($type);
        }

        // color
        $color = new ilColorPickerInputGUI($this->lng->txt('cal_calendar_color'), 'color');
        $color->setValue($category->getColor());
        if (!$this->isEditable()) {
            $color->setDisabled(true);
        }
        $color->setRequired(true);
        $this->form->addItem($color);

        $location = new ilRadioGroupInputGUI($this->lng->txt('cal_type_rl'), 'type_rl');
        $location->setDisabled($a_mode == 'edit');
        $location_local = new ilRadioOption($this->lng->txt('cal_type_local'),
            (string) ilCalendarCategory::LTYPE_LOCAL);
        $location->addOption($location_local);
        $location_remote = new ilRadioOption($this->lng->txt('cal_type_remote'),
            (string) ilCalendarCategory::LTYPE_REMOTE);
        $location->addOption($location_remote);
        $location->setValue((string) $category->getLocationType());

        $url = new ilTextInputGUI($this->lng->txt('cal_remote_url'), 'remote_url');
        $url->setDisabled($a_mode == 'edit');
        $url->setValue($category->getRemoteUrl());
        $url->setMaxLength(500);
        $url->setSize(60);
        $url->setRequired(true);
        $location_remote->addSubItem($url);

        $user = new ilTextInputGUI($this->lng->txt('username'), 'remote_user');
        $user->setDisabled($a_mode == 'edit');
        $user->setValue($category->getRemoteUser());
        $user->setMaxLength(50);
        $user->setSize(20);
        $user->setRequired(false);
        $location_remote->addSubItem($user);

        $pass = new ilPasswordInputGUI($this->lng->txt('password'), 'remote_pass');
        $pass->setDisabled($a_mode == 'edit');
        $pass->setValue($category->getRemotePass());
        $pass->setMaxLength(50);
        $pass->setSize(20);
        $pass->setRetype(false);
        $pass->setInfo($this->lng->txt('remote_pass_info'));
        $location_remote->addSubItem($pass);

        // permalink
        if ($a_mode == "edit" && $category->getType() == ilCalendarCategory::TYPE_OBJ) {
            $ne = new ilNonEditableValueGUI($this->lng->txt("perma_link"), "", true);
            $ne->setValue($this->addReferenceLinks($category->getObjId()));
            $this->form->addItem($ne);
        }

        // owner
        if ($a_mode == "edit" && $category->getType() == ilCalendarCategory::TYPE_USR) {
            $ne = new ilNonEditableValueGUI($this->lng->txt("cal_owner"), "", true);
            $ne->setValue(ilUserUtil::getNamePresentation($category->getObjId()));
            $this->form->addItem($ne);
        }

        $this->form->addItem($location);
        return $this->form;
    }

    protected function unshare() : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->readPermissions();
        $this->checkVisible();

        $status = new ilCalendarSharedStatus($this->user->getId());

        if (!ilCalendarShared::isSharedWithUser($this->user->getId(), $this->category_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
            return;
        }
        $status->decline($this->category_id);

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'manage');
    }

    protected function showAssignedAppointments() : string
    {
        $table_gui = new ilCalendarAppointmentsTableGUI($this, 'details', $this->category_id);
        $table_gui->setTitle($this->lng->txt('cal_assigned_appointments'));
        $table_gui->setAppointments(
            ilCalendarCategoryAssignments::_getAssignedAppointments(
                ilCalendarCategories::_getInstance()->getSubitemCategories($this->category_id)
            )
        );
        return $table_gui->getHTML();
    }

    protected function askDeleteAppointments() : void
    {
        if (!isset($_POST['appointments']) || !count($_POST['appointments'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->details();
            return;
        }

        $confirmation_gui = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, 'category_id', $this->category_id);
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('cal_del_app_sure'));
        $confirmation_gui->setConfirm($this->lng->txt('delete'), 'deleteAppointments');
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'details');

        foreach ($_POST['appointments'] as $app_id) {
            $app = new ilCalendarEntry($app_id);
            $confirmation_gui->addItem('appointments[]', (string) $app_id, $app->getTitle());
        }

        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    protected function deleteAppointments() : void
    {
        if (!isset($_POST['appointments']) || !count($_POST['appointments'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->details();
            return;
        }
        foreach ($_POST['appointments'] as $app_id) {
            $app = new ilCalendarEntry($app_id);
            $app->delete();
            ilCalendarCategoryAssignments::_deleteByAppointmentId($app_id);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->details();
    }

    public function getHTML() : string
    {
        $block_gui = new ilCalendarSelectionBlockGUI($this->seed, $this->ref_id);
        return $this->ctrl->getHTML($block_gui);
    }

    protected function appendCalendarSelection() : string
    {
        $this->lng->loadLanguageModule('pd');

        $tpl = new ilTemplate('tpl.calendar_selection.html', true, true, 'Services/Calendar');
        switch (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType()) {
            case ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP:
                $tpl->setVariable('HTEXT', $this->lng->txt('dash_memberships'));
                $tpl->touchBlock('head_item');
                $tpl->touchBlock('head_delim');
                $tpl->touchBlock('head_item');

                $this->ctrl->setParameter($this, 'calendar_mode', ilCalendarUserSettings::CAL_SELECTION_ITEMS);
                $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
                $tpl->setVariable('HHREF', $this->ctrl->getLinkTarget($this, 'switchCalendarMode'));
                $tpl->setVariable('HLINK', $this->lng->txt('dash_favourites'));
                $tpl->touchBlock('head_item');
                break;

            case ilCalendarUserSettings::CAL_SELECTION_ITEMS:
                $this->ctrl->setParameter($this, 'calendar_mode', ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP);
                $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
                $tpl->setVariable('HHREF', $this->ctrl->getLinkTarget($this, 'switchCalendarMode'));
                $tpl->setVariable('HLINK', $this->lng->txt('dash_memberships'));
                $tpl->touchBlock('head_item');
                $tpl->touchBlock('head_delim');
                $tpl->touchBlock('head_item');

                $tpl->setVariable('HTEXT', $this->lng->txt('dash_favourites'));
                $tpl->touchBlock('head_item');
                break;

        }
        return $tpl->get();
    }

    protected function switchCalendarMode() : void
    {
        $mode = 0;
        if ($this->http->wrapper()->query()->has('calendar_mode')) {
            $mode = $this->http->wrapper()->query()->retrieve(
                'calendar_mode',
                $this->refinery->kindlyTo()->int()
            );
        }
        ilCalendarUserSettings::_getInstance()->setCalendarSelectionType($mode);
        ilCalendarUserSettings::_getInstance()->save();

        $this->ctrl->returnToParent($this);
    }

    private function readPermissions() : void
    {
        $this->editable = false;
        $this->visible = false;
        $this->importable = false;

        $shared = ilCalendarShared::getSharedCalendarsForUser($this->user->getId());
        $cat = new ilCalendarCategory($this->category_id);

        switch ($cat->getType()) {
            case ilCalendarCategory::TYPE_USR:

                if ($cat->getObjId() == $this->user->getId()) {
                    $this->visible = true;
                    $this->editable = true;
                    $this->importable = true;
                } elseif (isset($shared[$cat->getCategoryID()])) {
                    $this->visible = true;
                }
                break;

            case ilCalendarCategory::TYPE_GLOBAL:
                $this->importable = $this->editable = $this->rbacsystem->checkAccess(
                    'edit_event',
                    ilCalendarSettings::_getInstance()->getCalendarSettingsId()
                );
                $this->visible = true;
                break;

            case ilCalendarCategory::TYPE_OBJ:
                $this->editable = false;

                $refs = ilObject::_getAllReferences($cat->getObjId());
                foreach ($refs as $ref) {
                    if ($this->access->checkAccess('read', '', $ref)) {
                        $this->visible = true;
                    }
                    if ($this->access->checkAccess('edit_event', '', $ref)) {
                        $this->importable = true;
                    }
                    if ($this->access->checkAccess('write', '', $ref)) {
                        $this->editable = true;
                    }
                }
                break;

            case ilCalendarCategory::TYPE_BOOK:
            case ilCalendarCategory::TYPE_CH:
                $this->editable = $this->user->getId() == $cat->getCategoryID();
                $this->visible = true;
                $this->importable = false;
                break;
        }
    }

    protected function checkVisible() : void
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        if (!$this->visible) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->FATAL);
        }
    }

    private function isEditable() : bool
    {
        return $this->editable;
    }

    protected function isImportable() : bool
    {
        return $this->importable;
    }

    protected function addReferenceLinks($a_obj_id) : string
    {
        $tpl = new ilTemplate('tpl.cal_reference_links.html', true, true, 'Services/Calendar');

        foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id => $ref_id) {
            $parent_ref_id = $this->tree->getParentId($ref_id);
            $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
            $parent_type = ilObject::_lookupType($parent_obj_id);
            $parent_title = ilObject::_lookupTitle($parent_obj_id);

            $type = ilObject::_lookupType($a_obj_id);
            $title = ilObject::_lookupTitle($a_obj_id);

            $tpl->setCurrentBlock('reference');
            $tpl->setVariable('PARENT_TITLE', $parent_title);
            $tpl->setVariable('PARENT_HREF', ilLink::_getLink($parent_ref_id));
            $tpl->setVariable('TITLE', $title);
            $tpl->setVariable('HREF', ilLink::_getLink($ref_id));
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    protected function manage($a_reset_offsets = false) : void
    {
        $this->addSubTabs("manage");

        $table_gui = new ilCalendarManageTableGUI($this);

        if ($a_reset_offsets) {
            $table_gui->resetToDefaults();
        }

        $table_gui->parse();

        $toolbar = new ilToolbarGui();
        $this->ctrl->setParameter($this, 'backvm', 1);
        $toolbar->addButton($this->lng->txt("cal_add_calendar"), $this->ctrl->getLinkTarget($this, "add"));

        $this->tpl->setContent($toolbar->getHTML() . $table_gui->getHTML());
    }

    protected function importAppointments(ilPropertyFormGUI $form = null) : void
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        $this->ctrl->setParameter($this, 'category_id', $this->category_id);

        // Check permissions
        $this->readPermissions();
        $this->checkVisible();

        if (!$this->isImportable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "cancel"));

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initImportForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function initImportForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('cal_import_tbl'));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('uploadAppointments', $this->lng->txt('import'));

        $ics = new ilFileInputGUI($this->lng->txt('cal_import_file'), 'file');
        $ics->setALlowDeletion(false);
        $ics->setSuffixes(array('ics'));
        $ics->setInfo($this->lng->txt('cal_import_file_info'));
        $form->addItem($ics);
        return $form;
    }

    protected function uploadAppointments() : void
    {
        $form = $this->initImportForm();
        if ($form->checkInput()) {
            $file = $form->getInput('file');
            $tmp = ilFileUtils::ilTempnam();
            ilFileUtils::moveUploadedFile($file['tmp_name'], $file['name'], $tmp);
            $num = $this->doImportFile($tmp, $this->initCategoryIdFromQuery());
            ilUtil::sendSuccess(sprintf($this->lng->txt('cal_imported_success'), $num), true);
            $this->ctrl->redirect($this, 'cancel');
        }

        ilUtil::sendFailure($this->lng->txt('cal_err_file_upload'), true);
        $this->ctrl->returnToParent($this);
    }

    protected function doImportFile($file, $category_id) : int
    {
        $assigned_before = ilCalendarCategoryAssignments::lookupNumberOfAssignedAppointments(array($category_id));
        $parser = new ilICalParser($file, ilICalParser::INPUT_FILE);
        $parser->setCategoryId($category_id);
        $parser->parse();
        $assigned_after = ilCalendarCategoryAssignments::lookupNumberOfAssignedAppointments(array($category_id));
        return $assigned_after - $assigned_before;
    }

    public function addSubTabs(string $a_active) : void
    {
        $this->tabs->addSubTab(
            "manage",
            $this->lng->txt("calendar"),
            $this->ctrl->getLinkTarget($this, "manage")
        );

        $status = new ilCalendarSharedStatus($this->user_id);
        $calendars = $status->getOpenInvitations();

        $this->tabs->addSubTab(
            "invitations",
            $this->lng->txt("cal_shared_calendars"),
            $this->ctrl->getLinkTarget($this, "invitations")
        );

        $this->tabs->activateSubTab($a_active);
    }

    public function invitations() : void
    {
        $this->addSubTabs("invitations");
        $table = new ilCalendarInboxSharedTableGUI($this, 'inbox');
        $table->setCalendars(ilCalendarShared::getSharedCalendarsForUser());
        $this->tpl->setContent($table->getHTML());
    }

    protected function acceptShared() : void
    {
        if (!$_POST['cal_ids'] or !is_array($_POST['cal_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->ctrl->returnToParent($this);
            return;
        }

        $status = new ilCalendarSharedStatus($this->user->getId());

        foreach ($_POST['cal_ids'] as $calendar_id) {
            if (!ilCalendarShared::isSharedWithUser($this->user->getId(), $calendar_id)) {
                ilUtil::sendFailure($this->lng->txt('permission_denied'));
                $this->ctrl->returnToParent($this);
                return;
            }
            $status->accept($calendar_id);
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'invitations');
    }

    /**
     * accept shared calendar
     * @access protected
     * @return
     */
    protected function declineShared() : void
    {
        if (!$_POST['cal_ids'] or !is_array($_POST['cal_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
            return;
        }

        $status = new ilCalendarSharedStatus($this->user->getId());

        foreach ($_POST['cal_ids'] as $calendar_id) {
            if (!ilCalendarShared::isSharedWithUser($this->user->getId(), $calendar_id)) {
                ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                $this->ctrl->returnToParent($this);
                return;
            }
            $status->decline($calendar_id);
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'invitations');
    }
}
