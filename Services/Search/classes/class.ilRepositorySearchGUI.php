<?php declare(strict_types=1);
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
*
* @package ilias-search
* @ilCtrl_Calls ilRepositorySearchGUI: ilFormPropertyDispatchGUI
*
*/

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as RefineryFactory;

class ilRepositorySearchGUI
{
    private array $search_results = [];
    
    protected array $add_options = [];
    protected bool $object_selection = false;

    protected bool $searchable_check = true;
    protected string $search_title = '';
    
    private string $search_type = 'usr';
    private string $string = '';
    protected bool $user_limitations = true;

    protected bool $stored = false;
    protected array $callback = [];
    protected array $role_callback = [];

    protected ilSearchResult $result_obj;
    protected ilSearchSettings $settings;
    protected ?ilPropertyFormGUI $form = null;

    /**
     * @var callable
     */
    protected $user_filter = null;
    
    private int $privacy_mode = ilUserAutoComplete::PRIVACY_MODE_IGNORE_USER_SETTING;

    protected ilTree $tree;
    protected Renderer $ui_renderer;
    protected Factory $ui_factory;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected ilRbacReview $rbacreview;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    private GlobalHttpState $http;
    private RefineryFactory $refinery;




    public function __construct()
    {
        global $DIC;



        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tree = $DIC->repositoryTree();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();
        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
        $this->refinery = $DIC->refinery();
        $this->http = $DIC->http();

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
     */
    public function addUserAccessFilterCallable(callable $user_filter) : void
    {
        $this->user_filter = $user_filter;
    }

    public function setTitle(string $a_title) : void
    {
        $this->search_title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->search_title;
    }

    public function enableSearchableCheck(bool $a_status)// @TODO: PHP8 Review: Missing return type.
    {
        $this->searchable_check = $a_status;
    }

    public function isSearchableCheckEnabled() : bool
    {
        return $this->searchable_check;
    }

    public function setPrivacyMode(int $privacy_mode) : void
    {
        $this->privacy_mode = $privacy_mode;
    }

    public function getPrivacyMode() : int
    {
        return $this->privacy_mode;
    }

    public function getSearchType() : string
    {
        return $this->search_type;
    }

    public function getRoleCallback() : array
    {
        return $this->role_callback;
    }


    /**
     * array(
     *		auto_complete_name = $lng->txt('user'),
     *		auto_complete_size = 15,
     *		user_type = array(ilCourseParticipants::CRS_MEMBER,ilCourseParticpants::CRS_TUTOR),
     *		submit_name = $lng->txt('add')
     * )
     */
    public static function fillAutoCompleteToolbar(
        object $parent_object,
        ilToolbarGUI $toolbar = null,
        array $a_options = [],
        bool $a_sticky = false
    ) : ilToolbarGUI {
        global $DIC;

        $ilToolbar = $DIC->toolbar();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();
        $user = $DIC->user();

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
        
        if (!isset($a_options['add_search'])) {
            $a_options['add_search'] = false;
        }
        if (!isset($a_options['add_from_container'])) {
            $a_options['add_from_container'] = null;
        }

        $ajax_url = $ilCtrl->getLinkTargetByClass(
            array(get_class($parent_object),'ilRepositorySearchGUI'),
            'doUserAutoComplete',
            '',
            true,
            false
        );

        $ul = new ilTextInputGUI($a_options['auto_complete_name'], 'user_login');
        $ul->setDataSource($ajax_url);
        $ul->setSize($a_options['auto_complete_size']);
        if (!$a_sticky) {
            $toolbar->addInputItem($ul, true);
        } else {
            $toolbar->addStickyItem($ul, true);
        }

        if (isset($a_options['user_type']) && count((array) $a_options['user_type'])) {
            $si = new ilSelectInputGUI("", "user_type");
            $si->setOptions($a_options['user_type']);
            $si->setValue($a_options['user_type_default']);
            if (!$a_sticky) {
                $toolbar->addInputItem($si);
            } else {
                $toolbar->addStickyItem($si);
            }
        }
        
        $clip = ilUserClipboard::getInstance($user->getId());
        if ($clip->hasContent()) {
            $action_button = ilSplitButtonGUI::getInstance();

            $add_button = ilSubmitButton::getInstance();
            $add_button->setCaption($a_options['submit_name'], false);
            $add_button->setCommand('addUserFromAutoComplete');

            $action_button->setDefaultButton($add_button);

            $clip_button = ilSubmitButton::getInstance();
            $clip_button->addCSSClass('btn btndefault');
            $lng->loadLanguageModule('user');
            $clip_button->setCaption($lng->txt('clipboard_add_from_btn'), false);
            $clip_button->setCommand('showClipboard');

            $action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($clip_button));

            $toolbar->addButtonInstance($action_button);
        } else {
            $button = ilSubmitButton::getInstance();
            $button->setCaption($a_options['submit_name'], false);
            $button->setCommand('addUserFromAutoComplete');
            if (!$a_sticky) {
                $toolbar->addButtonInstance($button);
            } else {
                $toolbar->addStickyItem($button);
            }
        }
        
        if ($a_options['add_search'] ||
            is_numeric($a_options['add_from_container'])) {
            $lng->loadLanguageModule("search");
            
            $toolbar->addSeparator();
                    
            if ($a_options['add_search']) {
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
                    if ($a_options['add_search']) {
                        $toolbar->addSpacer();
                    }
                    
                    $ilCtrl->setParameterByClass('ilRepositorySearchGUI', "list_obj", ilObject::_lookupObjId($parent_container_ref_id));
                    
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

    protected function doUserAutoComplete() : ?string
    {
        // hide anonymout request
        if ($this->user->getId() == ANONYMOUS_USER_ID) {
            return ilJsonUtil::encode(new stdClass());
        }
        if (!$this->http->wrapper()->query()->has('autoCompleteField')) {
            $a_fields = [
                'login',
                'firstname',
                'lastname',
                'email'
            ];
            $result_field = 'login';
        } else {
            $auto_complete_field = $this->http->wrapper()->query()->retrieve(
                'autoCompleteField',
                $this->refinery->kindlyTo()->string()
            );
            $a_fields = [$auto_complete_field];
            $result_field = $auto_complete_field;
        }
        $auto = new ilUserAutoComplete();
        $auto->setPrivacyMode($this->getPrivacyMode());

        if (($_REQUEST['fetchall'])) {// @TODO: PHP8 Review: Direct access to $_REQUEST.
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

        echo $auto->getList($_REQUEST['term']);// @TODO: PHP8 Review: Direct access to $_REQUEST.
        return null;
    }


    public function setString(string $a_str) : void
    {
        $_SESSION['search']['string'] = $this->string = $a_str;// @TODO: PHP8 Review: Direct access to $_SESSION.
    }
    public function getString() : string
    {
        return $this->string;
    }
        
    public function executeCommand() : bool
    {
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

    public function __clearSession() : void
    {
        unset($_SESSION['rep_search']);// @TODO: PHP8 Review: Direct access to $_SESSION.
        unset($_SESSION['append_results']);// @TODO: PHP8 Review: Direct access to $_SESSION.
        unset($_SESSION['rep_query']);// @TODO: PHP8 Review: Direct access to $_SESSION.
        unset($_SESSION['rep_search_type']);// @TODO: PHP8 Review: Direct access to $_SESSION.
    }

    public function cancel() : void
    {
        $this->ctrl->returnToParent($this);
    }

    public function start() : bool
    {
        // delete all session info
        $this->__clearSession();
        $this->showSearch();

        return true;
    }


    public function addRole() : void
    {
        $class = $this->role_callback['class'];
        $method = $this->role_callback['method'];

        // call callback if that function does give a return value => show error message
        // listener redirects if everything is ok.
        $obj_ids = [];
        if ($this->http->wrapper()->post()->has('obj')) {
            $obj_ids = $this->http->wrapper()->post()->retrieve(
                'obj',
                $this->refinery->kindlyTo()->listOf($this->refinery->int())
            );
        }
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
        $class->$method($role_ids);

        $this->showSearchResults();
    }

    public function addUser() : void
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        $users = [];
        if ($this->http->wrapper()->post()->has('user')) {
            $users = $this->http->wrapper()->post()->retrieve(
                'user',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        
        // call callback if that function does give a return value => show error message
        // listener redirects if everything is ok.
        $class->$method($users);

        $this->showSearchResults();
    }


    protected function addUserFromAutoComplete() : void
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        $users = explode(',', $_POST['user_login']);// @TODO: PHP8 Review: Direct access to $_POST.
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);
            if ($user_id) {
                $user_ids[] = $user_id;
            }
        }

        $user_type = $_REQUEST['user_type'] ?? 0;// @TODO: PHP8 Review: Direct access to $_REQUEST.

        if (!$class->$method($user_ids, (int) $user_type)) {
            $this->ctrl->returnToParent($this);
        }
    }
    
    protected function showClipboard() : void
    {
        $this->ctrl->setParameter($this, 'user_type', (int) $_REQUEST['user_type']);// @TODO: PHP8 Review: Direct access to $_REQUEST.
        
        ilLoggerFactory::getLogger('crs')->dump($_REQUEST);// @TODO: PHP8 Review: Direct access to $_REQUEST.
        
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getParentReturn($this)
        );
        
        $clip = new ilUserClipboardTableGUI($this, 'showClipboard', $this->user->getId());
        $clip->setFormAction($this->ctrl->getFormAction($this));
        $clip->init();
        $clip->parse();
        
        $this->tpl->setContent($clip->getHTML());
    }
    

    protected function addFromClipboard() : void
    {
        $this->ctrl->setParameter($this, 'user_type', (int) $_REQUEST['user_type']);// @TODO: PHP8 Review: Direct access to $_REQUEST.
        $users = (array) $_POST['uids'];// @TODO: PHP8 Review: Direct access to $_POST.
        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'showClipboard');
        }
        $class = $this->callback['class'];
        $method = $this->callback['method'];
        $user_type = $_REQUEST['user_type'] ?? 0;// @TODO: PHP8 Review: Direct access to $_REQUEST.

        if (!$class->$method($users, $user_type)) {
            $this->ctrl->returnToParent($this);
        }
    }


    protected function removeFromClipboard() : void
    {
        $users = [];
        if ($this->http->wrapper()->post()->has('uids')) {
            $users = $this->http->wrapper()->post()->retrieve(
                'uids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        $this->ctrl->setParameter($this, 'user_type', (int) $_REQUEST['user_type']);// @TODO: PHP8 Review: Direct access to $_REQUEST.
        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'showClipboard');
        }

        $clip = ilUserClipboard::getInstance($this->user->getId());
        $clip->delete($users);
        $clip->save();
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'showClipboard');
    }


    protected function emptyClipboard() : void
    {
        $clip = ilUserClipboard::getInstance($this->user->getId());
        $clip->clear();
        $clip->save();
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->returnToParent($this);
    }


    protected function handleMultiCommand() : void
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        // Redirects if everything is ok
        if (!$class->$method((array) $_POST['user'], $_POST['selectedCommand'])) {// @TODO: PHP8 Review: Direct access to $_POST.
            $this->showSearchResults();
        }
    }

    public function setCallback(object $class, string $method, array $a_add_options = array()) : void
    {
        $this->callback = array('class' => $class,'method' => $method);
        $this->add_options = $a_add_options;
    }

    public function setRoleCallback(object $class, string $method, array $a_add_options = array()) : void
    {
        $this->role_callback = array('class' => $class,'method' => $method);
        $this->add_options = $a_add_options;
    }
    

    public function setPermissionQueryCallback(object $class, string $method) : void
    {
    }
    
    public function showSearch() : void
    {
        // only autocomplete input field, no search form if user privay should be respected
        // see bug 25481
        if ($this->getPrivacyMode() == ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING) {
            return;
        }
        $this->initFormSearch();
        $this->tpl->setContent($this->form->getHTML());
    }
    
    public function showSearchSelected() : void
    {
        $selected = (int) $_REQUEST['selected_id'];// @TODO: PHP8 Review: Direct access to $_REQUEST.
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rep_search_result.html', 'Services/Search');
        $this->addNewSearchButton();
        $this->showSearchUserTable(array($selected), 'showSearchResults');
    }
    
    public function initFormSearch(ilObjUser $user = null) : void
    {
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
        foreach (ilUserSearchOptions::_getSearchableFieldsInfo(!$this->isSearchableCheckEnabled()) as $info) {
            switch ($info['type']) {
                case ilUserSearchOptions::FIELD_TYPE_UDF_SELECT:
                case ilUserSearchOptions::FIELD_TYPE_SELECT:
                        
                    $sel = new ilSelectInputGUI($info['lang'], "rep_query[usr][" . $info['db'] . "]");
                    $sel->setOptions($info['values']);
                    $users->addSubItem($sel);
                    break;
    
                case ilUserSearchOptions::FIELD_TYPE_MULTI:
                case ilUserSearchOptions::FIELD_TYPE_UDF_TEXT:
                case ilUserSearchOptions::FIELD_TYPE_TEXT:

                    if (isset($info['autoComplete']) and $info['autoComplete']) {
                        $this->ctrl->setParameterByClass(get_class($this), 'autoCompleteField', $info['db']);
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
                        $ul->setDataSource($this->ctrl->getLinkTarget(
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
            $orgu = new ilRepositorySelector2InputGUI(
                $this->lng->txt('select_orgu'),
                'rep_query_orgu',
                true,
                $this->form
            );
            $orgu->getExplorerGUI()->setSelectableTypes(["orgu"]);
            $orgu->getExplorerGUI()->setTypeWhiteList(["root", "orgu"]);
            $orgu->getExplorerGUI()->setRootId(ilObjOrgUnit::getRootOrgRefId());
            $orgu->getExplorerGUI()->setAjax(false);
            $orgus->addSubItem($orgu);
            $kind->addOption($orgus);
        }
    }
    

    public function show() : void
    {
        $this->showSearchResults();
    }

    public function appendSearch() : void
    {
        $_SESSION['search_append'] = true;// @TODO: PHP8 Review: Direct access to $_SESSION.
        $this->performSearch();
    }

    public function performSearch() : bool
    {
        // only autocomplete input field, no search form if user privay should be respected
        // see bug 25481
        if ($this->getPrivacyMode() == ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING) {
            return false;
        }
        $found_query = false;
        foreach ((array) $_POST['rep_query'][$_POST['search_for']] as $field => $value) {// @TODO: PHP8 Review: Direct access to $_POST.
            if (trim(ilUtil::stripSlashes($value))) {
                $found_query = true;
                break;
            }
        }
        if ($this->http->wrapper()->post()->has('rep_query_orgu')) {
            $found_query = true;
        }
        if (!$found_query) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_search_string'));
            $this->start();
            return false;
        }
    
        // unset search_append if called directly
        if ($_POST['cmd']['performSearch']) {// @TODO: PHP8 Review: Direct access to $_POST.
            unset($_SESSION['search_append']);// @TODO: PHP8 Review: Direct access to $_SESSION.
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
                $_POST['obj'] = array_map(// @TODO: PHP8 Review: Direct access to $_POST.
                    function ($ref_id) {
                        return ilObject::_lookupObjId($ref_id);
                    },
                    $_POST['rep_query_orgu']// @TODO: PHP8 Review: Direct access to $_POST.
                );
                return $this->listUsers();
            default:
                echo 'not defined';
        }
        
        $this->result_obj->setRequiredPermission('read');
        $this->result_obj->addObserver($this, 'searchResultFilterListener');
        $this->result_obj->filter(ROOT_FOLDER_ID, true);
        
        // User access filter
        if ($this->search_type == 'usr') {
            $callable_name = '';
            if (is_callable($this->user_filter, true, $callable_name)) {
                $result_ids = call_user_func_array($this->user_filter, [$this->result_obj->getResultIds()]);
            } else {
                $result_ids = $this->result_obj->getResultIds();
            }
            
            $this->search_results = array_intersect(
                $result_ids,
                ilUserFilter::getInstance()->filter($result_ids)
            );
        } else {
            $this->search_results = array();
            foreach ($this->result_obj->getResults() as $res) {
                $this->search_results[] = $res['obj_id'];
            }
        }

        if (!count($this->search_results)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('search_no_match'));
            $this->showSearch();
            return true;
        }
        $this->__updateResults();
        if ($this->result_obj->isLimitReached()) {
            $message = sprintf($this->lng->txt('search_limit_reached'), $this->settings->getMaxHits());
            $this->tpl->setOnScreenMessage('info', $message);
            return true;
        }
        // show results
        $this->show();
        return true;
    }

    public function __performUserSearch() : bool
    {
        foreach (ilUserSearchOptions::_getSearchableFieldsInfo(!$this->isSearchableCheckEnabled()) as $info) {
            $name = $info['db'];
            $query_string = $_SESSION['rep_query']['usr'][$name];// @TODO: PHP8 Review: Direct access to $_SESSION.

            // continue if no query string is given
            if (!$query_string) {
                continue;
            }
        
            if (!is_object($query_parser = $this->__parseQueryString($query_string, true, ($info['type'] == ilUserSearchOptions::FIELD_TYPE_SELECT)))) {
                $this->tpl->setOnScreenMessage('info', $query_parser);
                return false;
            }
            switch ($info['type']) {
                case ilUserSearchOptions::FIELD_TYPE_UDF_SELECT:
                    // Do a phrase query for select fields
                    $query_parser = $this->__parseQueryString('"' . $query_string . '"');
                            
                    // no break
                case ilUserSearchOptions::FIELD_TYPE_UDF_TEXT:
                    $udf_search = ilObjectSearchFactory::_getUserDefinedFieldSearchInstance($query_parser);
                    $udf_search->setFields(array($name));
                    $result_obj = $udf_search->performSearch();

                    // Store entries
                    $this->__storeEntries($result_obj);
                    break;

                case ilUserSearchOptions::FIELD_TYPE_SELECT:
                    
                    if ($info['db'] == 'org_units') {
                        $user_search = ilObjectSearchFactory::getUserOrgUnitAssignmentInstance($query_parser);
                        $result_obj = $user_search->performSearch();
                        $this->__storeEntries($result_obj);
                        break;
                    }
                    
                    // Do a phrase query for select fields
                    $query_parser = $this->__parseQueryString('"' . $query_string . '"', true, true);

                    // no break
                case ilUserSearchOptions::FIELD_TYPE_TEXT:
                    $user_search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
                    $user_search->setFields(array($name));
                    $result_obj = $user_search->performSearch();

                    // store entries
                    $this->__storeEntries($result_obj);
                    break;
                
                case ilUserSearchOptions::FIELD_TYPE_MULTI:
                    $multi_search = ilObjectSearchFactory::getUserMultiFieldSearchInstance($query_parser);
                    $multi_search->setFields(array($name));
                    $result_obj = $multi_search->performSearch();
                    $this->__storeEntries($result_obj);
                    break;
                
            }
        }
        return true;
    }

    public function __performGroupSearch() : bool
    {
        $query_string = $_SESSION['rep_query']['grp']['title'];// @TODO: PHP8 Review: Direct access to $_SESSION.
        if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
            $this->tpl->setOnScreenMessage('info', $query_parser, true);
            return false;
        }

        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array('grp'));
        $this->__storeEntries($object_search->performSearch());

        return true;
    }

    protected function __performCourseSearch() : bool
    {
        $query_string = $_SESSION['rep_query']['crs']['title'];// @TODO: PHP8 Review: Direct access to $_SESSION.
        if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
            $this->tpl->setOnScreenMessage('info', $query_parser, true);
            return false;
        }

        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array('crs'));
        $this->__storeEntries($object_search->performSearch());

        return true;
    }

    public function __performRoleSearch() : bool
    {
        $query_string = $_SESSION['rep_query']['role']['title'];// @TODO: PHP8 Review: Direct access to $_SESSION.
        if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
            $this->tpl->setOnScreenMessage('info', $query_parser, true);
            return false;
        }
        
        // Perform like search
        $object_search = new ilLikeObjectSearch($query_parser);
        $object_search->setFilter(array('role'));
        $this->__storeEntries($object_search->performSearch());

        return true;
    }

    /**
     * @return ilQueryParser|string
    */
    public function __parseQueryString(string $a_string, bool $a_combination_or = true, bool $a_ignore_length = false)
    {
        $query_parser = new ilQueryParser(ilUtil::stripSlashes($a_string));
        $query_parser->setCombination($a_combination_or ? ilQueryParser::QP_COMBINATION_OR : ilQueryParser::QP_COMBINATION_AND);
        $query_parser->setMinWordLength(1);
        
        // #17502
        if (!$a_ignore_length) {
            $query_parser->setGlobalMinLength(3); // #14768
        }
        
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }

    // Private
    public function __loadQueries() : void
    {
        if (is_array($_POST['rep_query'])) {// @TODO: PHP8 Review: Direct access to $_POST.
            $_SESSION['rep_query'] = $_POST['rep_query'];// @TODO: PHP8 Review: Direct access to $_POST.// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
    }


    public function __setSearchType() : bool
    {
        // Update search type. Default to user search
        if ($_POST['search_for']) {// @TODO: PHP8 Review: Direct access to $_POST.
            #echo 1;
            $_SESSION['rep_search_type'] = $_POST['search_for'];// @TODO: PHP8 Review: Direct access to $_POST.// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        if (!$_POST['search_for'] and !$_SESSION['rep_search_type']) {// @TODO: PHP8 Review: Direct access to $_POST.// @TODO: PHP8 Review: Direct access to $_SESSION.
            #echo 2;
            $_SESSION['rep_search_type'] = 'usr';// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        
        $this->search_type = $_SESSION['rep_search_type'];// @TODO: PHP8 Review: Direct access to $_SESSION.

        return true;
    }


    public function __updateResults() : bool
    {
        if (!$_SESSION['search_append']) {// @TODO: PHP8 Review: Direct access to $_SESSION.
            $_SESSION['rep_search'] = array();// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        foreach ($this->search_results as $result) {
            $_SESSION['rep_search'][$this->search_type][] = $result;// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        if (!$_SESSION['rep_search'][$this->search_type]) {// @TODO: PHP8 Review: Direct access to $_SESSION.
            $_SESSION['rep_search'][$this->search_type] = array();// @TODO: PHP8 Review: Direct access to $_SESSION.
        } else {
            // remove duplicate entries
            $_SESSION['rep_search'][$this->search_type] = array_unique($_SESSION['rep_search'][$this->search_type]);// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        return true;
    }

    /**
     * @return int[]
     */
    public function __appendToStoredResults(array $a_usr_ids) : array
    {
        if (!$_SESSION['search_append']) {// @TODO: PHP8 Review: Direct access to $_SESSION.
            return $_SESSION['rep_search']['usr'] = $a_usr_ids;// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        $_SESSION['rep_search']['usr'] = array();// @TODO: PHP8 Review: Direct access to $_SESSION.
        foreach ($a_usr_ids as $usr_id) {
            $_SESSION['rep_search']['usr'][] = $usr_id;// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        return $_SESSION['rep_search']['usr'] ? array_unique($_SESSION['rep_search']['usr']) : array();// @TODO: PHP8 Review: Direct access to $_SESSION.
    }

    public function __storeEntries(ilSearchResult $new_res) : bool
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

    protected function addNewSearchButton() : void
    {
        $toolbar = new ilToolbarGUI();
        $toolbar->addButton(
            $this->lng->txt('search_new'),
            $this->ctrl->getLinkTarget($this, 'showSearch')
        );
        $this->tpl->setVariable('ACTION_BUTTONS', $toolbar->getHTML());
    }
    
    public function showSearchResults() : void
    {
        $counter = 0;
        $f_result = array();
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rep_search_result.html', 'Services/Search');
        $this->addNewSearchButton();
        
        switch ($this->search_type) {
            case "usr":
                $this->showSearchUserTable($_SESSION['rep_search']['usr'], 'showSearchResults');// @TODO: PHP8 Review: Direct access to $_SESSION.
                break;

            case 'grp':
                $this->showSearchGroupTable($_SESSION['rep_search']['grp']);// @TODO: PHP8 Review: Direct access to $_SESSION.
                break;

            case 'crs':
                $this->showSearchCourseTable($_SESSION['rep_search']['crs']);// @TODO: PHP8 Review: Direct access to $_SESSION.
                break;

            case 'role':
                $this->showSearchRoleTable($_SESSION['rep_search']['role']);// @TODO: PHP8 Review: Direct access to $_SESSION.
                break;
        }
    }

    protected function showSearchUserTable(array $a_usr_ids, string $a_parent_cmd) : void
    {
        $is_in_admin = ($_REQUEST['baseClass'] == 'ilAdministrationGUI');// @TODO: PHP8 Review: Direct access to $_REQUEST.
        if ($is_in_admin) {
            // remember link target to admin search gui (this)
            $_SESSION["usr_search_link"] = $this->ctrl->getLinkTarget($this, 'show');// @TODO: PHP8 Review: Direct access to $_SESSION.
        }
        
        
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
    
    protected function showSearchRoleTable(array $a_obj_ids) : void
    {
        $table = new ilRepositoryObjectResultTableGUI($this, 'showSearchResults', $this->object_selection);
        $table->parseObjectIds($a_obj_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }

    protected function showSearchGroupTable(array $a_obj_ids) : void
    {
        $table = new ilRepositoryObjectResultTableGUI($this, 'showSearchResults', $this->object_selection);
        $table->parseObjectIds($a_obj_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }
    
    protected function showSearchCourseTable(array $a_obj_ids) : void
    {
        $table = new ilRepositoryObjectResultTableGUI($this, 'showSearchResults', $this->object_selection);
        $table->parseObjectIds($a_obj_ids);
        
        $this->tpl->setVariable('RES_TABLE', $table->getHTML());
    }

    protected function listUsers() : bool
    {
        // get parameter is used e.g. in exercises to provide
        // "add members of course" link
        $selected_entries = [];
        if ($this->http->wrapper()->post()->has('obj')) {
            $selected_entries = $this->http->wrapper()->post()->retrieve(
                'obj',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        if (
            $this->http->wrapper()->query()->has('list_obj') &&
            !count($selected_entries)
        ) {
            $selected_entries[] = $this->http->wrapper()->query()->retrieve(
                'list_obj',
                $this->refinery->kindlyTo()->int()
            );
        }
        if (!count($selected_entries)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showSearchResults();
            return false;
        }
        $_SESSION['rep_search']['objs'] = $selected_entries;// @TODO: PHP8 Review: Direct access to $_SESSION.
        
        // Get all members
        $members = array();
        foreach ($selected_entries as $obj_id) {
            $type = ilObject::_lookupType($obj_id);
            switch ($type) {
                case 'crs':
                case 'grp':
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
                    $assigned = [];
                    if (is_callable($this->user_filter)) {
                        $assigned = call_user_func_array(
                            $this->user_filter,
                            [
                                $this->rbacreview->assignedUsers($obj_id)
                            ]
                        );
                    } else {
                        $assigned = $this->rbacreview->assignedUsers($obj_id);
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
        $members = array_unique($members);
        $this->__appendToStoredResults($members);
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rep_search_result.html', 'Services/Search');
        
        $this->addNewSearchButton();
        $this->showSearchUserTable($_SESSION['rep_search']['usr'], 'storedUserList');// @TODO: PHP8 Review: Direct access to $_SESSION.
        return true;
    }
    
    protected function storedUserList() : bool
    {
        $_POST['obj'] = $_SESSION['rep_search']['objs'];// @TODO: PHP8 Review: Direct access to $_POST.// @TODO: PHP8 Review: Direct access to $_SESSION.
        $this->listUsers();
        return true;
    }
    
    /**
     * Listener called from ilSearchResult
     * Id is obj_id for role, usr
     * Id is ref_id for crs grp
     */
    public function searchResultFilterListener(int $a_ref_id, array $a_data) : bool
    {
        if ($a_data['type'] == 'usr') {
            if ($a_data['obj_id'] == ANONYMOUS_USER_ID) {
                return false;
            }
        }
        return true;
    }

    public function allowObjectSelection(bool $a_value = false) : void
    {
        $this->object_selection = $a_value;
    }

    /**
     * Return selection of course/group/roles to calling script
     */
    protected function selectObject() : bool
    {
        // get parameter is used e.g. in exercises to provide
        // "add members of course"
        $selected_entries = [];
        if ($this->http->wrapper()->post()->has('obj')) {
            $selected_entries = $this->http->wrapper()->post()->retrieve(
                'obj',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        if (
            $this->http->wrapper()->query()->has('list_obj') &&
            !count($selected_entries)
        ) {
            $selected_entries[] = $this->http->wrapper()->query()->retrieve(
                'list_obj',
                $this->refinery->kindlyTo()->int()
            );
        }

        if (!count($selected_entries)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showSearchResults();
            return false;
        }
        $this->ctrl->setParameter($this->callback["class"], "obj", implode(";", $selected_entries));
        $this->ctrl->redirect($this->callback["class"], $this->callback["method"]);
        return true;
    }

    /**
     * allow user limitations like inactive and access limitations
     */
    public function setUserLimitations(bool $a_limitations) : void
    {
        $this->user_limitations = $a_limitations;
    }

    /**
     * allow user limitations like inactive and access limitations
     */
    public function getUserLimitations() : bool
    {
        return $this->user_limitations;
    }
}
