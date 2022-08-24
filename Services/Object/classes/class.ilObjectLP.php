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
 * Base class for object lp connectors
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjectLP
{
    protected static ?array $type_defaults = null;

    protected ilTree $tree;
    protected ilDBInterface $db;
    protected ilObjectDefinition $objectDefinition;

    protected int $obj_id;

    protected ?ilLPCollection $collection_instance = null;
    protected ?int $mode = null;

    protected function __construct(int $obj_id)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->objectDefinition = $DIC['objDefinition'];

        $this->obj_id = $obj_id;
    }

    public static function getInstance(int $obj_id): ilObjectLP
    {
        static $instances = array();

        if (!isset($instances[$obj_id])) {
            $type = ilObject::_lookupType($obj_id);
            $class = self::getTypeClass($type);
            if ($class) {
                $instance = new $class($obj_id);
            } else {
                // :TODO: should we return anything?
                $instance = new self($obj_id);
            }
            $instances[$obj_id] = $instance;
        }
        return $instances[$obj_id];
    }

    public static function getTypeClass(string $type): string
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];

        if (self::isSupportedObjectType($type)) {
            switch ($type) {
                // container
                case "crs":
                    return "ilCourseLP";
                case 'crsr':
                    return 'ilCourseReferenceLP';
                case "grp":
                    return "ilGroupLP";
                case "fold":
                    return "ilFolderLP";
                case "lso":
                    return "ilLSLP";

                    // learning resources
                case "lm":
                    return "ilLearningModuleLP";
                case "htlm":
                    return "ilHTMLLearningModuleLP";
                case "sahs":
                    return "ilScormLP";

                    // misc
                case "tst":
                    return "ilTestLP";
                case "exc":
                    return "ilExerciseLP";
                case 'file':
                    return 'ilFileLP';
                case "mcst":
                    return "ilMediaCastLP";
                case "sess":
                    return  "ilSessionLP";
                case "svy":
                    return  "ilSurveyLP";
                case "prg":
                    return "ilStudyProgrammeLP";
                case "iass":
                    return "ilIndividualAssessmentLP";
                case "copa":
                    return "ilContentPageLP";
                case 'cmix':
                    return ilCmiXapiLP::class;
                case 'lti':
                    return ilLTIConsumerLP::class;
                case 'frm':
                    return ilForumLP::class;

                    // plugin
                case $objDefinition->isPluginTypeName($type):
                    return "ilPluginLP";
            }
        }
        return "";
    }

    public static function getSupportedObjectTypes(): array
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];

        $valid = [
            "crs",
            "grp",
            "fold",
            "lm",
            "htlm",
            "sahs",
            "tst",
            "exc",
            "sess",
            "svy",
            "file",
            "mcst",
            "prg",
            "iass",
            "copa",
            "lso",
            'cmix',
            'lti',
            'crsr',
            'frm'
        ];

        $plugins = $component_repository->getPluginSlotById("robj")->getActivePlugins();
        foreach ($plugins as $plugin) {
            $type = $plugin->getId();
            if (ilRepositoryObjectPluginSlot::isTypePluginWithLP($type)) {
                $valid[] = $type;
            }
        }


        return $valid;
    }

    public static function isSupportedObjectType(string $type): bool
    {
        $valid = self::getSupportedObjectTypes();

        if (in_array($type, $valid)) {
            return true;
        }

        return false;
    }

    public function resetCaches(): void
    {
        $this->mode = null;
        $this->collection_instance = null;
    }

    public function isAnonymized(): bool
    {
        // see ilLPCollectionOfRepositoryObjects::validateEntry()
        return false;
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_UNDEFINED;
    }

    /**
     * @return int[]
     */
    public function getValidModes(): array
    {
        return [];
    }

    public function getCurrentMode(): int
    {
        if ($this->mode === null) {
            // using global type default if LP is inactive
            if (!ilObjUserTracking::_enabledLearningProgress()) {
                $mode = self::getTypeDefaultFromDB(ilObject::_lookupType($this->obj_id));
                if ($mode === null) {
                    // fallback: inactive as type default may not be suitable
                    $mode = ilLPObjSettings::LP_MODE_DEACTIVATED;
                }
            }
            // use object LP setting
            else {
                $mode = ilLPObjSettings::_lookupDBMode($this->obj_id);
                if ($mode === null) {
                    // fallback: object type default
                    $mode = $this->getDefaultMode();
                }
            }
            $this->mode = (int) $mode;
        }

        return $this->mode;
    }

    public function isActive(): bool
    {
        // :TODO: check LP activation?

        $mode = $this->getCurrentMode();
        if ($mode == ilLPObjSettings::LP_MODE_DEACTIVATED ||
            $mode == ilLPObjSettings::LP_MODE_UNDEFINED) {
            return false;
        }
        return true;
    }

    public function getModeText(int $mode): string
    {
        return ilLPObjSettings::_mode2Text($mode);
    }

    public function getModeInfoText(int $mode): string
    {
        return ilLPObjSettings::_mode2InfoText($mode);
    }

    public function getSettingsInfo(): string
    {
        // type-specific
        return "";
    }


    public function getCollectionInstance(): ?ilLPCollection
    {
        if ($this->collection_instance === null) {
            $this->collection_instance = ilLPCollection::getInstanceByMode($this->obj_id, $this->getCurrentMode());
        }
        return $this->collection_instance;
    }

    public function getMembers(bool $search = true): array
    {
        if (!$search) {
            return [];
        }

        $ref_ids = ilObject::_getAllReferences($this->obj_id);
        $ref_id = current($ref_ids);

        // walk path to find parent with specific members
        $path = $this->tree->getPathId($ref_id);
        array_pop($path);
        foreach (array_reverse($path) as $path_ref_id) {
            $olp = self::getInstance(ilObject::_lookupObjId($path_ref_id));
            $all = $olp->getMembers(false);
            if (is_array($all)) {
                return $all;
            }
        }
        return [];
    }

    final public function resetLPDataForCompleteObject(bool $recursive = true): void
    {
        $user_ids = $this->gatherLPUsers();
        if (sizeof($user_ids)) {
            $this->resetLPDataForUserIds(array_unique($user_ids), $recursive);
        }
    }

    final public function resetLPDataForUserIds(array $user_ids, bool $recursive = true): void
    {
        if ($recursive && method_exists($this, "getPossibleCollectionItems")) { // #15203
            $subitems = $this->getPossibleCollectionItems();
            if (is_array($subitems)) {
                foreach ($subitems as $sub_ref_id) {
                    $olp = self::getInstance(ilObject::_lookupObjId($sub_ref_id));
                    $olp->resetLPDataForUserIds($user_ids, false);
                }
            }
        }

        $this->resetCustomLPDataForUserIds($user_ids, $recursive);

        ilLPMarks::_deleteForUsers($this->obj_id, $user_ids);

        ilChangeEvent::_deleteReadEventsForUsers($this->obj_id, $user_ids);

        // update LP status to get collections up-to-date
        foreach ($user_ids as $user_id) {
            ilLPStatusWrapper::_updateStatus($this->obj_id, $user_id);
        }
    }

    protected function resetCustomLPDataForUserIds(array $user_ids, bool $recursive = true): void
    {
        // this should delete all data that is relevant for the supported LP modes
    }

    protected function gatherLPUsers(): array
    {
        $user_ids = ilLPMarks::_getAllUserIds($this->obj_id);
        return array_merge($user_ids, ilChangeEvent::_getAllUserIds($this->obj_id));
    }

    final public static function handleMove(int $source_ref_id): void
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();

        $ref_ids = $tree->getSubTreeIds($source_ref_id);
        $ref_ids[] = $source_ref_id;

        // get "parent" path to source node (not including source node)
        $new_path = $tree->getPathId($source_ref_id);
        array_pop($new_path);
        $new_path = implode("/", $new_path);

        // find collections with ref_ids
        $sql =
            "SELECT DISTINCT(ut_lp_collections.obj_id) obj_id" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "JOIN ut_lp_collections ON" . PHP_EOL
            . "(" . $ilDB->in("object_reference.ref_id", $ref_ids, false, "integer") . PHP_EOL
            . "AND object_reference.ref_id = ut_lp_collections.item_id)" . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($result)) {
            if (in_array(ilObject::_lookupType($row["obj_id"]), ["crs", "grp", "fold"])) {
                $coll_ref_id = ilObject::_getAllReferences($row["obj_id"]);
                $coll_ref_id = array_pop($coll_ref_id);

                // #13402
                if ($coll_ref_id == $source_ref_id) {
                    continue;
                }

                // #17703 - collection has also been moved - nothing todo
                if ($tree->isGrandChild($source_ref_id, $coll_ref_id)) {
                    continue;
                }

                // get path to collection (including collection "parent")
                $coll_path = $tree->getPathId($coll_ref_id);
                $coll_path = implode("/", $coll_path);

                // collection path is not inside new path
                if (!stristr($new_path, $coll_path)) {
                    // delete all items of moved (sub-)tree
                    $sql =
                        "DELETE FROM ut_lp_collections" . PHP_EOL
                        . "WHERE obj_id = " . $ilDB->quote($row["obj_id"], "integer") . PHP_EOL
                        . "AND " . $ilDB->in("item_id", $ref_ids, false, "integer") . PHP_EOL
                    ;
                    $ilDB->manipulate($sql);

                    ilLPStatusWrapper::_refreshStatus($row["obj_id"]);
                }
            }
        }
    }

    final public function handleToTrash(): void
    {
        $this->updateParentCollections();
    }

    final public function handleDelete(): void
    {
        ilLPMarks::deleteObject($this->obj_id);

        ilChangeEvent::_delete($this->obj_id);

        $collection = $this->getCollectionInstance();
        if ($collection) {
            $collection->delete();
        }

        $this->updateParentCollections();
    }

    final protected function updateParentCollections(): void
    {
        // update parent collections?
        $sql =
            "SELECT ut_lp_collections.obj_id obj_id" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "JOIN ut_lp_collections ON" . PHP_EOL
            . "(object_reference.obj_id = " . $this->db->quote($this->obj_id, "integer") . PHP_EOL
            . "AND object_reference.ref_id = ut_lp_collections.item_id)" . PHP_EOL
        ;
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($result)) {
            if (in_array(ilObject::_lookupType($row["obj_id"]), array("crs", "grp", "fold"))) {
                // remove from parent collection
                $sql =
                    "DELETE FROM ut_lp_collections" . PHP_EOL
                    . "WHERE obj_id = " . $this->db->quote($row["obj_id"], "integer") . PHP_EOL
                    . "AND item_id = " . $this->db->quote($this->obj_id, "integer") . PHP_EOL
                ;
                $this->db->manipulate($sql);

                ilLPStatusWrapper::_refreshStatus($row["obj_id"]);
            }
        }
    }

    /**
     * Find (lp-relevant) members for given object ids
     */
    protected static function isLPMember(array &$res, int $usr_id, array $obj_ids): bool
    {
        // should be overwritten by object-type-specific class
        return false;
    }

    /**
     * Find (lp-relevant) memberships by path
     */
    protected static function findMembershipsByPath(
        array &$res,
        int $usr_id,
        int $parent_ref_id,
        array $obj_ids,
        bool $mapped_ref_ids = false
    ): array {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $found = array();

        // walk path to find course or group object and check members of that object
        $path = $tree->getPathId($parent_ref_id);
        foreach (array_reverse($path) as $path_ref_id) {
            $type = ilObject::_lookupType($path_ref_id, true);
            if ($type == "crs" || $type == "grp") {
                $class = self::getTypeClass($type);
                $path_ob_id = ilObject::_lookupObjId($path_ref_id);
                $chk = array();
                $class::isLPMember($chk, $usr_id, array($path_ob_id));
                if (!$mapped_ref_ids) {
                    // we found a grp/crs in path of (single) parent - mark all objects
                    foreach ($obj_ids as $obj_id) {
                        $found[] = $obj_id;
                        if ($chk[$path_ob_id] ?? false) {
                            $res[$obj_id] = true;
                        }
                    }
                } else {
                    // all children from current node are "lp-valid"
                    foreach ($obj_ids as $obj_id => $ref_ids) {
                        foreach ($ref_ids as $ref_id) {
                            if ($tree->isGrandChild($path_ref_id, $ref_id)) {
                                $found[$obj_id][] = $ref_id;
                                if ($chk[$path_ob_id]) {
                                    $res[$obj_id] = true;
                                }
                                break;
                            }
                        }
                    }
                }
                break;
            }
        }

        return $found;
    }

    /**
     * Get all objects where given user is member (from LP POV)
     */
    public static function getLPMemberships(
        int $usr_id,
        array $obj_ids,
        ?int $parent_ref_id = null,
        bool $mapped_ref_ids = false
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        // see ilTrQuery::getParticipantsForObject() [single object only]
        // this is optimized for larger number of objects, e.g. list GUIs

        $ref_map = [];
        if ($mapped_ref_ids) {
            $ref_map = $obj_ids;
            $obj_ids = array_keys($obj_ids);
        }

        $res = [];

        // get object types
        $types_map = [];
        $sql =
            "SELECT obj_id, type" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "WHERE " . $ilDB->in("obj_id", $obj_ids, false, "integer") . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($result)) {
            $types_map[$row["type"]][] = $row["obj_id"];
            $res[$row["obj_id"]] = false;
        }

        $find_by_parent = [];
        foreach ($types_map as $type => $type_obj_ids) {
            $class = self::getTypeClass($type);
            if ($class) {
                // lp-supported type?
                if (!$class::isLPMember($res, $usr_id, $type_obj_ids)) {
                    $find_by_parent = array_merge($find_by_parent, $type_obj_ids);
                }
            }
        }

        if (sizeof($find_by_parent)) {
            // single parent for all objects (repository/ilObjectListGUI)
            if ($parent_ref_id) {
                if (self::findMembershipsByPath($res, $usr_id, $parent_ref_id, $find_by_parent)) {
                    // we found a crs/grp in path, so no need to check read_events
                    $find_by_parent = null;
                }
            }
            // different parents (PD > LP)
            elseif (is_array($ref_map) && count($ref_map) > 0) {
                foreach ($find_by_parent as $obj_id) {
                    // maybe already found by path search from other object/reference
                    if ($res[$obj_id] === false) {
                        if (isset($ref_map[$obj_id]) && is_array($ref_map[$obj_id])) {
                            // check all references
                            foreach ($ref_map[$obj_id] as $ref_id) {
                                $parent_ref_id = $tree->getParentId($ref_id);
                                if ($parent_ref_id == ROOT_FOLDER_ID) {
                                    continue;
                                }

                                // we are checking the complete ref_map
                                // to find all relevant objects in subtree of current ref_id
                                $found = self::findMembershipsByPath($res, $usr_id, $parent_ref_id, $ref_map, true);
                                if (is_array($found) && count($found) > 0) {
                                    // if any references were found in a crs/grp-subtree
                                    // remove from "read-event"-last-resort-pool
                                    foreach ($found as $found_obj_id => $found_ref_ids) {
                                        $diff = array_diff($ref_map[$found_obj_id], $found_ref_ids);
                                        if ($diff) {
                                            // 1-n refs are in another subtree
                                            // have to be checked separately
                                            $ref_map[$found_obj_id] = $diff;
                                        } else {
                                            // all references found in subtree
                                            // no need to check again
                                            unset($ref_map[$found_obj_id]);
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }

                $find_by_parent = array_keys($ref_map);
            }

            // last resort: use read_event?
            if (is_array($find_by_parent) && count($find_by_parent) > 0) {
                $sql =
                    "SELECT obj_id" . PHP_EOL
                    . "FROM read_event" . PHP_EOL
                    . "WHERE " . $ilDB->in("obj_id", $find_by_parent, false, "integer") . PHP_EOL
                    . "AND usr_id = " . $ilDB->quote($usr_id, "integer") . PHP_EOL
                ;
                $result = $ilDB->query($sql);
                while ($row = $ilDB->fetchAssoc($result)) {
                    $res[$row["obj_id"]] = true;
                }
            }
        }

        return $res;
    }

    public function getMailTemplateId(): string
    {
        return '';
    }


    //
    // type-specific support of features (should be enhanced)
    //

    public static function supportsSpentSeconds(string $obj_type): bool
    {
        return !in_array($obj_type, ["exc", "file", "mcst", "mob", "htlm", "copa", 'cmix', 'lti', 'frm']);
    }

    public static function supportsMark(string $obj_type): bool
    {
        return !in_array($obj_type, ["lm", "dbk"]);
    }

    public static function supportsMatrixView(string $obj_type): bool
    {
        $types = ['svy', 'tst', 'htlm', 'exc', 'sess', 'file', 'frm', 'prg', 'copa', 'cmix', 'lti','crsr'];
        return !in_array($obj_type, $types);
    }

    /**
     * Get available type-specific default modes (no administration needed)
     * @return int[]
     */
    public static function getDefaultModes(bool $lp_active): array
    {
        return array(ilLPObjSettings::LP_MODE_UNDEFINED);
    }

    protected static function getTypeDefaultFromDB(string $type): ?int
    {
        global $DIC;
        $ilDB = $DIC->database();

        if (is_null(self::$type_defaults)) {
            self::$type_defaults = [];
            $result = $ilDB->query("SELECT type_id, lp_mode FROM ut_lp_defaults");
            while ($row = $ilDB->fetchAssoc($result)) {
                self::$type_defaults[(string) $row["type_id"]] = (int) $row["lp_mode"];
            }
        }
        return self::$type_defaults[$type] ?? null;
    }

    public static function saveTypeDefaults(array $data): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM ut_lp_defaults");
        foreach ($data as $type => $mode) {
            $ilDB->insert("ut_lp_defaults", [
                "type_id" => ["text", $type],
                "lp_mode" => ["integer", $mode]
            ]);
        }
    }

    public static function getTypeDefault(string $type): int
    {
        $db = self::getTypeDefaultFromDB($type);
        if ($db !== null) {
            return $db;
        }

        $class = self::getTypeClass($type);
        $olp = new $class(0);
        return $olp->getDefaultMode();
    }

    public function hasIndividualModeOptions(): bool
    {
        return false;
    }

    public function initInvidualModeOptions(ilRadioGroupInputGUI $modeRadio): void
    {
    }

    public function shouldFetchIndividualModeFromFormSubmission(): bool
    {
        return false;
    }

    public function fetchIndividualModeFromFormSubmission(ilPropertyFormGUI $form): int
    {
        return 0;
    }

    public function appendModeConfiguration(int $mode, ilRadioOption $modeElement): void
    {
    }

    public function saveModeConfiguration(ilPropertyFormGUI $form, bool &$modeChanged): void
    {
    }
}
