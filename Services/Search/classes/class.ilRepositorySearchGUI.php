<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilRepositorySearchGUI
*
* GUI class for user, group, role search
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
* @ilCtrl_Calls ilRepositorySearchGUI: ilFormPropertyDispatchGUI
*
*/
include_once 'Services/Search/classes/class.ilSearchResult.php';
include_once 'Services/Search/classes/class.ilSearchSettings.php';
include_once './Services/User/classes/class.ilUserAccountSettings.php';
include_once 'Services/Search/classes/class.ilQueryParser.php';
include_once("./Services/User/classes/class.ilUserAutoComplete.php");


class ilRepositorySearchGUI
{
    private $search_results = array();
    
    protected $add_options = array();
    protected $object_selection = false;

    protected $searchable_check = true;
    protected $search_title = '';
    
    public $search_type = 'usr';
    protected $user_limitations = true;
    
    /**
     * @var callable
     */
    protected $user_filter = null;
    
    /**
     * @var int
     */
    private $privacy_mode = ilUserAutoComplete::PRIVACY_MODE_IGNORE_USER_SETTING;


    /**
     * @var ilTree
     */
    protected $tree;
    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;
    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $tree = $DIC['tree'];
        $ui_renderer = $DIC["ui.renderer"];
        $ui_factory = $DIC["ui.factory"];

        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->tree = $tree;
        $this->ui_renderer = $ui_renderer;
        $this->ui_factory = $ui_factory;

        $this->lng = $lng;
        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('crs');

        $this->setTitle($this->lng->txt('add_members_header'));

        $this->__setSearchType();
        $this->__loadQueries();

        $this->result_obj = new ilSearchResult();
        $this->result_obj->setMaxHits(1000000);
        $this->settings = new ilSearchSettings();
    }
    
    /**
     * Closure for filtering users
     * e.g
     * $rep_search_gui->addUserAccessFilterCallable(function($user_ids) use($ref_id,$rbac_perm,$pos_perm)) {
     * // filter users
     * return $filtered_users
     * }
     * @param callable $user_filter
     */
    public function addUserAccessFilterCallable(callable $user_filter)
    {
        $this->user_filter = $user_filter;
    }

    /**
     * Set form title
     * @param string $a_title
     */
    public function setTitle($a_title)
    {
        $this->search_title = $a_title;
    }

    /**
     * Get search form title
     * @return string
     */
    public function getTitle()
    {
        return $this->search_title;
    }

    /**
     * En/disable the validation of the searchable flag
     * @param bool $a_status
     */
    public function enableSearchableCheck($a_status)
    {
        $this->searchable_check = $a_status;
    }

    /**
     *
     * @return bool
     */
    public function isSearchableCheckEnabled()
    {
        return $this->searchable_check;
    }

    /**
     * @param int $privacy_mode
     */
    public function setPrivacyMode($privacy_mode)
    {
        $this->privacy_mode = $privacy_mode;
    }

    /**
     * @return int
     */
    public function getPrivacyMode()
    {
        return $this->privacy_mode;
    }


    /**
     * fill toolbar with
     * @param ilToolbarGUI $toolbar
     * @param array options:  all are optional e.g.
     * array(
     *		auto_complete_name = $lng->txt('user'),
     *		auto_complete_size = 15,
     *		user_type = array(ilCourseParticipants::CRS_MEMBER,ilCourseParticpants::CRS_TUTOR),
     *		submit_name = $lng->txt('add')
     * )
     *
     * @return ilToolbarGUI
     */
    public static function fillAutoCompleteToolbar($parent_object, ilToolbarGUI $toolbar = null, $a_options = array(), $a_sticky = false)
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];

        if (!$toolbar instanceof ilToolbarGUI) {
            $toolbar = $ilToolbar;
        }
        
        // Fill default options
        if (!isset($a_options['auto_complete_name'])) {
            $a_options['auto_complete_name'] = $lng->txt('obj_user');
        }
        if (!isset($a_options['auto_complete_size'])) {
            $a_options['auto_complete_size'] = 15;
        }
        if (!isset($a_options['submit_name'])) {
            $a_options['submit_name'] = $lng->txt('btn_add');
        }
        if (!isset($a_options['user_type_default'])) {
            $a_options['user_type_default'] = null;
        }
        
        $ajax_url = $ilCtrl->getLinkTargetByClass(
            array(get_class($parent_object),'ilRepositorySearchGUI'),
            'doUserAutoComplete',
            '',
            true,
            false
        );

        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ul = new ilTextInputGUI($a_options['auto_complete_name'], 'user_login');
        $ul->setDataSource($ajax_url);
        $ul->setSize($a_options['auto_complete_size']);
        if (!$a_sticky) {
            $toolbar->addInputItem($ul, true);
        } else {
            $toolbar->addStickyItem($ul, true);
        }

        if (count((array) $a_options['user_type'])) {
            include_once './Services/Form/classes/class.ilSelectInputGUI.php';
            $si = new ilSelectInputGUI("", "user_type");
            $si->setOptions($a_options['user_type']);
            $si->setValue($a_options['user_type_default']);
            if (!$a_sticky) {
                $toolbar->addInputItem($si);
            } else {
                $toolbar->addStickyItem($si);
            }
        }
        
        include_once './Services/User/classes/class.ilUserClipboard.php';
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        if ($clip->hasContent()) {
            include_once './Services/UIComponent/SplitButton/classes/class.ilSplitButtonGUI.php';
            $action_button = ilSplitButtonGUI::getInstance();

            include_once './Services/UIComponent/Button/classes/class.ilLinkButton.php';
            $add_button = ilSubmitButton::getInstance();
            $add_button->setCaption($a_options['submit_name'], false);
            $add_button->setCommand('addUserFromAutoComplete');

            $action_button->setDefaultButton($add_button);

            include_once './Services/UIComponent/Button/classes/class.ilLinkButton.php';
            $clip_button = ilSubmitButton::getInstance();
            $clip_button->addCSSClass('btn btndefault');
            $GLOBALS['DIC']->language()->loadLanguageModule('user');
            $clip_button->setCaption($GLOBALS['DIC']->language()->txt('clipboard_add_from_btn'), false);
            $clip_button->setCommand('showClipboard');

            $action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($clip_button));

            $toolbar->addButtonInstance($action_button);
        } else {
            include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
            $button = ilSubmitButton::getInstance();
            $button->setCaption($a_options['submit_name'], false);
            $button->setCommand('addUserFromAutoComplete');
            if (!$a_sticky) {
                $toolbar->addButtonInstance($button);
            } else {
                $toolbar->addStickyItem($button);
            }
        }
        
        if ((bool) $a_options['add_search'] ||
            is_numeric($a_options['add_from_container'])) {
            $lng->loadLanguageModule("search");
            
            $toolbar->addSeparator();
                    
            if ((bool) $a_options['add_search']) {
                include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
                $button = ilLinkButton::getInstance();
                $button->setCaption("search_users");
                $button->setUrl($ilCtrl->getLinkTargetByClass('ilRepositorySearchGUI', ''));
                $toolbar->addButtonInstance($button);
            }

            if (is_numeric($a_options['add_from_container'])) {
                $parent_ref_id = (int) $a_options['add_from_container'];
                $parent_container_ref_id = $tree->checkForParentType($parent_ref_id, "grp");
                $parent_container_type = "grp";
                if (!$parent_container_ref_id) {
                    $parent_container_ref_id = $tree->checkForParentType($parent_ref_id, "crs");
                    $parent_container_type = "crs";
                }
                if ($parent_container_ref_id) {
                    if ((bool) $a_options['add_search']) {
                        $toolbar->addSpacer();
                    }
                    
                    $ilCtrl->setParameterByClass('ilRepositorySearchGUI', "list_obj", ilObject::_lookupObjId($parent_container_ref_id));
                    
                    include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
                    $button = ilLinkButton::getInstance();
                    $button->setCaption("search_add_members_from_container_" . $parent_container_type);
                    $button->setUrl($ilCtrl->getLinkTargetByClass(array(get_class($parent_object),'ilRepositorySearchGUI'), 'listUsers'));
                    $toolbar->addButtonInstance($button);
                }
            }
        }
        
        $toolbar->setFormAction(
            $ilCtrl->getFormActionByClass(
                array(
                    get_class($parent_object),
                    'ilRepositorySearchGUI')
            )
        );
        return $toolbar;
    }

    /**
     * Do auto completion
     * @return void
     */
    protected function doUserAutoComplete()
    {
        // hide anonymout request
        if ($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) {
            include_once './Services/JSON/classes/class.ilJsonUtil.php';
            return ilJsonUtil::encode(new stdClass());
            exit;
        }
        
        
        if (!isset($_GET['autoCompleteField'])) {
            $a_fields = array('login','firstname','lastname','email');
            $result_field = 'login';
        } else {
            $a_fields = array((string) $_GET['autoCompleteField']);
            $result_field = (string) $_GET['autoCompleteField'];
        }

        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setPrivacyMode($this->getPrivacyMode());

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        $auto->setMoreLinkAvailable(true);
        $auto->setSearchFields($a_fields);
        $auto->setResultField($result_field);
        $auto->enableFieldSearchableCheck(true);
        $auto->setUserLimitations($this->getUserLimitations());
        if (is_callable($this->user_filter)) {		// #0024249
            $auto->addUserAccessFilterCallable($this->user_filter);
        }

        echo $auto->getList($_REQUEST['term']);
        exit();
    }


    /**
    * Set/get search string
    * @access public
    */
    public function setString($a_str)
    {
        $_SESSION['search']['string'] = $this->string = $a_str;
    }
    public function getString()
    {
        return $this->string;
    }
        
    /**
    * Control
    * @access public
    */
    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->ctrl->setReturn($this, '');

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "showSearch";
                }
                $this->$cmd();
                break;
        }
        return true;
    }

    public function __clearSession()
    {
        unset($_SESSION['rep_search']);
        unset($_SESSION['append_results']);
        unset($_SESSION['rep_query']);
        unset($_SESSION['rep_search_type']);
    }

    public function cancel()
    {
        $this->ctrl->returnToParent($this);
    }

    public function start()
    {
        // delete all session info
        $this->__clearSession();
        $this->showSearch();

        return true;
    }


    public function addRole()
    {
        $class = $this->role_callback['class'];
        $method = $this->role_callback['method'];

        // call callback if that function does give a return value => show error message
        // listener redirects if everything is ok.
        $obj_ids = (array) $_POST['obj'];
        $role_ids = array();
        foreach ($obj_ids as $id) {
            $obj_type = ilObject::_lookupType($id);
            if ($obj_type == "crs" || $obj_type == "grp") {
                $refs = ilObject::_getAllReferences($id);
                $ref_id = end($refs);
                $mem_role = ilParticipants::getDefaultMemberRole($ref_id);
                $role_ids[] = $mem_role;
            } else {
                $role_ids[] = $id;
            }
        }
        $class->$method((array) $role_ids);

        $this->showSearchResults();
    }

    public function addUser()
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];
        
        // call callback if that function does give a return value => show error message
        // listener redirects if everything is ok.
        $class->$method((array) $_POST['user']);

        $this->showSearchResults();
    }

    /**
     * Add user from auto complete input
     */
    protected function addUserFromAutoComplete()
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        $users = explode(',', $_POST['user_login']);
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);
            if ($user_id) {
                $user_ids[] = $user_id;
            }
        }

        $user_type = isset($_REQUEST['user_type']) ? $_REQUEST['user_type'] : 0;

        if (!$class->$method($user_ids, $user_type)) {
            $GLOBALS['DIC']['ilCtrl']->returnToParent($this);
        }
    }
    
    protected function showClipboard()
    {
        $GLOBALS['DIC']['ilCtrl']->setParameter($this, 'user_type', (int) $_REQUEST['user_type']);
        
        ilLoggerFactory::getLogger('crs')->dump($_REQUEST);
        
        $GLOBALS['DIC']['ilTabs']->clearTargets();
        $GLOBALS['DIC']['ilTabs']->setBackTarget(
            $GLOBALS['DIC']['lng']->txt('back'),
            $GLOBALS['DIC']['ilCtrl']->getParentReturn($this)
        );
        
        include_once './Services/User/classes/class.ilUserClipboardTableGUI.php';
        $clip = new ilUserClipboardTableGUI($this, 'showClipboard', $GLOBALS['DIC']['ilUser']->getId());
        $clip->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this));
        $clip->init();
        $clip->parse();
        
        $GLOBALS['DIC']['tpl']->setContent($clip->getHTML());
    }
    
    /**
     * add users from clipboard
     */
    protected function addFromClipboard()
    {
        $GLOBALS['DIC']['ilCtrl']->setParameter($this, 'user_type', (int) $_REQUEST['user_type']);
        $users = (array) $_POST['uids'];
        if (!count($users)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'showClipboard');
        }
        $class = $this->callback['class'];
        $method = $this->callback['method'];
        $user_type = isset($_REQUEST['user_type']) ? $_REQUEST['user_type'] : 0;

        if (!$class->$method($users, $user_type)) {
            $GLOBALS['DIC']['ilCtrl']->returnToParent($this);
        }
    }

    /**
     * Remove from clipboard
     */
    protected function removeFromClipboard()
    {
        $GLOBALS['DIC']['ilCtrl']->setParameter($this, 'user_type', (int) $_REQUEST['user_type']);
        $users = (array) $_POST['uids'];
        if (!count($users)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'showClipboard');
        }

        include_once './Services/User/classes/class.ilUserClipboard.php';
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        $clip->delete($users);
        $clip->save();
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'showClipboard');
    }

    /**
     * Remove from clipboard
     */
    protected function emptyClipboard()
    {
        include_once './Services/User/classes/class.ilUserClipboard.php';
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        $clip->clear();
        $clip->save();
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Handle multi command
     */
    protected function handleMultiCommand()
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        // Redirects if everything is ok
        if (!$class->$method((array) $_POST['user'], $_POST['selectedCommand'])) {
            $this->showSearchResults();
        }
    }

    public function setCallback(&$class, $method, $a_add_options = array())
    {
        $this->callback = array('class' => $class,'method' => $method);
        $this->add_options = $a_add_options ? $a_add_options : array();
    }

    public function setRoleCallback(&$class, $method, $a_add_options = array())
    {
        $this->role_callback = array('class' => $class,'method' => $method);
        $this->add_options = $a_add_options ? $a_add_options : array();
    }
    
    /**
     * Set callback method for user permission access queries
     */
    public function setPermissionQueryCallback($class, $method)
    {
    }
    
    public function showSearch()
    {
        // only autocomplete input field, no search form if user privay should be respected
        // see bug 25481
        if ($this->getPrivacyMode() == ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING) {
            return;
        }
        $this->initFormSearch();
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * submit from autocomplete
     */
    public function showSearchSelected()
    {
        $selected = (int) $_REQUEST['selected_id'];
        
        #include_once './Services/Object/classes/class.ilObjectFactory.php';
        #$factory = new ilObjectFactory();
        #$user = $factory->getInstanceByObjId($selected);
        
        #$this->initFormSearch($user);
        #$this->tpl->setContent($this->form->getHTML());
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rep_search_result.html', 'Services/Search');
        $this->addNewSearchButton();
        $this->showSearchUserTable(array($selected), 'showSearchResults');
    }
    
    public function initFormSearch(ilObjUser $user = null)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'showSearch'));
        $this->form->setTitle($this->getTitle());
        $this->form->addCommandButton('performSearch', $this->lng->txt('search'));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        
        
        $kind = new ilRadioGroupInputGUI($this->lng->txt('search_type'), 'search_for');
        $kind->setValue($this->search_type);
        $this->form->addItem($kind);
        
        // Users
        $users = new ilRadioOption($this->lng->txt('search_for_users'), 'usr');
            
        // UDF
        include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
        foreach (ilUserSearchOptions::_getSearchableFieldsInfo(!$this->isSearchableCheckEnabled()) as $info) {
            switch ($info['type']) {
                case FIELD_TYPE_UDF_SELECT:
                case FIELD_TYPE_SELECT:
                        
                    $sel = new ilSelectInputGUI($info['lang'], "rep_query[usr][" . $info['db'] . "]");
                    $sel->setOptions($info['values']);
                    $users->addSubItem($sel);
                    break;
    
                case FIELD_TYPE_MULTI:
                case FIELD_TYPE_UDF_TEXT:
                case FIELD_TYPE_TEXT:

                    if (isset($info['autoComplete']) and $info['autoComplete']) {
                        $ilCtrl->setParameterByClass(get_class($this), 'autoCompleteField', $info['db']);
                        $ul = new ilTextInputGUI($info['lang'], "rep_query[usr][" . $info['db'] . "]");
                        $ul->setDataSourceSubmitOnSelection(true);
                        $ul->setDataSourceSubmitUrl(
                            $this->ctrl->getLinkTarget(
                                $this,
                                'showSearchSelected',
                                '',
                                false,
                                false
                                )
                        );
                        $ul->setDataSource($ilCtrl->getLinkTarget(
                            $this,
                            "doUserAutoComplete",
                            "",
                            true
                        ));
                        $ul->setSize(30);
                        $ul->setMaxLength(120);
                        
                        if ($user instanceof ilObjUser) {
                            switch ($info['db']) {
                                case 'firstname':
                                    $ul->setValue($user->getFirstname());
                                    break;
                                case 'lastname':
                                    $ul->setValue($user->getLastname());
                                    break;
                                case 'login':
                                    $ul->setValue($user->getLogin());
                                    break;
                            }
                        }
                        
                        
                        
                        $users->addSubItem($ul);
                    } else {
                        $txt = new ilTextInputGUI($info['lang'], "rep_query[usr][" . $info['db'] . "]");
                        $txt->setSize(30);
                        $txt->setMaxLength(120);
                        $users->addSubItem($txt);
                    }
                    break;
            }
        }
        $kind->addOption($users);



        // Role
        $roles = new ilRadioOption($this->lng->txt('search_for_role_members'), 'role');
        $role = new ilTextInputGUI($this->lng->txt('search_role_title'), 'rep_query[role][title]');
        $role->setSize(30);
        $role->setMaxLength(120);
        $roles->addSubItem($role);
        $kind->addOption($roles);
            
        // Course
        $groups = new ilRadioOption($this->lng->txt('search_for_crs_members'), 'crs');
        $group = new ilTextInputGUI($this->lng->txt('search_crs_title'), 'rep_query[crs][title]');
        $group->setSize(30);
        $group->setMaxLength(120);
        $groups->addSubItem($group);
        $kind->addOption($groups);

        // Group
        $groups = new ilRadioOption($this->lng->txt('search_for_grp_members'), 'grp');
        $group = new ilTextInputGUI($this->lng->txt('search_grp_title'), 'rep_query[grp][title]');
        $group->setSize(30);
        $group->setMaxLength(120);
        $groups->addSubItem($group);
        $kind->addOption($groups);

        // Orgus
        if (ilUserSearchOptions::_isEnabled("org_units")) {
            $orgus = new ilRadioOption($this->lng->txt('search_for_orgu_members'), 'orgu');
            $orgu = new ilRepositorySelector2InputGUI($this->lng->txt('select_orgu'), 'rep_query_orgu', true, get_class($this));
            $orgu->getExplorerGUI()->setSelectableTypes(["orgu"]);
            $orgu->getExplorerGUI()->setTypeWhiteList(["root", "orgu"]);
            $orgu->getExplorerGUI()->setRootId(ilObjOrgUnit::getRootOrgRefId());
            $orgu->getExplorerGUI()->setAjax(false);
            $orgus->addSubItem($orgu);
            $kind->addOption($orgus);
        }
    }
    

    public function show()
    {
        $this->showSearchResults();
    }

    public function appendSearch()
    {
        $_SESSION['search_append'] = true;
        $this->performSearch();
    }

    /**
     * Perform a search
     * @return
     */
    public function performSearch()
    {
        // only autocomplete input field, no search form if user privay should be respected
        // see bug 25481
        if ($this->getPrivacyMode() == ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING) {
            return "";
        }
        $found_query = false;
        foreach ((array) $_POST['rep_query'][$_POST['search_for']] as $field => $value) {
            if (trim(ilUtil::stripSlashes($value))) {
                $found_query = true;
                break;
            }
        }
        if (array_key_exists('rep_query_orgu', $_POST) && count($_POST['rep_query_orgu']) > 0) {
            $found_query = true;
        }
        if (!$found_query) {
            ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
            $this->start();
            return false;
        }
    
        // unset search_append if called directly
        if ($_POST['cmd']['performSearch']) {
            unset($_SESSION['search_append']);
        }

        switch ($this->search_type) {
            case 'usr':
                $this->__performUserSearch();
                break;

            case 'grp':
                $this->__performGroupSearch();
                break;

            case 'crs':
                $this->__performCourseSearch();
                break;

            case 'role':
                $this->__performRoleSearch();
                break;
            case 'orgu':
                $_POST['obj'] = array_map(
                    function ($ref_id) {
                        return (int) ilObject::_lookupObjId($ref_id);
                    },
                    $_POST['rep_query_orgu']
                );
                return $this->listUsers();
            default:
                echo 'not defined';
        }
        
        $this->result_obj->setRequiredPermission('read');
        $this->result_obj->addObserver($this, 'searchResultFilterListener');
        $this->result_obj->filter(ROOT_FOLDER_ID, QP_COMBINATION_OR);
        
        // User access filter
        if ($this->search_type == 'usr') {
            $callable_name = '';
            if (is_callable($this->user_filter, true, $callable_name)) {
                $result_ids = call_user_func_array($this->user_filter, [$this->result_obj->getResultIds()]);
            } else {
                $result_ids = $this->result_obj->getResultIds();
            }
            
            include_once './Services/User/classes/class.ilUserFilter.php';
            $this->search_results = array_intersect(
                $result_ids,
                ilUserFilter::getInstance()->filter($result_ids)
            );
        } else {
            $this->search_results = array();
            foreach ((array) $this->result_obj->getResults() as $res) {
                $this->search_results[] = $res['obj_id'];
            }
        }

        if (!count($this->search_results)) {
            ilUtil::sendFailure($this->lng->txt('search_no_match'));
            $this->showSearch();
            return true;
        }
        $this->__updateResults();
        if ($this->result_obj->isLimitReached()) {
            $message = sprintf($this->lng->txt('search_limit_reached'), $this->settings->getMaxHits());
            ilUtil::sendInfo($message);
            return true;
        }
        // show results
        $this->show();
        return true;
    }

    public function __performUserSearch()
    {
        include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

        foreach (ilUserSearchOptions::_getSearchableFieldsInfo(!$this->isSearchableCheckEnabled()) as $info) {
            $name = $info['db'];
            $query_string = $_SESSION['rep_query']['usr'][$name];

            // continue if no query string is given
            if (!$query_string) {
                continue;
            }
        
            if (!is_object($query_parser = $this->__parseQueryString($query_string, true, ($info['type'] == FIELD_TYPE_SELECT)))) {
                ilUtil::sendInfo($query_parser);
                return false;
            }
            switch ($info['type']) {
                case FIELD_TYPE_UDF_SELECT:
                    // Do a phrase query for select fields
                    $query_parser = $this->__parseQueryString('"' . $query_string . '"');
                            
                    // no break
                case FIELD_TYPE_UDF_TEXT:
                    $udf_search = ilObjectSearchFactory::_getUserDefinedFieldSearchInstance($query_parser);
                    $udf_search->setFields(array($name));
                    $result_obj = $udf_search->performSearch();

                    // Store entries
                    $this->__storeEntries($result_obj);
                    break;

                case FIELD_TYPE_SELECT:
                    
                    if ($info['db'] == 'org_units') {
                        $user_search = ilObjectSearchFactory::getUserOrgUnitAssignmentInstance($query_parser);
                        $result_obj = $user_search->performSearch();
                        $this->__storeEntries($result_obj);
                        break;
                    }
                    
                    // Do a phrase query for select fields
                    $query_parser = $this->__parseQueryString('"' . $query_string . '"', true, true);

                    // no break
                case FIELD_TYPE_TEXT:
                    $user_search = &ilObjectSearchFactory::_getUserSearchInstance($query_parser);
                    $user_search->setFields(array($name));
                    $result_obj = $user_search->performSearch();

                    // store entries
                    $this->__storeEntries($result_obj);
                    break;
                
                case FIELD_TYPE_MULTI:
                    $multi_search = ilObjectSearchFactory::getUserMultiFieldSearchInstance($query_parser);
                    $multi_search->setFields(array($name));
                    $result_obj = $multi_search->performSearch();
                    $this->__storeEntries($result_obj);
                    break;
                
            }
        }
    }

    /**
     * Search groups
     * @return
     */
    public function __performGroupSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

        $query_string = $_SESSION['rep_query']['grp']['title'];
        if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
            ilUtil::sendInfo($query_parser, true);
            return false;
        }

        include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array('grp'));
        $this->__storeEntries($object_search->performSearch());

        return true;
    }

    /**
     * Search courses
     * @return
     */
    protected function __performCourseSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

        $query_string = $_SESSION['rep_query']['crs']['title'];
        if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
            ilUtil::sendInfo($query_parser, true);
            return false;
        }

        include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array('crs'));
        $this->__storeEntries($object_search->performSearch());

        return true;
    }

    /**
     * Search roles
     * @return
     */
    public function __performRoleSearch()
    {
        include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

        $query_string = $_SESSION['rep_query']['role']['title'];
        if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
            ilUtil::sendInfo($query_parser, true);
            return false;
        }
        
        // Perform like search
        include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array('role'));
        $this->__storeEntries($object_search->performSearch());

        return true;
    }

    /**
    * parse query string, using query parser instance
    * @return object of query parser or error message if an error occured
    * @access public
    */
    public function &__parseQueryString($a_string, $a_combination_or = true, $a_ignore_length = false)
    {
        $query_parser = new ilQueryParser(ilUtil::stripSlashes($a_string));
        $query_parser->setCombination($a_combination_or ? QP_COMBINATION_OR : QP_COMBINATION_AND);
        $query_parser->setMinWordLength(1);
        
        // #17502
        if (!(bool) $a_ignore_length) {
            $query_parser->setGlobalMinLength(3); // #14768
        }
        
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }

    // Private
    public function __loadQueries()
    {
        if (is_array($_POST['rep_query'])) {
            $_SESSION['rep_query'] = $_POST['rep_query'];
        }
    }


    public function __setSearchType()
    {
        // Update search type. Default to user search
        if ($_POST['search_for']) {
            #echo 1;
            $_SESSION['rep_search_type'] = $_POST['search_for'];
        }
        if (!$_POST['search_for'] and !$_SESSION['rep_search_type']) {
            #echo 2;
            $_SESSION['rep_search_type'] = 'usr';
        }
        
        $this->search_type = $_SESSION['rep_search_type'];
        #echo $this->search_type;

        return true;
    }


    public function __updateResults()
    {
        if (!$_SESSION['search_append']) {
            $_SESSION['rep_search'] = array();
        }
        foreach ($this->search_results as $result) {
            $_SESSION['rep_search'][$this->search_type][] = $result;
        }
        if (!$_SESSION['rep_search'][$this->search_type]) {
            $_SESSION['rep_search'][$this->search_type] = array();
        } else {
            // remove duplicate entries
            $_SESSION['rep_search'][$this->search_type] = array_unique($_SESSION['rep_search'][$this->search_type]);
        }
        return true;
    }

    public function __appendToStoredResults($a_usr_ids)
    {
        if (!$_SESSION['search_append']) {
            return $_SESSION['rep_search']['usr'] = $a_usr_ids;
        }
        $_SESSION['rep_search']['usr'] = array();
        foreach ($a_usr_ids as $usr_id) {
            $_SESSION['rep_search']['usr'][] = $usr_id;
        }
        return $_SESSION['rep_search']['usr'] ? array_unique($_SESSION['rep_search']['usr']) : array();
    }

    public function __storeEntries(&$new_res)
    {
        if ($this->stored == false) {
            $this->result_obj->mergeEntries($new_res);
            $this->stored = true;
            return true;
        } else {
            $this->result_obj->intersectEntries($new_res);
            return true;
        }
    }

    /**
     * Add new search button
     * @return
     */
    protected function addNewSearchButton()
    {
        include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        $toolbar = new ilToolbarGUI();
        $toolbar->addButton(
            $this->lng->txt('search_new'),
            $this->ctrl->getLinkTarget($this, 'showSearch')
        );
        $this->tpl->setVariable('ACTION_BUTTONS', $toolbar->getHTML());
    }
    
    public function showSearchResults()
    {
        $counter = 0;
        $f_result = array();
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rep_search_result.html', 'Services/Search');
        $this->addNewSearchButton();
        
        switch ($this->search_type) {
            case "usr":
                $this->showSearchUserTable($_SESSION['rep_search']['usr'], 'showSearchResults');
                break;

            case 'grp':
                $this->showSearchGroupTable($_SESSION['rep_search']['grp']);
                break;

            case 'crs':
                $this->showSearchCourseTable($_SESSION['rep_search']['crs']);
                break;

            case 'role':
                $this->showSearchRoleTable($_SESSION['rep_search']['role']);
                break;
        }
    }

    /**
     * Show usr table
     * @return
     * @param object $a_usr_ids
     */
    protected function showSearchUserTable($a_usr_ids, $a_parent_cmd)
    {
        $is_in_admin = ($_REQUEST['baseClass'] == 'ilAdministrationGUI');
        if ($is_in_admin) {
            // remember link target to admin search gui (this)
            $_SESSION["usr_search_link"] = $this->ctrl->getLinkTarget($this, 'show');
        }
        
        include_once './Services/Search/classes/class.ilRepositoryUserResultTableGUI.php';
        
        $table = new ilRepositoryUserResultTableGUI($this, $a_parent_cmd, $is_in_admin);
        if (count($this->add_options)) {
            $table->addMultiItemSelectionButton(
                'selectedCommand',
                $this->add_options,
                'handleMultiCommand',
                $this->lng->txt('execute')
            );
        } else {
            $table->addMultiCommand('addUser', $this->lng->txt('btn_add'));
        }
        $table->setUserLimitations($this->getUserLimitations());
        $table->parseUserIds($a_usr_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }
    
    /**
     * Show usr table
     * @return
     * @param object $a_usr_ids
     **/
    protected function showSearchRoleTable($a_obj_ids)
    {
        include_once './Services/Search/classes/class.ilRepositoryObjectResultTableGUI.php';
        
        $table = new ilRepositoryObjectResultTableGUI($this, 'showSearchResults', $this->object_selection);
        $table->parseObjectIds($a_obj_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }

    /**
     *
     * @return
     * @param array $a_obj_ids
     */
    protected function showSearchGroupTable($a_obj_ids)
    {
        include_once './Services/Search/classes/class.ilRepositoryObjectResultTableGUI.php';
        
        $table = new ilRepositoryObjectResultTableGUI($this, 'showSearchResults', $this->object_selection);
        $table->parseObjectIds($a_obj_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }
    
    /**
     *
     * @return
     * @param array $a_obj_ids
     */
    protected function showSearchCourseTable($a_obj_ids)
    {
        include_once './Services/Search/classes/class.ilRepositoryObjectResultTableGUI.php';
        
        $table = new ilRepositoryObjectResultTableGUI($this, 'showSearchResults', $this->object_selection);
        $table->parseObjectIds($a_obj_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }

    /**
     * List users of course/group/roles
     * @return
     */
    protected function listUsers()
    {
        // get parameter is used e.g. in exercises to provide
        // "add members of course" link
        if ($_GET["list_obj"] != "" && !is_array($_POST['obj'])) {
            $_POST['obj'][0] = $_GET["list_obj"];
        }
        if (!is_array($_POST['obj']) or !$_POST['obj']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showSearchResults();
            return false;
        }
        
        $_SESSION['rep_search']['objs'] = $_POST['obj'];
        
        // Get all members
        $members = array();
        foreach ($_POST['obj'] as $obj_id) {
            $type = ilObject::_lookupType($obj_id);
            switch ($type) {
                case 'crs':
                case 'grp':
                    
                    include_once './Services/Membership/classes/class.ilParticipants.php';
                    if (ilParticipants::hasParticipantListAccess($obj_id)) {
                        $part = [];
                        if (is_callable($this->user_filter)) {
                            $part = call_user_func_array(
                                $this->user_filter,
                                [
                                    ilParticipants::getInstanceByObjId($obj_id)->getParticipants()
                                ]
                            );
                        } else {
                            $part = ilParticipants::getInstanceByObjId($obj_id)->getParticipants();
                        }
                        
                        $members = array_merge((array) $members, $part);
                    }
                    break;
                    
                case 'role':
                    global $DIC;

                    $rbacreview = $DIC['rbacreview'];
                    
                    $assigned = [];
                    if (is_callable($this->user_filter)) {
                        $assigned = call_user_func_array(
                            $this->user_filter,
                            [
                                $rbacreview->assignedUsers($obj_id)
                            ]
                        );
                    } else {
                        $assigned = $rbacreview->assignedUsers($obj_id);
                    }
                    
                    $members = array_merge($members, ilUserFilter::getInstance()->filter($assigned));
                    break;
                case 'orgu':
                    if ($ref_ids = ilObject::_getAllReferences($obj_id)) {
                        $assigned = ilOrgUnitUserAssignmentQueries::getInstance()
                            ->getUserIdsOfOrgUnit(array_shift($ref_ids));
                        if (is_callable($this->user_filter)) {
                            $assigned = call_user_func_array(
                                $this->user_filter,
                                [$assigned]
                            );
                        }

                        $members = array_merge(
                            $members,
                            $assigned
                        );
                    }
                    break;
            }
        }
        $members = array_unique((array) $members);
        $this->__appendToStoredResults($members);
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rep_search_result.html', 'Services/Search');
        
        $this->addNewSearchButton();
        $this->showSearchUserTable($_SESSION['rep_search']['usr'], 'storedUserList');
        return true;
    }
    
    /**
     * Called from table sort
     * @return
     */
    protected function storedUserList()
    {
        $_POST['obj'] = $_SESSION['rep_search']['objs'];
        $this->listUsers();
        return true;
    }
    
    /**
     * Listener called from ilSearchResult
     * Id is obj_id for role, usr
     * Id is ref_id for crs grp
     * @param int $a_id
     * @param array $a_data
     * @return
     */
    public function searchResultFilterListener($a_ref_id, $a_data)
    {
        if ($a_data['type'] == 'usr') {
            if ($a_data['obj_id'] == ANONYMOUS_USER_ID) {
                return false;
            }
        }
        return true;
    }

    /**
     * Toggle object selection status
     *
     * @param bool $a_value
     */
    public function allowObjectSelection($a_value = false)
    {
        $this->object_selection = (bool) $a_value;
    }

    /**
     * Return selection of course/group/roles to calling script
     */
    protected function selectObject()
    {
        // get parameter is used e.g. in exercises to provide
        // "add members of course" link
        if ($_GET["list_obj"] != "" && !is_array($_POST['obj'])) {
            $_POST['obj'][0] = $_GET["list_obj"];
        }
        if (!is_array($_POST['obj']) or !$_POST['obj']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showSearchResults();
            return false;
        }

        $this->ctrl->setParameter($this->callback["class"], "obj", implode(";", $_POST["obj"]));
        $this->ctrl->redirect($this->callback["class"], $this->callback["method"]);
    }

    /**
     * allow user limitations like inactive and access limitations
     *
     * @param bool $a_limitations
     */
    public function setUserLimitations($a_limitations)
    {
        $this->user_limitations = (bool) $a_limitations;
    }

    /**
     * allow user limitations like inactive and access limitations
     * @return bool
     */
    public function getUserLimitations()
    {
        return $this->user_limitations;
    }
}
