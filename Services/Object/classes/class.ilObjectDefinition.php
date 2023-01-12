<?php

declare(strict_types=1);

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
* parses the objects.xml
* it handles the xml-description of all ilias objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @externalTableAccess ilObjDefReader on il_object_def, il_object_subobj, il_object_group
*/
class ilObjectDefinition
{
    public const MODE_REPOSITORY = 1;
    public const MODE_WORKSPACE = 2;
    public const MODE_ADMINISTRATION = 3;

    protected ilSetting $settings;
    protected ilComponentRepository $component_repository;

    protected array $obj_data = [];
    protected array $obj_group = [];
    protected array $sub_types = [];
    protected string $parent_tag_name;
    protected string $current_tag;
    protected string $current_tag_name;

    public function __construct()
    {
        global $DIC;

        $this->component_repository = $DIC["component.repository"];
        $this->settings = $DIC->settings();
        $this->readDefinitionData();
    }

    protected function readDefinitionDataFromCache(): void
    {
        $this->obj_data = [];
        $defIds = [];

        $global_cache = ilCachedObjectDefinition::getInstance();
        foreach ($global_cache->getIlObjectDef() as $rec) {
            $this->obj_data[$rec["id"]] = [
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
            ];
            $this->obj_data[$rec["id"]]["subobjects"] = [];

            $defIds[] = $rec["id"];
        }

        $subobj = $global_cache->lookupSubObjForParent($defIds);

        foreach ($subobj as $rec2) {
            $max = $rec2["mmax"];
            if ($max <= 0) {
                $max = "";
            }
            $this->obj_data[$rec2["parent"]]["subobjects"][$rec2["subobj"]] = [
                "name" => $rec2["subobj"],
                "max" => $max,
                "lng" => $rec2["subobj"]
            ];
        }

        $this->obj_group = $global_cache->getIlObjectGroup();
        $this->readPluginData();
        $this->sub_types = $global_cache->getIlObjectSubType();
    }


    protected function readDefinitionDataFromDB(): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $this->obj_data = [];

        // Select all object_definitions and collect the definition id's in this array.
        $defIds = [];

        $sql =
            "SELECT id, class_name, component, location, checkbox, inherit, translate, devmode, allow_link," . PHP_EOL
            . "allow_copy, rbac, `system`, sideblock, default_pos, grp, default_pres_pos, `export`, repository," . PHP_EOL
            . "workspace, administration, amet, orgunit_permissions, lti_provider, offline_handling" . PHP_EOL
            . "FROM il_object_def" . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        while ($rec = $ilDB->fetchAssoc($result)) {
            $this->obj_data[$rec["id"]] = [
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
            ];
            $this->obj_data[$rec["id"]]["subobjects"] = [];

            $defIds[] = $rec["id"];
        }

        // get all sub object definitions in a single query
        $sql =
            "SELECT parent, subobj, mmax" . PHP_EOL
            . "FROM il_object_subobj" . PHP_EOL
            . "WHERE " . $ilDB->in('parent', $defIds, false, 'text') . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        while ($rec2 = $ilDB->fetchAssoc($result)) {
            $max = $rec2["mmax"];
            if ($max <= 0) { // for backward compliance
                $max = "";
            }
            $this->obj_data[$rec2["parent"]]["subobjects"][$rec2["subobj"]] = [
                "name" => $rec2["subobj"],
                "max" => $max,
                "lng" => $rec2["subobj"]
            ];
        }

        $sql =
            "SELECT id, name, default_pres_pos" . PHP_EOL
            . "FROM il_object_group" . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        $this->obj_group = array();
        while ($rec = $ilDB->fetchAssoc($result)) {
            $this->obj_group[$rec["id"]] = $rec;
        }

        $this->readPluginData();

        $sql =
            "SELECT obj_type, sub_type, amet" . PHP_EOL
            . "FROM il_object_sub_type" . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        $this->sub_types = array();
        while ($rec = $ilDB->fetchAssoc($result)) {
            $this->sub_types[$rec["obj_type"]][] = $rec;
        }
    }

    /**
    * Read object definition data
    */
    public function readDefinitionData(): void
    {
        if (ilGlobalCache::getInstance(ilGlobalCache::COMP_OBJ_DEF)->isActive()) {
            $this->readDefinitionDataFromCache();
        } else {
            $this->readDefinitionDataFromDB();
        }
    }

    protected static function getGroupedPluginObjectTypes(array $grouped_obj, string $slotId): array
    {
        global $DIC;

        $component_repository = $DIC["component.repository"];
        $plugins = $component_repository->getPluginSlotById($slotId)->getActivePlugins();
        foreach ($plugins as $plugin) {
            $pl_id = $plugin->getId();
            if (!isset($grouped_obj[$pl_id])) {
                $grouped_obj[$pl_id] = array(
                    "pos" => "99992000", // "unassigned" group
                    "objs" => array(0 => $pl_id)
                );
            }
        }
        return $grouped_obj;
    }

    public function getClassName(string $obj_name): string
    {
        return $this->obj_data[$obj_name]["class_name"] ?? '';
    }

    public function getLocation(string $obj_name): string
    {
        return $this->obj_data[$obj_name]["location"] ?? '';
    }

    /**
    * Get Group information
    */
    public function getGroup(string $id): array
    {
        return $this->obj_group[$id];
    }

    /**
    * Get Group of object type
    */
    public function getGroupOfObj(string $obj_name): ?string
    {
        return $this->obj_data[$obj_name]["group"] ?? null;
    }

    /**
    * should the object get a checkbox (needed for 'cut','copy' ...)
    */
    public function hasCheckbox(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["checkbox"] ?? false);
    }

    /**
    * get translation type (sys, db or null)
    */
    public function getTranslationType(string $obj_name): ?string
    {
        global $DIC;
        $ilDB = $DIC->database();

        if ($obj_name == "root") {
            if (!isset($this->root_trans_type)) {
                $sql =
                    "SELECT count(obj_id) cnt" . PHP_EOL
                    . "FROM object_translation" . PHP_EOL
                    . "WHERE obj_id = " . $ilDB->quote(ROOT_FOLDER_ID, 'integer') . PHP_EOL
                ;
                $set = $ilDB->query($sql);
                $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
                if ($rec["cnt"] > 0) {
                    $this->root_trans_type = "db";
                } else {
                    $this->root_trans_type = $this->obj_data[$obj_name]["translate"];
                }
            }
            return $this->root_trans_type;
        }

        if (isset($this->obj_data[$obj_name])) {
            return $this->obj_data[$obj_name]["translate"];
        }

        return "";
    }

    /**
    * Does object permits stopping inheritance?
    */
    public function stopInheritance(string $obj_name): bool
    {
        return (bool) $this->obj_data[$obj_name]["inherit"];
    }

    /**
    * get dev mode status by type
    */
    public function getDevMode(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["devmode"] ?? false);
    }

    /**
    * get all object types in dev mode
    *
    * @return	array	object types set to development
    */
    public function getDevModeAll(): array
    {
        $types = array_keys($this->obj_data);

        $dev_types = [];
        foreach ($types as $type) {
            if ($this->getDevMode($type)) {
                $dev_types[] = $type;
            }
        }

        return $dev_types;
    }

    /**
    * get RBAC status by type
    * returns true if object type is a RBAC object type
    */
    public function isRBACObject(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["rbac"] ?? false);
    }

    /**
    * get RBAC status by type
    * returns true if object type is an (activated) plugin type
    */
    public function isPlugin(string $obj_name): bool
    {
        return isset($this->obj_data[$obj_name]["plugin"]);
    }

    /**
     * Check if given type is a plugin type name (starts with an "x")
     */
    public function isPluginTypeName(string $str): bool
    {
        return (substr($str, 0, 1) == "x");
    }

    /**
     * Returns true if the given type is an active type of repositoryObject or Organisation Unit Extension plugin.
     */
    public function isActivePluginType(string $type): bool
    {
        if (!$this->component_repository->hasPluginId($type)) {
            return false;
        }
        $plugin_slot = $this->component_repository->getPluginById($type)->getPluginSlot();
        return $plugin_slot->getId() === "robj" || $plugin_slot->getId() === "orguext";
    }

    public function getAllRBACObjects(): array
    {
        $types = array_keys($this->obj_data);

        $rbac_types = [];
        foreach ($types as $type) {
            if ($this->isRBACObject($type)) {
                $rbac_types[] = $type;
            }
        }

        return $rbac_types;
    }

    /**
     * get all object types
     */
    public function getAllObjects(): array
    {
        return array_keys($this->obj_data);
    }

    /**
     * checks if linking of an object type is allowed
     */
    public function allowLink(string $obj_name): bool
    {
        return (bool) $this->obj_data[$obj_name]["allow_link"];
    }

    /**
     * checks if copying of an object type is allowed
     */
    public function allowCopy(string $obj_name): bool
    {
        return (bool) $this->obj_data[$obj_name]["allow_copy"];
    }

    public function allowExport(string $obj_name): bool
    {
        return (bool) $this->obj_data[$obj_name]['export'];
    }

    /**
     * Check whether the creation of local roles is allowed
     * Currently disabled for type "root" and "adm"
     */
    public function hasLocalRoles(string $obj_type): bool
    {
        switch ($obj_type) {
            case 'root':
                return false;

            default:
                return true;
        }
    }

    /**
     * get all sub objects by type
     */
    public function getSubObjects(string $obj_type, bool $filter = true): array
    {
        $subs = [];
        if ($subobjects = ($this->obj_data[$obj_type]["subobjects"] ?? false)) {
            // Filter some objects e.g. chat object are creatable if chat is active
            if ($filter) {
                $this->__filterObjects($subobjects);
            }
            foreach ($subobjects as $data => $sub) {
                if (!isset($sub["module"]) || $sub["module"] != "n") {
                    if (!($this->settings->get("obj_dis_creation_" . $data))) {
                        $subs[$data] = $sub;

                        // determine position
                        $pos = (int) $this->obj_data[$data]["default_pos"];
                        if ($this->settings->get("obj_add_new_pos_" . $data) > 0) {
                            $pos = (int) $this->settings->get("obj_add_new_pos_" . $data);
                        }
                        $subs[$data]["pos"] = $pos;
                    }
                }
            }

            return ilArrayUtil::sortArray($subs, "pos", 'ASC', true, true);
        }

        return $subs;
    }

    /**
    * Get all sub objects by type.
    * This function returns all sub objects allowed by the provided object type
    * and all its sub object types recursively.
    *
    * This function is used to create local role templates. It is important,
    * that we do not filter out any objects here!
    */
    public function getSubObjectsRecursively(
        string $obj_type,
        bool $include_source_obj = true,
        bool $add_admin_objects = false
    ): array {
        // This associative array is used to collect all sub object types.
        // key=>type, value=data
        $recursive_subs = [];

        // This array is used to keep track of the object types, we
        // need to call function getSubobjects() for.
        $to_do = [$obj_type];

        // This array is used to keep track of the object types, we
        // have called function getSubobjects() already. This is to
        // prevent endless loops, for object types that support
        // themselves as subobject types either directly or indirectly.
        $done = [];

        while (count($to_do) > 0) {
            $type = array_pop($to_do);
            $done[] = $type;

            // no recovery folder subitems
            if ($type == 'recf') {
                continue;
            }

            // Hide administration if desired
            if (!$add_admin_objects and $type == 'adm') {
                $subs = [];
            } else {
                $subs = $this->getSubObjects($type);
            }
            foreach ($subs as $subtype => $data) {
                // Hide role templates and folder from view
                if ($this->getDevMode($subtype) or !$this->isRBACObject($subtype)) {
                    continue;
                }
                if ($subtype == 'rolt') {
                    continue;
                }
                if (!$add_admin_objects and $subtype == 'adm') {
                    continue;
                }

                $recursive_subs[$subtype] = $data;
                if (!in_array($subtype, $done) && !in_array($subtype, $to_do)) {
                    $to_do[] = $subtype;
                }
            }
        }

        if ($include_source_obj) {
            if (!isset($recursive_subs[$obj_type])) {
                $recursive_subs[$obj_type]['name'] = $obj_type;
                $recursive_subs[$obj_type]['lng'] = $obj_type;
                $recursive_subs[$obj_type]['max'] = 0;
                $recursive_subs[$obj_type]['pos'] = -1;
            }
        }
        return ilArrayUtil::sortArray($recursive_subs, "pos", 'ASC', true, true);
    }


    /**
    * get all subjects except (rolf) of the adm object
    * This is necessary for filtering these objects in role perm view.
    * e.g. it is not necessary to view/edit role permission for the usr object since it's not possible to create a new one
    */
    public function getSubobjectsToFilter(string $obj_type = "adm"): array
    {
        foreach ($this->obj_data[$obj_type]["subobjects"] as $key => $value) {
            switch ($key) {
                case "rolf":
                case "orgu":
                    // DO NOTHING
                    break;

                default:
                    $tmp_subs[] = $key;
            }
        }
        $tmp_subs[] = "adm";

        return $tmp_subs;
    }

    public function getCreatableSubObjects(
        string $obj_type,
        int $context = self::MODE_REPOSITORY,
        int $parent_ref_id = null
    ): array {
        $sub_objects = $this->getSubObjects($obj_type);

        // remove role folder object from list
        unset($sub_objects["rolf"]);

        $sub_types = array_keys($sub_objects);

        // remove object types in development from list
        foreach ($sub_types as $type) {
            if ($this->getDevMode($type) || $this->isSystemObject($type)) {
                unset($sub_objects[$type]);
            }
            if ($context == self::MODE_REPOSITORY && !$this->isAllowedInRepository($type)) {
                unset($sub_objects[$type]);
            }
            if ($context == self::MODE_WORKSPACE && !$this->isAllowedInWorkspace($type)) {
                unset($sub_objects[$type]);
            }
            if ($context == self::MODE_ADMINISTRATION && !$this->isAdministrationObject($type)) {
                unset($sub_objects[$type]);
            }
        }

        if ($obj_type == "prg") {
            // ask study program which objects are allowed to create on the concrete node.
            return ilObjStudyProgramme::getCreatableSubObjects($sub_objects, $parent_ref_id);
        }

        return $sub_objects;
    }

    /**
     * get a string of all sub objects by type
     */
    public function getSubObjectsAsString(string $obj_type): string
    {
        $string = "";
        if (is_array($this->obj_data[$obj_type]["subobjects"])) {
            $data = array_keys($this->obj_data[$obj_type]["subobjects"]);
            $string = "'" . implode("','", $data) . "'";
        }

        return $string;
    }

    /**
     * Check if object type is container ('crs','fold','grp' ...)
     */
    public function isContainer(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]['subobjects'] ?? false);
    }

    public function setHandlers($xml_parser): void
    {
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($xml_parser, 'handlerCharacterData');
    }

    public function handlerBeginTag($xml_parser, string $name, array $attribs): void
    {
        switch ($name) {
            case 'object':
                $this->parent_tag_name = $attribs["name"];
                break;
            case 'property':
                $this->current_tag = "property";
                $this->current_tag_name = $attribs["name"];
                $this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["module"] = $attribs["module"];
                break;
        }
    }

    public function handlerCharacterData($xml_parser, string $data): void
    {
    }

    public function handlerEndTag($xml_parser, string $name): void
    {
        $this->current_tag = '';
        $this->current_tag_name = '';
    }

    public function __filterObjects(array &$sub_objects): void
    {
        // DO NOTHING
    }

    /**
    * checks if object type is a system object
    *
    * system objects are those object types that are only used for
    * internal purposes and to keep the object type model consistent.
    * Typically, they are used in the administration, exist only once
    * and may contain only specific object types.
    * To mark an object type as a system object type, use 'system=1'
    * in the object definition in objects.xml
    */
    public function isSystemObject(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["system"] ?? false);
    }

    /**
    * Check, whether object type is a side block.
    */
    public function isSideBlock(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["sideblock"] ?? false);
    }

    public function getSideBlockTypes(bool $filter_repository_types = true): array
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
    *
    * This is only every called with $a_component_type = "Modules".
    * This is only used in two locations:
    *    - Services/Repository/Administration/class.ilModulesTableGUI.php
    *    - Services/Repository/Administration/class.ilObjRepositorySettings.php
    */
    public static function getRepositoryObjectTypesForComponent(string $component_type, string $component_name): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql =
            "SELECT id, class_name, component, location, checkbox, inherit, translate, devmode, allow_link," . PHP_EOL
            . "allow_copy, rbac, `system`, sideblock, default_pos, grp, default_pres_pos, `export`, repository," . PHP_EOL
            . "workspace, administration, amet, orgunit_permissions, lti_provider, offline_handling" . PHP_EOL
            . "FROM il_object_def" . PHP_EOL
            . "WHERE component = %s" . PHP_EOL
        ;
        $result = $ilDB->queryF($sql, ["text"], [$component_type . "/" . $component_name]);

        $types = [];
        while ($rec = $ilDB->fetchAssoc($result)) {
            if ($rec["system"] != 1) {
                $types[] = $rec;
            }
        }

        return $types;
    }

    /**
    * Get component for object type
    */
    public static function getComponentForType(string $obj_type): string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->queryF("SELECT component FROM il_object_def WHERE id = %s", ["text"], [$obj_type]);

        if ($rec = $ilDB->fetchAssoc($result)) {
            return $rec["component"];
        }

        return "";
    }

    /**
     * @param mixed $parent_obj_type
     */
    public static function getGroupedRepositoryObjectTypes($parent_obj_type): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM il_object_group");
        $groups = array();
        while ($gr_rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $groups[$gr_rec["id"]] = $gr_rec;
        }

        $global_cache = ilCachedObjectDefinition::getInstance();

        $recs = $global_cache->lookupGroupedRepObj($parent_obj_type);

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
        $grouped_obj = self::getGroupedPluginObjectTypes($grouped_obj, "robj");
        $grouped_obj = self::getGroupedPluginObjectTypes($grouped_obj, "orguext");

        return ilArrayUtil::sortArray($grouped_obj, "pos", "asc", true, true);
    }

    /**
     * checks if object type can be used in repository context
     */
    public function isAllowedInRepository(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["repository"] ?? false);
    }

    /**
     * get all RBAC object types
     */
    public function getAllRepositoryTypes(bool $incl_adm = false): array
    {
        $types = array_keys($this->obj_data);

        $rbac_types = [];
        foreach ($types as $type) {
            if ($this->isAllowedInRepository($type) && (!$this->isAdministrationObject($type) || $incl_adm)) {
                $rbac_types[] = $type;
            }
        }

        return $rbac_types;
    }

    /**
     * checks if object type can be used in workspace context
     */
    public function isAllowedInWorkspace(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]["workspace"] ?? false);
    }

    /**
     * Check if administration object
     */
    public function isAdministrationObject(string $obj_name): bool
    {
        return (bool) ($this->obj_data[$obj_name]['administration'] ?? false);
    }

    /**
     * Check whether type belongs to inactive plugin
     */
    public function isInactivePlugin(string $type): bool
    {
        if (substr($type, 0, 1) == "x" && !$this->isPlugin($type)) {
            return true;
        }
        return false;
    }

    /**
     * Get advanced meta data objects
     */
    public function getAdvancedMetaDataTypes(): array
    {
        $amet = [];
        foreach ($this->obj_data as $k => $v) {
            if ($v["amet"] ?? false) {
                $amet[] = ["obj_type" => $k, "sub_type" => ""];
            }
        }

        foreach ($this->sub_types as $type => $sub_types) {
            foreach ($sub_types as $t) {
                if ($t["amet"]) {
                    $amet[] = ["obj_type" => $type, "sub_type" => $t["sub_type"]];
                }
            }
        }

        return $amet;
    }

    /**
     * Get object type with org unit position permission support
     *
     * @return string[] $types
     */
    public function getOrgUnitPermissionTypes(): array
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
    public function getLTIProviderTypes(): array
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
     * Check if object type offers org unit position support
     */
    public function isOrgUnitPermissionType(string $obj_type): bool
    {
        return in_array($obj_type, $this->getOrgUnitPermissionTypes());
    }

    /**
     * Get Position By Object Type
     */
    public function getPositionByType(string $type): int
    {
        if ($this->settings->get("obj_add_new_pos_" . $type) > 0) {
            return (int) $this->settings->get("obj_add_new_pos_" . $type);
        }
        return (int) $this->obj_data[$type]["default_pos"];
    }

    /**
     * Get plugin object info
     */
    public function getPlugins(): array
    {
        $plugins = [];
        foreach ($this->obj_data as $type => $pl_data) {
            if ($this->isPlugin($type)) {
                $plugins[$type] = $pl_data;
            }
        }
        return $plugins;
    }

    /**
     * Get all object types which are defined as container in an explorer context
     */
    public function getExplorerContainerTypes(): array
    {
        $res = $grp_map = $cnt_grp = [];

        // all repository object types
        foreach ($this->getSubObjectsRecursively("root") as $rtype) {
            $type = $rtype["name"];

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
        $res[] = "itgr";

        return array_unique($res);
    }

    /**
     * check whether obj_type supports centralised offline handling
     */
    public function supportsOfflineHandling(string $obj_type): bool
    {
        return (bool) ($this->obj_data[$obj_type]['offline_handling'] ?? false);
    }


    /**
     * Loads the different plugins into the object definition.
     * @internal param $rec
     */
    protected function readPluginData(): void
    {
        $this->parsePluginData("robj", false);
        $this->parsePluginData("orguext", true);
    }

    /**
     * loads a single plugin definition into the object definition
     * @param $slotId string slot id, e.g. robj
     * @param $isInAdministration bool can the object be created in the administration?
     */
    protected function parsePluginData(string $slotId, bool $isInAdministration): void
    {
        $plugins = $this->component_repository->getPluginSlotById($slotId)->getActivePlugins();
        foreach ($plugins as $plugin) {
            $pl_id = $plugin->getId();
            if ($pl_id != "" && !isset($this->obj_data[$pl_id])) {
                $loc = $plugin->getPath() . "/classes";
                // The plugin_id is the same as the type_id in repository object plugins.
                $pl = ilObjectPlugin::getPluginObjectByType($pl_id);

                $this->obj_data[$pl_id] = [
                    "name" => $pl_id,
                    "class_name" => $pl->getPluginName(),
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
                    'administration' => $isInAdministration ? '1' : '0',
                    "sideblock" => "0",
                    'export' => $plugin->supportsExport(),
                    'offline_handling' => '0',
                    'orgunit_permissions' => $pl->useOrguPermissions() ? '1' : '0'
                ];

                $parent_types = $pl->getParentTypes();
                foreach ($parent_types as $parent_type) {
                    $this->obj_data[$parent_type]["subobjects"][$pl_id] = [
                        "name" => $pl_id,
                        "max" => "",
                        "lng" => $pl_id,
                        "plugin" => true
                    ];
                }
            }
        }
    }
}
