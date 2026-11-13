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
 *********************************************************************/

declare(strict_types=1);

use ILIAS\ILIASObject\Definition\ObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;
use ILIAS\ILIASObject\Definition\ObjectTypeRegistry;

/**
 * Provides the definitions of all object types. The types are contributed
 * by the components via the bootstrap and collected in the ObjectTypeRegistry.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectDefinition
{
    public const MODE_REPOSITORY = 1;
    public const MODE_WORKSPACE = 2;
    public const MODE_ADMINISTRATION = 3;

    protected ilSetting $settings;
    protected ilComponentRepository $component_repository;
    protected ?ObjectTypeRegistry $registry;

    protected array $obj_data = [];
    protected array $obj_group = [];
    protected array $sub_types = [];
    protected string $parent_tag_name;
    protected string $current_tag;
    protected string $current_tag_name;
    protected ?string $root_trans_type = null;

    public function __construct()
    {
        global $DIC;

        $this->component_repository = $DIC["component.repository"];
        $this->settings = $DIC->settings();
        // the registry is not available in some setup objectives which build a fake $DIC
        $this->registry = $DIC->offsetExists(ObjectTypeRegistry::class)
            ? $DIC[ObjectTypeRegistry::class]
            : null;
        $this->readDefinitionData();
    }

    /**
     * @return array<string, ObjectType>
     */
    protected static function getObjectTypes(?ObjectTypeRegistry $registry): array
    {
        return $registry?->getAll() ?? [];
    }

    /**
     * Row of an object type in the format of the former table il_object_def.
     */
    protected static function toDefinitionRecord(ObjectType $type): array
    {
        $flag = static fn(ObjectTypeFlag $flag): string => $type->hasFlag($flag) ? '1' : '0';
        return [
            'id' => $type->getId(),
            'class_name' => $type->getClassName(),
            'component' => $type->getComponent(),
            'location' => $type->getLocation(),
            'checkbox' => $flag(ObjectTypeFlag::CHECKBOX),
            'inherit' => $flag(ObjectTypeFlag::INHERIT),
            'translate' => $type->getTranslationType(),
            'devmode' => $flag(ObjectTypeFlag::DEVMODE),
            'allow_link' => $flag(ObjectTypeFlag::ALLOW_LINK),
            'allow_copy' => $flag(ObjectTypeFlag::ALLOW_COPY),
            'rbac' => $flag(ObjectTypeFlag::RBAC),
            'system' => $flag(ObjectTypeFlag::SYSTEM),
            'sideblock' => $flag(ObjectTypeFlag::SIDEBLOCK),
            'default_pos' => (string) $type->getDefaultPosition(),
            'grp' => $type->getGroup(),
            'default_pres_pos' => (string) $type->getDefaultPresentationPosition(),
            'export' => $flag(ObjectTypeFlag::EXPORT),
            'repository' => $flag(ObjectTypeFlag::REPOSITORY),
            'workspace' => $flag(ObjectTypeFlag::WORKSPACE),
            'administration' => $flag(ObjectTypeFlag::ADMINISTRATION),
            'amet' => $flag(ObjectTypeFlag::AMET),
            'orgunit_permissions' => $flag(ObjectTypeFlag::ORGUNIT_PERMISSIONS),
            'lti_provider' => $flag(ObjectTypeFlag::LTI_PROVIDER),
            'offline_handling' => $flag(ObjectTypeFlag::OFFLINE_HANDLING),
        ];
    }

    /**
    * Read object definition data
    */
    public function readDefinitionData(): void
    {
        $types = self::getObjectTypes($this->registry);
        foreach ($types as $type) {
            $rec = self::toDefinitionRecord($type);
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
                "default_pos" => $type->isLegacyPlugin()
                    ? $rec["default_pos"]
                    : "9999" . str_pad($rec["default_pos"], 4, "0", STR_PAD_LEFT), // "unassigned" group
                "default_pres_pos" => $rec["default_pres_pos"],
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
            if ($type->isLegacyPlugin()) {
                $this->obj_data[$rec["id"]]["plugin"] = "1";
            }
            if ($type->getIconPath() !== null) {
                $this->obj_data[$rec["id"]]["icon"] = $type->getIconPath();
            }
            $this->obj_data[$rec["id"]]["subobjects"] ??= [];
            foreach ($type->getSubTypes() as $sub_type => $amet) {
                $this->sub_types[$rec["id"]][] = [
                    "obj_type" => $rec["id"],
                    "sub_type" => $sub_type,
                    "amet" => $amet ? "1" : "0"
                ];
            }
        }

        // relations are only added if both types are available, so components
        // can be removed without leaving references to undefined types
        foreach ($types as $type) {
            foreach ($type->getSubObjectTypes() as $sub_object_type => $max) {
                if (isset($types[$sub_object_type])) {
                    $this->addSubObject($type->getId(), $types[$sub_object_type], $max);
                }
            }
            foreach ($type->getParentTypes() as $parent_type => $max) {
                if (isset($types[$parent_type])) {
                    $this->addSubObject($parent_type, $type, $max);
                }
            }
        }

        foreach ($this->registry?->getGroups() ?? [] as $group) {
            $this->obj_group[$group->getId()] = [
                "id" => $group->getId(),
                "name" => $group->getName(),
                "default_pres_pos" => (string) $group->getDefaultPresentationPosition()
            ];
        }
        $this->readPluginData();
    }

    protected function addSubObject(string $parent_type, ObjectType $type, int $max): void
    {
        $this->obj_data[$parent_type]["subobjects"][$type->getId()] = [
            "name" => $type->getId(),
            "max" => $max > 0 ? (string) $max : "",
            "lng" => $type->getId()
        ];
        if ($type->isLegacyPlugin()) {
            $this->obj_data[$parent_type]["subobjects"][$type->getId()]["plugin"] = true;
        }
    }

    protected static function getGroupedPluginObjectTypes(array $grouped_obj, string $slotId): array
    {
        global $DIC;

        $component_repository = $DIC['component.repository'];
        $plugins = $component_repository->getPluginSlotById($slotId)->getActivePlugins();

        foreach ($plugins as $plugin) {
            $pl_id = $plugin->getId();
            if (!isset($grouped_obj[$pl_id])) {
                $grouped_obj[$pl_id] = [
                    'pos' => '99992000', // "unassigned" group
                    'objs' => [0 => $pl_id]
                ];
            }
        }
        return $grouped_obj;
    }

    /**
     * Icon of types which do not use a standard icon of the UI framework.
     */
    public function getIconPath(string $obj_name): ?string
    {
        return $this->obj_data[$obj_name]["icon"] ?? null;
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
    /**
     * Types with the prefix "x" are no longer plugins per se, components of
     * other vendors can contribute them, too. Only types of the plugin adapter
     * and unknown "x" types (e.g. of a removed plugin) are treated as plugins.
     */
    public function isPluginTypeName(string $str): bool
    {
        return $this->isPlugin($str)
            || (substr($str, 0, 1) == "x" && !$this->isAvailable($str));
    }

    /**
     * false if no component (or plugin) provides the type (anymore), e.g. because
     * the component has been removed.
     */
    public function isAvailable(string $type): bool
    {
        return isset($this->obj_data[$type]["class_name"]);
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
        if ($subobjects = ($this->obj_data[$obj_type]['subobjects'] ?? [])) {
            // Filter some objects e.g. chat object are creatable if chat is active
            if ($filter) {
                $this->__filterObjects($subobjects);
            }
            foreach ($subobjects as $data => $sub) {
                if (!isset($sub['module']) || $sub['module'] !== 'n') {
                    if (!($this->settings->get('obj_dis_creation_' . $data))) {
                        $subs[$data] = $sub;

                        // determine position
                        $pos = (int) $this->obj_data[$data]['default_pos'];
                        if ($this->settings->get('obj_add_new_pos_' . $data) > 0) {
                            $pos = (int) $this->settings->get("obj_add_new_pos_" . $data);
                        }
                        $subs[$data]['pos'] = $pos;
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
        ?int $parent_ref_id = null
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
        xml_set_element_handler($xml_parser, $this->handlerBeginTag(...), $this->handlerEndTag(...));
        xml_set_character_data_handler($xml_parser, $this->handlerCharacterData(...));
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
    * This is only every called with $a_component_type = "components/ILIAS".
    * This is only used in two locations:
    *    - Services/Repository/Administration/class.ilModulesTableGUI.php
    *    - Services/Repository/Administration/class.ilObjRepositorySettings.php
    */
    public static function getRepositoryObjectTypesForComponent(string $component_type, string $component_name): array
    {
        global $DIC;

        $types = [];
        foreach (self::getObjectTypes($DIC[ObjectTypeRegistry::class] ?? null) as $type) {
            if ($type->getComponent() === $component_type . "/" . $component_name
                && !$type->hasFlag(ObjectTypeFlag::SYSTEM)) {
                $types[] = self::toDefinitionRecord($type);
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

        $types = self::getObjectTypes($DIC[ObjectTypeRegistry::class] ?? null);
        return isset($types[$obj_type]) ? $types[$obj_type]->getComponent() : "";
    }

    /**
     * @param mixed $parent_obj_type
     */
    public static function getGroupedRepositoryObjectTypes($parent_obj_type): array
    {
        global $DIC;
        /** @var ilObjectDefinition $obj_definition */
        $obj_definition = $DIC["objDefinition"];
        $groups = $obj_definition->obj_group;

        $grouped_obj = [];
        $seen = [];
        foreach ((array) $parent_obj_type as $parent) {
            foreach (array_keys($obj_definition->obj_data[$parent]["subobjects"] ?? []) as $type) {
                $data = $obj_definition->obj_data[$type] ?? null;
                if ($data === null || isset($seen[$type]) || $data["system"] === "1" || $data["sideblock"] === "1") {
                    continue;
                }
                $seen[$type] = true;
                $group = $data["group"] ?? "";
                if ($group !== "" && isset($groups[$group])) {
                    $grouped_obj[$group]["pos"] = (int) $groups[$group]["default_pres_pos"];
                    $grouped_obj[$group]["objs"][] = $type;
                } else {
                    $grouped_obj[$type]["pos"] = isset($data["plugin"])
                        ? 99992000
                        : (int) $data["default_pres_pos"];
                    $grouped_obj[$type]["objs"][] = $type;
                }
            }
        }
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
    /**
     * Kept for the existing callers: true for every type which is not available,
     * not only for plugins.
     */
    public function isInactivePlugin(string $type): bool
    {
        return !$this->isAvailable($type);
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
            if (array_key_exists('lti_provider', $object_info) && $object_info['lti_provider']) {
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
        $res[] = "obgr";

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
        // repository object plugins are contributed as object types by the
        // adapter in the Repository component
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

        $pos = 0;
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
                    'default_pos' => (string) (99992000 + $pos++),
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
