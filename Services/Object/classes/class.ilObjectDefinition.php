<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* parses the objects.xml
* it handles the xml-description of all ilias objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @externalTableAccess ilObjDefReader on il_object_def, il_object_subobj, il_object_group
*/
class ilObjectDefinition // extends ilSaxParser
{
    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
    * // TODO: var is not used
    * object id of specific object
    * @var obj_id
    * @access private
    */
    public $obj_id;

    /**
    * parent id of object
    * @var parent id
    * @access private
    */
    public $parent;

    /**
    * array representation of objects
    * @var objects
    * @access private
    */
    public $obj_data;
    
    public $sub_types = array();

    const MODE_REPOSITORY = 1;
    const MODE_WORKSPACE = 2;
    const MODE_ADMINISTRATION = 3;

    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->settings = $DIC->settings();
        $this->readDefinitionData();
    }


    protected function readDefinitionDataFromCache()
    {
        $this->obj_data = array();
        $defIds = array();
        $global_cache = ilCachedComponentData::getInstance();
        foreach ($global_cache->getIlobjectDef() as $rec) {
            $this->obj_data[$rec["id"]] = array(
                "name" => $rec["id"],
                "class_name" => $rec["class_name"],
                "location" => $rec["location"],
                "checkbox" => $rec["checkbox"],
                "inherit" => $rec["inherit"],
                "component" => $rec["component"],
                "translate" => $rec["translate"],
                "devmode" => $rec["devmode"],
                "allow_link" => $rec["allow_link"],
                "allow_copy" => $rec["allow_copy"],
                "rbac" => $rec["rbac"],
                "group" => $rec["grp"],
                "system" => $rec["system"],
                "default_pos" => "9999" . str_pad($rec["default_pos"], 4, "0", STR_PAD_LEFT), // "unassigned" group
                "sideblock" => $rec["sideblock"],
                'export' => $rec['export'],
                'repository' => $rec['repository'],
                'workspace' => $rec['workspace'],
                'administration' => $rec['administration'],
                'amet' => $rec['amet'],
                'orgunit_permissions' => $rec['orgunit_permissions'],
                'lti_provider' => $rec['lti_provider'],
                'offline_handling' => $rec['offline_handling']
            );
            $this->obj_data[$rec["id"]]["subobjects"] = array();

            $defIds[] = $rec["id"];
        }

        $subobj = $global_cache->lookupSubObjForParent($defIds);

        foreach ($subobj as $rec2) {
            $max = $rec2["mmax"];
            if ($max <= 0) {
                $max = "";
            }
            $this->obj_data[$rec2["parent"]]["subobjects"][$rec2["subobj"]] = array(
                "name" => $rec2["subobj"],
                "max" => $max,
                "lng" => $rec2["subobj"]
            );
        }
        $this->obj_group = $global_cache->getIlObjectGroup();

        $this->readPluginData();

        $this->sub_types = $global_cache->getIlObjectSubType();
    }


    protected function readDefinitionDataFromDB()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->obj_data = array();

        // Select all object_definitions and collect the definition id's in
        // this array.
        $defIds = array();
        $set = $ilDB->query("SELECT * FROM il_object_def");
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->obj_data[$rec["id"]] = array(
                "name" => $rec["id"],
                "class_name" => $rec["class_name"],
                "location" => $rec["location"],
                "checkbox" => $rec["checkbox"],
                "inherit" => $rec["inherit"],
                "component" => $rec["component"],
                "translate" => $rec["translate"],
                "devmode" => $rec["devmode"],
                "allow_link" => $rec["allow_link"],
                "allow_copy" => $rec["allow_copy"],
                "rbac" => $rec["rbac"],
                "group" => $rec["grp"],
                "system" => $rec["system"],
                "default_pos" => "9999" . str_pad($rec["default_pos"], 4, "0", STR_PAD_LEFT), // "unassigned" group
                "sideblock" => $rec["sideblock"],
                'export' => $rec['export'],
                'repository' => $rec['repository'],
                'workspace' => $rec['workspace'],
                'administration' => $rec['administration'],
                'amet' => $rec['amet'],
                'orgunit_permissions' => $rec['orgunit_permissions'],
                'lti_provider' => $rec['lti_provider'],
                'offline_handling' => $rec['offline_handling']
            );
            $this->obj_data[$rec["id"]]["subobjects"] = array();

            $defIds[] = $rec["id"];
        }

        // get all subobject definitions in a single query
        $set2 = $ilDB->query("SELECT * FROM il_object_subobj WHERE " . $ilDB->in('parent', $defIds, false, 'text'));
        while ($rec2 = $ilDB->fetchAssoc($set2)) {
            $max = $rec2["mmax"];
            if ($max <= 0) { // for backward compliance
                $max = "";
            }
            $this->obj_data[$rec2["parent"]]["subobjects"][$rec2["subobj"]] = array(
                "name" => $rec2["subobj"],
                "max" => $max,
                "lng" => $rec2["subobj"]
            );
        }

        $set = $ilDB->query("SELECT * FROM il_object_group");
        $this->obj_group = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->obj_group[$rec["id"]] = $rec;
        }

        $this->readPluginData();

        $set = $ilDB->query("SELECT * FROM il_object_sub_type ");
        $this->sub_types = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->sub_types[$rec["obj_type"]][] = $rec;
        }
    }

        
    /**
    * Read object definition data
    */
    public function readDefinitionData()
    {
        if (ilGlobalCache::getInstance(ilGlobalCache::COMP_COMPONENT)->isActive()) {
            $this->readDefinitionDataFromCache();
        } else {
            $this->readDefinitionDataFromDB();
        }
    }

    /**
     * @param $grouped_obj
     * @param $component
     * @param $slotName
     * @param $slotId
     * @param $plugin_id
     * @return mixed
     * @internal param $ilPluginAdmin
     */
    protected static function getGroupedPluginObjectTypes($grouped_obj, $component, $slotName, $slotId)
    {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot($component, $slotName, $slotId);
        foreach ($pl_names as $pl_name) {
            include_once("./Services/Component/classes/class.ilPlugin.php");
            $pl_id = ilPlugin::lookupIdForName($component, $slotName, $slotId, $pl_name);
            if (!isset($grouped_obj[$pl_id])) {
                $grouped_obj[$pl_id] = array(
                    "pos" => "99992000", // "unassigned" group
                    "objs" => array(0 => $pl_id)
                );
            }
        }
        return $grouped_obj;
    }


    // PUBLIC METHODS

    /**
    * get class name by type
    *
    * @param	string	object type
    * @access	public
    */
    public function getClassName($a_obj_name)
    {
        return $this->obj_data[$a_obj_name]["class_name"];
    }


    /**
    * get location by type
    *
    * @param	string	object type
    * @access	public
    */
    public function getLocation($a_obj_name)
    {
        return $this->obj_data[$a_obj_name]["location"];
    }

    /**
    * Get Group information
    */
    public function getGroup($a_id)
    {
        return $this->obj_group[$a_id];
    }

    /**
    * Get Group of object type
    */
    public function getGroupOfObj($a_obj_name)
    {
        return $this->obj_data[$a_obj_name]["group"];
    }

    /**
    * should the object get a checkbox (needed for 'cut','copy' ...)
    *
    * @param	string	object type
    * @access	public
    */
    public function hasCheckbox($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["checkbox"];
    }
    
    /**
    * get translation type (sys, db or 0)s
    *
    * @param	string	object type
    * @access	public
    */
    public function getTranslationType($a_obj_name)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_obj_name == "root") {
            if (!isset($this->root_trans_type)) {
                $q = "SELECT count(obj_id) cnt FROM object_translation WHERE obj_id = " .
                    $ilDB->quote(ROOT_FOLDER_ID, 'integer') . " ";
                $set = $ilDB->query($q);
                $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
                if ($rec["cnt"] > 0) {
                    $this->root_trans_type = "db";
                } else {
                    $this->root_trans_type = $this->obj_data[$a_obj_name]["translate"];
                }
            }
            return $this->root_trans_type;
        }
        
        if (isset($this->obj_data[$a_obj_name])) {
            return $this->obj_data[$a_obj_name]["translate"];
        }
        
        return "";
    }
    

    /**
    * Does object permits stopping inheritance?
    *
    * @param	string	object type
    * @access	public
    */
    public function stopInheritance($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["inherit"];
    }

    /**
    * get devmode status by type
    *
    * @param	string	object type
    * @access	public
    */
    public function getDevMode($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["devmode"];
    }

    /**
    * get all object types in devmode
    *
    * @access	public
    * @return	array	object types set to development
    */
    public function getDevModeAll()
    {
        $types = array_keys($this->obj_data);
        
        foreach ($types as $type) {
            if ($this->getDevMode($type)) {
                $devtypes[] = $type;
            }
        }

        return $devtypes ? $devtypes : array();
    }

    /**
    * get RBAC status by type
    * returns true if object type is a RBAC object type
    *
    * @param	string	object type
    * @access	public
    */
    public function isRBACObject($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["rbac"];
    }

    /**
    * get RBAC status by type
    * returns true if object type is an (activated) plugin type
    *
    * @param	string	object type
    * @access	public
    */
    public function isPlugin($a_obj_name)
    {
        return (bool) isset($this->obj_data[$a_obj_name]["plugin"]);
    }

    /**
     * Check if given type is a plugin type name (starts with an "x")
     *
     * @param	string	object type
     * @access	public
     */
    public function isPluginTypeName($a_str)
    {
        return (substr($a_str, 0, 1) == "x");
    }

    /**
     * Returns true iff the given type is an active type of a repositoryObject or Organisation Unit Extension plugin.
     * @param $type
     * @return bool
     */
    public function isActivePluginType($type)
    {
        $ilPluginAdmin = $this->plugin_admin;
        $isRepoPlugin = $ilPluginAdmin->isActive(
            IL_COMP_SERVICE,
            "Repository",
            "robj",
            ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $type)
        );
        $isOrguPlugin = $ilPluginAdmin->isActive(
            IL_COMP_MODULE,
            "OrgUnit",
            "orguext",
            ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $type)
        );
        return $isRepoPlugin || $isOrguPlugin;
    }

    /**
    * get all RBAC object types
    *
    * @access	public
    * @return	array	object types set to development
    */
    public function getAllRBACObjects()
    {
        $types = array_keys($this->obj_data);
        
        foreach ($types as $type) {
            if ($this->isRBACObject($type)) {
                $rbactypes[] = $type;
            }
        }

        return $rbactypes ? $rbactypes : array();
    }

    /**
    * get all object types
    *
    * @access	public
    * @return	array	object types
    */
    public function getAllObjects()
    {
        return array_keys($this->obj_data);
    }

    /**
    * checks if linking of an object type is allowed
    *
    * @param	string	object type
    * @access	public
    */
    public function allowLink($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["allow_link"];
    }

    /**
    * checks if copying of an object type is allowed
    *
    * @param	string	object type
    * @access	public
    */
    public function allowCopy($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["allow_copy"];
    }
    
    public function allowExport($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]['export'];
    }
    
    /**
     * Check whether the creation of local roles is allowed
     * Currently disabled for type "root" and "adm"
     * @return
     */
    public function hasLocalRoles($a_obj_type)
    {
        switch ($a_obj_type) {
            case 'root':
                return false;
                
            default:
                return true;
        }
    }
    
    /**
    * get all subobjects by type
    *
    * @param	string	object type
    * @param	boolean	filter disabled objects? (default: true)
    * @access	public
    * @return	array	list of allowed object types
    */
    public function getSubObjects($a_obj_type, $a_filter = true)
    {
        $ilSetting = $this->settings;
        
        $subs = array();

        if ($subobjects = $this->obj_data[$a_obj_type]["subobjects"]) {
            // Filter some objects e.g chat object are creatable if chat is active
            if ($a_filter) {
                $this->__filterObjects($subobjects);
            }
            foreach ($subobjects as $data => $sub) {
                if ($sub["module"] != "n") {
                    if (!($ilSetting->get("obj_dis_creation_" . $data))) {
                        $subs[$data] = $sub;
                        
                        // determine position
                        $pos = ($ilSetting->get("obj_add_new_pos_" . $data) > 0)
                            ? (int) $ilSetting->get("obj_add_new_pos_" . $data)
                            : (int) $this->obj_data[$data]["default_pos"];
                        $subs[$data]["pos"] = $pos;
                    }
                }
            }

            $subs2 = ilUtil::sortArray($subs, "pos", 'ASC', true, true);

            return $subs2;
        }
        
        return $subs;
    }

    /**
    * Get all subobjects by type.
    * This function returns all subobjects allowed by the provided object type
    * and all its subobject types recursively.
    *
    * This function is used to create local role templates. It is important,
    * that we do not filter out any objects here!
    *
    *
    * @param	string	object type
    * @access	public
    * @return	array	list of allowed object types
    */
    public function getSubObjectsRecursively($a_obj_type, $a_include_source_obj = true, $a_add_admin_objects = false)
    {
        $ilSetting = $this->settings;
        
        // This associative array is used to collect all subobject types.
        // key=>type, value=data
        $recursivesubs = array();

        // This array is used to keep track of the object types, we
        // need to call function getSubobjects() for.
        $to_do = array($a_obj_type);

        // This array is used to keep track of the object types, we
        // have called function getSubobjects() already. This is to
        // prevent endless loops, for object types that support
        // themselves as subobject types either directly or indirectly.
        $done = array();

        while (count($to_do) > 0) {
            $type = array_pop($to_do);
            $done[] = $type;
            
            // no recovery folder subitems
            if ($type == 'recf') {
                continue;
            }
            
            // Hide administration if desired
            if (!$a_add_admin_objects and $type == 'adm') {
                $subs = array();
            } else {
                $subs = $this->getSubObjects($type);
            }
            #vd('xxxxxxxxxxxxx'.$type);
            foreach ($subs as $subtype => $data) {
                #vd('------------------------->'.$subtype);
                
                // Hide role templates and folder from view
                if ($this->getDevMode($subtype) or !$this->isRBACObject($subtype)) {
                    continue;
                }
                if ($subtype == 'rolt') {
                    continue;
                }
                if (!$a_add_admin_objects and $subtype == 'adm') {
                    continue;
                }
                
                $recursivesubs[$subtype] = $data;
                if (!in_array($subtype, $done)
                && !in_array($subtype, $to_do)) {
                    $to_do[] = $subtype;
                }
            }
        }
        
        if ($a_include_source_obj) {
            if (!isset($recursivesubs[$a_obj_type])) {
                $recursivesubs[$a_obj_type]['name'] = $a_obj_type;
                $recursivesubs[$a_obj_type]['lng'] = $a_obj_type;
                $recursivesubs[$a_obj_type]['max'] = 0;
                $recursivesubs[$a_obj_type]['pos'] = -1;
            }
        }
        return ilUtil::sortArray($recursivesubs, "pos", 'ASC', true, true);
    }
    

    /**
    * get all subjects except (rolf) of the adm object
    * This is necessary for filtering these objects in role perm view.
    * e.g It it not necessary to view/edit role permission for the usrf object since it's not possible to create a new one
    *
    * @param	string	object type
    * @access	public
    * @return	array	list of object types to filter
    */
    public function getSubobjectsToFilter($a_obj_type = "adm")
    {
        foreach ($this->obj_data[$a_obj_type]["subobjects"] as $key => $value) {
            switch ($key) {
                case "rolf":
                case "orgu":
                    // DO NOTHING
                    break;

                default:
                    $tmp_subs[] = $key;
            }
        }
        // ADD adm and root object
        $tmp_subs[] = "adm";
        #$tmp_subs[] = "root";

        return $tmp_subs ? $tmp_subs : array();
    }
        
    /**
    * get only creatable subobjects by type
    *
    * @param	string	object type
    * @param	integer	context
    * @param	integer	parent_ref_id
    * @access	public
    * @return	array	list of createable object types
    */
    public function getCreatableSubObjects($a_obj_type, $a_context = self::MODE_REPOSITORY, $a_parent_ref_id = null)
    {
        $subobjects = $this->getSubObjects($a_obj_type);

        // remove role folder object from list
        unset($subobjects["rolf"]);
        
        $sub_types = array_keys($subobjects);

        // remove object types in development from list
        foreach ($sub_types as $type) {
            if ($this->getDevMode($type) || $this->isSystemObject($type)) {
                unset($subobjects[$type]);
            }
            if ($a_context == self::MODE_REPOSITORY && !$this->isAllowedInRepository($type)) {
                unset($subobjects[$type]);
            }
            if ($a_context == self::MODE_WORKSPACE && !$this->isAllowedInWorkspace($type)) {
                unset($subobjects[$type]);
            }
            if ($a_context == self::MODE_ADMINISTRATION && !$this->isAdministrationObject($type)) {
                unset($subobjects[$type]);
            }
        }
        
        if ($a_obj_type == "prg") {
            // ask study program which objects are allowed to create on the concrete node.
            require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
            return ilObjStudyProgramme::getCreatableSubObjects($subobjects, $a_parent_ref_id);
        }

        return $subobjects;
    }
    
    /**
    * get a string of all subobjects by type
    *
    * @param	string	object type
    * @access	public
    */
    public function getSubObjectsAsString($a_obj_type)
    {
        $string = "";

        if (is_array($this->obj_data[$a_obj_type]["subobjects"])) {
            $data = array_keys($this->obj_data[$a_obj_type]["subobjects"]);

            $string = "'" . implode("','", $data) . "'";
        }
        
        return $string;
    }
    
    /**
     * Check if object type is container ('crs','fold','grp' ...)
     *
     * @access public
     * @param string object type
     * @return bool
     *
     */
    public function isContainer($a_obj_name)
    {
        if (!is_array($this->obj_data[$a_obj_name]['subobjects'])) {
            return false;
        }
        return count($this->obj_data[$a_obj_name]['subobjects']) >= 1 ? true : false;
    }

    // PRIVATE METHODS

    /**
    * set event handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @param	array		element attributes
    * @access	private
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case 'object':
                $this->parent_tag_name = $a_attribs["name"];
                break;
            case 'property':
                $this->current_tag = "property";
                $this->current_tag_name = $a_attribs["name"];
//				$this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["name"] = $a_attribs["name"];
                $this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["module"] = $a_attribs["module"];
//echo '<br>$this->obj_data["'.$this->parent_tag_name.'"]["properties"]["'.$this->current_tag_name.'"]["module"] = "'.$a_attribs["module"].'";';
                break;
        }
    }

    /**
    * end tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		data
    * @access	private
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
    }

    /**
    * end tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @access	private
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        $this->current_tag = '';
        $this->current_tag_name = '';
    }

    
    public function __filterObjects(&$subobjects)
    {
        foreach ($subobjects as $type => $data) {
            switch ($type) {
                default:
                    // DO NOTHING
            }
        }
    }
    
    /**
    * checks if object type is a system object
    *
    * system objects are those object types that are only used for
    * internal purposes and to keep the object type model consistent.
    * Typically they are used in the administation, exist only once
    * and may contain only specific object types.
    * To mark an object type as a system object type, use 'system=1'
    * in the object definition in objects.xml
    *
    * @param	string	object type
    * @access	public
    */
    public function isSystemObject($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["system"];
    }
    
    /**
    * Check, whether object type is a side block.
    *
    * @param	string		object type
    * @return	boolean		side block true/false
    */
    public function isSideBlock($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["sideblock"];
    }

    /**
     * @param bool $filter_repository_types
     * @return string[]
     */
    public function getSideBlockTypes(bool $filter_repository_types = true) : array
    {
        $side_block_types = [];
        foreach (array_keys($this->obj_data) as $type) {
            if (
                $filter_repository_types &&
                !$this->isAllowedInRepository($type)
            ) {
                continue;
            }
            if ($this->isSideBlock($type)) {
                $side_block_types[] = $type;
            }
        }
        return $side_block_types;
    }

    /**
    * Get all repository object types of component
    */
    public static function getRepositoryObjectTypesForComponent(
        $a_component_type,
        $a_component_name
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->queryF(
            "SELECT * FROM il_object_def WHERE component = %s",
            array("text"),
            array($a_component_type . "/" . $a_component_name)
        );
            
        $types = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["system"] != 1) {
                $types[] = $rec;
            }
        }
        
        return $types;
    }

    /**
    * Get component for object type
    */
    public static function getComponentForType($a_obj_type)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->queryF(
            "SELECT component FROM il_object_def WHERE id = %s",
            array("text"),
            array($a_obj_type)
        );
            
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["component"];
        }
        
        return "";
    }

    /**
     * @param $a_parent_obj_type
     * @return array
     */
    public static function getGroupedRepositoryObjectTypes($a_parent_obj_type)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT * FROM il_object_group");
        $groups = array();
        while ($gr_rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $groups[$gr_rec["id"]] = $gr_rec;
        }

        $global_cache = ilCachedComponentData::getInstance();

        $recs = $global_cache->lookupGroupedRepObj($a_parent_obj_type);
        
        $grouped_obj = array();
        foreach ((array) $recs as $rec) {
            if ($rec["grp"] != "") {
                $grouped_obj[$rec["grp"]]["pos"] = (int) $groups[$rec["grp"]]["default_pres_pos"];
                $grouped_obj[$rec["grp"]]["objs"][] = $rec["id"];
            } else {
                $grouped_obj[$rec["id"]]["pos"] = (int) $rec["default_pres_pos"];
                $grouped_obj[$rec["id"]]["objs"][] = $rec["id"];
            }
        }
        // now get objects from repository plugin
        $grouped_obj = self::getGroupedPluginObjectTypes($grouped_obj, IL_COMP_SERVICE, "Repository", "robj");
        $grouped_obj = self::getGroupedPluginObjectTypes($grouped_obj, IL_COMP_MODULE, "OrgUnit", "orguext");

        $ret = ilUtil::sortArray($grouped_obj, "pos", "asc", true, true);
        return $ret;
    }

    /**
    * checks if object type can be used in repository context
    *
    * @param	string	object type
    * @access	public
    * @return bool
    */
    public function isAllowedInRepository($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["repository"];
    }

    /**
    * get all RBAC object types
    *
    * @access	public
    * @return	array	object types set to development
    */
    public function getAllRepositoryTypes($a_incl_adm = false)
    {
        $types = array_keys($this->obj_data);
        
        foreach ($types as $type) {
            if ($this->isAllowedInRepository($type) &&
                (!$this->isAdministrationObject($type) || $a_incl_adm)) {
                $rbactypes[] = $type;
            }
        }

        return $rbactypes ? $rbactypes : array();
    }

    
    /**
    * checks if object type can be used in workspace context
    *
    * @param	string	object type
    * @access	public
    * @return bool
    */
    public function isAllowedInWorkspace($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]["workspace"];
    }

    /**
     * Check if administration object
     * @param string $a_obj_name
     * @return bool
     */
    public function isAdministrationObject($a_obj_name)
    {
        return (bool) $this->obj_data[$a_obj_name]['administration'];
    }
    
    /**
     * Check whether type belongs to inactive plugin
     *
     * @param
     * @return
     */
    public function isInactivePlugin($a_type)
    {
        if (substr($a_type, 0, 1) == "x" && !$this->isPlugin($a_type)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get advanced meta data objects
     *
     * @param
     * @return
     */
    public function getAdvancedMetaDataTypes()
    {
        $amet = array();
        foreach ($this->obj_data as $k => $v) {
            if ($v["amet"]) {
                $amet[] = array("obj_type" => $k, "sub_type" => "");
            }
        }

        foreach ($this->sub_types as $type => $sub_types) {
            foreach ($sub_types as $t) {
                if ($t["amet"]) {
                    $amet[] = array("obj_type" => $type, "sub_type" => $t["sub_type"]);
                }
            }
        }

        return $amet;
    }
    
    /**
     * Get object type with orgunit position permission support
     * @return string[] $types
     */
    public function getOrgUnitPermissionTypes()
    {
        $types = [];
        foreach ($this->obj_data as $type => $object_info) {
            if ($object_info['orgunit_permissions']) {
                $types[] = $type;
            }
        }
        return $types;
    }
    
    /**
     * Get object types which offer lti provider support.
     * @return string[] $types
     */
    public function getLTIProviderTypes()
    {
        $types = [];
        foreach ($this->obj_data as $type => $object_info) {
            if ($object_info['lti_provider']) {
                $types[] = $type;
            }
        }
        return $types;
    }
    
    /**
     * Check if object type offers orgunit position support
     * @param string $obj_type
     * @return bool
     */
    public function isOrgUnitPermissionType($a_obj_type)
    {
        return in_array($a_obj_type, $this->getOrgUnitPermissionTypes());
    }

    /**
     * Get Position By Object Type
     *
     * @param $a_type
     * @return int
     */
    public function getPositionByType($a_type)
    {
        $ilSetting = $this->settings;

        return ($ilSetting->get("obj_add_new_pos_" . $a_type) > 0)
            ? (int) $ilSetting->get("obj_add_new_pos_" . $a_type)
            : (int) $this->obj_data[$a_type]["default_pos"];
    }
    
    /**
     * Get plugin object info
     * @return type
     */
    public function getPlugins()
    {
        $plugins = array();
        foreach ((array) $this->obj_data as $type => $pl_data) {
            if ($this->isPlugin($type)) {
                $plugins[$type] = $pl_data;
            }
        }
        return $plugins;
    }
        
    /**
     * Get all object types which are defined as container in an explorer context
     *
     * @return array
     */
    public function getExplorerContainerTypes()
    {
        $res = $grp_map = $cnt_grp = array();
        
        // all repository object types
        foreach ($this->getSubObjectsRecursively("root") as $rtype) {
            $type = $rtype["name"];
            
            // obsolete
            if ($type == "rolf") {
                continue;
            }

            // gather group data
            $type_grp = $this->getGroupOfObj($type);
            if ($type_grp) {
                $grp_map[$type_grp][] = $type;
            }

            // add basic container types
            if ($this->isContainer($type)) {
                // add to cnt_grp
                if ($type_grp) {
                    $cnt_grp[] = $type_grp;
                }

                $res[] = $type;
            }
        }
        
        // add complete groups (cat => rcat, catr; crs => rcrs, crsr; ...)
        foreach ($cnt_grp as $grp) {
            $res = array_merge($res, $grp_map[$grp]);
        }
        
        // add very special case
        // outcommented, see bug #25662
        //		$res[] = "itgr";
        
        return array_unique($res);
    }

    /**
     * check whether obj_type supports centralised offline handling
     *
     * @param $a_obj_type
     * @return bool
     */
    public function supportsOfflineHandling($a_obj_type)
    {
        return
            isset($this->obj_data[$a_obj_type]) &&
            (bool) $this->obj_data[$a_obj_type]['offline_handling'];
    }


    /**
     * Loads the different plugins into the object definition.
     * @internal param $ilPluginAdmin
     * @internal param $rec
     */
    protected function readPluginData()
    {
        $this->parsePluginData(IL_COMP_SERVICE, "Repository", "robj", false);
        $this->parsePluginData(IL_COMP_MODULE, "OrgUnit", "orguext", true);
    }

    /**
     * loads a single plugin definition into the object definition
     * @param $component The component e.g. IL_COMP_SERVICE
     * @param $slotName The Slot name, e.g. Repository
     * @param $slotId the slot id, e.g. robj
     * @param $isInAdministration, can the object be created in the administration?
     */
    protected function parsePluginData($component, $slotName, $slotId, $isInAdministration)
    {
        $ilPluginAdmin = $this->plugin_admin;
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot($component, $slotName, $slotId);
        foreach ($pl_names as $pl_name) {
            include_once("./Services/Component/classes/class.ilPlugin.php");
            $pl_id = ilPlugin::lookupIdForName($component, $slotName, $slotId, $pl_name);
            if ($pl_id != "" && !isset($this->obj_data[$pl_id])) {
                include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
                $loc = ilPlugin::_getDirectory($component, $slotName, $slotId, $pl_name) . "/classes";
                // The plugin_id is the same as the type_id in repository object plugins.
                $pl = ilObjectPlugin::getPluginObjectByType($pl_id);

                $this->obj_data[$pl_id] = array(
                    "name" => $pl_id,
                    "class_name" => $pl_name,
                    "plugin" => "1",
                    "location" => $loc,
                    "checkbox" => "1",
                    "inherit" => "0",
                    "component" => "",
                    "translate" => "0",
                    "devmode" => "0",
                    "allow_link" => "1",
                    "allow_copy" => $pl->allowCopy() ? '1' : '0',
                    "rbac" => "1",
                    "group" => null,
                    "system" => "0",
                    "default_pos" => "99992000", // "unassigned" group
                    'repository' => '1',
                    'workspace' => '0',
                    'administration' => $isInAdministration?'1':'0',
                    "sideblock" => "0",
                    'export' => $ilPluginAdmin->supportsExport($component, $slotName, $slotId, $pl_name),
                    'offline_handling' => '0'
                );
                $parent_types = $pl->getParentTypes();
                foreach ($parent_types as $parent_type) {
                    $this->obj_data[$parent_type]["subobjects"][$pl_id] = array("name" => $pl_id, "max" => "", "lng" => $pl_id, "plugin" => true);
                }
            }
        }
    }
}
