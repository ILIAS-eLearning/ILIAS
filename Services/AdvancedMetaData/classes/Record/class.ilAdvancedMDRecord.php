<?php

declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @defgroup ServicesAdvancedMetaData Services/AdvancedMetaData
 * @author   Stefan Meyer <meyer@leifos.com>
 * @ingroup  ServicesAdvancedMetaData
 */
class ilAdvancedMDRecord
{
    private static $instances = [];

    protected int $record_id;
    protected int $global_position = 0;

    protected string $import_id = '';
    protected bool $active = false;
    protected string $title = '';
    protected string $description = '';
    protected string $language_default = '';

    /**
     * @var array<int, array{obj_type: string, sub_type: string, optional: bool}>
     */
    protected array $obj_types = array();
    protected int $parent_obj = 0;
    protected bool $scope_enabled = false;
    /**
     * @var ilAdvancedMDRecordScope[]
     */
    protected array $scopes = [];

    protected ilDBInterface $db;

    /**
     * Singleton constructor
     * To create an array of new records (without saving them)
     * call the constructor directly. Otherwise call getInstance...
     * @access public
     * @param int record id
     */
    public function __construct(int $a_record_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->record_id = $a_record_id;

        if ($this->getRecordId()) {
            $this->read();
        }
    }

    public static function _getInstanceByRecordId(int $a_record_id): ilAdvancedMDRecord
    {
        if (isset(self::$instances[$a_record_id])) {
            return self::$instances[$a_record_id];
        }
        return self::$instances[$a_record_id] = new ilAdvancedMDRecord($a_record_id);
    }

    /**
     * Get active searchable records
     * @return ilAdvancedMDRecord[]
     */
    public static function _getActiveSearchableRecords(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT DISTINCT(amr.record_id) FROM adv_md_record amr " .
            "JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id " .
            "WHERE searchable = 1 AND active = 1 ";

        $res = $ilDB->query($query);
        $records = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $records[] = self::_getInstanceByRecordId((int) $row->record_id);
        }
        return $records;
    }

    public static function _lookupTitle(int $a_record_id): string
    {
        static $title_cache = array();

        if (isset($title_cache[$a_record_id])) {
            return $title_cache[$a_record_id];
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT title FROM adv_md_record " .
            "WHERE record_id = " . $ilDB->quote($a_record_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        return $title_cache[$a_record_id] = (string) $row->title;
    }

    public static function _lookupRecordIdByImportId(string $a_ilias_id): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT record_id FROM adv_md_record " .
            "WHERE import_id = " . $ilDB->quote($a_ilias_id, 'text') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->record_id;
        }
        return 0;
    }

    /**
     * Get assignable object type
     * @access public
     * @static
     */
    public static function _getAssignableObjectTypes(bool $a_include_text = false): array
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $lng = $DIC['lng'];

        $types = array();
        $filter = array();
        $amet_types = $objDefinition->getAdvancedMetaDataTypes();

        if (!ilECSSetting::ecsConfigured()) {
            $filter = array_merge($filter, ilECSUtils::getPossibleRemoteTypes(false));
            $filter[] = 'rtst';
        }

        foreach ($amet_types as $at) {
            if (in_array($at["obj_type"], $filter)) {
                continue;
            }

            if ($a_include_text) {
                $text = $lng->txt("obj_" . $at["obj_type"]);
                if ($at["sub_type"] != "") {
                    $lng->loadLanguageModule($at["obj_type"]);
                    $text .= ": " . $lng->txt($at["obj_type"] . "_" . $at["sub_type"]);
                } else {
                    $at["sub_type"] = "-";
                }
                $at["text"] = $text;
            }

            $types[] = $at;
        }

        sort($types);
        return $types;
    }

    /**
     * get activated obj types
     * @return string[]
     */
    public static function _getActivatedObjTypes(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT DISTINCT(obj_type) FROM adv_md_record_objs amo " .
            "JOIN adv_md_record amr ON amo.record_id = amr.record_id " .
            "WHERE active = 1 ";
        $res = $ilDB->query($query);
        $obj_types = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_types[] = (string) $row->obj_type;
        }
        return $obj_types;
    }

    /**
     * Get records
     * @access public
     * @static
     * @param array array of record objects
     * @return ilAdvancedMDRecord[]
     */
    public static function _getRecords(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT record_id FROM adv_md_record ORDER BY gpos ";
        $res = $ilDB->query($query);
        $records = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $records[] = ilAdvancedMDRecord::_getInstanceByRecordId((int) $row->record_id);
        }
        return $records;
    }

    /**
     * Get records by obj_type
     * Note: this returns only records with no sub types!
     * @return array<string, array<int, ilAdvancedMDRecord>
     */
    public static function _getAllRecordsByObjectType(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $records = [];
        $query = "SELECT * FROM adv_md_record_objs WHERE sub_type=" . $ilDB->quote("-", "text");
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $records[(string) $row->obj_type][] = self::_getInstanceByRecordId((int) $row->record_id);
        }
        // #13359 hide ecs if not configured
        if (!ilECSSetting::ecsConfigured()) {
            $filter = ilECSUtils::getPossibleRemoteTypes(false);
            $filter[] = 'rtst';
            $records = array_diff_key($records, array_flip($filter));
        }

        return $records;
    }

    /**
     * Get activated records by object type
     * @return ilAdvancedMDRecord[]
     */
    public static function _getActivatedRecordsByObjectType(
        string $a_obj_type,
        string $a_sub_type = "",
        bool $a_only_optional = false
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_sub_type == "") {
            $a_sub_type = "-";
        }

        $records = [];
        $query = "SELECT amro.record_id record_id FROM adv_md_record_objs amro " .
            "JOIN adv_md_record amr ON amr.record_id = amro.record_id " .
            "WHERE active = 1 " .
            "AND obj_type = " . $ilDB->quote($a_obj_type, 'text') . " " .
            "AND sub_type = " . $ilDB->quote($a_sub_type, 'text');

        if ($a_only_optional) {
            $query .= " AND optional =" . $ilDB->quote(1, 'integer');
        }

        // #16428
        $query .= "ORDER by parent_obj DESC, record_id";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $records[] = self::_getInstanceByRecordId((int) $row->record_id);
        }
        return $records;
    }

    /**
     * @param string $a_obj_type
     * @param int    $a_id
     * @param string $a_sub_type
     * @param bool   $is_ref_id
     * @return array<int, ilAdvancedMDRecord>
     */
    public static function _getSelectedRecordsByObject(
        string $a_obj_type,
        int $a_id,
        string $a_sub_type = "",
        bool $is_ref_id = true
    ): array {
        $records = array();

        if ($a_sub_type == "") {
            $a_sub_type = "-";
        }

        $a_obj_id = $is_ref_id
            ? ilObject::_lookupObjId($a_id)
            : $a_id;

        // object-wide metadata configuration setting
        $config_setting = ilContainer::_lookupContainerSetting(
            $a_obj_id,
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ''
        );

        $optional = array();
        foreach (self::_getActivatedRecordsByObjectType($a_obj_type, $a_sub_type) as $record) {
            // check scope
            if ($is_ref_id && self::isFilteredByScope($a_id, $record->getScopes())) {
                continue;
            }
            foreach ($record->getAssignedObjectTypes() as $item) {
                if ($record->getParentObject()) {
                    // only matching local records
                    if ($record->getParentObject() != $a_obj_id) {
                        continue;
                    } // if object-wide setting is off, ignore local records
                    elseif (!$config_setting) {
                        continue;
                    }
                }

                if ($item['obj_type'] == $a_obj_type &&
                    $item['sub_type'] == $a_sub_type) {
                    if ($item['optional']) {
                        $optional[] = $record->getRecordId();
                    }
                    $records[$record->getRecordId()] = $record;
                }
            }
        }

        if ($optional) {
            if (!$config_setting && !in_array($a_sub_type, array("orgu_type", "prg_type"))) { //#16925 + #17777
                $selected = array();
            } else {
                $selected = self::getObjRecSelection($a_obj_id, $a_sub_type);
            }
            foreach ($optional as $record_id) {
                if (!in_array($record_id, $selected)) {
                    unset($records[$record_id]);
                }
            }
        }

        $orderings = new ilAdvancedMDRecordObjectOrderings();
        $records = $orderings->sortRecords($records, $a_obj_id);

        return $records;
    }

    /**
     * Check if a given ref id is not filtered by scope restriction.
     * @param int                       $a_ref_id
     * @param ilAdvancedMDRecordScope[] $scopes
     */
    public static function isFilteredByScope($a_ref_id, array $scopes): bool
    {
        $tree = $GLOBALS['DIC']->repositoryTree();
        $logger = $GLOBALS['DIC']->logger()->amet();

        if (!count($scopes)) {
            return false;
        }
        foreach ($scopes as $scope) {
            $logger->debug('Comparing: ' . $a_ref_id . ' with: ' . $scope->getRefId());
            if ($scope->getRefId() == $a_ref_id) {
                $logger->debug('Elements are equal. No scope restrictions.');
                return false;
            }
            if ($tree->getRelation($scope->getRefId(), $a_ref_id) == ilTree::RELATION_PARENT) {
                $logger->debug('Node is child node. No scope restrictions.');
                return false;
            }
        }
        $logger->info('Scope filter matches.');

        return true;
    }

    public static function _delete($a_record_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Delete fields
        foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($a_record_id) as $field) {
            $field->delete();
        }

        $query = "DELETE FROM adv_md_record " .
            "WHERE record_id = " . $ilDB->quote($a_record_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $query = "DELETE FROM adv_md_record_objs " .
            "WHERE record_id = " . $ilDB->quote($a_record_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    protected function setRecordId(int $record_id): void
    {
        $this->record_id = $record_id;
    }

    /**
     * @param string $language_code
     */
    public function setDefaultLanguage(string $language_code): void
    {
        $this->language_default = $language_code;
    }

    public function getDefaultLanguage(): string
    {
        return $this->language_default;
    }

    public function delete(): void
    {
        ilAdvancedMDRecord::_delete($this->getRecordId());
        ilAdvancedMDRecordScope::deleteByRecordId($this->getRecordId());
    }

    public function enabledScope(): bool
    {
        return $this->scope_enabled;
    }

    public function enableScope(bool $a_stat): void
    {
        $this->scope_enabled = $a_stat;
    }

    /**
     * @param ilAdvancedMDRecordScope[]
     */
    public function setScopes(array $a_scopes): void
    {
        $this->scopes = $a_scopes;
    }

    /**
     * Get scopes
     * @return ilAdvancedMDRecordScope[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @return int[]
     */
    public function getScopeRefIds(): array
    {
        $ref_ids = [];
        foreach ($this->scopes as $scope) {
            $ref_ids[] = $scope->getRefId();
        }
        return $ref_ids;
    }

    public function save(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Save import id if given
        $next_id = $ilDB->nextId('adv_md_record');

        $query = "INSERT INTO adv_md_record (record_id,import_id,active,title,description,parent_obj,lang_default) " .
            "VALUES(" .
            $ilDB->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getImportId(), 'text') . ", " .
            $this->db->quote($this->isActive(), 'integer') . ", " .
            $this->db->quote($this->getTitle(), 'text') . ", " .
            $this->db->quote($this->getDescription(), 'text') . ", " .
            $this->db->quote($this->getParentObject(), 'integer') . ", " .
            $this->db->quote($this->getDefaultLanguage(), ilDBConstants::T_TEXT) .
            ")";
        $res = $ilDB->manipulate($query);
        $this->record_id = $next_id;

        if (!strlen($this->getImportId())) {
            // set import id to default value
            $query = "UPDATE adv_md_record " .
                "SET import_id = " . $this->db->quote($this->generateImportId(), 'text') . " " .
                "WHERE record_id = " . $this->db->quote($this->record_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        }

        foreach ($this->getAssignedObjectTypes() as $type) {
            global $DIC;

            $ilDB = $DIC['ilDB'];
            $query = "INSERT INTO adv_md_record_objs (record_id,obj_type,sub_type,optional) " .
                "VALUES( " .
                $this->db->quote($this->getRecordId(), 'integer') . ", " .
                $this->db->quote($type["obj_type"], 'text') . ", " .
                $this->db->quote($type["sub_type"], 'text') . ", " .
                $this->db->quote($type["optional"], 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }

        foreach ($this->getScopes() as $scope) {
            $scope->setRecordId($this->getRecordId());
            $scope->save();
        }
    }

    public function update(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE adv_md_record " .
            "SET active = " . $this->db->quote($this->isActive(), 'integer') . ", " .
            "title = " . $this->db->quote($this->getTitle(), 'text') . ", " .
            "description = " . $this->db->quote($this->getDescription(), 'text') . ", " .
            'gpos = ' . $this->db->quote($this->getGlobalPosition(), 'integer') . ', ' .
            'lang_default = ' . $this->db->quote($this->getDefaultLanguage(), ilDBConstants::T_TEXT) . ' ' .
            "WHERE record_id = " . $this->db->quote($this->getRecordId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // Delete assignments
        $query = "DELETE FROM adv_md_record_objs " .
            "WHERE record_id = " . $this->db->quote($this->getRecordId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // Insert assignments
        foreach ($this->getAssignedObjectTypes() as $type) {
            $query = "INSERT INTO adv_md_record_objs (record_id,obj_type,sub_type,optional) " .
                "VALUES ( " .
                $this->db->quote($this->getRecordId(), 'integer') . ", " .
                $this->db->quote($type["obj_type"], 'text') . ", " .
                $this->db->quote($type["sub_type"], 'text') . ", " .
                $this->db->quote($type["optional"], 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }
        ilAdvancedMDRecordScope::deleteByRecordId($this->getRecordId());
        foreach ($this->getScopes() as $scope) {
            $scope->setRecordId($this->getRecordId());
            $scope->save();
        }
    }

    public function validate(): bool
    {
        if (!strlen($this->getTitle())) {
            return false;
        }
        return true;
    }

    public function setGlobalPosition(int $position): void
    {
        $this->global_position = $position;
    }

    public function getGlobalPosition(): int
    {
        return $this->global_position;
    }

    public function getRecordId(): int
    {
        return $this->record_id;
    }

    public function setActive(bool $a_active): void
    {
        $this->active = $a_active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setImportId(string $a_id_string): void
    {
        $this->import_id = $a_id_string;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }

    /**
     * @param string[]
     * @todo is this format of $a_obj_types correct?
     */
    public function setAssignedObjectTypes(array $a_obj_types): void
    {
        $this->obj_types = $a_obj_types;
    }

    public function appendAssignedObjectType(string $a_obj_type, string $a_sub_type, bool $a_optional = false): void
    {
        $this->obj_types[] = array(
            "obj_type" => $a_obj_type,
            "sub_type" => $a_sub_type,
            "optional" => $a_optional
        );
    }

    /**
     * @return array<int, array{obj_type: string, sub_type: string, optional: bool}>
     */
    public function getAssignedObjectTypes(): array
    {
        return $this->obj_types;
    }

    public function isAssignedObjectType(string $a_obj_type, string $a_sub_type): bool
    {
        foreach ($this->getAssignedObjectTypes() as $t) {
            if ($t["obj_type"] == $a_obj_type &&
                $t["sub_type"] == $a_sub_type) {
                return true;
            }
        }
        return false;
    }

    public function setParentObject(int $a_obj_id): void
    {
        $this->parent_obj = $a_obj_id;
    }

    public function getParentObject(): int
    {
        return $this->parent_obj;
    }

    /**
     * To Xml.
     * This method writes only the subset Record (including all fields)
     * Use class.ilAdvancedMDRecordXMLWriter to generate a complete xml presentation.
     */
    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('Record', array('active' => $this->isActive() ? 1 : 0,
                                             'id' => $this->generateImportId()
        ));
        $writer->xmlElement('Title', null, $this->getTitle());
        $writer->xmlElement('Description', null, $this->getDescription());

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->getRecordId());
        $translations->toXML($writer);

        foreach ($this->getAssignedObjectTypes() as $obj_type) {
            $optional = array("optional" => $obj_type["optional"]);
            if ($obj_type["sub_type"] == "") {
                $writer->xmlElement('ObjectType', $optional, $obj_type["obj_type"]);
            } else {
                $writer->xmlElement('ObjectType', $optional, $obj_type["obj_type"] . ":" . $obj_type["sub_type"]);
            }
        }

        // scopes
        if (count($this->getScopeRefIds())) {
            $writer->xmlStartTag('Scope');
        }
        foreach ($this->getScopeRefIds() as $ref_id) {
            $type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            $writer->xmlElement('ScopeEntry', ['id' => 'il_' . IL_INST_ID . '_' . $type . '_' . $ref_id]);
        }
        if (count($this->getScopeRefIds())) {
            $writer->xmlEndTag('Scope');
        }

        foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->getRecordId()) as $definition) {
            $definition->toXML($writer);
        }
        $writer->xmlEndTag('Record');
    }

    private function read(): void
    {
        $query = "SELECT * FROM adv_md_record " .
            "WHERE record_id = " . $this->db->quote($this->getRecordId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setImportId((string) $row->import_id);
            $this->setActive((bool) $row->active);
            $this->setTitle((string) $row->title);
            $this->setDescription((string) $row->description);
            $this->setParentObject((int) $row->parent_obj);
            $this->setGlobalPosition((int) $row->gpos);
            $this->setDefaultLanguage((string) $row->lang_default);
        }
        $query = "SELECT * FROM adv_md_record_objs " .
            "WHERE record_id = " . $this->db->quote($this->getRecordId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->obj_types[] = array(
                "obj_type" => (string) $row->obj_type,
                "sub_type" => (string) $row->sub_type,
                "optional" => (bool) $row->optional
            );
        }

        $query = 'SELECT scope_id FROM adv_md_record_scope ' .
            'WHERE record_id = ' . $this->db->quote($this->record_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $this->scope_enabled = false;
        $this->scopes = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->scope_enabled = true;
            $this->scopes[] = new ilAdvancedMDRecordScope((int) $row->scope_id);
        }
    }

    /**
     * generate unique record id
     */
    protected function generateImportId(): string
    {
        return 'il_' . IL_INST_ID . '_adv_md_record_' . $this->getRecordId();
    }

    public function __destruct()
    {
        unset(self::$instances[$this->getRecordId()]);
    }

    /**
     * Save repository object record selection
     * @param int    $a_obj_id        object id if repository object
     * @param string $a_sub_type      subtype
     * @param int[]  $a_records       array of record ids that are selected (in use) by the object
     * @param bool   $a_delete_before delete before update
     * @return void
     */
    public static function saveObjRecSelection(
        int $a_obj_id,
        string $a_sub_type = "",
        array $a_records = null,
        bool $a_delete_before = true
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_sub_type == "") {
            $a_sub_type = "-";
        }

        if ($a_delete_before) {
            $ilDB->manipulate("DELETE FROM adv_md_obj_rec_select WHERE " .
                " obj_id = " . $ilDB->quote($a_obj_id, "integer") .
                " AND sub_type = " . $ilDB->quote($a_sub_type, "text"));
        }

        if (is_array($a_records)) {
            foreach ($a_records as $r) {
                if ($r > 0) {
                    $ilDB->manipulate("INSERT INTO adv_md_obj_rec_select " .
                        "(obj_id, rec_id, sub_type) VALUES (" .
                        $ilDB->quote($a_obj_id, "integer") . "," .
                        $ilDB->quote($r, "integer") . "," .
                        $ilDB->quote($a_sub_type, "text") .
                        ")");
                }
            }
        }
    }

    /**
     * Delete repository object record selection
     */
    public static function deleteObjRecSelection(int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate("DELETE FROM adv_md_obj_rec_select WHERE " .
            " obj_id = " . $ilDB->quote($a_obj_id, "integer"));
    }

    /**
     * Get repository object record selection
     * @param int   $a_obj_id  object id if repository object
     * @param array $a_records array of record ids that are selected (in use) by the object
     * @return int[]
     */
    public static function getObjRecSelection(int $a_obj_id, string $a_sub_type = ""): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_sub_type == "") {
            $a_sub_type = "-";
        }

        $recs = array();
        $set = $ilDB->query(
            $r = "SELECT * FROM adv_md_obj_rec_select " .
                " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
                " AND sub_type = " . $ilDB->quote($a_sub_type, "text")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $recs[] = (int) $rec["rec_id"];
        }
        return $recs;
    }

    public function _clone(array &$a_fields_map, int $a_parent_obj_id = null): ilAdvancedMDRecord
    {
        $new_obj = new self();
        $new_obj->setActive($this->isActive());
        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setParentObject($a_parent_obj_id
            ?: $this->getParentObject());
        $new_obj->setAssignedObjectTypes($this->getAssignedObjectTypes());
        $new_obj->setDefaultLanguage($this->getDefaultLanguage());
        $new_obj->save();

        foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->getRecordId()) as $definition) {
            $new_def = $definition->_clone($new_obj->getRecordId());
            $a_fields_map[$definition->getFieldId()] = $new_def->getFieldId();
        }

        $record_translation = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->getRecordId());
        $record_translation->cloneRecord($new_obj->getRecordId());

        return $new_obj;
    }

    public static function getSharedRecords(int $a_obj1_id, int $a_obj2_id, string $a_sub_type = "-"): array
    {
        $obj_type = ilObject::_lookupType($a_obj1_id);
        $sel = array_intersect(
            ilAdvancedMDRecord::getObjRecSelection($a_obj1_id, $a_sub_type),
            ilAdvancedMDRecord::getObjRecSelection($a_obj2_id, $a_sub_type)
        );

        $res = array();

        foreach (self::_getRecords() as $record) {
            // local records cannot be shared
            if ($record->getParentObject()) {
                continue;
            }

            // :TODO: inactive records can be ignored?
            if (!$record->isActive()) {
                continue;
            }

            // parse assigned types
            foreach ($record->getAssignedObjectTypes() as $item) {
                if ($item["obj_type"] == $obj_type &&
                    $item["sub_type"] == $a_sub_type) {
                    // mandatory
                    if (!$item["optional"]) {
                        $res[] = $record->getRecordId();
                    } // optional
                    elseif (in_array($record->getRecordId(), $sel)) {
                        $res[] = $record->getRecordId();
                    }
                }
            }
        }

        return $res;
    }
}
