<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_LIST_AS_TRIGGER", "trigger");
define("IL_LIST_FULL", "full");
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* Class ilObjectListGUI
*
* Important note:
*
* All access checking should be made within $ilAccess and
* the checkAccess of the ilObj...Access classes. Do not additionally
* enable or disable any commands within this GUI class or in derived
* classes, except when the container (e.g. a search result list)
* generally refuses them.
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
*/
class ilObjectListGUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilTemplate
     */
    protected $tpl;

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
    
    public $ctrl;
    public $description_enabled = true;
    public $preconditions_enabled = true;
    public $properties_enabled = true;
    public $notice_properties_enabled = true;
    public $commands_enabled = true;
    public $cust_prop = array();
    public $cust_commands = array();
    public $info_screen_enabled = false;
    public $condition_depth = 0;
    public $std_cmd_only = false;
    public $sub_item_html = array();
    public $multi_download_enabled = false;
    public $download_checkbox_state = self::DOWNLOAD_CHECKBOX_NONE;
    
    protected $obj_id;
    protected $ref_id;
    protected $type;
    protected $sub_obj_id;
    protected $sub_obj_type;
    
    protected $substitutions = null;
    protected $substitutions_enabled = false;
    
    protected $icons_enabled = false;
    protected $checkboxes_enabled = false;
    protected $position_enabled = false;
    protected $progress_enabled = false;
    protected $item_detail_links_enabled = false;
    protected $item_detail_links = array();
    protected $item_detail_links_intro = '';
    
    protected $search_fragments_enabled = false;
    protected $search_fragment = '';
    protected $path_linked = false;

    protected $enabled_relevance = false;
    protected $relevance = 0;

    protected $expand_enabled = false;
    protected $is_expanded = true;
    protected $bold_title = false;
    
    protected $copy_enabled = true;
    
    protected $details_level = self::DETAILS_ALL;
    
    protected $reference_ref_id = false;
    protected $separate_commands = false;
    protected $search_fragment_enabled = false;
    protected $additional_information = false;
    protected $static_link_enabled = false;
    
    protected $repository_transfer_enabled = false;
    protected $shared = false;
    protected $restrict_to_goto = false;
    
    protected $comments_enabled = false;
    protected $comments_settings_enabled = false;
    protected $notes_enabled = false;
    protected $tags_enabled = false;
    
    protected $rating_enabled = false;
    protected $rating_categories_enabled = false;
    protected $rating_text = false;
    protected $rating_ctrl_path = false;
    
    protected $timings_enabled = true;
    protected $force_visible_only = false;
    protected $prevent_duplicate_commands = array();
    protected $parent_ref_id;
    protected $context;

    protected static $cnt_notes = array();
    protected static $cnt_tags = array();
    protected static $tags = array();
    protected static $comments_activation = array();
    protected static $preload_done = false;
    
    protected $title_link = '';
    protected $title_link_disabled = false;
    
    protected static $js_unique_id = 0;
    
    
    protected static $tpl_file_name = "tpl.container_list_item.html";
    protected static $tpl_component = "Services/Container";

    /**
     * @var ilPathGUI|null
     */
    protected $path_gui = null;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilObjectService
     */
    protected $object_service;

    /**
     * @var ilFavouritesManager
     */
    protected $fav_manager;

    /**
    * constructor
    *
    */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
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
        $this->mode = IL_LIST_FULL;
        $this->path_enabled = false;
        $this->context = $a_context;

        $this->object_service = $DIC->object();
        
        $this->enableComments(false);
        $this->enableNotes(false);
        $this->enableTags(false);
        
        // unique js-ids
        $this->setParentRefId((int) $_REQUEST["ref_id"]);
        
        //echo "list";
        $this->init();
        
        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $this->ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
        $this->fav_manager = new ilFavouritesManager();

        $this->lng->loadLanguageModule("obj");
        $this->lng->loadLanguageModule("rep");
    }


    /**
    * set the container object (e.g categorygui)
    * Used for link, delete ... commands
    *
    * this method should be overwritten by derived classes
    */
    public function setContainerObject($container_obj)
    {
        $this->container_obj = $container_obj;
    }
    
    /**
     * get container object
     *
     * @access public
     * @param
     * @return object container
     */
    public function getContainerObject()
    {
        return $this->container_obj;
    }


    /**
    * initialisation
    *
    * this method should be overwritten by derived classes
    */
    public function init()
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
        include_once('./Services/Object/classes/class.ilObjectAccess.php');
        $this->commands = ilObjectAccess::_getCommands();
    }

    // Single get set methods
    /**
    * En/disable properties
    *
    * @param bool
    * @return void
    */
    public function enableProperties($a_status)
    {
        $this->properties_enabled = $a_status;

        return;
    }
    /**
    *
    *
    * @param bool
    * @return bool
    */
    public function getPropertiesStatus()
    {
        return $this->properties_enabled;
    }
    /**
    * En/disable preconditions
    *
    * @param bool
    * @return void
    */
    public function enablePreconditions($a_status)
    {
        $this->preconditions_enabled = $a_status;

        return;
    }
    
    public function getNoticePropertiesStatus()
    {
        return $this->notice_properties_enabled;
    }
    
    /**
    * En/disable notices
    *
    * @param bool
    * @return void
    */
    public function enableNoticeProperties($a_status)
    {
        $this->notice_properties_enabled = $a_status;

        return;
    }
    /**
    *
    *
    * @param bool
    * @return bool
    */
    public function getPreconditionsStatus()
    {
        return $this->preconditions_enabled;
    }
    /**
    * En/disable description
    *
    * @param bool
    * @return void
    */
    public function enableDescription($a_status)
    {
        $this->description_enabled = $a_status;

        return;
    }
    /**
    *
    *
    * @param bool
    * @return bool
    */
    public function getDescriptionStatus()
    {
        return $this->description_enabled;
    }
    
    /**
    * Show hide search result fragments
    *
    * @param bool
    * @return bool
    */
    public function getSearchFragmentStatus()
    {
        return $this->search_fragment_enabled;
    }
    
    /**
    * En/disable description
    *
    * @param bool
    * @return void
    */
    public function enableSearchFragments($a_status)
    {
        $this->search_fragment_enabled = $a_status;

        return;
    }
    
    /**
     * Enable linked path
     * @param bool
     * @return
     */
    public function enableLinkedPath($a_status)
    {
        $this->path_linked = $a_status;
    }
    
    /**
     * enabled relevance
     * @return
     */
    public function enabledRelevance()
    {
        return $this->enabled_relevance;
    }
    
    /**
     * enable relevance
     * @return
     */
    public function enableRelevance($a_status)
    {
        $this->enabled_relevance = $a_status;
    }
    
    /**
     * set relevance
     * @param int
     * @return
     */
    public function setRelevance($a_rel)
    {
        $this->relevance = $a_rel;
    }
    
    /**
     * get relevance
     * @param
     * @return
     */
    public function getRelevance()
    {
        return $this->relevance;
    }
    
    /**
    * En/Dis-able icons
    *
    * @param boolean	icons on/off
    */
    public function enableIcon($a_status)
    {
        $this->icons_enabled = $a_status;
    }
    
    /**
    * Are icons enabled?
    *
    * @return boolean	icons enabled?
    */
    public function getIconStatus()
    {
        return $this->icons_enabled;
    }
        
    /**
    * En/Dis-able checkboxes
    *
    * @param boolean	checkbox on/off
    */
    public function enableCheckbox($a_status)
    {
        $this->checkboxes_enabled = $a_status;
    }
    
    /**
    * Are checkboxes enabled?
    *
    * @return boolean	icons enabled?
    */
    public function getCheckboxStatus()
    {
        return $this->checkboxes_enabled;
    }
    
    /**
    * En/Dis-able expand/collapse link
    *
    * @param boolean	checkbox on/off
    */
    public function enableExpand($a_status)
    {
        $this->expand_enabled = $a_status;
    }
    
    /**
    * Is expand/collapse enabled
    *
    * @return boolean	icons enabled?
    */
    public function getExpandStatus()
    {
        return $this->expand_enabled;
    }
    
    public function setExpanded($a_status)
    {
        $this->is_expanded = $a_status;
    }
    
    public function isExpanded()
    {
        return $this->is_expanded;
    }
    /**
    * Set position input field
    *
    * @param	string		$a_field_index			e.g. "[crs][34]"
    * @param	string		$a_position_value		e.g. "2.0"
    */
    public function setPositionInputField($a_field_index, $a_position_value)
    {
        $this->position_enabled = true;
        $this->position_field_index = $a_field_index;
        $this->position_value = $a_position_value;
    }

    /**
    * En/disable delete
    *
    * @param bool
    * @return void
    */
    public function enableDelete($a_status)
    {
        $this->delete_enabled = $a_status;

        return;
    }
    /**
    *
    *
    * @param bool
    * @return bool
    */
    public function getDeleteStatus()
    {
        return $this->delete_enabled;
    }

    /**
    * En/disable cut
    *
    * @param bool
    * @return void
    */
    public function enableCut($a_status)
    {
        $this->cut_enabled = $a_status;

        return;
    }
    /**
    *
    * @param bool
    * @return bool
    */
    public function getCutStatus()
    {
        return $this->cut_enabled;
    }
    
    /**
    * En/disable copy
    *
    * @param bool
    * @return void
    */
    public function enableCopy($a_status)
    {
        $this->copy_enabled = $a_status;

        return;
    }
    /**
    *
    * @param bool
    * @return bool
    */
    public function getCopyStatus()
    {
        return $this->copy_enabled;
    }

    /**
    * En/disable subscribe
    *
    * @param bool
    * @return void
    */
    public function enableSubscribe($a_status)
    {
        $this->subscribe_enabled = $a_status;

        return;
    }
    /**
    *
    * @param bool
    * @return bool
    */
    public function getSubscribeStatus()
    {
        return $this->subscribe_enabled;
    }

    /**
    * En/disable link
    *
    * @param bool
    * @return void
    */
    public function enableLink($a_status)
    {
        $this->link_enabled = $a_status;

        return;
    }
    /**
    *
    * @param bool
    * @return bool
    */
    public function getLinkStatus()
    {
        return $this->link_enabled;
    }

    /**
    * En/disable path
    *
    * @param bool
    * @param int
    * @return void
    */
    public function enablePath($a_path, $a_start_node = null, \ilPathGUI $path_gui = null)
    {
        $this->path_enabled = $a_path;
        $this->path_start_node = (int) $a_start_node;
        $this->path_gui = $path_gui;
    }

    /**
    *
    * @param bool
    * @return bool
    */
    public function getPathStatus()
    {
        return $this->path_enabled;
    }
    
    /**
    * En/disable commands
    *
    * @param bool
    * @return void
    */
    public function enableCommands($a_status, $a_std_only = false)
    {
        $this->commands_enabled = $a_status;
        $this->std_cmd_only = $a_std_only;
    }
    /**
    *
    * @param bool
    * @return bool
    */
    public function getCommandsStatus()
    {
        return $this->commands_enabled;
    }

    /**
    * En/disable path
    *
    * @param bool
    * @return void
    */
    public function enableInfoScreen($a_info_screen)
    {
        $this->info_screen_enabled = $a_info_screen;
    }

    /**
    * Add HTML for subitem (used for sessions)
    *
    * @param	string	$a_html		subitems HTML
    */
    public function addSubItemHTML($a_html)
    {
        $this->sub_item_html[] = $a_html;
    }
    
    /**
    *
    * @param bool
    * @return bool
    */
    public function getInfoScreenStatus()
    {
        return $this->info_screen_enabled;
    }
    
    /**
     * enable progress info
     *
     * @access public
     * @param
     * @return
     */
    public function enableProgressInfo($a_status)
    {
        $this->progress_enabled = $a_status;
    }
    
    /**
     * get progress info status
     *
     * @access public
     * @param
     * @return
     */
    public function getProgressInfoStatus()
    {
        return $this->progress_enabled;
    }
    
    /**
     * Enable substitutions
     *
     * @access public
     * @param
     *
     */
    public function enableSubstitutions($a_status)
    {
        $this->substitutions_enabled = $a_status;
    }
    
    /**
     * Get substitution status
     *
     * @access public
     *
     */
    public function getSubstitutionStatus()
    {
        return $this->substitutions_enabled;
    }
    
    /**
     * enable item detail links
     * E.g Direct links to chapters or pages
     *
     * @access public
     * @param bool
     * @return
     */
    public function enableItemDetailLinks($a_status)
    {
        $this->item_detail_links_enabled = $a_status;
    }
    
    /**
     * get item detail link status
     *
     * @access public
     * @return bool
     */
    public function getItemDetailLinkStatus()
    {
        return $this->item_detail_links_enabled;
    }
    
    /**
     * set items detail links
     *
     * @access public
     * @param array e.g. array(0 => array('desc' => 'Page: ','link' => 'ilias.php...','name' => 'Page XYZ')
     * @return
     */
    public function setItemDetailLinks($a_detail_links, $a_intro_txt = '')
    {
        $this->item_detail_links = $a_detail_links;
        $this->item_detail_links_intro = $a_intro_txt;
    }
    
    /**
     * insert item detail links
     *
     * @access public
     * @param
     * @return
     */
    public function insertItemDetailLinks()
    {
        if (!count($this->item_detail_links)) {
            return true;
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
    
    

    /**
    * @param string title
    * @return bool
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * getTitle overwritten in class.ilObjLinkResourceList.php
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * @param string description
    * @return bool
    */
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }

    /**
     * getDescription overwritten in class.ilObjLinkResourceList.php
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * set search fragment
     * @param string $a_text highlighted search fragment
     * @return
     */
    public function setSearchFragment($a_text)
    {
        $this->search_fragment = $a_text;
    }
    
    /**
     * get search fragment
     * @return
     */
    public function getSearchFragment()
    {
        return $this->search_fragment;
    }
    
    /**
    * Set separate commands
    *
    * @param	boolean	 separate commands
    */
    public function setSeparateCommands($a_val)
    {
        $this->separate_commands = $a_val;
    }
    
    /**
    * Get separate commands
    *
    * @return	boolean	 separate commands
    */
    public function getSeparateCommands()
    {
        return $this->separate_commands;
    }
    /**
     * get command id
     * Normally the ref id.
     * Overwritten for course and category references
     *
     * @access public
     * @param
     * @return
     */
    public function getCommandId()
    {
        return $this->ref_id;
    }
    
    /**
    * Set additional information
    *
    * @param	string		additional information
    */
    public function setAdditionalInformation($a_val)
    {
        $this->additional_information = $a_val;
    }
    
    /**
    * Get additional information
    *
    * @return	string		additional information
    */
    public function getAdditionalInformation()
    {
        return $this->additional_information;
    }
    
    /**
     * Details level
     * Currently used in Search which shows only limited properties of forums
     * Currently used for Sessions (switch between minimal and extended view for each session)
     * @param int $a_level
     * @return
     */
    public function setDetailsLevel($a_level)
    {
        $this->details_level = $a_level;
    }
    
    /**
     * Get current details level
     * @return
     */
    public function getDetailsLevel()
    {
        return $this->details_level;
    }
    
    /**
     * Enable copy/move to repository (from personal workspace)
     *
     * @param bool $a_value
     */
    public function enableRepositoryTransfer($a_value)
    {
        $this->repository_transfer_enabled = (bool) $a_value;
    }
    
    /**
     * Restrict all actions/links to goto
     *
     * @param bool $a_value
     */
    public function restrictToGoto($a_value)
    {
        $this->restrict_to_goto = (bool) $a_value;
    }


    /**
     * Get default command
     *
     * @return array
     */
    public function getDefaultCommand()
    {
        return $this->default_command;
    }

    /**
     *
     * @param
     * @return
     */
    public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id = "")
    {
        $ilAccess = $this->access;
        
        // e.g: subitems should not be readable since their parent sesssion is readonly.
        if ($a_permission != 'visible' and $this->isVisibleOnlyForced()) {
            return false;
        }

        $cache_prefix = null;
        if ($this->context == self::CONTEXT_WORKSPACE || $this->context == self::CONTEXT_WORKSPACE_SHARING) {
            $cache_prefix = "wsp";
            if (!$this->ws_access) {
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
                $this->ws_access = new ilWorkspaceAccessHandler();
            }
        }

        if (isset($this->access_cache[$a_permission]["-" . $a_cmd][$cache_prefix . $a_ref_id])) {
            return $this->access_cache[$a_permission]["-" . $a_cmd][$cache_prefix . $a_ref_id];
        }

        if ($this->context == self::CONTEXT_REPOSITORY) {
            $access = $ilAccess->checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
            if ($ilAccess->getPreventCachingLastResult()) {
                $this->prevent_access_caching = true;
            }
        } else {
            $access = $this->ws_access->checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type);
        }

        $this->access_cache[$a_permission]["-" . $a_cmd][$cache_prefix . $a_ref_id] = $access;
        return $access;
    }
    
    /**
    * inititialize new item (is called by getItemHTML())
    *
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	string		$a_title		title
    * @param	string		$a_description	description
    * @param	int			$a_context		tree/workspace
    */
    public function initItem($a_ref_id, $a_obj_id, $type, $a_title = "", $a_description = "")
    {
        $this->offline_mode = false;
        if ($this->type == "sahs") {
            include_once('Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php');
            $this->offline_mode = ilObjSAHSLearningModuleAccess::_lookupUserIsOfflineMode($a_obj_id);
        }
        $this->access_cache = array();
        $this->ref_id = $a_ref_id;
        $this->obj_id = $a_obj_id;
        $this->setTitle($a_title);
        $this->setDescription($a_description);
        #$this->description = $a_description;
                
        // checks, whether any admin commands are included in the output
        $this->adm_commands_included = false;
        $this->prevent_access_caching = false;

        // prepare ajax calls
        include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
        if ($this->context == self::CONTEXT_REPOSITORY) {
            $node_type = ilCommonActionDispatcherGUI::TYPE_REPOSITORY;
        } else {
            $node_type = ilCommonActionDispatcherGUI::TYPE_WORKSPACE;
        }
        $this->setAjaxHash(ilCommonActionDispatcherGUI::buildAjaxHash($node_type, $a_ref_id, $type, $a_obj_id));
    }

    public function setConditionTarget($a_ref_id, $a_obj_id, $a_target_type)
    {
        $this->condition_target = array(
            'ref_id' => $a_ref_id,
            'obj_id' => $a_obj_id,
            'target_type' => $a_target_type
        );
    }
    
    public function resetConditionTarget()
    {
        $this->condition_target = array();
    }
    
    public function disableTitleLink($a_status)
    {
        $this->title_link_disabled = $a_status;
    }
    // end-patch lok
    
    public function setDefaultCommandParameters(array $a_params)
    {
        $this->default_command_params = $a_params;
    }
    
    /**
     * Get default command link
     * Overwritten for e.g categories,courses => they return a goto link
     * If search engine visibility is enabled these object type return a goto_CLIENT_ID_cat_99.html link
     *
     * @access public
     * @param int command link
     *
     */
    public function createDefaultCommand($command)
    {
        // begin-patch lok
        if ($this->static_link_enabled and !$this->default_command_params) {
            include_once('./Services/Link/classes/class.ilLink.php');
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
            
            // #12370
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
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command link url
    */
    public function getCommandLink($a_cmd)
    {
        if ($this->context == self::CONTEXT_REPOSITORY) {
            // BEGIN WebDAV Get mount webfolder link.
            require_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
            if ($a_cmd == 'mount_webfolder' && ilDAVActivationChecker::_isActive()) {
                global $DIC;
                $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                return $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
            }
            // END WebDAV Get mount webfolder link.

            $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
            $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
            return $cmd_link;

        /* separate method for this line
        $cmd_link = $this->ctrl->getLinkTargetByClass($this->gui_class_name,
            $a_cmd);
        return $cmd_link;
        */
        } else {
            $this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", "");
            $this->ctrl->setParameterByClass($this->gui_class_name, "wsp_id", $this->ref_id);
            return $this->ctrl->getLinkTargetByClass($this->gui_class_name, $a_cmd);
        }
    }


    /**
    * Get command target frame.
    *
    * Overwrite this method if link frame is not current frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        // begin-patch fm
        if ($a_cmd == 'fileManagerLaunch') {
            return '_blank';
        }
        // end-patch fm
        return "";
    }

    /**
    * Get command icon image
    *
    * Overwrite this method if an icon is provided
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		image path
    */
    public function getCommandImage($a_cmd)
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
    public function getProperties()
    {
        $lng = $this->lng;
        $ilUser = $this->user;

        $props = array();
        // please list alert properties first
        // example (use $lng->txt instead of "Status"/"Offline" strings):
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
                        'property' => $lng->txt("status"),
                        'value' => $lng->txt("offline")
                    ];
            }

            // BEGIN WebDAV Display locking information
            require_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
            if (ilDAVActivationChecker::_isActive()) {
                // Show lock info
                require_once('Services/WebDAV/classes/lock/class.ilWebDAVLockBackend.php');
                $webdav_lock_backend = new ilWebDAVLockBackend();
                if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                    if ($lock = $webdav_lock_backend->getLocksOnObjectId($this->obj_id)) {
                        $lock_user = new ilObjUser($lock->getIliasOwner());

                        $props[] = array(
                            "alert" => false,
                            "property" => $lng->txt("in_use_by"),
                            "value" => $lock_user->getLogin(),
                            "link" => "./ilias.php?user=" . $lock_user->getId() . '&cmd=showUserProfile&cmdClass=ildashboardgui&baseClass=ilDashboardGUI',
                        );
                    }
                }
                // END WebDAV Display locking information

                if ($this->getDetailsLevel() == self::DETAILS_SEARCH) {
                    return $props;
                }
            }
            // END WebDAV Display warning for invisible files and files with special characters
        }

        return $props;
    }
    
    /**
    * add custom property
    */
    public function addCustomProperty(
        $a_property = "",
        $a_value = "",
        $a_alert = false,
        $a_newline = false
    ) {
        $this->cust_prop[] = array("property" => $a_property, "value" => $a_value,
            "alert" => $a_alert, "newline" => $a_newline);
    }
    
    /**
    * get custom properties
    */
    public function getCustomProperties($a_prop)
    {
        if (is_array($this->cust_prop)) {
            foreach ($this->cust_prop as $prop) {
                $a_prop[] = $prop;
            }
        }
        return $a_prop;
    }

    /**
     * get all alert properties
     * @return array
     */
    public function getAlertProperties()
    {
        $alert = array();
        foreach ((array) $this->getProperties() as $prop) {
            if ($prop['alert'] == true) {
                $alert[] = $prop;
            }
        }
        return $alert;
    }
    
    /**
    * get notice properties
    */
    public function getNoticeProperties()
    {
        $this->notice_prop = array();
        if ($infos = $this->ldap_mapping->getInfoStrings($this->obj_id, true)) {
            foreach ($infos as $info) {
                $this->notice_prop[] = array('value' => $info);
            }
        }
        return $this->notice_prop ? $this->notice_prop : array();
    }
    /**
    * add a custom command
    */
    public function addCustomCommand($a_link, $a_lang_var, $a_frame = "", $onclick = "")
    {
        $this->cust_commands[] =
            array("link" => $a_link, "lang_var" => $a_lang_var,
            "frame" => $a_frame, "onclick" => $onclick);
    }
    
    /**
     * Force visible access only.
     * @param type $a_stat
     */
    public function forceVisibleOnly($a_stat)
    {
        $this->force_visible_only = $a_stat;
    }

    /**
     * Force unreadable
     * @return type
     */
    public function isVisibleOnlyForced()
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
    * classes becaus it will get pretty large and much code will be simply
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
    public function getCommands()
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;

        $ref_commands = array();
        foreach ($this->commands as $command) {
            $permission = $command["permission"];
            $cmd = $command["cmd"];
            $lang_var = $command["lang_var"];
            $txt = "";
            $info_object = null;
            
            if (isset($command["txt"])) {
                $txt = $command["txt"];
            }

            // BEGIN WebDAV: Suppress commands that don't make sense for anonymous users.
            // Suppress commands that don't make sense for anonymous users
            if ($ilUser->getId() == ANONYMOUS_USER_ID &&
                $command['enable_anonymous'] == 'false') {
                continue;
            }
            // END WebDAV: Suppress commands that don't make sense for anonymous users.

            // all access checking should be made within $ilAccess and
            // the checkAccess of the ilObj...Access classes
            //$access = $ilAccess->checkAccess($permission, $cmd, $this->ref_id, $this->type);
            $access = $this->checkCommandAccess($permission, $cmd, $this->ref_id, $this->type);

            if ($access) {
                $cmd_link = $this->getCommandLink($command["cmd"]);
                $cmd_frame = $this->getCommandFrame($command["cmd"]);
                $cmd_image = $this->getCommandImage($command["cmd"]);
                $access_granted = true;
            } else {
                $access_granted = false;
                $info_object = $ilAccess->getInfo();
            }

            if (!isset($command["default"])) {
                $command["default"] = "";
            }
            $ref_commands[] = array(
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
            );
        }

        return $ref_commands;
    }

    // BEGIN WebDAV: Visualize object state in its icon.
    /**
    * Returns the icon image type.
    * For most objects, this is same as the object type, e.g. 'cat','fold'.
    * We can return here other values, to express a specific state of an object,
    * e.g. 'crs_offline", and/or to express a specific kind of object, e.g.
    * 'file_inline'.
    */
    public function getIconImageType()
    {
        if ($this->type == "sahs" && $this->offline_mode) {
            return $this->type . "_offline";
        }
        return $this->type;
    }
    // END WebDAV: Visualize object state in its icon.

    /**
    * insert item title
    *
    * @access	private
    * @param	object		$a_tpl		template object
    * @param	string		$a_title	item title
    */
    public function insertTitle()
    {
        if ($this->restrict_to_goto) {
            $this->default_command = array("frame" => "",
                "link" => $this->buildGotoLink());
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
            $this->tpl->parseCurrentBlock();
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
            #var_dump("<pre>",$this->default_command['link'],"</pre>");
            $this->default_command["link"] =
                $this->appendRepositoryFrameParameter($this->default_command["link"]);

            #var_dump("<pre>",$this->default_command['link'],"</pre>");
            

            // the default command is linked with the title
            $this->tpl->setCurrentBlock("item_title_linked");
            $this->tpl->setVariable("TXT_TITLE_LINKED", $this->getTitle());
            $this->tpl->setVariable("HREF_TITLE_LINKED", $this->default_command["link"]);
            
            // has preview?
            include_once("./Services/Preview/classes/class.ilPreview.php");
            if (ilPreview::hasPreview($this->obj_id, $this->type)) {
                include_once("./Services/Preview/classes/class.ilPreviewGUI.php");
                
                // get context for access checks later on
                $access_handler = null;
                switch ($this->context) {
                    case self::CONTEXT_WORKSPACE:
                    case self::CONTEXT_WORKSPACE_SHARING:
                        $context = ilPreviewGUI::CONTEXT_WORKSPACE;
                        include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php");
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
                $this->tpl->setVariable("SRC_PREVIEW_ICON", ilUtil::getImagePath("preview.png", "Services/Preview"));
                $this->tpl->setVariable("ALT_PREVIEW_ICON", $this->lng->txt($preview_text_topic));
                $this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt($preview_text_topic));
                $this->tpl->setVariable("SCRIPT_PREVIEW_CLICK", $preview->getJSCall($this->getUniqueItemId(true)));
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->parseCurrentBlock();
        }
        
        if ($this->bold_title == true) {
            $this->tpl->touchBlock('bold_title_start');
            $this->tpl->touchBlock('bold_title_end');
        }
    }
    
    protected function buildGotoLink()
    {
        switch ($this->context) {
            case self::CONTEXT_WORKSPACE_SHARING:
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
                return ilWorkspaceAccessHandler::getGotoLink($this->ref_id, $this->obj_id);
            
            default:
                // not implemented yet
                break;
        }
    }
    
    /**
     * Insert substitutions
     *
     * @access public
     *
     */
    public function insertSubstitutions()
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
        if ($fields_shown) {
            $this->tpl->touchBlock('newline_prop');
        }
    }


    /**
    * insert item description
    *
    * @access	private
    * @param	object		$a_tpl		template object
    * @param	string		$a_desc		item description
    */
    public function insertDescription()
    {
        if ($this->getSubstitutionStatus()) {
            $this->insertSubstitutions();
            if (!$this->substitutions->isDescriptionEnabled()) {
                return true;
            }
        }

        // see bug #16519
        $d = $this->getDescription();
        $d = strip_tags($d, "<b>");
        $this->tpl->setCurrentBlock("item_description");
        $this->tpl->setVariable("TXT_DESC", $d);
        $this->tpl->parseCurrentBlock();
    }
    
    /**
     * Insert highlighted search fragment
     * @return
     */
    public function insertSearchFragment()
    {
        if (strlen($this->getSearchFragment())) {
            $this->tpl->setCurrentBlock('search_fragment');
            $this->tpl->setVariable('TXT_SEARCH_FRAGMENT', $this->getSearchFragment() . ' ...');
            $this->tpl->parseCurrentBlock();
        }
    }
    
    /**
     * insert relevance
     * @param
     * @return
     */
    public function insertRelevance()
    {
        if (!$this->enabledRelevance() or !(int) $this->getRelevance()) {
            return false;
        }
        
        include_once "Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php";
        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($this->getRelevance());
        
        $this->tpl->setCurrentBlock('relevance');
        $this->tpl->setVariable('REL_PBAR', $pbar->render());
        $this->tpl->parseCurrentBlock();
    }

    /**
    * set output mode
    *
    * @param	string	$a_mode		output mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
    */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    /**
    * get output mode
    *
    * @return	string		output mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
    */
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
    * set depth for precondition output (stops at level 5)
    */
    public function setConditionDepth($a_depth)
    {
        $this->condition_depth = $a_depth;
    }

    /**
    * check current output mode
    *
    * @param	string		$a_mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
    *
    * @return 	boolen		true if current mode is $a_mode
    */
    public function isMode($a_mode)
    {
        if ($a_mode == $this->mode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine properties
     * @return array
     */
    public function determineProperties()
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilUser = $this->user;

        $props = $this->getProperties();
        $props = $this->getCustomProperties($props);

        if ($this->context != self::CONTEXT_WORKSPACE && $this->context != self::CONTEXT_WORKSPACE_SHARING) {
            // add learning progress custom property
            include_once "Services/Tracking/classes/class.ilLPStatus.php";
            $lp = ilLPStatus::getListGUIStatus($this->obj_id);
            if ($lp) {
                $props[] = array("alert" => false,
                    "property" => $lng->txt("learning_progress"),
                    "value" => $lp,
                    "newline" => true);
            }

            // add no item access note in public section
            // for items that are visible but not readable
            if ($ilUser->getId() == ANONYMOUS_USER_ID) {
                if (!$ilAccess->checkAccess("read", "", $this->ref_id, $this->type, $this->obj_id)) {
                    $props[] = array("alert" => true,
                        "value" => $lng->txt("no_access_item_public"),
                        "newline" => true);
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
        require_once 'Services/Notes/classes/class.ilNote.php';
        if ((self::$cnt_notes[$note_obj_id][IL_NOTE_PRIVATE] > 0 ||
                self::$cnt_notes[$note_obj_id][IL_NOTE_PUBLIC] > 0 ||
                self::$cnt_tags[$note_obj_id] > 0 ||
                is_array(self::$tags[$note_obj_id])) &&
            ($ilUser->getId() != ANONYMOUS_USER_ID)) {
            include_once("./Services/Notes/classes/class.ilNoteGUI.php");
            include_once("./Services/Tagging/classes/class.ilTaggingGUI.php");

            $nl = true;
            if ($this->isCommentsActivated($this->type, $this->ref_id, $this->obj_id, false, false)
                && self::$cnt_notes[$note_obj_id][IL_NOTE_PUBLIC] > 0) {
                $props[] = array("alert" => false,
                    "property" => $lng->txt("notes_comments"),
                    "value" => "<a href='#' onclick=\"return " .
                        ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $redraw_js) . "\">" .
                        self::$cnt_notes[$note_obj_id][IL_NOTE_PUBLIC] . "</a>",
                    "newline" => $nl);
                $nl = false;
            }

            if ($this->notes_enabled && self::$cnt_notes[$note_obj_id][IL_NOTE_PRIVATE] > 0) {
                $props[] = array("alert" => false,
                    "property" => $lng->txt("notes"),
                    "value" => "<a href='#' onclick=\"return " .
                        ilNoteGUI::getListNotesJSCall($this->ajax_hash, $redraw_js) . "\">" .
                        self::$cnt_notes[$note_obj_id][IL_NOTE_PRIVATE] . "</a>",
                    "newline" => $nl);
                $nl = false;
            }
            if ($this->tags_enabled &&
                (self::$cnt_tags[$note_obj_id] > 0 ||
                    is_array(self::$tags[$note_obj_id]))) {
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
                        $prop_text = $lng->txt("tagging_tags");
                    }
                    $props[] = array("alert" => false,
                        "property" => $prop_text,
                        "value" => $tags_value,
                        "newline" => $nl);
                    $nl = false;
                }
            }
        }
        if (!is_array($props)) {
            return [];
        }
        return $props;
    }

    /**
    * insert properties
    *
    * @access	private
    */
    public function insertProperties()
    {
        $props = $this->determineProperties();
        $cnt = 1;
        if (is_array($props) && count($props) > 0) {
            foreach ($props as $prop) {
                // BEGIN WebDAV: Display a separator between properties.
                if ($cnt > 1) {
                    $this->tpl->touchBlock("separator_prop");
                }
                // END WebDAV: Display a separator between properties.

                if ($prop["alert"] == true) {
                    $this->tpl->touchBlock("alert_prop");
                } else {
                    $this->tpl->touchBlock("std_prop");
                }
                if ($prop["newline"] == true && $cnt > 1) {
                    $this->tpl->touchBlock("newline_prop");
                }
                //BEGIN WebDAV: Support hidden property names.
                if (isset($prop["property"]) && $prop['propertyNameVisible'] !== false && $prop["property"] != "") {
                    //END WebDAV: Support hidden property names.
                    $this->tpl->setCurrentBlock("prop_name");
                    $this->tpl->setVariable("TXT_PROP", $prop["property"]);
                    $this->tpl->parseCurrentBlock();
                }
                $this->tpl->setCurrentBlock("item_property");
                //BEGIN WebDAV: Support links in property values.
                if ($prop['link']) {
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
    
    public function insertNoticeProperties()
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

    protected function parseConditions($toggle_id, $conditions, $obligatory = true)
    {
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        $tree = $this->tree;
        
        $num_required = ilConditionHandler::calculateEffectiveRequiredTriggers($this->ref_id, $this->obj_id);
        $num_optional_required =
            $num_required - count($conditions) + count(ilConditionHandler::getEffectiveOptionalConditionsOfTarget($this->ref_id, $this->obj_id));

        // Check if all conditions are fullfilled
        $visible_conditions = array();
        $passed_optional = 0;
        foreach ($conditions as $condition) {
            if ($obligatory and !$condition['obligatory']) {
                continue;
            }
            if (!$obligatory and $condition['obligatory']) {
                continue;
            }

            if ($tree->isDeleted($condition['trigger_ref_id'])) {
                continue;
            }

            include_once 'Services/Container/classes/class.ilMemberViewSettings.php';
            $ok = ilConditionHandler::_checkCondition($condition) and
                !ilMemberViewSettings::getInstance()->isActive();

            if (!$ok) {
                $visible_conditions[] = $condition['id'];
            }

            if (!$obligatory and $ok) {
                ++$passed_optional;
                // optional passed
                if ($passed_optional >= $num_optional_required) {
                    return true;
                }
            }
        }

        foreach ($conditions as $condition) {
            if (!in_array($condition['id'], $visible_conditions)) {
                continue;
            }

            include_once './Services/Conditions/classes/class.ilConditionHandlerGUI.php';
            $cond_txt = ilConditionHandlerGUI::translateOperator($condition['trigger_obj_id'], $condition['operator']) . ' ' . $condition['value'];
            
            // display trigger item
            $class = $objDefinition->getClassName($condition["trigger_type"]);
            $location = $objDefinition->getLocation($condition["trigger_type"]);
            if ($class == "" && $location == "") {
                continue;
            }
            $missing_cond_exist = true;

            $full_class = "ilObj" . $class . "ListGUI";
            include_once($location . "/class." . $full_class . ".php");
            $item_list_gui = new $full_class($this);
            $item_list_gui->setMode(IL_LIST_AS_TRIGGER);
            $item_list_gui->enablePath(false);
            $item_list_gui->enableIcon(true);
            $item_list_gui->setConditionDepth($this->condition_depth + 1);
            $item_list_gui->setParentRefId($this->getUniqueItemId()); // yes we can
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
        
        if ($missing_cond_exist and $obligatory) {
            $this->tpl->setCurrentBlock("preconditions");
            $this->tpl->setVariable("CONDITION_TOGGLE_ID", "_obl_" . $toggle_id);
            $this->tpl->setVariable("TXT_PRECONDITIONS", $lng->txt("preconditions_obligatory_hint"));
            $this->tpl->parseCurrentBlock();
        } elseif ($missing_cond_exist and !$obligatory) {
            $this->tpl->setCurrentBlock("preconditions");
            $this->tpl->setVariable("CONDITION_TOGGLE_ID", "_opt_" . $toggle_id);
            $this->tpl->setVariable("TXT_PRECONDITIONS", sprintf($lng->txt("preconditions_optional_hint"), $num_optional_required));
            $this->tpl->parseCurrentBlock();
        }

        return !$missing_cond_exist;
    }

    /**
    * insert all missing preconditions
    */
    public function insertPreconditions()
    {
        include_once("./Services/Conditions/classes/class.ilConditionHandler.php");

        // do not show multi level conditions (messes up layout)
        if ($this->condition_depth > 0) {
            return;
        }

        if ($this->condition_target) {
            $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget(
                $this->condition_target['ref_id'],
                $this->condition_target['obj_id'],
                $this->condition_target['target_type']
            );
        } else {
            $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($this->ref_id, $this->obj_id);
        }
        
        if (sizeof($conditions)) {
            for ($i = 0; $i < count($conditions); $i++) {
                $conditions[$i]['title'] = ilObject::_lookupTitle($conditions[$i]['trigger_obj_id']);
            }
            $conditions = ilUtil::sortArray($conditions, 'title', 'DESC');
        
            ++self::$js_unique_id;

            // Show obligatory and optional preconditions seperated
            $all_done_obl = $this->parseConditions(self::$js_unique_id, $conditions, true);
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
    *
    * @access	private
    * @param	string		$a_href		link url target
    * @param	string		$a_text		link text
    * @param	string		$a_frame	link frame target
    */
    public function insertCommand($a_href, $a_text, $a_frame = "", $a_img = "", $a_cmd = "", $a_onclick = "")
    {
        // #11099
        $chksum = md5($a_href . $a_text);
        if ($a_href == "#" ||
            !in_array($chksum, $this->prevent_duplicate_commands)) {
            if ($a_href != "#") {
                $this->prevent_duplicate_commands[] = $chksum;
            }
            
            $prevent_background_click = false;
            if ($a_cmd == 'mount_webfolder') {
                $a_onclick = "triggerWebDAVModal('$a_href')";
                $a_href = "#";
                ilWebDAVMountInstructionsModalGUI::maybeRenderWebDAVModalInGlobalTpl();
            }

            $this->current_selection_list->addItem(
                $a_text,
                "",
                $a_href,
                $a_img,
                $a_text,
                $a_frame,
                "",
                $prevent_background_click,
                $a_onclick
            );
        }
    }

    /**
    * insert cut command
    *
    * @access	private
    * @param	object		$a_tpl		template object
    * @param	int			$a_ref_id	item reference id
    */
    public function insertDeleteCommand()
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
                return true;
            }
            return false;
        }
        
        if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type)) {
            $this->ctrl->setParameter(
                $this->container_obj,
                "ref_id",
                $this->container_obj->object->getRefId()
            );
            $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "delete");
            $this->insertCommand(
                $cmd_link,
                $this->lng->txt("delete"),
                "",
                ""
            );
            $this->adm_commands_included = true;
        }
    }

    /**
    * insert link command
    *
    * @access	private
    * @param	object		$a_tpl		template object
    * @param	int			$a_ref_id	item reference id
    */
    public function insertLinkCommand()
    {
        $objDefinition = $this->obj_definition;

        if ($this->std_cmd_only) {
            return;
        }
        
        // #17307
        if (!$this->checkCommandAccess('delete', '', $this->ref_id, $this->type) or
            !$objDefinition->allowLink($this->type)) {
            return false;
        }
        
        // BEGIN PATCH Lucene search
        
        if (is_object($this->getContainerObject()) and
            $this->getContainerObject() instanceof ilAdministrationCommandHandling) {
            $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "link");
            $this->insertCommand($cmd_link, $this->lng->txt("link"));
            $this->adm_commands_included = true;
            return true;
        }
        // END PATCH Lucene Search

        // if the permission is changed here, it  has
        // also to be changed in ilContainerGUI, admin command check
        $this->ctrl->setParameter(
            $this->container_obj,
            "ref_id",
            $this->container_obj->object->getRefId()
        );
        $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
        $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "link");
        $this->insertCommand(
            $cmd_link,
            $this->lng->txt("link"),
            "",
            ""
        );
        $this->adm_commands_included = true;
        return true;
    }

    /**
    * insert cut command
    *
    * @access	protected
    * @param	bool	$a_to_repository
    */
    public function insertCutCommand($a_to_repository = false)
    {
        if ($this->std_cmd_only) {
            return;
        }
        // BEGIN PATCH Lucene search
        if (is_object($this->getContainerObject()) and
            $this->getContainerObject() instanceof ilAdministrationCommandHandling) {
            if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type)) {
                $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
                $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "cut");
                $this->insertCommand($cmd_link, $this->lng->txt("move"));
                $this->adm_commands_included = true;
                return true;
            }
            return false;
        }
        // END PATCH Lucene Search

        // if the permission is changed here, it  has
        // also to be changed in ilContainerContentGUI, determineAdminCommands
        if ($this->checkCommandAccess('delete', '', $this->ref_id, $this->type) &&
            $this->container_obj->object) {
            $this->ctrl->setParameter(
                $this->container_obj,
                "ref_id",
                $this->container_obj->object->getRefId()
            );
            $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
            
            if (!$a_to_repository) {
                $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "cut");
                $this->insertCommand(
                    $cmd_link,
                    $this->lng->txt("move"),
                    "",
                    ""
                );
            } else {
                $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "cut_for_repository");
                $this->insertCommand(
                    $cmd_link,
                    $this->lng->txt("wsp_move_to_repository"),
                    "",
                    ""
                );
            }
            
            $this->adm_commands_included = true;
        }
    }
    
    /**
     * Insert copy command
     *
     * @param	bool	$a_to_repository
     */
    public function insertCopyCommand($a_to_repository = false)
    {
        $objDefinition = $this->obj_definition;
        
        if ($this->std_cmd_only) {
            return;
        }
        
        if ($this->checkCommandAccess('copy', 'copy', $this->ref_id, $this->type) &&
            $objDefinition->allowCopy($this->type)) {
            if ($this->context != self::CONTEXT_WORKSPACE && $this->context != self::CONTEXT_WORKSPACE_SHARING) {
                $this->ctrl->setParameterByClass('ilobjectcopygui', 'source_id', $this->getCommandId());
                $cmd_copy = $this->ctrl->getLinkTargetByClass('ilobjectcopygui', 'initTargetSelection');
                $this->insertCommand($cmd_copy, $this->lng->txt('copy'));
            } else {
                $this->ctrl->setParameter(
                    $this->container_obj,
                    "ref_id",
                    $this->container_obj->object->getRefId()
                );
                $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
                
                if (!$a_to_repository) {
                    $cmd_copy = $this->ctrl->getLinkTarget($this->container_obj, 'copy');
                    $this->insertCommand($cmd_copy, $this->lng->txt('copy'));
                } else {
                    $cmd_copy = $this->ctrl->getLinkTarget($this->container_obj, 'copy_to_repository');
                    $this->insertCommand($cmd_copy, $this->lng->txt('wsp_copy_to_repository'));
                }
            }
                
            $this->adm_commands_included = true;
        }
        return;
    }


    /**
     * Insert paste command
     */
    public function insertPasteCommand()
    {
        $objDefinition = $this->obj_definition;
        
        if ($this->std_cmd_only) {
            return;
        }
        
        if (!$objDefinition->isContainer(ilObject::_lookupType($this->obj_id))) {
            return false;
        }
        
        if (is_object($this->getContainerObject()) and
            $this->getContainerObject() instanceof ilAdministrationCommandHandling and
            isset($_SESSION['clipboard'])) {
            $this->ctrl->setParameter($this->getContainerObject(), 'item_ref_id', $this->getCommandId());
            $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "paste");
            $this->insertCommand($cmd_link, $this->lng->txt("paste"));
            $this->adm_commands_included = true;
            return true;
        }
        return false;
    }

    /**
    * insert subscribe command
    *
    * @access	private
    * @param	object		$a_tpl		template object
    * @param	int			$a_ref_id	item reference id
    */
    public function insertSubscribeCommand()
    {
        $ilSetting = $this->settings;
        $ilUser = $this->user;

        if ($this->std_cmd_only) {
            return;
        }
        
        if ((int) $ilSetting->get('disable_my_offers')) {
            return;
        }
        
        $type = ilObject::_lookupType(ilObject::_lookupObjId($this->getCommandId()));

        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            // #17467 - add ref_id to link (in repository only!)
            if (is_object($this->container_obj) &&
                !($this->container_obj instanceof ilAdministrationCommandHandling) &&
                is_object($this->container_obj->object)) {
                $this->ctrl->setParameter($this->container_obj, "ref_id", $this->container_obj->object->getRefId());
            }

            if (!$this->fav_manager->ifIsFavourite($ilUser->getId(), $this->getCommandId())) {
                // Pass type and object ID to ilAccess to improve performance
                if ($this->checkCommandAccess("read", "", $this->ref_id, $this->type, $this->obj_id)) {
                    if ($this->getContainerObject() instanceof ilDesktopItemHandling) {
                        $this->ctrl->setParameter($this->container_obj, "type", $type);
                        $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
                        $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "addToDesk");
                        $this->insertCommand(
                            $cmd_link,
                            $this->lng->txt("rep_add_to_favourites"),
                            "",
                            ""
                        );
                    }
                }
            } else {
                if ($this->getContainerObject() instanceof ilDesktopItemHandling) {
                    $this->ctrl->setParameter($this->container_obj, "type", $type);
                    $this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
                    $cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "removeFromDesk");
                    $this->insertCommand(
                        $cmd_link,
                        $this->lng->txt("rep_remove_from_favourites"),
                        "",
                        ""
                    );
                }
            }
        }
    }

    /**
     * insert info screen command
     */
    public function insertInfoScreenCommand()
    {
        if ($this->std_cmd_only) {
            return;
        }
        $cmd_link = $this->getCommandLink("infoScreen");
        $cmd_frame = $this->getCommandFrame("infoScreen");
        $this->insertCommand(
            $cmd_link,
            $this->lng->txt("info_short"),
            $cmd_frame,
            ilUtil::getImagePath("icon_info.svg")
        );
    }
    
    /**
     * Insert common social commands (comments, notes, tagging)
     *
     * @param
     * @return
     */
    public function insertCommonSocialCommands($a_header_actions = false)
    {
        $lng = $this->lng;
        $ilUser = $this->user;

        if ($this->std_cmd_only ||
            ($ilUser->getId() == ANONYMOUS_USER_ID)) {
            return;
        }
        $lng->loadLanguageModule("notes");
        $lng->loadLanguageModule("tagging");
        $cmd_frame = $this->getCommandFrame("infoScreen");
        include_once("./Services/Notes/classes/class.ilNoteGUI.php");
        
        // reference objects have translated ids, revert to originals
        $note_ref_id = $this->ref_id;
        if ($this->reference_ref_id) {
            $note_ref_id = $this->reference_ref_id;
        }
        
        $js_updater = $a_header_actions
            ? "il.Object.redrawActionHeader();"
            : "il.Object.redrawListItem(" . $note_ref_id . ")";
        
        $comments_enabled = $this->isCommentsActivated($this->type, $this->ref_id, $this->obj_id, $a_header_actions, true);
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
            include_once("./Services/Tagging/classes/class.ilTaggingGUI.php");
            //$this->insertCommand($cmd_tag_link, $this->lng->txt("tagging_set_tag"), $cmd_frame);
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
    
    /**
    * insert edit timings command
    *
    * @access	protected
    */
    public function insertTimingsCommand()
    {
        if ($this->std_cmd_only || !$this->container_obj->object) {
            return;
        }
        
        $parent_ref_id = $this->container_obj->object->getRefId();
        $parent_type = $this->container_obj->object->getType();
        
        // #18737
        if ($this->reference_ref_id) {
            $this->ctrl->setParameterByClass('ilobjectactivationgui', 'ref_id', $this->reference_ref_id);
        }
        
        if ($this->checkCommandAccess('write', '', $parent_ref_id, $parent_type) ||
            $this->checkCommandAccess('write', '', $this->ref_id, $this->type)) {
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
    *
    * @access	private
    * @param	object		$a_tpl		template object
    * @param	int			$a_ref_id	item reference id
    */
    public function insertCommands(
        $a_use_asynch = false,
        $a_get_asynch_commands = false,
        $a_asynch_url = "",
        $a_header_actions = false
    ) {
        $lng = $this->lng;
        $ilUser = $this->user;

        if (!$this->getCommandsStatus()) {
            return;
        }

        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $this->current_selection_list = new ilAdvancedSelectionListGUI();
        $this->current_selection_list->setAsynch($a_use_asynch && !$a_get_asynch_commands);
        $this->current_selection_list->setAsynchUrl($a_asynch_url);
        if ($a_header_actions) {
            $this->current_selection_list->setListTitle("<span class='hidden-xs'>" . $lng->txt("actions") . "</span>");
        } else {
            $this->current_selection_list->setListTitle("");
        }
        $this->current_selection_list->setId("act_" . $this->getUniqueItemId(false));
        $this->current_selection_list->setSelectionHeaderClass("small");
        $this->current_selection_list->setItemLinkClass("xsmall");
        $this->current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $this->current_selection_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $this->current_selection_list->setUseImages(false);
        $this->current_selection_list->setAdditionalToggleElement($this->getUniqueItemId(true), "ilContainerListItemOuterHighlight");

        $this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", $this->ref_id);

        // only standard command?
        $only_default = false;
        if ($a_use_asynch && !$a_get_asynch_commands) {
            $only_default = true;
        }

        $this->default_command = false;
        $this->prevent_duplicate_commands = array();
        
        // we only allow the following commands inside the header actions
        $valid_header_commands = array("mount_webfolder");

        $commands = $this->getCommands($this->ref_id, $this->obj_id);
        foreach ($commands as $command) {
            if ($a_header_actions && !in_array($command["cmd"], $valid_header_commands)) {
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
                    //$this->default_command = $command;
                }
            }
        }

        if (!$only_default) {
            // custom commands
            if (is_array($this->cust_commands)) {
                foreach ($this->cust_commands as $command) {
                    $this->insertCommand(
                        $command["link"],
                        $this->lng->txt($command["lang_var"]),
                        $command["frame"],
                        "",
                        $command["cmd"],
                        $command["onclick"]
                    );
                }
            }

            // info screen commmand
            if ($this->getInfoScreenStatus()) {
                $this->insertInfoScreenCommand();
            }

            if (!$this->isMode(IL_LIST_AS_TRIGGER)) {
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
                if ($this->multi_download_enabled && $a_header_actions) {
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
        if (!$only_default && !$this->isMode(IL_LIST_AS_TRIGGER)) {
            $this->insertCommonSocialCommands($a_header_actions);
        }
        
        if (!$a_header_actions) {
            $this->ctrl->clearParametersByClass($this->gui_class_name);
        }

        // fix bug #12417
        // there is one case, where no action menu should be displayed:
        // public area, category, no info tab
        // todo: make this faster and remove type specific implementation if possible
        if ($a_use_asynch && !$a_get_asynch_commands && !$a_header_actions) {
            if ($ilUser->getId() == ANONYMOUS_USER_ID && $this->checkInfoPageOnAsynchronousRendering()) {
                include_once("./Services/Container/classes/class.ilContainer.php");
                include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
                if (!ilContainer::_lookupContainerSetting(
                    $this->obj_id,
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                    true
                )) {
                    return;
                }
            }
        }

        if ($a_use_asynch && $a_get_asynch_commands) {
            return $this->current_selection_list->getHTML(true);
        }
        
        return $this->current_selection_list->getHTML();
    }

    /**
     * Toogle comments action status
     *
     * @param boolean $a_value
     */
    public function enableComments($a_value, $a_enable_comments_settings = true)
    {
        $ilSetting = $this->settings;
        
        // global switch
        if ($ilSetting->get("disable_comments")) {
            $a_value = false;
        }
        
        $this->comments_enabled = (bool) $a_value;
        $this->comments_settings_enabled = (bool) $a_enable_comments_settings;
    }
    
    /**
     * Toogle notes action status
     *
     * @param boolean $a_value
     */
    public function enableNotes($a_value)
    {
        $ilSetting = $this->settings;
        
        // global switch
        if ($ilSetting->get("disable_notes")) {
            $a_value = false;
        }
        
        $this->notes_enabled = (bool) $a_value;
    }
    
    /**
     * Toogle tags action status
     *
     * @param boolean $a_value
     */
    public function enableTags($a_value)
    {
        $tags_set = new ilSetting("tags");
        if (!$tags_set->get("enable")) {
            $a_value = false;
        }
        $this->tags_enabled = (bool) $a_value;
    }
    
    /**
     * Toogle rating action status
     *
     * @param boolean $a_value
     * @param string $a_text
     * @param boolean $a_categories
     * @param array $a_ctrl_path
     */
    public function enableRating($a_value, $a_text = null, $a_categories = false, array $a_ctrl_path = null)
    {
        $this->rating_enabled = (bool) $a_value;
        
        if ($this->rating_enabled) {
            $this->rating_categories_enabled = (bool) $a_categories;
            $this->rating_text = $a_text;
            $this->rating_ctrl_path = $a_ctrl_path;
        }
    }
    
    /**
     * Toggles whether multiple objects can be downloaded at once or not.
     *
     * @param boolean $a_value true, to allow downloading of multiple objects; otherwise, false.
     */
    public function enableMultiDownload($a_value)
    {
        $folder_set = new ilSetting("fold");
        if (!$folder_set->get("enable_multi_download")) {
            $a_value = false;
        }
        $this->multi_download_enabled = (bool) $a_value;
    }
    
    public function insertMultiDownloadCommand()
    {
        $objDefinition = $this->obj_definition;
        
        if ($this->std_cmd_only) {
            return;
        }
        
        if (!$objDefinition->isContainer(ilObject::_lookupType($this->obj_id))) {
            return false;
        }
        
        if (is_object($this->getContainerObject()) &&
            $this->getContainerObject() instanceof ilContainerGUI) {
            $this->ctrl->setParameter($this->getContainerObject(), "type", "");
            $this->ctrl->setParameter($this->getContainerObject(), "item_ref_id", "");
            $this->ctrl->setParameter($this->getContainerObject(), "active_node", "");
            // bugfix mantis 24559
            // undoing an erroneous change inside mantis 23516 by adding "Download Multiple Objects"-functionality for non-admins
            // as they don't have the possibility to use the multi-download-capability of the manage-tab
            $user_id = $this->user->getId();
            $hasAdminAccess = $this->access->checkAccessOfUser($user_id, "crs_admin", $this->ctrl->getCmd(), $_GET['ref_id']);
            // to still prevent duplicate download functions for admins the following if-else statement keeps the redirection for admins
            // while letting other course members access the original multi-download functionality
            if ($hasAdminAccess) {
                $cmd = $_GET["cmd"] == "enableAdministrationPanel" ? "render" : "enableAdministrationPanel";
            } else {
                $cmd = $_GET["cmd"] == "enableMultiDownload" ? "render" : "enableMultiDownload";
            }
            $cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), $cmd);
            $this->insertCommand($cmd_link, $this->lng->txt("download_multiple_objects"));
            return true;
        }
        
        return false;
    }
    
    public function enableDownloadCheckbox($a_ref_id, $a_value)
    {
        $ilAccess = $this->access;
        
        // TODO: delegate to list object class!
        if (!$this->getContainerObject()->isActiveAdministrationPanel() || $_SESSION["clipboard"]) {
            if (in_array($this->type, array("file", "fold")) &&
                $ilAccess->checkAccess("read", "", $a_ref_id, $this->type)) {
                $this->download_checkbox_state = self::DOWNLOAD_CHECKBOX_ENABLED;
            } else {
                $this->download_checkbox_state = self::DOWNLOAD_CHECKBOX_DISABLED;
            }
        } else {
            $this->download_checkbox_state = self::DOWNLOAD_CHECKBOX_NONE;
        }
    }
    
    public function getDownloadCheckboxState()
    {
        return $this->download_checkbox_state;
    }
    
    /**
     * Insert js/ajax links into template
     */
    public static function prepareJsLinks($a_redraw_url, $a_notes_url, $a_tags_url, $a_tpl = null)
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        
        if (is_null($a_tpl)) {
            $a_tpl = $tpl;
        }
        
        if ($a_notes_url) {
            include_once("./Services/Notes/classes/class.ilNoteGUI.php");
            ilNoteGUI::initJavascript($a_notes_url, IL_NOTE_PRIVATE, $a_tpl);
        }
        
        if ($a_tags_url) {
            include_once("./Services/Tagging/classes/class.ilTaggingGUI.php");
            ilTaggingGUI::initJavascript($a_tags_url, $a_tpl);
        }
        
        if ($a_redraw_url) {
            $a_tpl->addOnLoadCode("il.Object.setRedrawAHUrl('" .
                        $a_redraw_url . "');");
        }
    }
    
    /**
     * Set sub object identifier
     *
     * @param string $a_type
     * @param int $a_id
     */
    public function setHeaderSubObject($a_type, $a_id)
    {
        $this->sub_obj_type = $a_type;
        $this->sub_obj_id = (int) $a_id;
    }
    
    /**
     *
     * @param string $a_id
     * @param string $a_img
     * @param string $a_tooltip
     * @param string $a_onclick
     * @param string $a_status_text
     * @param string $a_href
     */
    public function addHeaderIcon($a_id, $a_img, $a_tooltip = null, $a_onclick = null, $a_status_text = null, $a_href = null)
    {
        $this->header_icons[$a_id] = array("img" => $a_img,
                "tooltip" => $a_tooltip,
                "onclick" => $a_onclick,
                "status_text" => $a_status_text,
                "href" => $a_href);
    }
    
    /**
     *
     * @param string $a_id
     * @param string $a_html
     */
    public function addHeaderIconHTML($a_id, $a_html)
    {
        $this->header_icons[$a_id] = $a_html;
    }

    /**
     *
     * @param string $a_id
     * @param string $a_html
     */
    public function addHeaderGlyph($a_id, $a_glyph, $a_onclick = null)
    {
        $this->header_icons[$a_id] = array("glyph" => $a_glyph, "onclick" => $a_onclick);
    }

    public function setAjaxHash($a_hash)
    {
        $this->ajax_hash = $a_hash;
    }
    
    /**
     * Get header action
     *
     * @return string
     */
    public function getHeaderAction(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        $ilUser = $this->user;
        $lng = $this->lng;

        if ($a_main_tpl == null) {
            $main_tpl = $DIC["tpl"];
        } else {
            $main_tpl = $a_main_tpl;
        }

        $htpl = new ilTemplate("tpl.header_action.html", true, true, "Services/Repository");
        
        $redraw_js = "il.Object.redrawActionHeader();";
        
        // tags
        if ($this->tags_enabled) {
            include_once("./Services/Tagging/classes/class.ilTagging.php");
            $tags = ilTagging::getTagsForUserAndObject(
                $this->obj_id,
                ilObject::_lookupType($this->obj_id),
                0,
                "",
                $ilUser->getId()
            );
            if (count($tags) > 0) {
                include_once("./Services/Tagging/classes/class.ilTaggingGUI.php");
                $lng->loadLanguageModule("tagging");
                /*$this->addHeaderIcon("tags",
                    ilUtil::getImagePath("icon_tag.svg"),
                    $lng->txt("tagging_tags").": ".count($tags),
                    ilTaggingGUI::getListTagsJSCall($this->ajax_hash, $redraw_js),
                    count($tags));*/

                $f = $this->ui->factory();
                $this->addHeaderGlyph(
                    "tags",
                    $f->symbol()->glyph()->tag("#")
                    ->withCounter($f->counter()->status((int) count($tags))),
                    ilTaggingGUI::getListTagsJSCall($this->ajax_hash, $redraw_js)
                );
            }
        }
                
        // notes and comments
        $comments_enabled = $this->isCommentsActivated($this->type, $this->ref_id, $this->obj_id, true, false);
        if ($this->notes_enabled || $comments_enabled) {
            include_once("./Services/Notes/classes/class.ilNote.php");
            include_once("./Services/Notes/classes/class.ilNoteGUI.php");
            $type = ($this->sub_obj_type == "")
                ? $this->type
                : $this->sub_obj_type;
            $cnt = ilNote::_countNotesAndComments($this->obj_id, $this->sub_obj_id, $type);

            if ($this->notes_enabled && $cnt[$this->obj_id][IL_NOTE_PRIVATE] > 0) {
                /*$this->addHeaderIcon("notes",
                    ilUtil::getImagePath("note_unlabeled.svg"),
                    $lng->txt("private_notes").": ".$cnt[$this->obj_id][IL_NOTE_PRIVATE],
                    ilNoteGUI::getListNotesJSCall($this->ajax_hash, $redraw_js),
                    $cnt[$this->obj_id][IL_NOTE_PRIVATE]
                    );*/

                $f = $this->ui->factory();
                $this->addHeaderGlyph(
                    "notes",
                    $f->symbol()->glyph()->note("#")
                    ->withCounter($f->counter()->status((int) $cnt[$this->obj_id][IL_NOTE_PRIVATE])),
                    ilNoteGUI::getListNotesJSCall($this->ajax_hash, $redraw_js)
                );
            }

            if ($comments_enabled && $cnt[$this->obj_id][IL_NOTE_PUBLIC] > 0) {
                $lng->loadLanguageModule("notes");
                
                /*$this->addHeaderIcon("comments",
                    ilUtil::getImagePath("comment_unlabeled.svg"),
                    $lng->txt("notes_public_comments").": ".$cnt[$this->obj_id][IL_NOTE_PUBLIC],
                    ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $redraw_js),
                    $cnt[$this->obj_id][IL_NOTE_PUBLIC]);*/

                $f = $this->ui->factory();
                $this->addHeaderGlyph(
                    "comments",
                    $f->symbol()->glyph()->comment("#")
                    ->withCounter($f->counter()->status((int) $cnt[$this->obj_id][IL_NOTE_PUBLIC])),
                    ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $redraw_js)
                );
            }
        }
        
        // rating
        if ($this->rating_enabled) {
            include_once("./Services/Rating/classes/class.ilRatingGUI.php");
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
                $ajax_url = $this->ctrl->getLinkTargetByClass($this->rating_ctrl_path, "saveRating", "", true, false);
            } else {
                // ???
                $ajax_url = $this->ctrl->getLinkTargetByClass("ilRatingGUI", "saveRating", "", true, false);
            }
            $main_tpl->addOnLoadCode("il.Object.setRatingUrl('" . $ajax_url . "');");
            $this->addHeaderIconHTML(
                "rating",
                $rating_gui->getHtml(
                    true,
                    $this->checkCommandAccess("read", "", $this->ref_id, $this->type),
                    "il.Object.saveRating(%rating%);"
                )
            );
        }
        
        if ($this->header_icons) {
            include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
            
            $chunks = array();
            foreach ($this->header_icons as $id => $attr) {
                $id = "headp_" . $id;
                
                if (is_array($attr)) {
                    if ($attr["glyph"]) {
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
        
        $htpl->setVariable(
            "ACTION_DROP_DOWN",
            $this->insertCommands(false, false, "", true)
        );
        
        return $htpl->get();
    }
    

    /**
    * workaround: all links into the repository (from outside)
    * must tell repository to setup the frameset
    */
    public function appendRepositoryFrameParameter($a_link)
    {
        // we should get rid of this nonsense with 4.4 (alex)
        if ((strtolower($_GET["baseClass"]) != "ilrepositorygui") &&
            is_int(strpos($a_link, "baseClass=ilRepositoryGUI"))) {
            if ($this->type != "frm") {
                $a_link =
                    ilUtil::appendUrlParameterString($a_link, "rep_frame=1");
            }
        }
        
        return $a_link;
    }
    
    protected function modifyTitleLink($a_default_link)
    {
        if ($this->default_command_params) {
            $params = array();
            foreach ($this->default_command_params as $name => $value) {
                $params[] = $name . '=' . $value;
            }
            $params = implode('&', $params);
            
            
            // #12370
            if (!stristr($a_default_link, '?')) {
                $a_default_link = ($a_default_link . '?' . $params);
            } else {
                $a_default_link = ($a_default_link . '&' . $params);
            }
        }
        return $a_default_link;
    }

    /**
    * workaround: SAHS in new javavasript-created window or iframe
    */
    public function modifySAHSlaunch($a_link, $wtarget)
    {
        global $DIC;

        if (strstr($a_link, 'ilSAHSPresentationGUI') && !$this->offline_mode) {
            include_once 'Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
            $sahs_obj = new ilObjSAHSLearningModule($this->ref_id);
            $om = $sahs_obj->getOpenMode();
            $width = $sahs_obj->getWidth();
            $height = $sahs_obj->getHeight();
            if (($om == 5 || $om == 1) && $width > 0 && $height > 0) {
                $om++;
            }
            if ($om != 0 && !$DIC['ilBrowser']->isMobile()) {
                $this->default_command["frame"] = "";
                $a_link = "javascript:void(0); onclick=startSAHS('" . $a_link . "','" . $wtarget . "'," . $om . "," . $width . "," . $height . ");";
            }
        }
        return $a_link;
    }

    /**
    * insert path
    */
    public function insertPath()
    {
        $lng = $this->lng;
        
        if ($this->getPathStatus() != false) {
            if (!$this->path_gui instanceof \ilPathGUI) {
                $path_gui = new \ilPathGUI();
            } else {
                $path_gui = $this->path_gui;
            }

            $path_gui->enableTextOnly(!$this->path_linked);
            $path_gui->setUseImages(false);
            
            $start_node = $this->path_start_node
                ? $this->path_start_node
                : ROOT_FOLDER_ID;
                
            $this->tpl->setCurrentBlock("path_item");
            $this->tpl->setVariable('PATH_ITEM', $path_gui->getPath($start_node, $this->ref_id));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("path");
            $this->tpl->setVariable("TXT_LOCATION", $lng->txt("locator"));
            $this->tpl->parseCurrentBlock();
            return true;
        }
    }
    
    /**
     * insert progress info
     *
     * @access public
     * @return
     */
    public function insertProgressInfo()
    {
        return true;
    }
    
    
    /**
    * Insert icons and checkboxes
    */
    public function insertIconsAndCheckboxes()
    {
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        
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
                $this->tpl->touchBlock("i_1");	// indent
            }
            
            // icon link
            if ($this->title_link_disabled || !$this->default_command || (!$this->getCommandsStatus() && !$this->restrict_to_goto)) {
            } else {
                /*  see #28926
                $this->tpl->setCurrentBlock("icon_link_s");

                if ($this->default_command["frame"] != "") {
                    $this->tpl->setVariable("ICON_TAR", "target='" . $this->default_command["frame"] . "'");
                }

                $this->tpl->setVariable(
                    "ICON_HREF",
                    $this->default_command["link"]
                );
                $this->tpl->parseCurrentBlock();
                $this->tpl->touchBlock("icon_link_e");
                */
            }

            $this->tpl->setCurrentBlock("icon");
            if (!$objDefinition->isPlugin($this->getIconImageType())) {
                $this->tpl->setVariable("ALT_ICON", $lng->txt("obj_" . $this->getIconImageType()));
            } else {
                include_once("Services/Component/classes/class.ilPlugin.php");
                $this->tpl->setVariable("ALT_ICON",
                    ilObjectPlugin::lookupTxtById($this->getIconImageType(), "obj_" . $this->getIconImageType()));
            }

            $this->tpl->setVariable(
                "SRC_ICON",
                $this->getTypeIcon()
            );
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        }
        
        $this->tpl->touchBlock("d_" . $cnt);	// indent main div
    }

    /**
     * Get object type specific type icon
     * @return string
     */
    public function getTypeIcon()
    {
        return ilObject::_getIcon(
            $this->obj_id,
            'small',
            $this->getIconImageType()
        );
    }
    
    /**
    * Insert subitems
    */
    public function insertSubItems()
    {
        foreach ($this->sub_item_html as $sub_html) {
            $this->tpl->setCurrentBlock("subitem");
            $this->tpl->setVariable("SUBITEM", $sub_html);
            $this->tpl->parseCurrentBlock();
        }
    }
    
    /**
    * Insert field for positioning
    */
    public function insertPositionField()
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
    public function adminCommandsIncluded()
    {
        return $this->adm_commands_included;
    }

    /**
     * Store access cache
     */
    public function storeAccessCache()
    {
        $ilUser = $this->user;
        if ($this->acache->getLastAccessStatus() == "miss" &&
            !$this->prevent_access_caching) {
            $this->acache->storeEntry(
                $ilUser->getId() . ":" . $this->ref_id,
                serialize($this->access_cache),
                $this->ref_id
            );
        }
    }
    
    /**
    * Get all item information (title, commands, description) in HTML
    *
    * @access	public
    * @param	int			$a_ref_id		item reference id
    * @param	int			$a_obj_id		item object id
    * @param	int			$a_title		item title
    * @param	int			$a_description	item description
    * @param	bool		$a_use_asynch
    * @param	bool		$a_get_asynch_commands
    * @param	string		$a_asynch_url
    * @param	bool		$a_context	    workspace/tree context
    * @return	string		html code
    */
    public function getListItemHTML(
        $a_ref_id,
        $a_obj_id,
        $a_title,
        $a_description,
        $a_use_asynch = false,
        $a_get_asynch_commands = false,
        $a_asynch_url = ""
    ) {
        $ilUser = $this->user;

        // this variable stores wheter any admin commands
        // are included in the output
        $this->adm_commands_included = false;

        // only for permformance exploration
        $type = ilObject::_lookupType($a_obj_id);

        // initialization
        $this->initItem($a_ref_id, $a_obj_id, $type, $a_title, $a_description);

        if ($a_use_asynch && $a_get_asynch_commands) {
            return $this->insertCommands(true, true);
        }
        
        if ($this->rating_enabled) {
            if (ilRating::hasRatingInListGUI($this->obj_id, $this->type)) {
                $may_rate = $this->checkCommandAccess("read", "", $this->ref_id, $this->type);
                
                $rating = new ilRatingGUI();
                $rating->setObject($this->obj_id, $this->type);
                /*				$this->addCustomProperty(
                                    $this->lng->txt("rating_average_rating"),
                                    $rating->getListGUIProperty($this->ref_id, $may_rate, $this->ajax_hash, $this->parent_ref_id),
                                    false,
                                    true
                                );*/
                $this->addCustomProperty(
                    "",
                    $rating->getListGUIProperty($this->ref_id, $may_rate, $this->ajax_hash, $this->parent_ref_id),
                    false,
                    true
                );
            }
        }
        
        // read from cache
        include_once("Services/Object/classes/class.ilListItemAccessCache.php");
        $this->acache = new ilListItemAccessCache();
        $cres = $this->acache->getEntry($ilUser->getId() . ":" . $a_ref_id);
        if ($this->acache->getLastAccessStatus() == "hit") {
            $this->access_cache = unserialize($cres);
        } else {
            // write to cache
            $this->storeAccessCache();
        }
        
        // visible check
        if (!$this->checkCommandAccess("visible", "", $a_ref_id, "", $a_obj_id)) {
            $this->resetCustomData();
            return "";
        }
        
        // BEGIN WEBDAV
        if ($type == 'file' and ilObjFileAccess::_isFileHidden($a_title)) {
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
                    $this->insertCommands($a_use_asynch, $a_get_asynch_commands, $a_asynch_url)
                );
            }
        }
        
        if ($this->getProgressInfoStatus()) {
            $this->insertProgressInfo();
        }

        // insert title and describtion
        $this->insertTitle();
        if (!$this->isMode(IL_LIST_AS_TRIGGER)) {
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
        
        // file upload
        if ($this->isFileUploadAllowed()) {
            $this->insertFileUpload();
        }

        $this->resetCustomData();

        $this->tpl->setVariable("DIV_CLASS", 'ilContainerListItemOuter');
        $this->tpl->setVariable("DIV_ID", 'data-list-item-id="' . $this->getUniqueItemId(true) . '" id = "' . $this->getUniqueItemId(true) . '"');
        $this->tpl->setVariable("ADDITIONAL", $this->getAdditionalInformation());

        if (is_object($this->getContainerObject())) {
            // #11554 - make sure that internal ids are reset
            $this->ctrl->setParameter($this->getContainerObject(), "item_ref_id", "");
        }

        return $this->tpl->get();
    }
    
    /**
     * reset properties and commands
     */
    protected function resetCustomData()
    {
        // #15747
        $this->cust_prop = array();
        $this->cust_commands = array();
        $this->sub_item_html = array();
        $this->position_enabled = false;
    }
    
    /**
     * Set current parent ref id to enable unique js-ids (sessions, etc.)
     *
     * @param string $a_ref_id
     */
    public function setParentRefId($a_ref_id)
    {
        $this->parent_ref_id = $a_ref_id;
    }
    
    /**
     * Get unique item identifier (for js-actions)
     *
     * @param bool $a_as_div
     * @return string
     */
    public function getUniqueItemId($a_as_div = false)
    {
        // use correct id for references
        $id_ref = ($this->reference_ref_id > 0)
            ? $this->reference_ref_id
            : $this->ref_id;
        
        // add unique identifier for preconditions (objects can appear twice in same container)
        if ($this->condition_depth) {
            $id_ref .= "_pc" . $this->condition_depth;
        }
        
        // unique
        $id_ref .= "_pref_" . $this->parent_ref_id;
    
        if (!$a_as_div) {
            return $id_ref;
        } else {
            // action menu [yellow] toggle
            return "lg_div_" . $id_ref;
        }
    }
    
    /**
    * Get commands HTML (must be called after get list item html)
    */
    public function getCommandsHTML()
    {
        return $this->insertCommands();
    }
    
    /**
    * Returns whether current item is a block in a side column or not
    */
    public function isSideBlock()
    {
        return false;
    }

    /**
    *
    * @access	public
    * @params	boolean	$a_bold_title	set the item title bold
    */
    public function setBoldTitle($a_bold_title)
    {
        $this->bold_title = $a_bold_title;
    }
    
    /**
    *
    * @access	public
    * @return	boolean	returns if the item title is bold or not
    */
    public function isTitleBold()
    {
        return $this->bold_title;
    }
    
    /**
     * Preload common properties
     *
     * @param
     * @return
     */
    public static function preloadCommonProperties($a_obj_ids, $a_context)
    {
        global $DIC;
        $lng = $DIC->language();
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        if ($a_context == self::CONTEXT_REPOSITORY) {
            $active_notes = !$ilSetting->get("disable_notes");
            $active_comments = !$ilSetting->get("disable_comments");
        
            if ($active_notes || $active_comments) {
                include_once("./Services/Notes/classes/class.ilNote.php");
            }
            
            if ($active_comments) {
                // needed for action
                self::$comments_activation = ilNote::getRepObjActivation($a_obj_ids);
            }
            
            // properties are optional
            if ($ilSetting->get('comments_tagging_in_lists')) {
                if ($active_notes || $active_comments) {
                    self::$cnt_notes = ilNote::_countNotesAndCommentsMultiple($a_obj_ids, true);
                    
                    $lng->loadLanguageModule("notes");
                }
                                
                $tags_set = new ilSetting("tags");
                if ($tags_set->get("enable")) {
                    $all_users = $tags_set->get("enable_all_users");
                
                    include_once("./Services/Tagging/classes/class.ilTagging.php");
                    if (!$ilSetting->get('comments_tagging_in_lists_tags')) {
                        self::$cnt_tags = ilTagging::_countTags($a_obj_ids, $all_users);
                    } else {
                        $tag_user_id = null;
                        if (!$all_users) {
                            $tag_user_id = $ilUser->getId();
                        }
                        self::$tags = ilTagging::_getListTagsForObjects($a_obj_ids, $tag_user_id);
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
     *
     * @param string $a_type
     * @param int $a_ref_id
     * @param int $a_obj_id
     * @param bool $a_header_actions
     * @param bool $a_check_write_access
     * @return bool
     */
    protected function isCommentsActivated($a_type, $a_ref_id, $a_obj_id, $a_header_actions, $a_check_write_access = true)
    {
        if ($this->comments_enabled) {
            if (!$this->comments_settings_enabled) {
                return true;
            }
            if ($a_check_write_access && $this->checkCommandAccess('write', '', $a_ref_id, $a_type)) {
                return true;
            }
            // fallback to single object check if no preloaded data
            // only the repository does preloadCommonProperties() yet
            if (!$a_header_actions && self::$preload_done) {
                if (self::$comments_activation[$a_obj_id][$a_type]) {
                    return true;
                }
            } else {
                include_once("./Services/Notes/classes/class.ilNote.php");
                if (ilNote::commentsActivated($a_obj_id, 0, $a_type)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * enable timings link
     *
     * @access public
     * @param bool
     * @return
     */
    public function enableTimings($a_status)
    {
        $this->timings_enabled = (bool) $a_status;
    }
    
    /**
     * Gets a value indicating whether file uploads to this object are allowed or not.
     *
     * @return bool true, if file upload is allowed; otherwise, false.
     */
    public function isFileUploadAllowed()
    {
        // check if file upload allowed
        include_once("./Services/FileUpload/classes/class.ilFileUploadUtil.php");
        return ilFileUploadUtil::isUploadAllowed($this->ref_id, $this->type);
    }
    
    /**
     * Inserts a file upload component
     */
    public function insertFileUpload()
    {
        include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
        ilFileUploadGUI::initFileUpload();

        $upload = new ilFileUploadGUI($this->getUniqueItemId(true), $this->ref_id);
        
        $this->tpl->setCurrentBlock("fileupload");
        $this->tpl->setVariable("FILE_UPLOAD", $upload->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Get list item ui object
     *
     * @param int $ref_id
     * @param int $obj_id
     * @param string $type
     * @param string $title
     * @param string $description
     * @return \ILIAS\UI\Component\Item\Item|null
     */
    public function getAsListItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        string $description
    ) : ?\ILIAS\UI\Component\Item\Item {
        $ui = $this->ui;

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
                $action = $action->withAdditionalOnLoadCode(function ($id) use ($action_item) {
                    return "$('#$id').click(function(){" . $action_item['onclick'] . ";});";
                });
            }

            $actions[] = $action;
        }

        $dropdown = $ui->factory()
            ->dropdown()
            ->standard($actions);

        $def_command = $this->getDefaultCommand();

        $icon = $this->ui->factory()
            ->symbol()
            ->icon()
            ->custom(ilObject::_getIcon($obj_id), $this->lng->txt("icon") . " " . $this->lng->txt('obj_' . $type))
            ->withSize('medium');


        if ($def_command['link']) {
            $list_item = $ui->factory()->item()->standard($this->ui->factory()->link()->standard($this->getTitle(), $def_command['link']));
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
     *
     * @param int $ref_id
     * @param int $obj_id
     * @param string $type
     * @param string $title
     * @param string $description
     * @return \ILIAS\UI\Component\Card\Card|null
     */
    public function getAsCard(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        string $description
    ) : ?\ILIAS\UI\Component\Card\Card
    {
        $ui = $this->ui;

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

        foreach ($this->current_selection_list->getItems() as $action_item) {
            $actions[] = $ui->factory()
                            ->button()
                            ->shy($action_item['title'], $action_item['link']);
        }

        $def_command = $this->getDefaultCommand();

        if ($def_command["frame"] != "") {
            /* this seems to be introduced due to #25624, but does not fix it
                removed with ##30732
            $button =
                $ui->factory()->button()->shy("Open", "")->withAdditionalOnLoadCode(function ($id) use ($def_command) {
                    return
                        "$('#$id').click(function(e) { window.open('" . str_replace("&amp;", "&",
                            $def_command["link"]) . "', '" . $def_command["frame"] . "');});";
                });
            $actions[] = $button;*/
        }
        $dropdown = $ui->factory()->dropdown()->standard($actions);

        $img = $this->object_service->commonSettings()->tileImage()->getByObjId((int) $obj_id);
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
                $image = $image->withAdditionalOnLoadCode(function ($id) use ($def_command) {
                    return
                        "$('#$id').click(function(e) { window.open('" . str_replace("&amp;", "&",
                            $def_command["link"]) . "', '" . $def_command["frame"] . "');});";
                });

                $button =
                    $ui->factory()->button()->shy($title, "")->withAdditionalOnLoadCode(function ($id) use ($def_command
                    ) {
                        return
                            "$('#$id').click(function(e) { window.open('" . str_replace("&amp;", "&",
                                $def_command["link"]) . "', '" . $def_command["frame"] . "');});";
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

        $icon = $this->ui->factory()
                         ->symbol()
                         ->icon()
                         ->standard($type, $this->lng->txt('obj_' . $type))
                         ->withIsOutlined(true);

        // card title action
        $card_title_action = "";
        if ($def_command["link"] != "" && ($def_command["frame"] == "" || $modified_link != $def_command["link"])) {    // #24256
            $card_title_action = $modified_link;
        } else {
            if ($def_command['link'] == "" &&
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

    /**
     * @return bool
     */
    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return false;
    }
}
