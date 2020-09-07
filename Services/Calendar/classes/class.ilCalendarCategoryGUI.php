<?php
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

/**
* Administration, Side-Block presentation of calendar categories
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarCategoryGUI: ilCalendarAppointmentGUI, ilCalendarSelectionBlockGUI
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategoryGUI
{
    const SEARCH_USER = 1;
    const SEARCH_ROLE = 2;
    
    const VIEW_MANAGE = 1;
    
    protected $user_id;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    
    protected $editable = false;
    protected $visible = false;

    /**
     * @var int
     */
    protected $category_id = 0;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * Constructor
     *
     * @access public
     * @param int $a_user_id user id
     * @param int $seed seed
     * @param int $a_ref_id container ref id
     */
    public function __construct($a_user_id, $seed, $a_ref_id = 0)
    {
        global $DIC;

        $this->user_id = $a_user_id;
        $this->seed = $seed;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->ctrl = $DIC->ctrl();
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->lng->loadLanguageModule("dateplaner");

        if (in_array($this->ctrl->getNextClass(), array("", "ilcalendarcategorygui")) && $this->ctrl->getCmd() == "manage") {
            if ($a_ref_id > 0) {		// no manage screen in repository
                $this->ctrl->returnToParent($this);
            }
            if ((int) $_GET['category_id'] > 0) {
                // reset category id on manage screen (redirect needed to initialize categories correctly)
                $this->ctrl->setParameter($this, "category_id", "");
                $this->ctrl->setParameterByClass("ilcalendarpresentationgui", "category_id", "");
                $this->ctrl->redirect($this, "manage");
            }
        }

        $this->category_id = (int) $_GET['category_id'];

        include_once("./Services/Calendar/classes/class.ilCalendarActions.php");
        $this->actions = ilCalendarActions::getInstance();
    }
    
    /**
     * Execute command
     *
     * @access public
     * @param
     *
     */
    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        $tpl = $DIC['tpl'];

        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->saveParameter($this, 'category_id');
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));

        if (array_key_exists('backvm', $_REQUEST)) {
            $this->ctrl->setParameter($this, 'backvm', 1);
        }
        switch ($next_class) {
            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, 'details');
                
                include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, (int) $_GET['app_id']);
                $this->ctrl->forwardCommand($app);
                break;
            
            default:
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                if (!in_array($cmd, array("details", "askDeleteAppointments", "deleteAppointments"))) {
                    return true;
                }
        }
        return false;
    }
    
    /**
     * cancel
     *
     * @access protected
     * @return
     */
    protected function cancel()
    {
        if (array_key_exists('backvm', $_REQUEST)) {
            $this->ctrl->redirect($this, 'manage');
            return true;
        }

        $this->ctrl->returnToParent($this);
        return true;
    }

    
    /**
     * add new calendar
     *
     * @access protected
     * @return
     */
    protected function add(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt("cal_back_to_list"), $this->ctrl->getLinkTarget($this, 'cancel'));
        
        $ed_tpl = new ilTemplate('tpl.edit_category.html', true, true, 'Services/Calendar');
        
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormCategory('create');
        }
        $ed_tpl->setVariable('EDIT_CAT', $form->getHTML());
        $tpl->setContent($ed_tpl->get());
    }
    
    /**
     * save new calendar
     *
     * @access protected
     */
    protected function save()
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
            return false;
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
            return false;
        }
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $GLOBALS['DIC']->ctrl()->redirect($this, 'manage');
    }
    
    /**
     * edit category
     *
     * @access protected
     */
    protected function edit(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $tabs = $DIC->tabs();
        $tabs->activateTab("edit");

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
        $tpl->setContent($form->getHTML());
    }

    /**
     * show calendar details
     *
     * @access protected
     */
    protected function details()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->readPermissions();
        $this->checkVisible();

        // Non editable category
        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($this->ctrl->getFormAction($this));

        $info->addSection($this->lng->txt('cal_cal_details'));

        $tpl->setContent($info->getHTML() . $this->showAssignedAppointments());
    }
    
    protected function synchroniseCalendar()
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
    
    /**
     * Sync calendar
     * @param ilCalendarCategory $cat
     */
    protected function doSynchronisation(ilCalendarCategory $category)
    {
        include_once './Services/Calendar/classes/class.ilCalendarRemoteReader.php';
        $remote = new ilCalendarRemoteReader($category->getRemoteUrl());
        $remote->setUser($category->getRemoteUser());
        $remote->setPass($category->getRemotePass());
        $remote->read();
        $remote->import($category);
    }
    
    /**
     * update
     *
     * @access protected
     * @return
     */
    protected function update()
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->edit();
            return false;
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
    
    /**
     * confirm delete
     *
     * @access protected
     * @return
     */
    protected function confirmDelete()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        $cat_ids = (is_array($_POST['selected_cat_ids']) && count($_POST['selected_cat_ids']) > 0)
            ? $_POST['selected_cat_ids']
            : ($_GET["category_id"] > 0 ? array($_GET["category_id"]) : null);

        if (!is_array($cat_ids)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->manage();
        }

        /*
        $this->readPermissions();
        if(!$this->isEditable())
        {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->manage();
            return false;
        }
         */
        
        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirmation_gui = new ilConfirmationGUI();
        
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('cal_del_cal_sure'));
        $confirmation_gui->setConfirm($this->lng->txt('delete'), 'delete');
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'manage');
        
        include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
        foreach ($cat_ids as $cat_id) {
            $category = new ilCalendarCategory((int) $cat_id);
            $confirmation_gui->addItem('category_id[]', $cat_id, $category->getTitle());
        }
        
        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    /**
     * Delete
     *
     * @access protected
     * @param
     * @return
     */
    protected function delete()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        if (!$_POST['category_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->manage();
        }

        /*
        $this->readPermissions();
        if(!$this->isEditable())
        {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->edit();
            return false;
        }
         */

        include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
        foreach ($_POST['category_id'] as $cat_id) {
            $category = new ilCalendarCategory((int) $cat_id);
            $category->delete();
        }
        
        ilUtil::sendSuccess($this->lng->txt('cal_cal_deleted'), true);
        $ilCtrl->redirect($this, 'manage');
    }
    
    
    
    /**
     * save selection of categories
     *
     * @access public
     * @param
     * @return
     */
    public function saveSelection()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
        include_once('./Services/Calendar/classes/class.ilCalendarVisibility.php');
            
        $selected_cat_ids = $_POST['selected_cat_ids'] ? $_POST['selected_cat_ids'] : array();
        $shown_cat_ids = $_POST['shown_cat_ids'] ? $_POST['shown_cat_ids'] : array();
        
        $cats = ilCalendarCategories::_getInstance($ilUser->getId());
        $cat_ids = $cats->getCategories();
        
        $cat_visibility = ilCalendarVisibility::_getInstanceByUserId($ilUser->getId(), $this->ref_id);



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

    /**
     * share calendar
     *
     * @access public
     * @param
     * @return
     */
    public function shareSearch()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        $tabs = $DIC->tabs();
        $tabs->activateTab("share");

        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->manage();
            return false;
        }


        $_SESSION['cal_query'] = '';
        
        $this->ctrl->saveParameter($this, 'category_id');

        include_once('./Services/Calendar/classes/class.ilCalendarSharedListTableGUI.php');
        $table = new ilCalendarSharedListTableGUI($this, 'shareSearch');
        $table->setTitle($this->lng->txt('cal_cal_shared_with'));
        $table->setCalendarId($this->category_id);
        $table->parse();

        $this->getSearchToolbar();
        $tpl->setContent($table->getHTML());
    }
    
    /**
     * share perform search
     *
     * @access public
     * @return
     */
    public function sharePerformSearch()
    {
        global $DIC;

        $tabs = $DIC->tabs();
        $tabs->activateTab("share");

        $this->lng->loadLanguageModule('search');
        
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        $this->ctrl->saveParameter($this, 'category_id');
        
        
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
            return false;
        }

        $this->getSearchToolbar();

        include_once 'Services/Search/classes/class.ilQueryParser.php';
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
        include_once 'Services/Search/classes/class.ilSearchResult.php';
        
        $res_sum = new ilSearchResult();
        
        $query_parser = new ilQueryParser(ilUtil::stripSlashes($query));
        $query_parser->setCombination(QP_COMBINATION_OR);
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
                
                $res_sum->filter(ROOT_FOLDER_ID, QP_COMBINATION_OR);
                break;
                
            case self::SEARCH_ROLE:
                 
                include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
                $search = new ilLikeObjectSearch($query_parser);
                $search->setFilter(array('role'));
                
                $res = $search->performSearch();
                $res_sum->mergeEntries($res);
                
                $res_sum->filter(ROOT_FOLDER_ID, QP_COMBINATION_OR);
                break;
        }
        
        if (!count($res_sum->getResults())) {
            ilUtil::sendFailure($this->lng->txt('search_no_match'));
            $this->shareSearch();
            return true;
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
    public function shareAssignEditable()
    {
        return $this->shareAssign(true);
    }
    
    /**
     * share assign
     *
     * @access public
     * @return
     */
    public function shareAssign($a_editable = false)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        if (!count($_POST['user_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->sharePerformSearch();
            return false;
        }
        
        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->shareSearch();
            return false;
        }
        
        
        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        $shared = new ilCalendarShared($this->category_id);
        
        foreach ($_POST['user_ids'] as $user_id) {
            if ($ilUser->getId() != $user_id) {
                $shared->share($user_id, ilCalendarShared::TYPE_USR, $a_editable);
            }
        }
        ilUtil::sendSuccess($this->lng->txt('cal_shared_selected_usr'));
        $this->shareSearch();
    }
    
    /**
     * Share editable
     */
    protected function shareAssignRolesEditable()
    {
        return $this->shareAssignRoles(true);
    }
    
    /**
     * share assign roles
     *
     * @access public
     * @param
     * @return
     */
    public function shareAssignRoles($a_editable = false)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        if (!count($_POST['role_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->sharePerformSearch();
            return false;
        }
        
        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->shareSearch();
            return false;
        }
        
        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        $shared = new ilCalendarShared($this->category_id);
        
        foreach ($_POST['role_ids'] as $role_id) {
            $shared->share($role_id, ilCalendarShared::TYPE_ROLE, $a_editable);
        }
        ilUtil::sendSuccess($this->lng->txt('cal_shared_selected_usr'));
        $this->shareSearch();
    }
    
    /**
     * desassign users/roles from calendar
     *
     * @access public
     * @param
     * @return
     */
    public function shareDeassign()
    {
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }
        if (!count($_POST['obj_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->shareSearch();
            return false;
        }
        
        $this->readPermissions();
        if (!$this->isEditable()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->shareSearch();
            return false;
        }
        

        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        $shared = new ilCalendarShared($this->category_id);
        
        foreach ($_POST['obj_ids'] as $obj_id) {
            $shared->stopSharing($obj_id);
        }
        ilUtil::sendSuccess($this->lng->txt('cal_unshared_selected_usr'));
        $this->shareSearch();
        return true;
    }
    
    
    /**
     * show user list
     *
     * @access protected
     * @param array array of user ids
     * @return
     */
    protected function showUserList($a_ids = array())
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        include_once('./Services/Calendar/classes/class.ilCalendarSharedUserListTableGUI.php');
        
        $table = new ilCalendarSharedUserListTableGUI($this, 'sharePerformSearch');
        $table->setTitle($this->lng->txt('cal_share_search_usr_header'));
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setUsers($a_ids);
        $table->parse();
        
        // $table->addCommandButton('shareSearch',$this->lng->txt('search_new'));
        // $table->addCommandButton('manage',$this->lng->txt('cancel'));
        
        $tpl->setContent($table->getHTML());
    }
    
    /**
     * show role list
     *
     * @access protected
     * @param array array of role ids
     * @return
     */
    protected function showRoleList($a_ids = array())
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        include_once('./Services/Calendar/classes/class.ilCalendarSharedRoleListTableGUI.php');
        
        $table = new ilCalendarSharedRoleListTableGUI($this, 'sharePerformSearch');
        $table->setTitle($this->lng->txt('cal_share_search_role_header'));
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setRoles($a_ids);
        $table->parse();
        
        // $table->addCommandButton('shareSearch',$this->lng->txt('search_new'));
        // $table->addCommandButton('manage',$this->lng->txt('cancel'));
        
        $tpl->setContent($table->getHTML());
    }

    /**
     * Get search toolbar
     *
     * @param
     */
    public function getSearchToolbar()
    {
        global $DIC;

        $tb = $DIC->toolbar();
        $lng = $DIC->language();

        $lng->loadLanguageModule('search');

        $tb->setFormAction($this->ctrl->getFormAction($this));

        // search term
        $search = new ilTextInputGUI($this->lng->txt('cal_search'), 'query');
        $search->setValue($_POST['query']);
        $search->setSize(16);
        $search->setMaxLength(128);

        $tb->addInputItem($search, true);

        // search type
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $options = array(
            self::SEARCH_USER => $this->lng->txt('obj_user'),
            self::SEARCH_ROLE => $this->lng->txt('obj_role'),
            );
        $si = new ilSelectInputGUI($this->lng->txt('search_type'), "query_type");
        $si->setValue($_POST['query_type']);
        $si->setOptions($options);
        $si->setInfo($this->lng->txt(""));
        $tb->addInputItem($si);


        $tb->addFormButton($this->lng->txt('search'), "sharePerformSearch");
    }


    /**
     * init edit/create category form
     *
     * @access protected
     * @return
     */
    protected function initFormCategory($a_mode)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenIdComponent("cal");
        $ilHelp->setScreenId("cal");
        if ($a_mode == "edit") {
            $ilHelp->setSubScreenId("edit");
        } else {
            $ilHelp->setSubScreenId("create");
        }
        
        include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
        $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($this->category_id);
        
        $this->form = new ilPropertyFormGUI();
        #$this->form->setTableWidth('40%');
        switch ($a_mode) {
            case 'edit':
                $category = new ilCalendarCategory($this->category_id);
                $this->form->setTitle($this->lng->txt('cal_edit_category'));
                $this->ctrl->saveParameter($this, array('seed','category_id'));
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
        
        
        include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
        if ($a_mode == 'create' and $rbacsystem->checkAccess('edit_event', ilCalendarSettings::_getInstance()->getCalendarSettingsId())) {
            $type = new ilRadioGroupInputGUI($this->lng->txt('cal_cal_type'), 'type');
            $type->setValue($category->getType());
            $type->setRequired(true);
            
            $opt = new ilRadioOption($this->lng->txt('cal_type_personal'), ilCalendarCategory::TYPE_USR);
            $type->addOption($opt);
                
            $opt = new ilRadioOption($this->lng->txt('cal_type_system'), ilCalendarCategory::TYPE_GLOBAL);
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
        $location_local = new ilRadioOption($this->lng->txt('cal_type_local'), ilCalendarCategory::LTYPE_LOCAL);
        $location->addOption($location_local);
        $location_remote = new ilRadioOption($this->lng->txt('cal_type_remote'), ilCalendarCategory::LTYPE_REMOTE);
        $location->addOption($location_remote);
        $location->setValue($category->getLocationType());
        
        
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

    /**
     * Stop calendar sharing
     */
    protected function unshare()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if (!$this->category_id) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->readPermissions();
        $this->checkVisible();

        include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
        $status = new ilCalendarSharedStatus($ilUser->getId());

        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        if (!ilCalendarShared::isSharedWithUser($ilUser->getId(), $this->category_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'));
            $this->inbox();
            return false;
        }
        $status->decline($this->category_id);

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'manage');
    }

    /**
     * show assigned aapointments
     *
     * @access protected
     * @return
     */
    protected function showAssignedAppointments()
    {
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryTableGUI.php');
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        include_once('./Services/Calendar/classes/class.ilCalendarAppointmentsTableGUI.php');
        
        $table_gui = new ilCalendarAppointmentsTableGUI($this, 'details', $this->category_id);
        $table_gui->setTitle($this->lng->txt('cal_assigned_appointments'));
        $table_gui->setAppointments(
            ilCalendarCategoryAssignments::_getAssignedAppointments(
                ilCalendarCategories::_getInstance()->getSubitemCategories($this->category_id)
            )
        );
        return $table_gui->getHTML();
    }
    
    /**
     * ask delete appointments
     *
     * @access protected
     * @return
     */
    protected function askDeleteAppointments()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        if (!count($_POST['appointments'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->details();
            return true;
        }

        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirmation_gui = new ilConfirmationGUI();
        
        $this->ctrl->setParameter($this, 'category_id', $this->category_id);
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('cal_del_app_sure'));
        $confirmation_gui->setConfirm($this->lng->txt('delete'), 'deleteAppointments');
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'details');
        
        include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
        foreach ($_POST['appointments'] as $app_id) {
            $app = new ilCalendarEntry($app_id);
            $confirmation_gui->addItem('appointments[]', (int) $app_id, $app->getTitle());
        }
        
        $tpl->setContent($confirmation_gui->getHTML());
    }
    
    /**
     * delete appointments
     *
     * @access protected
     * @return
     */
    protected function deleteAppointments()
    {
        if (!count($_POST['appointments'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->details();
            return true;
        }
        include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
        foreach ($_POST['appointments'] as $app_id) {
            $app = new ilCalendarEntry($app_id);
            $app->delete();

            include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
            ilCalendarCategoryAssignments::_deleteByAppointmentId($app_id);
        }
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->details();
        return true;
    }

    public function getHTML()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];

        include_once("./Services/Calendar/classes/class.ilCalendarSelectionBlockGUI.php");
        $block_gui = new ilCalendarSelectionBlockGUI($this->seed, $this->ref_id);
        $html = $ilCtrl->getHTML($block_gui);
        return $html;
    }
    
    
    /**
     *
     * @param
     * @return
     */
    protected function appendCalendarSelection()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $this->lng->loadLanguageModule('pd');
        
        $tpl = new ilTemplate('tpl.calendar_selection.html', true, true, 'Services/Calendar');
        include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
        switch (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType()) {
            case ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP:
                $tpl->setVariable('HTEXT', $this->lng->txt('pd_my_memberships'));
                $tpl->touchBlock('head_item');
                $tpl->touchBlock('head_delim');
                $tpl->touchBlock('head_item');
                
                $this->ctrl->setParameter($this, 'calendar_mode', ilCalendarUserSettings::CAL_SELECTION_ITEMS);
                $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
                $tpl->setVariable('HHREF', $this->ctrl->getLinkTarget($this, 'switchCalendarMode'));
                $tpl->setVariable('HLINK', $this->lng->txt('pd_my_offers'));
                $tpl->touchBlock('head_item');
                break;
                
            case ilCalendarUserSettings::CAL_SELECTION_ITEMS:
                $this->ctrl->setParameter($this, 'calendar_mode', ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP);
                $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));
                $tpl->setVariable('HHREF', $this->ctrl->getLinkTarget($this, 'switchCalendarMode'));
                $tpl->setVariable('HLINK', $this->lng->txt('pd_my_memberships'));
                $tpl->touchBlock('head_item');
                $tpl->touchBlock('head_delim');
                $tpl->touchBlock('head_item');
                
                $tpl->setVariable('HTEXT', $this->lng->txt('pd_my_offers'));
                $tpl->touchBlock('head_item');
                break;
            
                                
        }
        return $tpl->get();
    }
     
    /**
    * Switch calendar selection nmode
    * @return
    */
    protected function switchCalendarMode()
    {
        include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
        ilCalendarUserSettings::_getInstance()->setCalendarSelectionType((int) $_GET['calendar_mode']);
        ilCalendarUserSettings::_getInstance()->save();
        
        $this->ctrl->returnToParent($this);
    }
    
    
    /**
     * read permissions
     *
     * @access private
     * @param
     * @return
     */
    private function readPermissions()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        $this->editable = false;

        $this->visible = false;
        $this->importable = false;
        
        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        
        $shared = ilCalendarShared::getSharedCalendarsForUser($ilUser->getId());
        $cat = new ilCalendarCategory($this->category_id);
        
        switch ($cat->getType()) {
            case ilCalendarCategory::TYPE_USR:
                
                if ($cat->getObjId() == $ilUser->getId()) {
                    $this->visible = true;
                    $this->editable = true;
                    $this->importable = true;
                } elseif (isset($shared[$cat->getCategoryID()])) {
                    $this->visible = true;
                }
                break;
            
            case ilCalendarCategory::TYPE_GLOBAL:
                $this->importable = $this->editable = $rbacsystem->checkAccess('edit_event', ilCalendarSettings::_getInstance()->getCalendarSettingsId());
                $this->visible = true;
                break;
                
            case ilCalendarCategory::TYPE_OBJ:
                $this->editable = false;
                
                $refs = ilObject::_getAllReferences($cat->getObjId());
                foreach ($refs as $ref) {
                    if ($ilAccess->checkAccess('read', '', $ref)) {
                        $this->visible = true;
                    }
                    if ($ilAccess->checkAccess('edit_event', '', $ref)) {
                        $this->importable = true;
                    }
                    if ($ilAccess->checkAccess('write', '', $ref)) {
                        $this->editable = true;
                    }
                }
                break;

            case ilCalendarCategory::TYPE_BOOK:
            case ilCalendarCategory::TYPE_CH:
                $this->editable = $ilUser->getId() == $cat->getCategoryID();
                $this->visible = true;
                $this->importable = false;
                break;
        }
    }
    
    /**
     *
     * @param
     * @return
     */
    protected function checkVisible()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        
        if (!$this->visible) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->FATAL);
        }
    }
    
    /**
     * check if calendar is editable
     * @access private
     * @return
     */
    private function isEditable()
    {
        return $this->editable;
    }
    
    protected function isImportable()
    {
        return $this->importable;
    }


    /**
     * Show links to references
     * @param int $a_obj_id $obj_id
     * @return
     */
    protected function addReferenceLinks($a_obj_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        $tpl = new ilTemplate('tpl.cal_reference_links.html', true, true, 'Services/Calendar');
        
        foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id => $ref_id) {
            include_once('./Services/Link/classes/class.ilLink.php');
            
            $parent_ref_id = $tree->getParentId($ref_id);
            $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
            $parent_type = ilObject::_lookupType($parent_obj_id);
            $parent_title = ilObject::_lookupTitle($parent_obj_id);
            
            $type = ilObject::_lookupType($a_obj_id);
            $title = ilObject::_lookupTitle($a_obj_id);
            
            $tpl->setCurrentBlock('reference');
            //$tpl->setVariable('PIMG_SRC',ilUtil::getTypeIconPath($parent_type,$parent_obj_id,'tiny'));
            //$tpl->setVariable('PIMG_ALT',$this->lng->txt('obj_'.$parent_type));
            $tpl->setVariable('PARENT_TITLE', $parent_title);
            $tpl->setVariable('PARENT_HREF', ilLink::_getLink($parent_ref_id));
             
            //$tpl->setVariable('SRC',ilUtil::getTypeIconPath($type,$a_obj_id,'tiny'));
            //$tpl->setVariable('ALT',$this->lng->txt('obj_'.$type));
            $tpl->setVariable('TITLE', $title);
            $tpl->setVariable('HREF', ilLink::_getLink($ref_id));
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
     * Manage calendars
     * @global type $lng
     * @global type $ilCtrl
     * @global type $tpl
     */
    protected function manage($a_reset_offsets = false)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];

        $this->addSubTabs("manage");

        include_once('./Services/Calendar/classes/class.ilCalendarManageTableGUI.php');
        $table_gui = new ilCalendarManageTableGUI($this);
        
        if ($a_reset_offsets) {
            $table_gui->resetToDefaults();
        }
        
        $table_gui->parse();

        include_once "./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php";
        $toolbar = new ilToolbarGui();
        $ilCtrl->setParameter($this, 'backvm', 1);
        $toolbar->addButton($lng->txt("cal_add_calendar"), $ilCtrl->getLinkTarget($this, "add"));

        $tpl->setContent($toolbar->getHTML() . $table_gui->getHTML());
    }
    
    /**
     * import appointments
     */
    protected function importAppointments(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];
        
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

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "cancel"));


        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initImportForm();
        }
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Create import form
     */
    protected function initImportForm()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
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
    
    /**
     * Upload appointments
     */
    protected function uploadAppointments()
    {
        $form = $this->initImportForm();
        if ($form->checkInput()) {
            $file = $form->getInput('file');
            $tmp = ilUtil::ilTempnam();
            
            ilUtil::moveUploadedFile($file['tmp_name'], $file['name'], $tmp);
            
            $num = $this->doImportFile($tmp, (int) $_REQUEST['category_id']);
            
            ilUtil::sendSuccess(sprintf($this->lng->txt('cal_imported_success'), (int) $num), true);
            $this->ctrl->redirect($this, 'cancel');
        }
        
        ilUtil::sendFailure($this->lng->txt('cal_err_file_upload'), true);
        $this->initImportForm($form);
    }
    
    /**
     * Import ics
     * @param type $file
     * @param type $category_id
     */
    protected function doImportFile($file, $category_id)
    {
        include_once './Services/Calendar/classes/../classes/iCal/class.ilICalParser.php';
        include_once './Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
        
        $assigned_before = ilCalendarCategoryAssignments::lookupNumberOfAssignedAppointments(array($category_id));
        
        $parser = new ilICalParser($file, ilICalParser::INPUT_FILE);
        $parser->setCategoryId($category_id);
        $parser->parse();
        
        $assigned_after = ilCalendarCategoryAssignments::lookupNumberOfAssignedAppointments(array($category_id));
        
        return $assigned_after - $assigned_before;
    }

    /**
     * Add subtabs
     *
     * @param
     * @return
     */
    public function addSubTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->addSubTab(
            "manage",
            $lng->txt("calendar"),
            $ilCtrl->getLinkTarget($this, "manage")
        );

        $status = new ilCalendarSharedStatus($this->user_id);
        $calendars = $status->getOpenInvitations();

        //if (count($calendars) > 0)
        //{
        $ilTabs->addSubTab(
            "invitations",
            $lng->txt("cal_shared_calendars"),
            $ilCtrl->getLinkTarget($this, "invitations")
        );
        //}

        $ilTabs->activateSubTab($a_active);
    }

    /**
     * Invitations
     *
     * @param
     * @return
     */
    public function invitations()
    {
        $this->addSubTabs("invitations");

        // shared calendar invitations: @todo needs to be moved
        include_once('./Services/Calendar/classes/class.ilCalendarInboxSharedTableGUI.php');
        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');

        $table = new ilCalendarInboxSharedTableGUI($this, 'inbox');
        $table->setCalendars(ilCalendarShared::getSharedCalendarsForUser());

        //if($table->parse())
        //{
        $this->tpl->setContent($table->getHTML());
        //}
    }

    /**
     * accept shared calendar
     *
     * @access protected
     * @return
     */
    protected function acceptShared()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!$_POST['cal_ids'] or !is_array($_POST['cal_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->inbox();
            return false;
        }

        include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
        $status = new ilCalendarSharedStatus($ilUser->getId());

        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        foreach ($_POST['cal_ids'] as $calendar_id) {
            if (!ilCalendarShared::isSharedWithUser($ilUser->getId(), $calendar_id)) {
                ilUtil::sendFailure($this->lng->txt('permission_denied'));
                $this->inbox();
                return false;
            }
            $status->accept($calendar_id);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);

        $this->ctrl->redirect($this, 'invitations');
    }

    /**
     * accept shared calendar
     *
     * @access protected
     * @return
     */
    protected function declineShared()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!$_POST['cal_ids'] or !is_array($_POST['cal_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->inbox();
            return false;
        }

        include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
        $status = new ilCalendarSharedStatus($ilUser->getId());

        include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
        foreach ($_POST['cal_ids'] as $calendar_id) {
            if (!ilCalendarShared::isSharedWithUser($ilUser->getId(), $calendar_id)) {
                ilUtil::sendFailure($this->lng->txt('permission_denied'));
                $this->inbox();
                return false;
            }
            $status->decline($calendar_id);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);

        $this->ctrl->redirect($this, 'invitations');
    }
}
