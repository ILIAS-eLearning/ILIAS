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
    protected ?ilObjUser $user = null;
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
        $this->user = $DIC->user();

        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('crs');

        $this->setTitle($this->lng->txt('add_members_header'));

        $this->__setSearchType();
        $this->__loadQueries();

        $this->result_obj = new ilSearchResult();
        $this->result_obj->setMaxHits(1000000);
        $this->settings = new ilSearchSettings();
    }

    protected function initUserTypeFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('user_type')) {
            return $this->http->wrapper()->query()->retrieve(
                'user_type',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
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

    public function enableSearchableCheck(bool $a_status) : void
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
            echo json_encode(new stdClass(), JSON_THROW_ON_ERROR);
            exit;
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

        if ($this->http->wrapper()->query()->has('fetchall')) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        $auto->setMoreLinkAvailable(true);
        $auto->setSearchFields($a_fields);
        $auto->setResultField($result_field);
        $auto->enableFieldSearchableCheck(true);
        $auto->setUserLimitations($this->getUserLimitations());
        if (is_callable($this->user_filter)) {		// #0024249
            $auto->addUserAccessFilterCallable(Closure::fromCallable($this->user_filter));
        }

        $query = '';
        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($query === "") {
            if ($this->http->wrapper()->query()->has('term')) {
                $query = $this->http->wrapper()->query()->retrieve(
                    'term',
                    $this->refinery->kindlyTo()->string()
                );
            }
        }
        echo $auto->getList($query);
        exit;
    }


    public function setString(string $a_str) : void
    {
        $search = ilSession::get('search');
        $search['string'] = $this->string = $a_str;
        ilSession::set('search', $search);
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
        ilSession::clear('rep_search');
        ilSession::clear('append_results');
        ilSession::clear('rep_query');
        ilSession::clear('rep_search_type');
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
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
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

        $post_users = '';
        if ($this->http->wrapper()->post()->has('user_login')) {
            $post_users = $this->http->wrapper()->post()->retrieve(
                'user_login',
                $this->refinery->kindlyTo()->string()
            );
        }

        $users = explode(',', $post_users);
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);
            if ($user_id) {
                $user_ids[] = $user_id;
            }
        }

        $user_type = $this->initUserTypeFromQuery();

        if (!$class->$method($user_ids, $user_type)) {
            $this->ctrl->returnToParent($this);
        }
    }
    
    protected function showClipboard() : void
    {
        $this->ctrl->setParameter($this, 'user_type', $this->initUserTypeFromQuery());
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
        $this->ctrl->setParameter($this, 'user_type', $this->initUserTypeFromQuery());

        $users = [];
        if ($this->http->wrapper()->post()->has('uids')) {
            $users = $this->http->wrapper()->post()->retrieve(
                'uids',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'showClipboard');
        }
        $class = $this->callback['class'];
        $method = $this->callback['method'];
        $user_type = $this->initUserTypeFromQuery();

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

        $this->ctrl->setParameter($this, 'user_type', $this->initUserTypeFromQuery());
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

        $post_selected_command = (string) ($this->http->request()->getParsedBody()['selectedCommand'] ?? '');
        $post_user = (array) ($this->http->request()->getParsedBody()['user'] ?? []);

        // Redirects if everything is ok
        if (!$class->$method($post_user, $post_selected_command)) {
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
        $selected = [];
        if ($this->http->wrapper()->post()->has('selected_id')) {
            $selected = $this->http->wrapper()->post()->retrieve(
                'selected_id',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
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
        ilSession::set('search_append', true);
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

        $post_rep_query = (array) ($this->http->request()->getParsedBody()['rep_query'] ?? []);
        $post_search_for = (string) ($this->http->request()->getParsedBody()['search_for'] ?? '');
        foreach ((array) $post_rep_query[$post_search_for] as $field => $value) {
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

        $post_cmd = (array) ($this->http->request()->getParsedBody()['cmd'] ?? []);
        // unset search_append if called directly
        if (isset($post_cmd['performSearch'])) {
            ilSession::clear('search_append');
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
                $post_rep_query_orgu = (array) ($this->http->request()->getParsedBody()['rep_query_orgu'] ?? []);
                $selected_objects = array_map(
                    function ($ref_id) {
                        return ilObject::_lookupObjId($ref_id);
                    },
                    $post_rep_query_orgu
                );
                return $this->listUsers($selected_objects);
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

            $rep_query = ilSession::get('rep_query');
            $query_string = $rep_query['usr'][$name] ?? '';
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
        $rep_query = ilSession::get('rep_query');
        $query_string = $rep_query['grp']['title'] ?? '';
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
        $rep_query = ilSession::get('rep_query');
        $query_string = $rep_query['crs']['title'] ?? '';
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
        $rep_query = ilSession::get('rep_query');
        $query_string = $rep_query['role']['title'] ?? '';
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
        if ($this->http->wrapper()->post()->has('rep_query')) {
            ilSession::set(
                'rep_query',
                $this->http->request()->getParsedBody()['rep_query']
            );
        }
    }


    public function __setSearchType() : bool
    {
        // Update search type. Default to user search
        if ($this->http->wrapper()->post()->has('search_for')) {
            ilSession::set(
                'rep_search_type',
                $this->http->request()->getParsedBody()['search_for']
            );
        } elseif (!ilSession::get('rep_search_type')) {
            ilSession::set('rep_search_type', 'usr');
        }
        $this->search_type = (string) ilSession::get('rep_search_type');
        return true;
    }


    public function __updateResults() : bool
    {
        if (!ilSession::get('search_append')) {
            ilSession::set('rep_search', []);
        }
        $rep_search = ilSession::get('rep_search') ?? [];
        foreach ($this->search_results as $result) {
            $rep_search[$this->search_type][] = $result;
        }
        if (!$rep_search[$this->search_type]) {
            $rep_search[$this->search_type] = [];
        } else {
            $rep_search[$this->search_type] = array_unique($rep_search[$this->search_type]);
        }
        ilSession::set('rep_search', $rep_search);
        return true;
    }

    /**
     * @return int[]
     */
    public function __appendToStoredResults(array $a_usr_ids) : array
    {
        if (!ilSession::get('search_append')) {
            ilSession::set('rep_search', ['usr' => $a_usr_ids]);
        }
        $rep_search = ilSession::get('rep_search') ?? [];
        foreach ($a_usr_ids as $usr_id) {
            $rep_search['usr'][] = $usr_id;
        }
        $rep_search['usr'] = array_unique($rep_search['usr'] ?? []);
        ilSession::set('rep_search', $rep_search);
        return $rep_search['usr'];
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

        $rep_search = ilSession::get('rep_search');

        switch ($this->search_type) {
            case "usr":
                $this->showSearchUserTable($rep_search['usr'] ?? [], 'showSearchResults');
                break;

            case 'grp':
                $this->showSearchGroupTable($rep_search['grp'] ?? []);
                break;

            case 'crs':
                $this->showSearchCourseTable($rep_search['crs'] ?? []);
                break;

            case 'role':
                $this->showSearchRoleTable($rep_search['role'] ?? []);
                break;
        }
    }

    protected function showSearchUserTable(array $a_usr_ids, string $a_parent_cmd) : void
    {
        $base_class = '';
        if ($this->http->wrapper()->query()->has('baseClass')) {
            $base_class = $this->http->wrapper()->query()->retrieve(
                'baseClass',
                $this->refinery->kindlyTo()->string()
            );
        }
        $is_in_admin = $base_class === ilAdministrationGUI::class;
        if ($is_in_admin) {
            // remember link target to admin search gui (this)
            ilSession::set('usr_search_link', $this->ctrl->getLinkTarget($this, 'show'));
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

    protected function listUsers(array $selected_entries = []) : bool
    {
        // get parameter is used e.g. in exercises to provide
        // "add members of course" link
        if (
            $this->http->wrapper()->post()->has('obj') &&
            !count($selected_entries)
        ) {
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
        $rep_search = ilSession::get('rep_search') ?? [];
        $rep_search['objs'] = $selected_entries;
        ilSession::set('rep_search', $rep_search);

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
        $rep_search = ilSession::get('rep_search');
        $this->showSearchUserTable($rep_search['usr'] ?? [], 'storedUserList');
        return true;
    }
    
    protected function storedUserList() : bool
    {
        $rep_search = ilSession::get('rep_search');
        $objects = $rep_search['objs'] ?? [];
        $this->listUsers($objects);
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
