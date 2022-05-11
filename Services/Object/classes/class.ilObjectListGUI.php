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
 
use ILIAS\Repository\Clipboard\ClipboardManager;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Card\RepositoryObject;
use ILIAS\UI\Component\Item\Item;
use ILIAS\Notes\Note;

/**
 * Important note:
 *
 * All access checking should be made within $ilAccess and
 * the checkAccess of the ilObj...Access classes. Do not additionally
 * enable or disable any commands within this GUI class or in derived
 * classes, except when the container (e.g. a search result list)
 * generally refuses them.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjectListGUI
{
    const IL_LIST_AS_TRIGGER = "trigger";
    const IL_LIST_FULL = "full";

    const DETAILS_MINIMAL = 10;
    const DETAILS_SEARCH = 20 ;
    const DETAILS_ALL = 30;

    const CONTEXT_REPOSITORY = 1;
    const CONTEXT_WORKSPACE = 2;
    const CONTEXT_WORKSPACE_SHARING = 4;
    const CONTEXT_PERSONAL_DESKTOP = 5;
    const CONTEXT_SEARCH = 6;

    const DOWNLOAD_CHECKBOX_NONE = 0;
    const DOWNLOAD_CHECKBOX_ENABLED = 1;
    const DOWNLOAD_CHECKBOX_DISABLED = 2;

    protected static array $cnt_notes = [];
    protected static array $cnt_tags = [];
    protected static array $tags = [];
    protected static array $comments_activation = [];
    protected static bool $preload_done = false;
    protected static int $js_unique_id = 0;
    protected static string $tpl_file_name = "tpl.container_list_item.html";
    protected static string $tpl_component = "Services/Container";
    private \ILIAS\Notes\Service $notes_service;

    protected array $access_cache;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected ilSetting $settings;
    protected UIServices $ui;
    protected ilRbacSystem $rbacsystem;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected string $mode;
    protected bool $path_enabled;
    protected int $context;
    protected ilObjectService $object_service;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected bool $static_link_enabled = false;
    protected bool $delete_enabled = false;
    protected bool $cut_enabled = false;
    protected bool $subscribe_enabled = false;
    protected bool $link_enabled = false;
    protected bool $copy_enabled = true;
    protected bool $progress_enabled = false;
    protected bool $notice_properties_enabled = true;
    protected bool $info_screen_enabled = false;
    protected string $type;
    protected string $gui_class_name = "";
    protected array $commands = [];

    protected ?ilLDAPRoleGroupMapping $ldap_mapping;
    protected ilFavouritesManager $fav_manager;
    protected int $requested_ref_id;
    protected string $requested_cmd;
    protected string $requested_base_class;
    protected ClipboardManager $clipboard;


    protected bool $description_enabled = true;
    protected bool $preconditions_enabled = true;
    protected bool $properties_enabled = true;
    protected bool $commands_enabled = true;
    protected array $cust_prop = [];
    /** @var Button[]|array[] */
    protected array $cust_commands = [];
    /** @var Modal[] */
    protected array $cust_modals = [];
    protected int $condition_depth = 0;
    protected bool $std_cmd_only = false;
    protected array $sub_item_html = [];
    protected bool $multi_download_enabled = false;
    protected int $download_checkbox_state = self::DOWNLOAD_CHECKBOX_NONE;
    protected int $obj_id;
    protected int $ref_id;
    protected int $sub_obj_id;
    protected ?string $sub_obj_type;
    protected ?ilAdvancedMDSubstitution $substitutions = null;
    protected bool $substitutions_enabled = false;
    protected bool $icons_enabled = false;
    protected bool $checkboxes_enabled = false;
    protected bool $position_enabled = false;
    protected bool $item_detail_links_enabled = false;
    protected array $item_detail_links = [];
    protected string $item_detail_links_intro = '';
    protected bool $search_fragments_enabled = false;
    protected string $search_fragment = '';
    protected bool $path_linked = false;
    protected bool $enabled_relevance = false;
    protected int $relevance = 0;
    protected bool $expand_enabled = false;
    protected bool $is_expanded = true;
    protected bool $bold_title = false;
    protected int $details_level = self::DETAILS_ALL;
    protected int $reference_ref_id = 0;
    protected ?int $reference_obj_id = null;
    protected bool $separate_commands = false;
    protected bool $search_fragment_enabled = false;
    protected ?string $additional_information = "";
    protected bool $repository_transfer_enabled = false;
    protected bool $shared = false;
    protected bool $restrict_to_goto = false;
    protected bool $comments_enabled = false;
    protected bool $comments_settings_enabled = false;
    protected bool $notes_enabled = false;
    protected bool $tags_enabled = false;
    protected bool $rating_enabled = false;
    protected bool $rating_categories_enabled = false;
    protected ?string $rating_text = null;
    protected ?array $rating_ctrl_path = null;
    protected bool $timings_enabled = true;
    protected bool $force_visible_only = false;
    protected array $prevent_duplicate_commands = [];
    protected int $parent_ref_id;
    protected string $title_link = '';
    protected bool $title_link_disabled = false;
    protected bool $lp_cmd_enabled = false;
    protected ilAdvancedSelectionListGUI $current_selection_list;
    protected ?ilPathGUI $path_gui = null;
    protected array $default_command_params = [];
    protected array $header_icons = [];
    protected ?object $container_obj = null;
    protected ilTemplate $tpl;
    protected string $position_value;
    protected int $path_start_node;
    protected array $default_command = [];
    protected bool $adm_commands_included;
    protected bool $prevent_access_caching;
    protected array $condition_target;
    protected array $notice_prop = [];
    protected string $ajax_hash;
    protected ilListItemAccessCache $acache;
    protected string $position_field_index = "";
    protected string $title = "";
    protected string $description = "";
    protected ilWorkspaceAccessHandler $ws_access;
    
    public function __construct(int $context = self::CONTEXT_REPOSITORY)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->ui = $DIC->ui();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->mode = self::IL_LIST_FULL;
        $this->path_enabled = false;
        $this->context = $context;
        $this->object_service = $DIC->object();
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        
        $this->enableComments(false);
        $this->enableNotes(false);
        $this->enableTags(false);
        
        // unique js-ids
        $this->setParentRefId((int) ($_REQUEST["ref_id"] ?? 0));

        $this->init();
        
        $this->ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
        $this->fav_manager = new ilFavouritesManager();

        $this->lng->loadLanguageModule("obj");
        $this->lng->loadLanguageModule("rep");
        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? null);
        $this->requested_cmd = (string) ($params["cmd"] ?? null);
        $this->requested_base_class = (string) ($params["baseClass"] ?? null);
        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();
        $this->notes_service = $DIC->notes();
    }

    public function setContainerObject(object $container_obj) : void
    {
        $this->container_obj = $container_obj;
    }
    
    public function getContainerObject() : ?object
    {
        return $this->container_obj;
    }


    /**
    * initialisation
    *
    * this method should be overwritten by derived classes
    */
    public function init() : void
    {
        // Create static links for default command (linked title) or not
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->copy_enabled = false;
        $this->progress_enabled = false;
        $this->notice_properties_enabled = true;
        $this->info_screen_enabled = false;
        $this->type = "";					// "cat", "course", ...
        $this->gui_class_name = "";			// "ilobjcategorygui", "ilobjcoursegui", ...

        // general commands array, e.g.
        $this->commands = ilObjectAccess::_getCommands();
    }

    public function enableProperties(bool $status) : void
    {
        $this->properties_enabled = $status;
    }

    public function getPropertiesStatus() : bool
    {
        return $this->properties_enabled;
    }

    public function enablePreconditions(bool $status) : void
    {
        $this->preconditions_enabled = $status;
    }

    public function getPreconditionsStatus() : bool
    {
        return $this->preconditions_enabled;
    }

    public function enableNoticeProperties(bool $status) : void
    {
        $this->notice_properties_enabled = $status;
    }

    public function getNoticePropertiesStatus() : bool
    {
        return $this->notice_properties_enabled;
    }

    public function enableDescription(bool $status) : void
    {
        $this->description_enabled = $status;
    }

    public function getDescriptionStatus() : bool
    {
        return $this->description_enabled;
    }

    public function enableSearchFragments(bool $status) : void
    {
        $this->search_fragment_enabled = $status;
    }
    
    public function getSearchFragmentStatus() : bool
    {
        return $this->search_fragment_enabled;
    }

    public function enableLinkedPath(bool $status) : void
    {
        $this->path_linked = $status;
    }

    public function enableRelevance(bool $status) : void
    {
        $this->enabled_relevance = $status;
    }
    
    public function enabledRelevance() : bool
    {
        return $this->enabled_relevance;
    }
    
    public function setRelevance(int $rel) : void
    {
        $this->relevance = $rel;
    }
    
    public function getRelevance() : int
    {
        return $this->relevance;
    }
    
    public function enableIcon(bool $status) : void
    {
        $this->icons_enabled = $status;
    }
    
    public function getIconStatus() : bool
    {
        return $this->icons_enabled;
    }
    
    public function enableCheckbox(bool $status) : void
    {
        $this->checkboxes_enabled = $status;
    }
    
    public function getCheckboxStatus() : bool
    {
        return $this->checkboxes_enabled;
    }
    
    public function enableExpand(bool $status) : void
    {
        $this->expand_enabled = $status;
    }
    
    public function getExpandStatus() : bool
    {
        return $this->expand_enabled;
    }
    
    public function setExpanded(bool $status) : void
    {
        $this->is_expanded = $status;
    }
    
    public function isExpanded() : bool
    {
        return $this->is_expanded;
    }
    /**
     * @param string	$field_index e.g. "[crs][34]"
     * @param string	$position_value	e.g. "2.0"
     */
    public function setPositionInputField(string $field_index, string $position_value) : void
    {
        $this->position_enabled = true;
        $this->position_field_index = $field_index;
        $this->position_value = $position_value;
    }

    public function enableDelete(bool $status) : void
    {
        $this->delete_enabled = $status;
    }

    public function getDeleteStatus() : bool
    {
        return $this->delete_enabled;
    }

    public function enableCut(bool $status) : void
    {
        $this->cut_enabled = $status;
    }

    public function getCutStatus() : bool
    {
        return $this->cut_enabled;
    }
    
    public function enableCopy(bool $status) : void
    {
        $this->copy_enabled = $status;
    }

    public function getCopyStatus() : bool
    {
        return $this->copy_enabled;
    }

    public function enableSubscribe(bool $status) : void
    {
        $this->subscribe_enabled = $status;
    }

    public function getSubscribeStatus() : bool
    {
        return $this->subscribe_enabled;
    }

    public function enableLink(bool $status) : void
    {
        $this->link_enabled = $status;
    }

    public function getLinkStatus() : bool
    {
        return $this->link_enabled;
    }

    public function enablePath(bool $path, int $start_node = 0, \ilPathGUI $path_gui = null) : void
    {
        $this->path_enabled = $path;
        $this->path_start_node = $start_node;
        $this->path_gui = $path_gui;
    }

    public function getPathStatus() : bool
    {
        return $this->path_enabled;
    }
    
    public function enableCommands(bool $status, bool $std_only = false) : void
    {
        $this->commands_enabled = $status;
        $this->std_cmd_only = $std_only;
    }

    public function getCommandsStatus() : bool
    {
        return $this->commands_enabled;
    }

    public function enableInfoScreen(bool $info_screen) : void
    {
        $this->info_screen_enabled = $info_screen;
    }

    public function getInfoScreenStatus() : bool
    {
        return $this->info_screen_enabled;
    }

    protected function enableLearningProgress(bool $enabled) : void
    {
        $this->lp_cmd_enabled = $enabled;
    }

    /**
    * Add HTML for sub item (used for sessions)
    *
    * @param string	$html sub items HTML
    */
    public function addSubItemHTML(string $html) : void
    {
        $this->sub_item_html[] = $html;
    }
    
    public function enableProgressInfo(bool $status) : void
    {
        $this->progress_enabled = $status;
    }
    
    public function getProgressInfoStatus() : bool
    {
        return $this->progress_enabled;
    }
    
    public function enableSubstitutions(bool $status) : void
    {
        $this->substitutions_enabled = $status;
    }
    
    public function getSubstitutionStatus() : bool
    {
        return $this->substitutions_enabled;
    }
    
    /**
     * enable item detail links
     * E.g Direct links to chapters or pages
     */
    public function enableItemDetailLinks(bool $status) : void
    {
        $this->item_detail_links_enabled = $status;
    }
    
    /**
     * get item detail link status
     */
    public function getItemDetailLinkStatus() : bool
    {
        return $this->item_detail_links_enabled;
    }
    
    /**
     * set items detail links
     *
     * @param array $detail_links e.g. array(0 => array('desc' => 'Page: ','link' => 'ilias.php...','name' => 'Page XYZ')
     */
    public function setItemDetailLinks(array $detail_links, string $intro_txt = '') : void
    {
        $this->item_detail_links = $detail_links;
        $this->item_detail_links_intro = $intro_txt;
    }
    
    public function insertItemDetailLinks() : void
    {
        if (!count($this->item_detail_links)) {
            return;
        }
        if (strlen($this->item_detail_links_intro)) {
            $this->tpl->setCurrentBlock('item_detail_intro');
            $this->tpl->setVariable('ITEM_DETAIL_INTRO_TXT', $this->item_detail_links_intro);
            $this->tpl->parseCurrentBlock();
        }
        
        foreach ($this->item_detail_links as $info) {
            $this->tpl->setCurrentBlock('item_detail_link');
            $this->tpl->setVariable('ITEM_DETAIL_LINK_TARGET', $info['target']);
            $this->tpl->setVariable('ITEM_DETAIL_LINK_DESC', $info['desc']);
            $this->tpl->setVariable('ITEM_DETAIL_LINK_HREF', $info['link']);
            $this->tpl->setVariable('ITEM_DETAIL_LINK_NAME', $info['name']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock('item_detail_links');
        $this->tpl->parseCurrentBlock();
    }
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    /**
     * getTitle overwritten in class.ilObjLinkResourceList.php
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    /**
     * getDescription overwritten in class.ilObjLinkResourceList.php
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * @param string $text highlighted search fragment
     */
    public function setSearchFragment(string $text) : void
    {
        $this->search_fragment = $text;
    }
    
    public function getSearchFragment() : string
    {
        return $this->search_fragment;
    }
    
    public function setSeparateCommands(bool $val) : void
    {
        $this->separate_commands = $val;
    }
    
    public function getSeparateCommands() : bool
    {
        return $this->separate_commands;
    }

    /**
     * get command id
     * Normally the ref id.
     * Overwritten for course and category references
     */
    public function getCommandId() : int
    {
        return $this->ref_id;
    }
    
    public function setAdditionalInformation(?string $val) : void
    {
        $this->additional_information = $val;
    }
    
    public function getAdditionalInformation() : ?string
    {
        return $this->additional_information;
    }
    
    /**
     * Details level
     * Currently used in Search which shows only limited properties of forums
     * Currently used for Sessions (switch between minimal and extended view for each session)
     */
    public function setDetailsLevel(int $level) : void
    {
        $this->details_level = $level;
    }
    
    public function getDetailsLevel() : int
    {
        return $this->details_level;
    }
    
    /**
     * Enable copy/move to repository (from personal workspace)
     */
    public function enableRepositoryTransfer(bool $value) : void
    {
        $this->repository_transfer_enabled = $value;
    }
    
    /**
     * Restrict all actions/links to goto
     */
    public function restrictToGoto(bool $value) : void
    {
        $this->restrict_to_goto = $value;
    }

    public function getDefaultCommand() : array
    {
        return $this->default_command;
    }

    public function checkCommandAccess(
        string $permission,
        string $cmd,
        int $ref_id,
        string $type,
        ?int $obj_id = null
    ) : bool {
        // e.g: sub items should not be readable since their parent session is readonly.
        if ($permission != 'visible' and $this->isVisibleOnlyForced()) {
            return false;
        }

        $cache_prefix = null;
        if ($this->context == self::CONTEXT_WORKSPACE || $this->context == self::CONTEXT_WORKSPACE_SHARING) {
            $cache_prefix = "wsp";
            if (!isset($this->ws_access)) {
                $this->ws_access = new ilWorkspaceAccessHandler();
            }
        }

        if (isset($this->access_cache[$permission]["-" . $cmd][$cache_prefix . $ref_id])) {
            return $this->access_cache[$permission]["-" . $cmd][$cache_prefix . $ref_id];
        }

        if ($this->context == self::CONTEXT_REPOSITORY) {
            $access = $this->access->checkAccess($permission, $cmd, $ref_id, $type, (int) $obj_id);
            if ($this->access->getPreventCachingLastResult()) {
                $this->prevent_access_caching = true;
            }
        } else {
            $access = $this->ws_access->checkAccess($permission, $cmd, $ref_id, $type);
        }

        $this->access_cache[$permission]["-" . $cmd][$cache_prefix . $ref_id] = $access;
        return $access;
    }
    
    /**
     * initialize new item (is called by getItemHTML())
     */
    public function initItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title = "",
        string $description = ""
    ) : void {
        $this->access_cache = array();
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
        $this->setTitle($title);
        $this->setDescription($description);
        
        // checks, whether any admin commands are included in the output
        $this->adm_commands_included = false;
        $this->prevent_access_caching = false;

        // prepare ajax calls
        if ($this->context == self::CONTEXT_REPOSITORY) {
            $node_type = ilCommonActionDispatcherGUI::TYPE_REPOSITORY;
        } else {
            $node_type = ilCommonActionDispatcherGUI::TYPE_WORKSPACE;
        }
        $this->setAjaxHash(ilCommonActionDispatcherGUI::buildAjaxHash($node_type, $ref_id, $type, $obj_id));
    }

    public function setConditionTarget(int $ref_id, int $obj_id, string $target_type) : void
    {
        $this->condition_target = [
            'ref_id' => $ref_id,
            'obj_id' => $obj_id,
            'target_type' => $target_type
        ];
    }
    
    public function resetConditionTarget() : void
    {
        $this->condition_target = [];
    }
    
    public function disableTitleLink(bool $status) : void
    {
        $this->title_link_disabled = $status;
    }

    public function setDefaultCommandParameters(array $params) : void
    {
        $this->default_command_params = $params;
    }
    
    /**
     * Get default command link
     * Overwritten for e.g categories,courses => they return a goto link
     * If search engine visibility is enabled these object type return a goto_CLIENT_ID_cat_99.html link
     */
    public function createDefaultCommand(array $command) : array
    {
        if ($this->static_link_enabled and !$this->default_command_params) {
            if ($link = ilLink::_getStaticLink($this->ref_id, $this->type, false)) {
                $command['link'] = $link;
                $command['frame'] = '_top';
            }
        }
        if ($this->default_command_params) {
            $params = array();
            foreach ($this->default_command_params as $name => $value) {
                $params[] = $name . '=' . $value;
            }
            $params = implode('&', $params);
            
            if (!stristr($command['link'], '?')) {
                $command['link'] .= '?' . $params;
            } else {
                $command['link'] .= '&' . $params;
            }
        }
        return $command;
    }

    /**
    * Get command link url.
    *
    * Overwrite this method, if link target is not build by ctrl class
    * (e.g. "forum.php"). This is the case
    * for all links now, but bringing everything to ilCtrl should
    * be realised in the future.
    */
    public function getCommandLink(string $cmd) : string
    {
        if ($this->context == self::CONTEXT_REPOSITORY) {
            // BEGIN WebDAV Get mount webfolder link.
            if ($cmd == 'mount_webfolder' && ilDAVActivationChecker::_isActive()) {
                global $DIC;
                $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                return $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
            }
            // END WebDAV Get mount webfolder link.

            $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $cmd);
            $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);
            return $cmd_link;
        }

        $this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", "");
        $this->ctrl->setParameterByClass($this->gui_class_name, "wsp_id", $this->ref_id);
        return $this->ctrl->getLinkTargetByClass($this->gui_class_name, $cmd);
    }

    /**
    * Get command target frame.
    *
    * Overwrite this method if link frame is not current frame
    *
    * @param string	$cmd command
    * @return string command target frame
    */
    public function getCommandFrame(string $cmd) : string
    {
        return "";
    }

    /**
    * Get command icon image
    *
    * Overwrite this method if an icon is provided
    *
    * @param string	$cmd command
    * @return string image path
    */
    public function getCommandImage(string $cmd) : string
    {
        return "";
    }

    /**
    * Get item properties
    *
    * Overwrite this method to add properties at
    * the bottom of the item html
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties() : array
    {
        $props = [];
        // please list alert properties first
        // example (use $this->lng->txt instead of "Status"/"Offline" strings):
        // $props[] = array("alert" => true, "property" => "Status", "value" => "Offline");
        // $props[] = array("alert" => false, "property" => ..., "value" => ...);
        // ...
        
        // #8280: WebDav is only supported in repository
        if ($this->context == self::CONTEXT_REPOSITORY) {
            // add centralized offline status
            if (ilObject::lookupOfflineStatus($this->obj_id)) {
                $props[] =
                    [
                        'alert' => true,
                        'property' => $this->lng->txt("status"),
                        'value' => $this->lng->txt("offline")
                    ];
            }

            // BEGIN WebDAV Display locking information
            if (ilDAVActivationChecker::_isActive()) {
                // Show lock info
                $webdav_dic = new ilWebDAVDIC();
                $webdav_dic->initWithoutDIC();
                $webdav_lock_backend = $webdav_dic->locksbackend();
                if ($this->user->getId() !== ANONYMOUS_USER_ID) {
                    if ($lock = $webdav_lock_backend->getLocksOnObjectId($this->obj_id)) {
                        $lock_user = new ilObjUser($lock->getIliasOwner());

                        $props[] = [
                            "alert" => false,
                            "property" => $this->lng->txt("in_use_by"),
                            "value" => $lock_user->getLogin(),
                            "link" =>
                                "./ilias.php?user=" .
                                $lock_user->getId() .
                                '&cmd=showUserProfile&cmdClass=ildashboardgui&baseClass=ilDashboardGUI'
                        ];
                    }
                }
            }
            // END WebDAV Display warning for invisible files and files with special characters
        }
        
        return $props;
    }
    
    public function addCustomProperty(
        string $property = "",
        string $value = "",
        bool $alert = false,
        bool $newline = false
    ) : void {
        $this->cust_prop[] = [
            "property" => $property,
            "value" => $value,
            "alert" => $alert,
            "newline" => $newline
        ];
    }
    
    public function getCustomProperties(array $prop) : array
    {
        if (is_array($this->cust_prop)) {
            foreach ($this->cust_prop as $property) {
                $prop[] = $property;
            }
        }
        return $prop;
    }

    public function getAlertProperties() : array
    {
        $alert = [];
        foreach ($this->getProperties() as $prop) {
            if (isset($prop['alert']) && $prop['alert'] == true) {
                $alert[] = $prop;
            }
        }
        return $alert;
    }
    
    public function getNoticeProperties() : array
    {
        $this->notice_prop = [];
        if ($infos = $this->ldap_mapping->getInfoStrings($this->obj_id, true)) {
            foreach ($infos as $info) {
                $this->notice_prop[] = ['value' => $info];
            }
        }
        return $this->notice_prop;
    }

    public function addCustomCommand(string $link, string $lang_var, string $frame = "", string $onclick = "") : void
    {
        $this->cust_commands[] = [
            "link" => $link,
            "lang_var" => $lang_var,
            "frame" => $frame,
            "onclick" => $onclick
        ];
    }

    public function addCustomCommandButton(
        Button $button,
        ?Modal $triggeredModal = null
    ) : void {
        $this->cust_commands[] = $button;
        if ($triggeredModal !== null) {
            $this->cust_modals[] = $triggeredModal;
        }
    }
    
    public function forceVisibleOnly(bool $stat) : void
    {
        $this->force_visible_only = $stat;
    }

    public function isVisibleOnlyForced() : bool
    {
        return $this->force_visible_only;
    }

    /**
    * get all current commands for a specific ref id (in the permission
    * context of the current user)
    *
    * !!!NOTE!!!: Please use getListHTML() if you want to display the item
    * including all commands
    *
    * !!!NOTE 2!!!: Please do not overwrite this method in derived
    * classes because it will get pretty large and much code will be simply
    * copy-and-pasted. Insert smaller object type related method calls instead.
    * (like getCommandLink() or getCommandFrame())
    *
    * @access	public
    * @param	int		$a_ref_id		ref id of object
    * @return	array	array of command arrays including
    *					"permission" => permission name
    *					"cmd" => command
    *					"link" => command link url
    *					"frame" => command link frame
    *					"lang_var" => language variable of command
    *					"granted" => true/false: command granted or not
    *					"access_info" => access info object (to do: implementation)
    */
    public function getCommands() : array
    {
        $ref_commands = [];
        foreach ($this->commands as $command) {
            $permission = $command["permission"];
            $cmd = $command["cmd"];
            $lang_var = $command["lang_var"];
            $txt = "";
            $info_object = null;
            $cmd_link = '';
            $cmd_frame = '';
            $cmd_image = '';
            $access_granted = false;

            if (isset($command["txt"])) {
                $txt = $command["txt"];
            }

            // Suppress commands that don't make sense for anonymous users
            if (
                $this->user->getId() == ANONYMOUS_USER_ID &&
                (isset($command['enable_anonymous']) && $command['enable_anonymous'] == 'false')
            ) {
                continue;
            }

            // all access checking should be made within $this->access and
            // the checkAccess of the ilObj...Access classes
            // $access = $this->access->checkAccess($permission, $cmd, $this->ref_id, $this->type);
            $access = $this->checkCommandAccess($permission, $cmd, $this->ref_id, $this->type);

            if ($access) {
                $access_granted = true;
                if (ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION === $cmd) {
                    global $DIC;
                    $file_obj = new ilObjFile($this->ref_id);
                    $file_rid = $DIC->resourceStorage()->manage()->find($file_obj->getResourceId());
                    if (null !== $file_rid &&
                        'application/zip' === $DIC->resourceStorage()
                                                  ->manage()
                                                  ->getCurrentRevision($file_rid)
                                                  ->getInformation()
                                                  ->getMimeType()
                    ) {
                        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->ref_id);
                        $cmd_link = $DIC->ctrl()->getLinkTargetByClass(
                            ilRepositoryGUI::class,
                            ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION
                        );
                        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->requested_ref_id);
                    } else {
                        $access_granted = false;
                    }
                } else {
                    $cmd_link = $this->getCommandLink($command["cmd"]);
                }

                $cmd_frame = $this->getCommandFrame($command["cmd"]);
                $cmd_image = $this->getCommandImage($command["cmd"]);
            } else {
                $info_object = $this->access->getInfo();
            }

            if (!isset($command["default"])) {
                $command["default"] = "";
            }
            $ref_commands[] = [
                "permission" => $permission,
                "cmd" => $cmd,
                "link" => $cmd_link,
                "frame" => $cmd_frame,
                "lang_var" => $lang_var,
                "txt" => $txt,
                "granted" => $access_granted,
                "access_info" => $info_object,
                "img" => $cmd_image,
                "default" => $command["default"]
            ];
        }

        return $ref_commands;
    }

    /**
    * Returns the icon image type.
    * For most objects, this is same as the object type, e.g. 'cat','fold'.
    * We can return here other values, to express a specific state of an object,
    * e.g. 'crs_offline', and/or to express a specific kind of object, e.g.
    * 'file_inline'.
    */
    public function getIconImageType() : string
    {
        return $this->type;
    }

    public function insertTitle() : void
    {
        if ($this->restrict_to_goto) {
            $this->default_command = [
                "frame" => "",
                "link" => $this->buildGotoLink()
            ];
        }
        // begin-patch lok
        if (
            !$this->default_command ||
            (!$this->getCommandsStatus() && !$this->restrict_to_goto) ||
            $this->title_link_disabled
        ) {
            // end-patch lok
            $this->tpl->setCurrentBlock("item_title");
            $this->tpl->setVariable("TXT_TITLE", $this->getTitle());
        } else {
            $this->default_command['link'] = $this->modifyTitleLink($this->default_command['link']);
            
            $this->default_command["link"] =
                $this->modifySAHSlaunch($this->default_command["link"], $this->default_command["frame"]);

            if ($this->default_command["frame"] != "") {
                $this->tpl->setCurrentBlock("title_linked_frame");
                $this->tpl->setVariable("TARGET_TITLE_LINKED", $this->default_command["frame"]);
                $this->tpl->parseCurrentBlock();
            }
            
            // workaround for repository frameset
            $this->default_command["link"] = $this->appendRepositoryFrameParameter($this->default_command["link"]);

            // the default command is linked with the title
            $this->tpl->setCurrentBlock("item_title_linked");
            $this->tpl->setVariable("TXT_TITLE_LINKED", $this->getTitle());
            $this->tpl->setVariable("HREF_TITLE_LINKED", $this->default_command["link"]);
            
            // has preview?
            if (ilPreview::hasPreview($this->obj_id, $this->type)) {

                // get context for access checks later on
                switch ($this->context) {
                    case self::CONTEXT_WORKSPACE:
                    case self::CONTEXT_WORKSPACE_SHARING:
                        $context = ilPreviewGUI::CONTEXT_WORKSPACE;
                        $access_handler = new ilWorkspaceAccessHandler();
                        break;
                    
                    default:
                        $ilAccess = $this->access;
                        $context = ilPreviewGUI::CONTEXT_REPOSITORY;
                        $access_handler = $ilAccess;
                        break;
                }
                
                $preview = new ilPreviewGUI($this->ref_id, $context, $this->obj_id, $access_handler);
                $preview_status = ilPreview::lookupRenderStatus($this->obj_id);
                $preview_status_class = "";
                $preview_text_topic = "preview_show";
                if ($preview_status == ilPreview::RENDER_STATUS_NONE) {
                    $preview_status_class = "ilPreviewStatusNone";
                    $preview_text_topic = "preview_none";
                }
                $this->tpl->setCurrentBlock("item_title_linked");
                $this->tpl->setVariable("PREVIEW_STATUS_CLASS", $preview_status_class);
                $this->tpl->setVariable("SRC_PREVIEW_ICON", ilUtil::getImagePath("preview.png"));
                $this->tpl->setVariable("ALT_PREVIEW_ICON", $this->lng->txt($preview_text_topic));
                $this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt($preview_text_topic));
                $this->tpl->setVariable("SCRIPT_PREVIEW_CLICK", $preview->getJSCall($this->getUniqueItemId(true)));
                $this->tpl->parseCurrentBlock();
            }
        }
        $this->tpl->parseCurrentBlock();

        if ($this->bold_title == true) {
            $this->tpl->touchBlock('bold_title_start');
            $this->tpl->touchBlock('bold_title_end');
        }
    }
    
    protected function buildGotoLink() : ?string
    {
        switch ($this->context) {
            case self::CONTEXT_WORKSPACE_SHARING:
                return ilWorkspaceAccessHandler::getGotoLink($this->ref_id, $this->obj_id);
            
            default:
                // not implemented yet
                break;
        }
        return null;
    }
    
    public function insertSubstitutions() : void
    {
        $fields_shown = false;
        foreach ($this->substitutions->getParsedSubstitutions($this->ref_id, $this->obj_id) as $data) {
            if ($data['bold']) {
                $data['name'] = '<strong>' . $data['name'] . '</strong>';
                $data['value'] = '<strong>' . $data['value'] . '</strong>';
            }
            $this->tpl->touchBlock("std_prop");
            $this->tpl->setCurrentBlock('item_property');
            if ($data['show_field']) {
                $this->tpl->setVariable('TXT_PROP', $data['name']);
            }
            $this->tpl->setVariable('VAL_PROP', $data['value']);
            $this->tpl->parseCurrentBlock();

            if ($data['newline']) {
                $this->tpl->touchBlock('newline_prop');
            }
            $fields_shown = false;
        }
    }

    public function insertDescription() : void
    {
        if ($this->getSubstitutionStatus()) {
            $this->insertSubstitutions();
            if (!$this->substitutions->isDescriptionEnabled()) {
                return;
            }
        }

        // see bug #16519
        $d = $this->getDescription();
        // even b tag produced bugs, see #32304
        $d = strip_tags($d);
        $this->tpl->setCurrentBlock("item_description");
        $this->tpl->setVariable("TXT_DESC", $d);
        $this->tpl->parseCurrentBlock();
    }
    
    /**
     * Insert highlighted search fragment
     */
    public function insertSearchFragment() : void
    {
        if (strlen($this->getSearchFragment())) {
            $this->tpl->setCurrentBlock('search_fragment');
            $this->tpl->setVariable('TXT_SEARCH_FRAGMENT', $this->getSearchFragment() . ' ...');
            $this->tpl->parseCurrentBlock();
        }
    }
    
    public function insertRelevance() : void
    {
        if (!$this->enabledRelevance() or !$this->getRelevance()) {
            return;
        }
        
        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($this->getRelevance());
        
        $this->tpl->setCurrentBlock('relevance');
        $this->tpl->setVariable('REL_PBAR', $pbar->render());
        $this->tpl->parseCurrentBlock();
    }

    /**
     * set output mode
     *
     * @param string $mode output mode (self::IL_LIST_FULL | self::IL_LIST_AS_TRIGGER)
     */
    public function setMode(string $mode) : void
    {
        $this->mode = $mode;
    }

    /**
     * get output mode
     *
     * @return string output mode (self::IL_LIST_FULL | self::IL_LIST_AS_TRIGGER)
     */
    public function getMode() : string
    {
        return $this->mode;
    }
    
    /**
     * set depth for precondition output (stops at level 5)
     */
    public function setConditionDepth(int $depth) : void
    {
        $this->condition_depth = $depth;
    }

    /**
    * check current output mode
    *
    * @param string	$mode (self::IL_LIST_FULL | self::IL_LIST_AS_TRIGGER)
    * @return bool true if current mode is $a_mode
    */
    public function isMode(string $mode) : bool
    {
        return $mode === $this->mode;
    }

    public function determineProperties() : array
    {
        $props = $this->getProperties();
        $props = $this->getCustomProperties($props);

        if ($this->context != self::CONTEXT_WORKSPACE && $this->context != self::CONTEXT_WORKSPACE_SHARING) {
            // add learning progress custom property
            $lp = ilLPStatus::getListGUIStatus($this->obj_id);
            if ($lp) {
                $props[] = [
                    "alert" => false,
                    "property" => $this->lng->txt("learning_progress"),
                    "value" => $lp,
                    "newline" => true
                ];
            }

            // add no item access note in public section
            // for items that are visible but not readable
            if ($this->user->getId() === ANONYMOUS_USER_ID) {
                if (!$this->access->checkAccess("read", "", $this->ref_id, $this->type, $this->obj_id)) {
                    $props[] = [
                        "alert" => true,
                        "value" => $this->lng->txt("no_access_item_public"),
                        "newline" => true
                    ];
                }
            }
        }

        // reference objects have translated ids, revert to originals
        $note_ref_id = $this->ref_id;
        $note_obj_id = $this->obj_id;
        if ($this->reference_ref_id) {
            $note_ref_id = $this->reference_ref_id;
            $note_obj_id = $this->reference_obj_id;
        }
        $redraw_js = "il.Object.redrawListItem(" . $note_ref_id . ");";

        // add common properties (comments, notes, tags)
        if (
            (
                (
                    isset(self::$cnt_notes[$note_obj_id][Note::PRIVATE]) &&
                    self::$cnt_notes[$note_obj_id][Note::PRIVATE] > 0
                ) || (
                    isset(self::$cnt_notes[$note_obj_id][Note::PUBLIC]) &&
                    self::$cnt_notes[$note_obj_id][Note::PUBLIC] > 0
                ) || (
                    isset(self::$cnt_tags[$note_obj_id]) && self::$cnt_tags[$note_obj_id] > 0
                ) || (
                    isset(self::$tags[$note_obj_id]) && is_array(self::$tags[$note_obj_id])
                )
            ) && ($this->user->getId() !== ANONYMOUS_USER_ID)
        ) {
            $nl = true;
            if ($this->isCommentsActivated($this->type, $this->ref_id, $this->obj_id, false, false)
                && self::$cnt_notes[$note_obj_id][Note::PUBLIC] > 0) {
                $props[] = [
                    "alert" => false,
                    "property" => $this->lng->txt("notes_comments"),
                    "value" =>
                        "<a href='#' onclick=\"return " .
                        ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $redraw_js) . "\">" .
                        self::$cnt_notes[$note_obj_id][Note::PUBLIC] . "</a>",
                    "newline" => $nl
                ];
                $nl = false;
            }

            if ($this->notes_enabled && self::$cnt_notes[$note_obj_id][Note::PRIVATE] > 0) {
                $props[] = [
                    "alert" => false,
                    "property" => $this->lng->txt("notes"),
                    "value" =>
                        "<a href='#' onclick=\"return " .
                        ilNoteGUI::getListNotesJSCall($this->ajax_hash, $redraw_js) . "\">" .
                        self::$cnt_notes[$note_obj_id][Note::PRIVATE] . "</a>",
                    "newline" => $nl
                ];
                $nl = false;
            }
            if ($this->tags_enabled && (self::$cnt_tags[$note_obj_id] > 0 || is_array(self::$tags[$note_obj_id]))) {
                $tags_set = new ilSetting("tags");
                if ($tags_set->get("enable")) {
                    $tags_url = ilTaggingGUI::getListTagsJSCall($this->ajax_hash, $redraw_js);

                    // list object tags
                    if (is_array(self::$tags[$note_obj_id])) {
                        $tags_tmp = array();
                        foreach (self::$tags[$note_obj_id] as $tag => $is_tag_owner) {
                            if ($is_tag_owner) {
                                $tags_tmp[] = "<a class=\"ilTag ilTagRelHigh\" href='#' onclick=\"return " .
                                    $tags_url . "\">" . $tag . "</a>";
                            } else {
                                $tags_tmp[] = "<span class=\"ilTag ilTagRelMiddle\">" . $tag . "</span>";
                            }
                        }
                        $tags_value = implode(" ", $tags_tmp);
                        $nl = true;
                        $prop_text = "";
                    } // tags counter
                    else {
                        $tags_value = "<a href='#' onclick=\"return " . $tags_url . "\">" .
                            self::$cnt_tags[$note_obj_id] . "</a>";
                        $prop_text = $this->lng->txt("tagging_tags");
                    }
                    $props[] = [
                        "alert" => false,
                        "property" => $prop_text,
                        "value" => $tags_value,
                        "newline" => $nl
                    ];
                }
            }
        }

        if (!is_array($props)) {
            return [];
        }

        return $props;
    }

    public function insertProperties() : void
    {
        $props = $this->determineProperties();
        $cnt = 1;
        if (is_array($props) && count($props) > 0) {
            foreach ($props as $prop) {
                if ($cnt > 1) {
                    $this->tpl->touchBlock("separator_prop");
                }

                if (isset($prop["alert"]) && $prop["alert"] == true) {
                    $this->tpl->touchBlock("alert_prop");
                } else {
                    $this->tpl->touchBlock("std_prop");
                }

                if (isset($prop["newline"]) && $prop["newline"] == true && $cnt > 1) {
                    $this->tpl->touchBlock("newline_prop");
                }

                //BEGIN WebDAV: Support hidden property names.
                if (
                    isset($prop["property"]) &&
                    (isset($prop['propertyNameVisible']) && $prop['propertyNameVisible'] !== false) &&
                    $prop["property"] != ""
                ) {
                    //END WebDAV: Support hidden property names.
                    $this->tpl->setCurrentBlock("prop_name");
                    $this->tpl->setVariable("TXT_PROP", $prop["property"]);
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("item_property");
                //BEGIN WebDAV: Support links in property values.
                if (isset($prop['link']) && $prop['link']) {
                    $this->tpl->setVariable("LINK_PROP", $prop['link']);
                    $this->tpl->setVariable("LINK_VAL_PROP", $prop["value"]);
                } else {
                    $this->tpl->setVariable("VAL_PROP", $prop["value"]);
                }
                //END WebDAV: Support links in property values.
                $this->tpl->parseCurrentBlock();

                $cnt++;
            }
            $this->tpl->setCurrentBlock("item_properties");
            $this->tpl->parseCurrentBlock();
        }
    }
    
    public function insertNoticeProperties() : void
    {
        $this->getNoticeProperties();
        foreach ($this->notice_prop as $property) {
            $this->tpl->setCurrentBlock('notice_item');
            $this->tpl->setVariable('NOTICE_ITEM_VALUE', $property['value']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock('notice_property');
        $this->tpl->parseCurrentBlock();
    }

    protected function parseConditions(int $toggle_id, array $conditions, bool $obligatory = true) : bool
    {
        $num_required = ilConditionHandler::calculateEffectiveRequiredTriggers($this->ref_id, $this->obj_id);
        $num_optional_required =
            $num_required -
            count($conditions) +
            count(ilConditionHandler::getEffectiveOptionalConditionsOfTarget($this->ref_id, $this->obj_id))
        ;

        // Check if all conditions are fulfilled
        $visible_conditions = [];
        $passed_optional = 0;
        foreach ($conditions as $condition) {
            if ($obligatory && !$condition['obligatory']) {
                continue;
            }
            if (!$obligatory && $condition['obligatory']) {
                continue;
            }

            if ($this->tree->isDeleted($condition['trigger_ref_id'])) {
                continue;
            }

            $ok = ilConditionHandler::_checkCondition($condition) && !ilMemberViewSettings::getInstance()->isActive();

            if (!$ok) {
                $visible_conditions[] = $condition['id'];
            }

            if (!$obligatory && $ok) {
                ++$passed_optional;
                // optional passed
                if ($passed_optional >= $num_optional_required) {
                    return true;
                }
            }
        }

        $missing_cond_exist = false;
        foreach ($conditions as $condition) {
            if (!in_array($condition['id'], $visible_conditions)) {
                continue;
            }

            $operator = ilConditionHandlerGUI::translateOperator($condition['trigger_obj_id'], $condition['operator']);
            $cond_txt = $operator . ' ' . $condition['value'];
            
            // display trigger item
            $class = $this->obj_definition->getClassName($condition["trigger_type"]);
            $location = $this->obj_definition->getLocation($condition["trigger_type"]);
            if ($class == "" && $location == "") {
                continue;
            }
            $missing_cond_exist = true;

            $full_class = "ilObj" . $class . "ListGUI";
            $item_list_gui = new $full_class($this);
            $item_list_gui->setMode(self::IL_LIST_AS_TRIGGER);
            $item_list_gui->enablePath(false);
            $item_list_gui->enableIcon(true);
            $item_list_gui->setConditionDepth($this->condition_depth + 1);
            $item_list_gui->setParentRefId($this->getUniqueItemId());
            $item_list_gui->addCustomProperty($this->lng->txt("precondition_required_itemlist"), $cond_txt, false, true);
            $item_list_gui->enableCommands($this->commands_enabled, $this->std_cmd_only);
            $item_list_gui->enableProperties($this->properties_enabled);
            
            $trigger_html = $item_list_gui->getListItemHTML(
                $condition['trigger_ref_id'],
                $condition['trigger_obj_id'],
                ilObject::_lookupTitle($condition["trigger_obj_id"]),
                ""
            );
            $this->tpl->setCurrentBlock("precondition");
            if ($trigger_html == "") {
                $trigger_html = $this->lng->txt("precondition_not_accessible");
            }
            $this->tpl->setVariable("TXT_CONDITION", trim($cond_txt));
            $this->tpl->setVariable("TRIGGER_ITEM", $trigger_html);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($missing_cond_exist && $obligatory) {
            $this->tpl->setCurrentBlock("preconditions");
            $this->tpl->setVariable("CONDITION_TOGGLE_ID", "_obl_" . $toggle_id);
            $this->tpl->setVariable("TXT_PRECONDITIONS", $this->lng->txt("preconditions_obligatory_hint"));
            $this->tpl->parseCurrentBlock();
        } elseif ($missing_cond_exist && !$obligatory) {
            $this->tpl->setCurrentBlock("preconditions");
            $this->tpl->setVariable("CONDITION_TOGGLE_ID", "_opt_" . $toggle_id);
            $this->tpl->setVariable(
                "TXT_PRECONDITIONS",
                sprintf($this->lng->txt("preconditions_optional_hint"), $num_optional_required)
            );
            $this->tpl->parseCurrentBlock();
        }

        return !$missing_cond_exist;
    }

    /**
    * insert all missing preconditions
    */
    public function insertPreconditions() : void
    {
        // do not show multi level conditions (messes up layout)
        if ($this->condition_depth > 0) {
            return;
        }

        if ($this->context == self::CONTEXT_WORKSPACE) {
            return;
        }

        if (isset($this->condition_target) && is_array($this->condition_target)
            && count($this->condition_target) > 0) {
            $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget(
                (int) $this->condition_target['ref_id'],
                (int) $this->condition_target['obj_id'],
                $this->condition_target['target_type'] ?? ""
            );
        } else {
            $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($this->ref_id, $this->obj_id);
        }
        
        if (sizeof($conditions)) {
            for ($i = 0; $i < count($conditions); $i++) {
                $conditions[$i]['title'] = ilObject::_lookupTitle($conditions[$i]['trigger_obj_id']);
            }
            $conditions = ilArrayUtil::sortArray($conditions, 'title', 'DESC');
        
            ++self::$js_unique_id;

            // Show obligatory and optional preconditions seperated
            $all_done_obl = $this->parseConditions(self::$js_unique_id, $conditions);
            $all_done_opt = $this->parseConditions(self::$js_unique_id, $conditions, false);
            
            if (!$all_done_obl || !$all_done_opt) {
                $this->tpl->setCurrentBlock("preconditions_toggle");
                $this->tpl->setVariable("PRECONDITION_TOGGLE_INTRO", $this->lng->txt("precondition_toggle"));
                $this->tpl->setVariable("PRECONDITION_TOGGLE_TRIGGER", $this->lng->txt("show"));
                $this->tpl->setVariable("PRECONDITION_TOGGLE_ID", self::$js_unique_id);
                $this->tpl->setVariable("TXT_PRECONDITION_SHOW", $this->lng->txt("show"));
                $this->tpl->setVariable("TXT_PRECONDITION_HIDE", $this->lng->txt("hide"));
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    /**
    * insert command button
    */
    public function insertCommand(
        string $href,
        string $text,
        string $frame = "",
        string $img = "",
        string $cmd = "",
        string $onclick = ""
    ) : void {
        // #11099
        $checksum = md5($href . $text);
        if ($href == "#" || !in_array($checksum, $this->prevent_duplicate_commands)) {
            if ($href != "#") {
                $this->prevent_duplicate_commands[] = $checksum;
            }
            
            $prevent_background_click = false;
            if ($cmd == 'mount_webfolder') {
                $onclick = "triggerWebDAVModal('$href')";
                $href = "#";
                ilWebDAVMountInstructionsModalGUI::maybeRenderWebDAVModalInGlobalTpl();
            }

            $this->current_selection_list->addItem(
                $text,
                "",
                $href,
                $img,
                $text,
                $frame,
                "",
                $prevent_background_click,
                $onclick
            );
        }
    }

    public function insertDeleteCommand() : void
    {
        if ($this->std_cmd_only) {
            return;
        }

        if (is_object($this->getContainerObject()) and
            $this->getContainerObject() instanceof ilAdministrationCommandHandling) {
            if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type)) {
                $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
                $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "delete");
                $this->insertCommand($cmd_link, $this->lng->txt("delete"));
                $this->adm_commands_included = true;
            }
            return;
        }
        
        if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type)) {
            $this->ctrl->setParameter(
                $this->container_obj,
                "ref_id",
                $this->container_obj->getObject()->getRefId()
            );
            $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "delete");
            $this->insertCommand($cmd_link, $this->lng->txt("delete"));
            $this->adm_commands_included = true;
        }
    }

    public function insertLinkCommand() : void
    {
        $objDefinition = $this->obj_definition;

        if ($this->std_cmd_only) {
            return;
        }
        
        // #17307
        if (
            !$this->checkCommandAccess('delete', '', $this->ref_id, $this->type) ||
            !$objDefinition->allowLink($this->type)
        ) {
            return;
        }
        
        // BEGIN PATCH Lucene search
        if ($this->getContainerObject() instanceof ilAdministrationCommandHandling) {
            $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "link");
            $this->insertCommand($cmd_link, $this->lng->txt("link"));
            $this->adm_commands_included = true;
            return;
        }
        // END PATCH Lucene Search

        // if the permission is changed here, it  has
        // also to be changed in ilContainerGUI, admin command check
        $this->ctrl->setParameter(
            $this->container_obj,
            "ref_id",
            $this->container_obj->getObject()->getRefId()
        );
        $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
        $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "link");
        $this->insertCommand($cmd_link, $this->lng->txt("link"));
        $this->adm_commands_included = true;
    }

    public function insertCutCommand(bool $to_repository = false) : void
    {
        if ($this->std_cmd_only) {
            return;
        }
        // BEGIN PATCH Lucene search
        if (
            $this->getContainerObject() instanceof ilAdministrationCommandHandling
        ) {
            if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type)) {
                $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
                $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "cut");
                $this->insertCommand($cmd_link, $this->lng->txt("move"));
                $this->adm_commands_included = true;
            }
            return;
        }
        // END PATCH Lucene Search

        // if the permission is changed here, it  has
        // also to be changed in ilContainerContentGUI, determineAdminCommands
        if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type) && $this->container_obj->getObject()) {
            $this->ctrl->setParameter(
                $this->container_obj,
                "ref_id",
                $this->container_obj->getObject()->getRefId()
            );
            $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
            
            if (!$to_repository) {
                $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "cut");
                $this->insertCommand($cmd_link, $this->lng->txt("move"));
            } else {
                $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "cut_for_repository");
                $this->insertCommand($cmd_link, $this->lng->txt("wsp_move_to_repository"));
            }
            
            $this->adm_commands_included = true;
        }
    }
    
    public function insertCopyCommand(bool $to_repository = false) : void
    {
        if ($this->std_cmd_only) {
            return;
        }
        
        if ($this->checkCommandAccess('copy', 'copy', $this->ref_id, $this->type) &&
            $this->obj_definition->allowCopy($this->type)) {
            if ($this->context != self::CONTEXT_WORKSPACE && $this->context != self::CONTEXT_WORKSPACE_SHARING) {
                $this->ctrl->setParameterByClass('ilobjectcopygui', 'source_id', $this->getCommandId());
                $cmd_copy = $this->ctrl->getLinkTargetByClass('ilobjectcopygui', 'initTargetSelection');
                $this->insertCommand($cmd_copy, $this->lng->txt('copy'));
            } else {
                $this->ctrl->setParameter(
                    $this->container_obj,
                    "ref_id",
                    $this->container_obj->getObject()->getRefId()
                );
                $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
                
                if (!$to_repository) {
                    $cmd_copy = $this->ctrl->getLinkTarget($this->container_obj, 'copy');
                    $this->insertCommand($cmd_copy, $this->lng->txt('copy'));
                } else {
                    $cmd_copy = $this->ctrl->getLinkTarget($this->container_obj, 'copy_to_repository');
                    $this->insertCommand($cmd_copy, $this->lng->txt('wsp_copy_to_repository'));
                }
            }
            
            $this->adm_commands_included = true;
        }
    }

    public function insertPasteCommand() : void
    {
        if ($this->std_cmd_only) {
            return;
        }
        
        if (!$this->obj_definition->isContainer(ilObject::_lookupType($this->obj_id))) {
            return;
        }
        
        if (
            $this->getContainerObject() instanceof ilAdministrationCommandHandling &&
            $this->clipboard->hasEntries()
        ) {
            $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "paste");
            $this->insertCommand($cmd_link, $this->lng->txt("paste"));
            $this->adm_commands_included = true;
        }
    }

    public function insertSubscribeCommand() : void
    {
        if ($this->std_cmd_only) {
            return;
        }

        // note: the setting disable_my_offers is used for
        // presenting the favourites in the main section of the dashboard
        // see also bug #32014
        if (!(bool) $this->settings->get('rep_favourites', "0")) {
            return;
        }
        
        $type = ilObject::_lookupType(ilObject::_lookupObjId($this->getCommandId()));

        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            // #17467 - add ref_id to link (in repository only!)
            if (
                is_object($this->container_obj) &&
                !($this->container_obj instanceof ilAdministrationCommandHandling) &&
                method_exists($this->container_obj, "getObject") &&
                is_object($this->container_obj->getObject())
            ) {
                $this->ctrl->setParameter($this->container_obj, "ref_id", $this->container_obj->getObject()->getRefId());
            }

            if (!$this->fav_manager->ifIsFavourite($this->user->getId(), $this->getCommandId())) {
                // Pass type and object ID to ilAccess to improve performance
                if ($this->checkCommandAccess("read", "", $this->ref_id, $this->type, $this->obj_id)) {
                    if ($this->getContainerObject() instanceof ilDesktopItemHandling) {
                        $this->ctrl->setParameter($this->container_obj, "type", $type);
                        $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
                        $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "addToDesk");
                        $this->insertCommand($cmd_link, $this->lng->txt("rep_add_to_favourites"));
                    }
                }
            } else {
                if ($this->getContainerObject() instanceof ilDesktopItemHandling) {
                    $this->ctrl->setParameter($this->container_obj, "type", $type);
                    $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
                    $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "removeFromDesk");
                    $this->insertCommand($cmd_link, $this->lng->txt("rep_remove_from_favourites"));
                }
            }
        }
    }

    public function insertInfoScreenCommand() : void
    {
        if ($this->std_cmd_only) {
            return;
        }
        $this->insertCommand(
            $this->getCommandLink("infoScreen"),
            $this->lng->txt("info_short"),
            $this->getCommandFrame("infoScreen"),
            ilUtil::getImagePath("icon_info.svg")
        );
    }

    /**
     * Insert common social commands (comments, notes, tagging)
     */
    public function insertCommonSocialCommands(bool $header_actions = false) : void
    {
        if ($this->std_cmd_only || ($this->user->getId() == ANONYMOUS_USER_ID)) {
            return;
        }

        $this->lng->loadLanguageModule("notes");
        $this->lng->loadLanguageModule("tagging");
        $cmd_frame = $this->getCommandFrame("infoScreen");

        // reference objects have translated ids, revert to originals
        $note_ref_id = $this->ref_id;
        if ($this->reference_ref_id) {
            $note_ref_id = $this->reference_ref_id;
        }
        
        $js_updater = $header_actions
            ? "il.Object.redrawActionHeader();"
            : "il.Object.redrawListItem(" . $note_ref_id . ")";
        
        $comments_enabled = $this->isCommentsActivated($this->type, $this->ref_id, $this->obj_id, $header_actions);
        if ($comments_enabled) {
            $this->insertCommand(
                "#",
                $this->lng->txt("notes_comments"),
                $cmd_frame,
                "",
                "",
                ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $js_updater)
            );
        }

        if ($this->notes_enabled) {
            $this->insertCommand(
                "#",
                $this->lng->txt("notes"),
                $cmd_frame,
                "",
                "",
                ilNoteGUI::getListNotesJSCall($this->ajax_hash, $js_updater)
            );
        }
        
        if ($this->tags_enabled) {
            $this->insertCommand(
                "#",
                $this->lng->txt("tagging_set_tag"),
                $cmd_frame,
                "",
                "",
                ilTaggingGUI::getListTagsJSCall($this->ajax_hash, $js_updater)
            );
        }
    }
    
    public function insertTimingsCommand() : void
    {
        if (
            $this->std_cmd_only ||
            !method_exists($this->container_obj, "getObject") ||
            !is_object($this->container_obj->getObject())
        ) {
            return;
        }
        
        $parent_ref_id = $this->container_obj->getObject()->getRefId();
        $parent_type = $this->container_obj->getObject()->getType();
        
        // #18737
        if ($this->reference_ref_id) {
            $this->ctrl->setParameterByClass('ilobjectactivationgui', 'ref_id', $this->reference_ref_id);
        }
        
        if (
            $this->checkCommandAccess('write', '', $parent_ref_id, $parent_type) ||
            $this->checkCommandAccess('write', '', $this->ref_id, $this->type)
        ) {
            $this->ctrl->setParameterByClass(
                'ilobjectactivationgui',
                'cadh',
                $this->ajax_hash
            );
            $this->ctrl->setParameterByClass(
                'ilobjectactivationgui',
                'parent_id',
                $parent_ref_id
            );
            $cmd_lnk = $this->ctrl->getLinkTargetByClass(
                array($this->gui_class_name, 'ilcommonactiondispatchergui', 'ilobjectactivationgui'),
                'edit'
            );
            
            $this->insertCommand($cmd_lnk, $this->lng->txt('obj_activation_list_gui'));
        }
        
        if ($this->reference_ref_id) {
            $this->ctrl->setParameterByClass('ilobjectactivationgui', 'ref_id', $this->ref_id);
        }
    }

    /**
     * insert all commands into html code
     */
    public function insertCommands(
        bool $use_async = false,
        bool $get_async_commands = false,
        string $async_url = "",
        bool $header_actions = false
    ) : string {
        if (!$this->getCommandsStatus()) {
            return "";
        }

        $this->current_selection_list = new ilAdvancedSelectionListGUI();
        $this->current_selection_list->setAriaListTitle(sprintf($this->lng->txt('actions_for'), $this->getTitle()));
        $this->current_selection_list->setAsynch($use_async && !$get_async_commands);
        $this->current_selection_list->setAsynchUrl($async_url);
        if ($header_actions) {
            $this->current_selection_list->setListTitle(
                "<span class='hidden-xs'>" .
                $this->lng->txt("actions") .
                "</span>"
            );
        } else {
            $this->current_selection_list->setListTitle("");
        }
        $this->current_selection_list->setId("act_" . $this->getUniqueItemId());
        $this->current_selection_list->setSelectionHeaderClass("small");
        $this->current_selection_list->setItemLinkClass("xsmall");
        $this->current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $this->current_selection_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $this->current_selection_list->setUseImages(false);
        $this->current_selection_list->setAdditionalToggleElement(
            $this->getUniqueItemId(true),
            "ilContainerListItemOuterHighlight"
        );

        $this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", $this->ref_id);

        // only standard command?
        $only_default = false;
        if ($use_async && !$get_async_commands) {
            $only_default = true;
        }

        $this->default_command = [];
        $this->prevent_duplicate_commands = [];
        
        // we only allow the following commands inside the header actions
        $valid_header_commands = array("mount_webfolder");

        $commands = $this->getCommands();
        foreach ($commands as $command) {
            if ($header_actions && !in_array($command["cmd"], $valid_header_commands)) {
                continue;
            }
            
            if ($command["granted"] == true) {
                if (!$command["default"] === true) {
                    if (!$this->std_cmd_only && !$only_default) {
                        // workaround for repository frameset
                        $command["link"] =
                            $this->appendRepositoryFrameParameter($command["link"]);

                        $cmd_link = $command["link"];
                        $txt = ($command["lang_var"] == "")
                            ? $command["txt"]
                            : $this->lng->txt($command["lang_var"]);
                        $this->insertCommand(
                            $cmd_link,
                            $txt,
                            $command["frame"],
                            $command["img"],
                            $command["cmd"]
                        );
                    }
                } else {
                    $this->default_command = $this->createDefaultCommand($command);
                }
            }
        }

        if (!$only_default) {
            // custom commands
            if (is_array($this->cust_commands)) {
                foreach ($this->cust_commands as $command) {
                    if ($command instanceof Button) {
                        $this->current_selection_list->addComponent($command);
                        continue;
                    }

                    $this->insertCommand(
                        $command["link"],
                        $this->lng->txt($command["lang_var"]),
                        $command["frame"],
                        "",
                        $command["cmd"] ?? "",
                        $command["onclick"]
                    );
                }
            }

            // info screen command
            if ($this->getInfoScreenStatus()) {
                $this->insertInfoScreenCommand();
            }

            $this->insertLPCommand();

            if (!$this->isMode(self::IL_LIST_AS_TRIGGER)) {
                // edit timings
                if ($this->timings_enabled) {
                    $this->insertTimingsCommand();
                }

                // delete
                if ($this->delete_enabled) {
                    $this->insertDeleteCommand();
                }

                // link
                if ($this->link_enabled) {
                    $this->insertLinkCommand();
                }

                // cut
                if ($this->cut_enabled) {
                    $this->insertCutCommand();
                }

                // copy
                if ($this->copy_enabled) {
                    $this->insertCopyCommand();
                }

                // cut/copy from workspace to repository
                if ($this->repository_transfer_enabled) {
                    $this->insertCutCommand(true);
                    $this->insertCopyCommand(true);
                }

                // subscribe
                if ($this->subscribe_enabled) {
                    $this->insertSubscribeCommand();
                }

                // multi download
                if ($this->multi_download_enabled && $header_actions) {
                    $this->insertMultiDownloadCommand();
                }

                // BEGIN PATCH Lucene search
                if ($this->cut_enabled or $this->link_enabled) {
                    $this->insertPasteCommand();
                }
                // END PATCH Lucene Search
            }
        }
        
        // common social commands (comment, notes, tags)
        if (!$only_default && !$this->isMode(self::IL_LIST_AS_TRIGGER)) {
            $this->insertCommonSocialCommands($header_actions);
        }
        
        if (!$header_actions) {
            $this->ctrl->clearParametersByClass($this->gui_class_name);
        }

        // fix bug #12417
        // there is one case, where no action menu should be displayed:
        // public area, category, no info tab
        // todo: make this faster and remove type specific implementation if possible
        if ($use_async && !$get_async_commands && !$header_actions) {
            if ($this->user->getId() === ANONYMOUS_USER_ID && $this->checkInfoPageOnAsynchronousRendering()) {
                if (
                    !ilContainer::_lookupContainerSetting($this->obj_id, ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY)
                ) {
                    return "";
                }
            }
        }

        if ($use_async && $get_async_commands) {
            return $this->current_selection_list->getHTML(true);
        }
        
        return $this->current_selection_list->getHTML();
    }

    public function enableComments(bool $value, bool $enable_comments_settings = true) : void
    {
        if ($this->settings->get("disable_comments")) {
            $value = false;
        }
        
        $this->comments_enabled = $value;
        $this->comments_settings_enabled = $enable_comments_settings;
    }
    
    public function enableNotes(bool $value) : void
    {
        if ($this->settings->get("disable_notes")) {
            $value = false;
        }
        
        $this->notes_enabled = $value;
    }
    
    public function enableTags(bool $value) : void
    {
        $tags_set = new ilSetting("tags");
        if (!$tags_set->get("enable")) {
            $value = false;
        }
        $this->tags_enabled = $value;
    }

    public function enableRating(
        bool $value,
        string $text = null,
        bool $categories = false,
        array $ctrl_path = null
    ) : void {
        $this->rating_enabled = $value;
        
        if ($this->rating_enabled) {
            $this->rating_categories_enabled = $categories;
            $this->rating_text = $text;
            $this->rating_ctrl_path = $ctrl_path;
        }
    }
    
    /**
     * Toggles whether multiple objects can be downloaded at once or not.
     *
     * @param boolean $value true, to allow downloading of multiple objects; otherwise, false.
     */
    public function enableMultiDownload(bool $value) : void
    {
        $folder_set = new ilSetting("fold");
        if (!$folder_set->get("enable_multi_download")) {
            $value = false;
        }
        $this->multi_download_enabled = $value;
    }
    
    public function insertMultiDownloadCommand() : void
    {
        if ($this->std_cmd_only) {
            return;
        }
        
        if (!$this->obj_definition->isContainer(ilObject::_lookupType($this->obj_id))) {
            return;
        }
        
        if ($this->getContainerObject() instanceof ilContainerGUI) {
            $this->ctrl->setParameter($this->getContainerObject(), "type", "");
            $this->ctrl->setParameter($this->getContainerObject(), "item_ref_id", "");
            $this->ctrl->setParameter($this->getContainerObject(), "active_node", "");
            // bugfix mantis 24559
            // undoing an erroneous change inside mantis 23516 by
            // adding "Download Multiple Objects"-functionality for non-admins
            // as they don't have the possibility to use the multi-download-capability of the manage-tab
            $user_id = $this->user->getId();
            $hasAdminAccess = $this->access->checkAccessOfUser($user_id, "crs_admin", $this->ctrl->getCmd(), $this->requested_ref_id);
            // to still prevent duplicate download functions for admins
            // the following if-else statement keeps the redirection for admins
            // while letting other course members access the original multi-download functionality
            if ($hasAdminAccess) {
                $cmd = ($this->requested_cmd == "enableAdministrationPanel")
                    ? "render"
                    : "enableAdministrationPanel";
            } else {
                $cmd = ($this->requested_cmd == "enableMultiDownload")
                    ? "render"
                    : "enableMultiDownload";
            }
            $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), $cmd);
            $this->insertCommand($cmd_link, $this->lng->txt("download_multiple_objects"));
        }
    }
    
    public function enableDownloadCheckbox(int $ref_id) : void
    {
        // TODO: delegate to list object class!
        if (!$this->getContainerObject()->isActiveAdministrationPanel() || $this->clipboard->hasEntries()) {
            if (
                in_array($this->type, ["file", "fold"]) &&
                $this->access->checkAccess("read", "", $ref_id, $this->type)
            ) {
                $this->download_checkbox_state = self::DOWNLOAD_CHECKBOX_ENABLED;
            } else {
                $this->download_checkbox_state = self::DOWNLOAD_CHECKBOX_DISABLED;
            }
        } else {
            $this->download_checkbox_state = self::DOWNLOAD_CHECKBOX_NONE;
        }
    }
    
    public function getDownloadCheckboxState() : int
    {
        return $this->download_checkbox_state;
    }
    
    /**
     * Insert js/ajax links into template
     */
    public static function prepareJsLinks(
        string $redraw_url,
        string $notes_url,
        string $tags_url,
        ilGlobalTemplateInterface $tpl = null
    ) : void {
        global $DIC;

        if (is_null($tpl)) {
            $tpl = $DIC["tpl"];
        }
        
        //if ($notes_url) {
        $DIC->notes()->gui()->initJavascript($notes_url);
        //}
        
        if ($tags_url) {
            ilTaggingGUI::initJavascript($tags_url, $tpl);
        }
        
        if ($redraw_url) {
            $tpl->addOnLoadCode("il.Object.setRedrawAHUrl('" . $redraw_url . "');");
        }
    }
    
    public function setHeaderSubObject(?string $type, ?int $id) : void
    {
        $this->sub_obj_type = $type;
        $this->sub_obj_id = (int) $id;
    }

    public function addHeaderIcon(
        string $id,
        string $img,
        string $tooltip = null,
        string $onclick = null,
        string $status_text = null,
        string $href = null
    ) : void {
        $this->header_icons[$id] = [
            "img" => $img,
            "tooltip" => $tooltip,
            "onclick" => $onclick,
            "status_text" => $status_text,
            "href" => $href
        ];
    }
    
    public function addHeaderIconHTML(string $id, string $html) : void
    {
        $this->header_icons[$id] = $html;
    }

    public function addHeaderGlyph(string $id, ILIAS\UI\Component\Symbol\Glyph\Glyph $glyph, $onclick = null) : void
    {
        $this->header_icons[$id] = ["glyph" => $glyph, "onclick" => $onclick];
    }

    public function setAjaxHash(string $hash) : void
    {
        $this->ajax_hash = $hash;
    }
    
    public function getHeaderAction(ilGlobalTemplateInterface $main_tpl = null) : string
    {
        if ($main_tpl == null) {
            global $DIC;
            $main_tpl = $DIC["tpl"];
        }

        $htpl = new ilTemplate("tpl.header_action.html", true, true, "Services/Repository");

        $redraw_js = "il.Object.redrawActionHeader();";

        // tags
        if ($this->tags_enabled) {
            $tags = ilTagging::getTagsForUserAndObject(
                $this->obj_id,
                ilObject::_lookupType($this->obj_id),
                0,
                "",
                $this->user->getId()
            );
            if (count($tags) > 0) {
                $this->lng->loadLanguageModule("tagging");

                $f = $this->ui->factory();
                $this->addHeaderGlyph(
                    "tags",
                    $f->symbol()->glyph()->tag("#")
                      ->withCounter($f->counter()->status(count($tags))),
                    ilTaggingGUI::getListTagsJSCall($this->ajax_hash, $redraw_js)
                );
            }
        }

        // notes and comments
        $comments_enabled = $this->isCommentsActivated($this->type, $this->ref_id, $this->obj_id, true, false);
        if ($this->notes_enabled || $comments_enabled) {
            $type = ($this->sub_obj_type == "") ? $this->type : $this->sub_obj_type;
            $context = $this->notes_service->data()->context($this->obj_id, $this->sub_obj_id, $type);
            $cnt[$this->obj_id][Note::PUBLIC] = $this->notes_service->domain()->getNrOfCommentsForContext($context);
            $cnt[$this->obj_id][Note::PRIVATE] = $this->notes_service->domain()->getNrOfNotesForContext($context);
            if (
                $this->notes_enabled &&
                isset($cnt[$this->obj_id][Note::PRIVATE]) &&
                $cnt[$this->obj_id][Note::PRIVATE] > 0
            ) {
                $f = $this->ui->factory();
                $this->addHeaderGlyph(
                    "notes",
                    $f->symbol()->glyph()->note("#")
                      ->withCounter($f->counter()->status((int) $cnt[$this->obj_id][Note::PRIVATE])),
                    ilNoteGUI::getListNotesJSCall($this->ajax_hash, $redraw_js)
                );
            }

            if (
                $comments_enabled &&
                isset($cnt[$this->obj_id][Note::PUBLIC]) &&
                $cnt[$this->obj_id][Note::PUBLIC] > 0
            ) {
                $this->lng->loadLanguageModule("notes");
                $f = $this->ui->factory();
                $this->addHeaderGlyph(
                    "comments",
                    $f->symbol()->glyph()->comment("#")
                      ->withCounter($f->counter()->status((int) $cnt[$this->obj_id][Note::PUBLIC])),
                    ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $redraw_js)
                );
            }
        }

        // rating
        if ($this->rating_enabled) {
            $rating_gui = new ilRatingGUI();
            $rating_gui->enableCategories($this->rating_categories_enabled);
            // never rate sub objects from header action!
            $rating_gui->setObject($this->obj_id, $this->type);
            if ($this->rating_text) {
                $rating_gui->setYourRatingText($this->rating_text);
            }

            $this->ctrl->setParameterByClass("ilRatingGUI", "cadh", $this->ajax_hash);
            $this->ctrl->setParameterByClass("ilRatingGUI", "rnsb", true);
            if ($this->rating_ctrl_path) {
                $rating_gui->setCtrlPath($this->rating_ctrl_path);
                $ajax_url = $this->ctrl->getLinkTargetByClass($this->rating_ctrl_path, "saveRating", "", true);
            } else {
                $ajax_url = $this->ctrl->getLinkTargetByClass("ilRatingGUI", "saveRating", "", true);
            }
            $main_tpl->addOnLoadCode("il.Object.setRatingUrl('" . $ajax_url . "');");
            $this->addHeaderIconHTML(
                "rating",
                $rating_gui->getHTML(
                    true,
                    $this->checkCommandAccess("read", "", $this->ref_id, $this->type),
                    "il.Object.saveRating(%rating%);"
                )
            );
        }

        if ($this->header_icons) {
            $chunks = [];
            foreach ($this->header_icons as $id => $attr) {
                $id = "headp_" . $id;

                if (is_array($attr)) {
                    if (isset($attr["glyph"]) && $attr["glyph"]) {
                        if ($attr["onclick"]) {
                            $htpl->setCurrentBlock("prop_glyph_oc");
                            $htpl->setVariable("GLYPH_ONCLICK", $attr["onclick"]);
                            $htpl->parseCurrentBlock();
                        }
                        $renderer = $this->ui->renderer();
                        $html = $renderer->render($attr["glyph"]);
                        $htpl->setCurrentBlock("prop_glyph");
                        $htpl->setVariable("GLYPH", $html);
                        $htpl->parseCurrentBlock();
                    } else {
                        if ($attr["onclick"]) {
                            $htpl->setCurrentBlock("onclick");
                            $htpl->setVariable("PROP_ONCLICK", $attr["onclick"]);
                            $htpl->parseCurrentBlock();
                        }

                        if ($attr["status_text"]) {
                            $htpl->setCurrentBlock("status");
                            $htpl->setVariable("PROP_TXT", $attr["status_text"]);
                            $htpl->parseCurrentBlock();
                        }


                        $htpl->setCurrentBlock("prop");
                        if ($attr["href"] || $attr["onclick"]) {
                            $htpl->setVariable("TAG", "a");
                        } else {
                            $htpl->setVariable("TAG", "span");
                        }
                        $htpl->setVariable("PROP_ID", $id);
                        $htpl->setVariable("IMG", ilUtil::img($attr["img"], $attr["tooltip"]));
                        if ($attr["href"] != "") {
                            $htpl->setVariable("PROP_HREF", ' href="' . $attr["href"] . '" ');
                        }
                        $htpl->parseCurrentBlock();

                        if ($attr["tooltip"]) {
                            ilTooltipGUI::addTooltip($id, $attr["tooltip"]);
                        }
                    }
                } else {
                    $chunks[] = $attr;
                }
            }

            if (sizeof($chunks)) {
                $htpl->setVariable(
                    "PROP_CHUNKS",
                    implode("&nbsp;&nbsp;&nbsp;", $chunks) . "&nbsp;&nbsp;&nbsp;"
                );
            }
        }

        $this->title = ilObject::_lookupTitle($this->obj_id);
        $htpl->setVariable(
            "ACTION_DROP_DOWN",
            $this->insertCommands(false, false, "", true)
        );

        if ($this->cust_modals !== []) {
            $htpl->setVariable('TRIGGERED_MODALS', $this->ui->renderer()->render($this->cust_modals));
        }

        return $htpl->get();
    }


    /**
    * workaround: all links into the repository (from outside)
    * must tell repository to set up the frameset
    */
    public function appendRepositoryFrameParameter(string $link) : string
    {
        // we should get rid of this nonsense with 4.4 (alex)
        $base_class = $this->request_wrapper->retrieve("baseClass", $this->refinery->kindlyTo()->string());
        if (
            (strtolower($base_class) != "ilrepositorygui") &&
            is_int(strpos($link, "baseClass=ilRepositoryGUI"))
        ) {
            if ($this->type != "frm") {
                $link = ilUtil::appendUrlParameterString($link, "rep_frame=1");
            }
        }
        
        return $link;
    }
    
    protected function modifyTitleLink(string $default_link) : string
    {
        if ($this->default_command_params) {
            $params = array();
            foreach ($this->default_command_params as $name => $value) {
                $params[] = $name . '=' . $value;
            }
            $params = implode('&', $params);
            
            
            // #12370
            if (!stristr($default_link, '?')) {
                $default_link = ($default_link . '?' . $params);
            } else {
                $default_link = ($default_link . '&' . $params);
            }
        }
        return $default_link;
    }

    /**
    * workaround: SAHS in new javavasript-created window or iframe
    */
    public function modifySAHSlaunch(string $link, string $wtarget) : string
    {
        global $DIC;
    
        if (strstr($link, ilSAHSPresentationGUI::class)) {
            $sahs_obj = new ilObjSAHSLearningModule($this->ref_id);
            $om = $sahs_obj->getOpenMode();
            $width = $sahs_obj->getWidth();
            $height = $sahs_obj->getHeight();
            if (($om == 5 || $om == 1) && $width > 0 && $height > 0) {
                $om++;
            }
            if ($om != 0 && !$DIC->http()->agent()->isMobile()) {
                $this->default_command["frame"] = "";
                $link =
                    "javascript:void(0); onclick=startSAHS('" .
                    $link .
                    "','" .
                    $wtarget .
                    "'," .
                    $om .
                    "," .
                    $width .
                    "," .
                    $height .
                    ");"
                ;
            }
        }
        return $link;
    }

    public function insertPath() : void
    {
        if ($this->getPathStatus() != false) {
            if (!$this->path_gui instanceof \ilPathGUI) {
                $path_gui = new \ilPathGUI();
            } else {
                $path_gui = $this->path_gui;
            }

            $path_gui->enableTextOnly(!$this->path_linked);
            $path_gui->setUseImages(false);

            $start_node = ROOT_FOLDER_ID;
            if ($this->path_start_node) {
                $start_node = $this->path_start_node;
            }

            $this->tpl->setCurrentBlock("path_item");
            $this->tpl->setVariable('PATH_ITEM', $path_gui->getPath($start_node, $this->ref_id));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("path");
            $this->tpl->setVariable("TXT_LOCATION", $this->lng->txt("locator"));
            $this->tpl->parseCurrentBlock();
        }
    }
    
    public function insertProgressInfo() : void
    {
    }

    public function insertIconsAndCheckboxes() : void
    {
        $cnt = 0;
        if ($this->getCheckboxStatus()) {
            $this->tpl->setCurrentBlock("check");
            $this->tpl->setVariable("VAL_ID", $this->getCommandId());
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        } elseif ($this->getDownloadCheckboxState() != self::DOWNLOAD_CHECKBOX_NONE) {
            $this->tpl->setCurrentBlock("check_download");
            if ($this->getDownloadCheckboxState() == self::DOWNLOAD_CHECKBOX_ENABLED) {
                $this->tpl->setVariable("VAL_ID", $this->getCommandId());
            } else {
                $this->tpl->setVariable("VAL_VISIBILITY", "visibility: hidden;\" disabled=\"disabled");
            }
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        } elseif ($this->getExpandStatus()) {
            $this->tpl->setCurrentBlock('expand');

            if ($this->isExpanded()) {
                $this->ctrl->setParameter($this->container_obj, 'expand', -1 * $this->obj_id);
                // "view" added, see #19922
                $this->tpl->setVariable('EXP_HREF', $this->ctrl->getLinkTarget($this->container_obj, 'view', $this->getUniqueItemId(true)));
                $this->ctrl->clearParameters($this->container_obj);
                $this->tpl->setVariable('EXP_IMG', ilUtil::getImagePath('tree_exp.svg'));
                $this->tpl->setVariable('EXP_ALT', $this->lng->txt('collapse'));
            } else {
                $this->ctrl->setParameter($this->container_obj, 'expand', $this->obj_id);
                // "view" added, see #19922
                $this->tpl->setVariable('EXP_HREF', $this->ctrl->getLinkTarget($this->container_obj, 'view', $this->getUniqueItemId(true)));
                $this->ctrl->clearParameters($this->container_obj);
                $this->tpl->setVariable('EXP_IMG', ilUtil::getImagePath('tree_col.svg'));
                $this->tpl->setVariable('EXP_ALT', $this->lng->txt('expand'));
            }

            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        }

        if ($this->getIconStatus()) {
            if ($cnt == 1) {
                $this->tpl->touchBlock("i_1");
            }

            $this->tpl->setCurrentBlock("icon");
            if (!$this->obj_definition->isPlugin($this->getIconImageType())) {
                $this->tpl->setVariable("ALT_ICON", $this->lng->txt("obj_" . $this->getIconImageType()));
            } else {
                $this->tpl->setVariable(
                    "ALT_ICON",
                    ilObjectPlugin::lookupTxtById($this->getIconImageType(), "obj_" . $this->getIconImageType())
                );
            }

            $this->tpl->setVariable(
                "SRC_ICON",
                $this->getTypeIcon()
            );
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        }

        $this->tpl->touchBlock("d_" . $cnt);
    }

    /**
     * Get object type specific type icon
     */
    public function getTypeIcon() : string
    {
        return ilObject::getIconForReference(
            $this->ref_id,
            $this->obj_id,
            'small',
            $this->getIconImageType()
        );
    }
    
    public function insertSubItems() : void
    {
        foreach ($this->sub_item_html as $sub_html) {
            $this->tpl->setCurrentBlock("subitem");
            $this->tpl->setVariable("SUBITEM", $sub_html);
            $this->tpl->parseCurrentBlock();
        }
    }
    
    public function insertPositionField() : void
    {
        if ($this->position_enabled) {
            $this->tpl->setCurrentBlock("position");
            $this->tpl->setVariable("POS_ID", $this->position_field_index);
            $this->tpl->setVariable("POS_VAL", $this->position_value);
            $this->tpl->parseCurrentBlock();
        }
    }
    
    /**
     * returns whether any admin commands (link, delete, cut)
     * are included in the output
     */
    public function adminCommandsIncluded() : bool
    {
        return $this->adm_commands_included;
    }

    public function storeAccessCache() : void
    {
        if ($this->acache->getLastAccessStatus() == "miss" && !$this->prevent_access_caching) {
            $this->acache->storeEntry(
                $this->user->getId() . ":" . $this->ref_id,
                serialize($this->access_cache),
                $this->ref_id
            );
        }
    }
    
    /**
     * Get all item information (title, commands, description) in HTML
     */
    public function getListItemHTML(
        int $ref_id,
        int $obj_id,
        string $title,
        string $description,
        bool $use_async = false,
        bool $get_async_commands = false,
        string $async_url = ""
    ) : string {
        // this variable stores whether any admin commands
        // are included in the output
        $this->adm_commands_included = false;

        // only for performance exploration
        $type = ilObject::_lookupType($obj_id);

        $this->initItem($ref_id, $obj_id, $type, $title, $description);

        if ($use_async && $get_async_commands) {
            return $this->insertCommands(true, true);
        }
        
        if ($this->rating_enabled) {
            if (ilRating::hasRatingInListGUI($this->obj_id, $this->type)) {
                $may_rate = $this->checkCommandAccess("read", "", $this->ref_id, $this->type);
                $rating = new ilRatingGUI();
                $rating->setObject($this->obj_id, $this->type);
                $this->addCustomProperty(
                    "",
                    $rating->getListGUIProperty($this->ref_id, $may_rate, $this->ajax_hash, $this->parent_ref_id),
                    false,
                    true
                );
            }
        }
        
        // read from cache
        $this->acache = new ilListItemAccessCache();
        $cres = $this->acache->getEntry($this->user->getId() . ":" . $ref_id);
        if ($this->acache->getLastAccessStatus() == "hit") {
            $this->access_cache = unserialize($cres);
        } else {
            // write to cache
            $this->storeAccessCache();
        }
        
        // visible check
        if (!$this->checkCommandAccess("visible", "", $ref_id, "", $obj_id)) {
            $this->resetCustomData();
            return "";
        }
        
        // BEGIN WEBDAV
        if ($type == 'file' and ilObjFileAccess::_isFileHidden($title)) {
            $this->resetCustomData();
            return "";
        }
        // END WEBDAV

        $this->tpl = new ilTemplate(
            static::$tpl_file_name,
            true,
            true,
            static::$tpl_component,
            "DEFAULT",
            false,
            true
        );

        if ($this->getCommandsStatus()) {
            if (!$this->getSeparateCommands()) {
                $this->tpl->setVariable(
                    "COMMAND_SELECTION_LIST",
                    $this->insertCommands($use_async, $get_async_commands, $async_url)
                );
            }
        }
        
        if ($this->getProgressInfoStatus()) {
            $this->insertProgressInfo();
        }

        // insert title and describtion
        $this->insertTitle();
        if (!$this->isMode(self::IL_LIST_AS_TRIGGER)) {
            if ($this->getDescriptionStatus()) {
                $this->insertDescription();
            }
        }

        if ($this->getSearchFragmentStatus()) {
            $this->insertSearchFragment();
        }
        if ($this->enabledRelevance()) {
            $this->insertRelevance();
        }

        // properties
        if ($this->getPropertiesStatus()) {
            $this->insertProperties();
        }

        // notice properties
        if ($this->getNoticePropertiesStatus()) {
            $this->insertNoticeProperties();
        }

        // preconditions
        if ($this->getPreconditionsStatus()) {
            $this->insertPreconditions();
        }

        // path
        $this->insertPath();

        if ($this->getItemDetailLinkStatus()) {
            $this->insertItemDetailLinks();
        }

        // icons and checkboxes
        $this->insertIconsAndCheckboxes();
        
        // input field for position
        $this->insertPositionField();

        // subitems
        $this->insertSubItems();

        $this->resetCustomData();

        $this->tpl->setVariable("DIV_CLASS", 'ilContainerListItemOuter');
        $this->tpl->setVariable(
            "DIV_ID",
            'data-list-item-id="' . $this->getUniqueItemId(true) . '" id = "' . $this->getUniqueItemId(true) . '"'
        );
        $this->tpl->setVariable("ADDITIONAL", $this->getAdditionalInformation());

        if (is_object($this->getContainerObject())) {
            // #11554 - make sure that internal ids are reset
            $this->ctrl->setParameter($this->getContainerObject(), "item_ref_id", "");
        }

        // if file upload is enabled the content is wrapped by a UI dropzone.
        $file_upload_dropzone = new ilObjFileUploadDropzone($this->ref_id, $this->tpl->get());
        if ($file_upload_dropzone->isUploadAllowed($this->type)) {
            return $file_upload_dropzone->getDropzoneHtml();
        }

        return $this->tpl->get();
    }
    
    /**
     * reset properties and commands
     */
    protected function resetCustomData() : void
    {
        // #15747
        $this->cust_prop = [];
        $this->cust_commands = [];
        $this->cust_modals = [];
        $this->sub_item_html = [];
        $this->position_enabled = false;
    }
    
    /**
     * Set current parent ref id to enable unique js-ids (sessions, etc.)
     */
    public function setParentRefId(int $ref_id) : void
    {
        $this->parent_ref_id = $ref_id;
    }
    
    /**
     * Get unique item identifier (for js-actions)
     *
     * @param bool $a_as_div
     * @return string
     */
    public function getUniqueItemId(bool $as_div = false) : string
    {
        // use correct id for references
        $id_ref = $this->ref_id;
        if ($this->reference_ref_id > 0) {
            $id_ref = $this->reference_ref_id;
        }
        
        // add unique identifier for preconditions (objects can appear twice in same container)
        if ($this->condition_depth) {
            $id_ref .= "_pc" . $this->condition_depth;
        }
        
        // unique
        $id_ref .= "_pref_" . $this->parent_ref_id;
    
        if (!$as_div) {
            return $id_ref;
        } else {
            // action menu [yellow] toggle
            return "lg_div_" . $id_ref;
        }
    }
    
    /**
    * Get commands HTML (must be called after get list item html)
    */
    public function getCommandsHTML() : string
    {
        return $this->insertCommands();
    }
    
    /**
    * Returns whether current item is a block in a side column or not
    */
    public function isSideBlock() : bool
    {
        return false;
    }

    public function setBoldTitle(bool $bold_title) : void
    {
        $this->bold_title = $bold_title;
    }
    
    public function isTitleBold() : bool
    {
        return $this->bold_title;
    }
    
    public static function preloadCommonProperties(array $obj_ids, int $context) : void
    {
        global $DIC;
        $lng = $DIC->language();
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        $notes_manager = $DIC->notes()->internal()->domain()->notes();

        if ($context == self::CONTEXT_REPOSITORY) {
            $active_notes = !$ilSetting->get("disable_notes");
            $active_comments = !$ilSetting->get("disable_comments");
        
            if ($active_comments) {
                // needed for action
                self::$comments_activation = $DIC->notes()
                    ->internal()
                    ->domain()
                    ->notes()->commentsActiveMultiple($obj_ids);
            }
            
            // properties are optional
            if ($ilSetting->get('comments_tagging_in_lists')) {
                if ($active_notes || $active_comments) {

                    // @todo: should be refactored, see comment in notes db repo
                    self::$cnt_notes = $notes_manager->countNotesAndCommentsMultipleObjects(
                        $obj_ids,
                        true
                    );
                    
                    $lng->loadLanguageModule("notes");
                }
                
                $tags_set = new ilSetting("tags");
                if ($tags_set->get("enable")) {
                    $all_users = (bool) $tags_set->get("enable_all_users");
                
                    if (!$ilSetting->get('comments_tagging_in_lists_tags')) {
                        self::$cnt_tags = ilTagging::_countTags($obj_ids, $all_users);
                    } else {
                        $tag_user_id = null;
                        if (!$all_users) {
                            $tag_user_id = $ilUser->getId();
                        }
                        self::$tags = ilTagging::_getListTagsForObjects($obj_ids, $tag_user_id);
                    }
                    
                    $lng->loadLanguageModule("tagging");
                }
            }
            
            $lng->loadLanguageModule("rating");
        }
        
        self::$preload_done = true;
    }
    
    /**
     * Check comments status against comments settings and context
     */
    protected function isCommentsActivated(
        string $type,
        int $ref_id,
        int $obj_id,
        bool $header_actions,
        bool $check_write_access = true
    ) : bool {
        if ($this->comments_enabled) {
            if (!$this->comments_settings_enabled) {
                return true;
            }
            if ($check_write_access && $this->checkCommandAccess('write', '', $ref_id, $type)) {
                return true;
            }
            // fallback to single object check if no preloaded data
            // only the repository does preloadCommonProperties() yet
            if (!$header_actions && self::$preload_done) {
                if (isset(self::$comments_activation[$obj_id]) &&
                    self::$comments_activation[$obj_id]) {
                    return true;
                }
            } elseif ($this->notes_service->domain()->commentsActive($obj_id)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * enable timings link
     */
    public function enableTimings(bool $status) : void
    {
        $this->timings_enabled = $status;
    }

    /**
     * Get list item ui object
     */
    public function getAsListItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        string $description
    ) : ?Item {
        $ui = $this->ui;

        // even b tag produced bugs, see #32304
        $description = strip_tags($description);

        $this->initItem(
            $ref_id,
            $obj_id,
            $type,
            $title,
            $description
        );

        $this->enableCommands(true);

        // actions
        $this->insertCommands();
        $actions = [];
        foreach ($this->current_selection_list->getItems() as $action_item) {
            $action = $ui->factory()
                ->button()
                ->shy($action_item['title'], $action_item['link']);

            // Dirty hack to remain the "onclick" action of action items
            if ($action_item['onclick'] != null && $action_item['onclick'] != '') {
                $action = $action->withAdditionalOnLoadCode(function ($id) use ($action_item) : string {
                    return "$('#$id').click(function(){" . $action_item['onclick'] . ";});";
                });
            }

            $actions[] = $action;
        }

        $dropdown = $ui->factory()
            ->dropdown()
            ->standard($actions)
            ->withAriaLabel(sprintf(
                $this->lng->txt('actions_for'),
                $title
            ));

        $def_command = $this->getDefaultCommand();

        $icon = $this->ui->factory()
            ->symbol()
            ->icon()
            ->custom(ilObject::_getIcon($obj_id), $this->lng->txt("icon") . " " . $this->lng->txt('obj_' . $type))
            ->withSize('medium');


        if ($def_command['link']) {
            $list_item = $ui->factory()->item()->standard(
                $this->ui->factory()->link()->standard($this->getTitle(), $def_command['link'])
            );
        } else {
            $list_item = $ui->factory()->item()->standard($this->getTitle());
        }

        if ($description != "") {
            $list_item = $list_item->withDescription($description);
        }
        $list_item = $list_item->withActions($dropdown)->withLeadIcon($icon);


        $l = [];
        $this->enableComments(true);
        $this->enableNotes(true);
        $this->enableTags(true);
        $this->enableRating(true);

        foreach ($this->determineProperties() as $p) {
            //if ($p['property'] !== $this->lng->txt('learning_progress')) {
            $l[(string) $p['property']] = (string) $p['value'];
            //}
        }
        if (count($l) > 0) {
            $list_item = $list_item->withProperties($l);
        }

        // @todo: learning progress
        /*
        $lp = ilLPStatus::getListGUIStatus($item['obj_id'], false);
        if (is_array($lp) && array_key_exists('status', $lp)) {
            $percentage = (int)ilLPStatus::_lookupPercentage($item['obj_id'], $this->user->getId());
            if ($lp['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                $percentage = 100;
            }

            $card = $card->withProgress(
                $this->uiFactory
                    ->chart()
                    ->progressMeter()
                    ->mini(100, $percentage)
            );
        }*/

        return $list_item;
    }

    /**
     * Get card object
     */
    public function getAsCard(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        string $description
    ) : ?RepositoryObject {
        $ui = $this->ui;

        // even b tag produced bugs, see #32304
        $description = strip_tags($description);

        $this->initItem(
            $ref_id,
            $obj_id,
            $type,
            $title,
            $description
        );

        $user = $this->user;
        $access = $this->access;

        $this->enableCommands(true);

        $sections = [];

        // description, @todo: move to new ks element
        if ($description != "") {
            $sections[] = $ui->factory()->legacy("<div class='il-multi-line-cap-3'>" . $description . "</div>");
        }

        $this->insertCommands();
        $actions = [];

        foreach ($this->current_selection_list->getItems() as $item) {
            if (!isset($item["onclick"]) || $item["onclick"] == "") {
                $actions[] =
                    $ui->factory()->button()->shy($item["title"], $item["link"]);
            } else {
                $actions[] =
                    $ui->factory()->button()->shy($item["title"], "")->withAdditionalOnLoadCode(function ($id) use ($item) : string {
                        return
                            "$('#$id').click(function(e) { " . $item["onclick"] . "});";
                    });
            }
        }

        $def_command = $this->getDefaultCommand();

        $dropdown = $ui->factory()->dropdown()->standard($actions)
                       ->withAriaLabel(sprintf(
                           $this->lng->txt('actions_for'),
                           $title
                       ));

        $img = $this->object_service->commonSettings()->tileImage()->getByObjId($obj_id);
        if ($img->exists()) {
            $path = $img->getFullPath();
        } else {
            $path = ilUtil::getImagePath('cont_tile/cont_tile_default_' . $type . '.svg');
            if (!is_file($path)) {
                $path = ilUtil::getImagePath('cont_tile/cont_tile_default.svg');
            }
        }

        // workaround for #26205
        // we should get rid of _top links completely and gifure our how
        // to manage scorm links better
        if ($def_command["frame"] == "_top") {
            $def_command["frame"] = "";
        }

        // workaround for scorm
        $modified_link =
            $this->modifySAHSlaunch($def_command["link"], $def_command["frame"]);

        $image = $this->ui->factory()
                          ->image()
                          ->responsive($path, '');
        if ($def_command['link'] != '') {    // #24256
            if ($def_command["frame"] != "" && ($modified_link == $def_command["link"])) {
                $image = $image->withAdditionalOnLoadCode(function ($id) use ($def_command) : string {
                    return
                        "$('#$id').click(function(e) { window.open('" . str_replace(
                            "&amp;",
                            "&",
                            $def_command["link"]
                        ) . "', '" . $def_command["frame"] . "');});";
                });

                $button =
                    $ui->factory()->button()->shy($title, "")->withAdditionalOnLoadCode(function ($id) use (
                        $def_command
                    ) : string {
                        return
                            "$('#$id').click(function(e) { window.open('" . str_replace(
                                "&amp;",
                                "&",
                                $def_command["link"]
                            ) . "', '" . $def_command["frame"] . "');});";
                    });
                $title = $ui->renderer()->render($button);
            } else {
                $image = $image->withAction($modified_link);
            }
        }

        if ($type == 'sess') {
            if ($title != "") {
                $title = ": " . $title;
            }
            $app_info = ilSessionAppointment::_lookupAppointment($obj_id);
            $title = ilSessionAppointment::_appointmentToString(
                $app_info['start'],
                $app_info['end'],
                $app_info['fullday']
            ) . $title;
        }

        $icon = $this->ui
            ->factory()
            ->symbol()
            ->icon()
            ->standard($type, $this->lng->txt('obj_' . $type))
            ->withIsOutlined(true)
        ;

        // card title action
        $card_title_action = "";
        if ($def_command["link"] != "" && ($def_command["frame"] == "" || $modified_link != $def_command["link"])) {    // #24256
            $card_title_action = $modified_link;
        } elseif ($def_command['link'] == "" &&
            $this->getInfoScreenStatus() &&
            $access->checkAccessOfUser(
                $user->getId(),
                "visible",
                "",
                $ref_id
            )) {
            $card_title_action = ilLink::_getLink($ref_id);
            if ($image->getAction() == "") {
                $image = $image->withAction($card_title_action);
            }
        }

        $card = $ui->factory()->card()->repositoryObject(
            $title . '<span data-list-item-id="' . $this->getUniqueItemId(true) . '"></span>',
            $image
        )->withObjectIcon(
            $icon
        )->withActions(
            $dropdown
        );

        if ($card_title_action != "") {
            $card = $card->withTitleAction($card_title_action);
        }

        $l = [];
        foreach ($this->determineProperties() as $p) {
            if ($p["alert"] && $p['property'] !== $this->lng->txt('learning_progress')) {
                $l[(string) $p['property']] = (string) $p['value'];
            }
        }
        if (count($l) > 0) {
            $prop_list = $ui->factory()->listing()->descriptive($l);
            $sections[] = $prop_list;
        }
        if (count($sections) > 0) {
            $card = $card->withSections($sections);
        }

        $lp = ilLPStatus::getListGUIStatus($obj_id, false);
        if (is_array($lp) && array_key_exists('status', $lp)) {
            $percentage = (int) ilLPStatus::_lookupPercentage($obj_id, $this->user->getId());
            if ($lp['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                $percentage = 100;
            }

            $card = $card->withProgress(
                $ui->factory()
                   ->chart()
                   ->progressMeter()
                   ->mini(100, $percentage)
            );
        }

        return $card;
    }

    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return false;
    }

    /**
     * insert learning progress command
     */
    public function insertLPCommand() : void
    {
        if ($this->std_cmd_only || !$this->lp_cmd_enabled) {
            return;
        }
        $relevant = ilLPStatus::hasListGUIStatus($this->obj_id);
        if ($relevant) {
            $cmd_link = $this->getCommandLink("learningProgress");
            $this->insertCommand(
                $cmd_link,
                $this->lng->txt("learning_progress")
            );
        }
    }
}
