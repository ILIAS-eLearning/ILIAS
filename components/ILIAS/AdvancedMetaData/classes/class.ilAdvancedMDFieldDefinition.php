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

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;
use ILIAS\AdvancedMetaData\Repository\FieldDefinition\GenericData\Gateway as DBGateway;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericDataImplementation;
use ILIAS\AdvancedMetaData\Repository\FieldDefinition\GenericData\DatabaseGatewayImplementation;

/**
 * AMD field abstract base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
abstract class ilAdvancedMDFieldDefinition
{
    /**
     * TODO: put this in when minimum php version is set to 8.2
     */
    /*public const TYPE_SELECT = Type::SELECT->value;
    public const TYPE_TEXT = Type::TEXT->value;
    public const TYPE_DATE = Type::DATE->value;
    public const TYPE_DATETIME = Type::DATETIME->value;
    public const TYPE_INTEGER = Type::INTEGER->value;
    public const TYPE_FLOAT = Type::FLOAT->value;
    public const TYPE_LOCATION = Type::LOCATION->value;
    public const TYPE_SELECT_MULTI = Type::SELECT_MULTI->value;
    public const TYPE_ADDRESS = Type::ADDRESS->value;
    public const TYPE_EXTERNAL_LINK = Type::EXTERNAL_LINK->value;
    public const TYPE_INTERNAL_LINK = Type::INTERNAL_LINK->value;*/

    public const TYPE_SELECT = 1;
    public const TYPE_TEXT = 2;
    public const TYPE_DATE = 3;
    public const TYPE_DATETIME = 4;
    public const TYPE_INTEGER = 5;
    public const TYPE_FLOAT = 6;
    public const TYPE_LOCATION = 7;
    public const TYPE_SELECT_MULTI = 8;
    public const TYPE_ADDRESS = 99;
    public const TYPE_EXTERNAL_LINK = 9;
    public const TYPE_INTERNAL_LINK = 10;

    protected GenericData $generic_data;

    protected ?ilADTDefinition $adt_def = null;
    protected ?ilADT $adt = null;

    protected string $language = '';

    protected ilDBInterface $db;
    private DBGateway $db_gateway;
    protected ilLanguage $lng;
    protected ilLogger $logger;
    protected GlobalHttpState $http;
    protected RefineryFactory $refinery;

    public function __construct(GenericData $generic_data, string $language = '')
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->language = $DIC->language()->getLangKey();
        $this->refinery = $DIC->refinery();
        $this->http = $DIC->http();


        if ($language) {
            $this->language = $language;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->amet();
        $this->db = $DIC->database();
        $this->db_gateway = new DatabaseGatewayImplementation($this->db);

        $this->generic_data = $generic_data;
        if (!empty($generic_data->getFieldValues())) {
            $this->importFieldDefinition($generic_data->getFieldValues());
        }
    }

    public static function getInstance(
        ?int $a_field_id,
        ?int $a_type = null,
        string $language = ''
    ): ilAdvancedMDFieldDefinition {
        global $DIC;

        $db_gateway = new DatabaseGatewayImplementation($DIC->database());

        if (self::isValidType((int) $a_type)) {
            /**
             * TODO: In the future, this should probably be supplied by the repo.
             */
            $generic_data = new GenericDataImplementation(
                Type::from($a_type),
                0,
                '',
                '',
                '',
                0,
                false,
                false,
                []
            );
        }

        if ($a_field_id) {
            $generic_data = $db_gateway->readByID($a_field_id);
        }

        if (isset($generic_data)) {
            return self::getInstanceWithData($generic_data);
        }
        throw new ilException("unknown type " . $a_type);
    }

    protected static function getInstanceWithData(
        GenericData $generic_data,
        string $language = ''
    ): ilAdvancedMDFieldDefinition {
        $class = "ilAdvancedMDFieldDefinition" . $generic_data->type()->stringValue();
        return new $class($generic_data, $language);
    }

    public static function exists(int $a_field_id): bool
    {
        global $DIC;

        $db = $DIC['ilDB'];
        $set = $db->query("SELECT field_type" .
            " FROM adv_mdf_definition" .
            " WHERE field_id = " . $db->quote($a_field_id, "integer"));
        if ($db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Get instance by type string (used by import)
     */
    public static function getInstanceByTypeString(string $a_type): ?ilAdvancedMDFieldDefinition
    {
        // see self::getTypeString()
        $map = array(
            self::TYPE_TEXT => "Text",
            self::TYPE_SELECT => "Select",
            self::TYPE_DATE => "Date",
            self::TYPE_DATETIME => "DateTime",
            self::TYPE_FLOAT => "Float",
            self::TYPE_LOCATION => "Location",
            self::TYPE_INTEGER => "Integer",
            self::TYPE_SELECT_MULTI => "SelectMulti",
            self::TYPE_EXTERNAL_LINK => 'ExternalLink',
            self::TYPE_INTERNAL_LINK => 'InternalLink',
            self::TYPE_ADDRESS => "Address"
        );
        $map = array_flip($map);
        if (array_key_exists($a_type, $map)) {
            return self::getInstance(null, $map[$a_type]);
        }
        return null;
    }

    /**
     * Get definitions by record id
     * @param int    $a_record_id
     * @param bool   $a_only_searchable
     * @param string $language
     * @return array<int, ilAdvancedMDFieldDefinition>
     */
    public static function getInstancesByRecordId(
        $a_record_id,
        $a_only_searchable = false,
        string $language = ''
    ): array {
        global $DIC;

        $db_gateway = new DatabaseGatewayImplementation($DIC->database());

        $defs = [];
        foreach ($db_gateway->readByRecords($a_only_searchable, $a_record_id) as $data) {
            $defs[$data->id()] = self::getInstanceWithData($data);
        }
        return $defs;
    }

    /**
     * @param string $a_obj_type
     * @param bool   $a_active_only
     * @return array<int, ilAdvancedMDFieldDefinition>
     */
    public static function getInstancesByObjType($a_obj_type, $a_active_only = true): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $db_gateway = new DatabaseGatewayImplementation($ilDB);

        $query = "SELECT amf.* FROM adv_md_record_objs aro" .
            " JOIN adv_md_record amr ON aro.record_id = amr.record_id" .
            " JOIN adv_mdf_definition amf ON aro.record_id = amf.record_id" .
            " WHERE obj_type = " . $ilDB->quote($a_obj_type, 'text');
        if ($a_active_only) {
            $query .= " AND active = " . $ilDB->quote(1, "integer");
        }
        $query .= " ORDER BY aro.record_id,position";

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $ids[] = (int) $row["field_id"];
        }
        $data = $db_gateway->readByIDs(...$ids);

        $defs = [];
        foreach ($data as $datum) {
            $defs[$datum->id()] = self::getInstanceWithData($datum);
        }

        return $defs;
    }

    public static function getInstanceByImportId(string $a_import_id): ?ilAdvancedMDFieldDefinition
    {
        global $DIC;

        $db_gateway = new DatabaseGatewayImplementation($DIC->database());

        if (is_null($data = $db_gateway->readByImportID($a_import_id))) {
            return null;
        }
        return self::getInstanceWithData($data);
    }

    /**
     * Get searchable definition ids (performance is key)
     * @return int[]
     */
    public static function getSearchableDefinitionIds()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $field_ids = array();

        $query = "SELECT field_id FROM adv_md_record amr" .
            " JOIN adv_mdf_definition amfd ON (amr.record_id = amfd.record_id)" .
            " WHERE active = " . $ilDB->quote(1, "integer") .
            " AND searchable = " . $ilDB->quote(1, "integer");
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            $field_ids[] = (int) $row["field_id"];
        }
        return $field_ids;
    }

    /**
     * Init ADTGroup for definitions
     * @param array<int, ilADTDefinition>
     * @return ilADTGroup
     * @todo check return type array<string, ilADTDefinition> or array<string, ilADTDefinition>
     */
    public static function getADTGroupForDefinitions(array $a_defs): ilADT
    {
        $factory = ilADTFactory::getInstance();
        $group_def = $factory->getDefinitionInstanceByType("Group");
        foreach ($a_defs as $def) {
            $group_def->addElement($def->getFieldId(), $def->getADTDefinition());
        }
        $group = $factory->getInstanceByDefinition($group_def);

        // bind adt instances to definition
        foreach ($group->getElements() as $element_id => $element) {
            $a_defs[$element_id]->setADT($element);
        }
        return $group;
    }

    /**
     * Get all valid types
     * @return int[]
     */
    public static function getValidTypes(): array
    {
        $types = Type::cases();
        $values = [];
        foreach ($types as $type) {
            $values[] = $type->value;
        }
        return $values;
    }

    public static function isValidType(int $a_type): bool
    {
        return !is_null(Type::tryFrom($a_type));
    }

    /**
     * Get type
     */
    abstract public function getType(): int;

    /**
     * Get type as string
     */
    protected static function getTypeString(int $a_type): string
    {
        return (string) (Type::tryFrom($a_type)?->stringValue());
    }

    /**
     * Check if default language mode has to be used: no language given or language equals default language
     */
    public function useDefaultLanguageMode(string $language): bool
    {
        if (!strlen($language)) {
            return true;
        }
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordID());
        return strcmp($record->getDefaultLanguage(), $language) === 0;
    }

    /**
     * @todo check udf usage
     */
    public function getTypeTitle(): string
    {
        return "udf_type_" . strtolower(self::getTypeString($this->getType()));
    }

    /**
     * Init adt instance
     */
    abstract protected function initADTDefinition(): ilADTDefinition;

    /**
     * Get ADT definition instance
     * @return ilADTDefinition
     */
    public function getADTDefinition(): ilADTDefinition
    {
        if (!$this->adt_def instanceof ilADTDefinition) {
            $this->adt_def = $this->initADTDefinition();
        }
        return $this->adt_def;
    }

    public function getADT(): ilADT
    {
        if (!$this->adt instanceof ilADT) {
            $this->adt = ilADTFactory::getInstance()->getInstanceByDefinition($this->getADTDefinition());
        }
        return $this->adt;
    }

    /**
     * Set ADT instance
     * @see self::getADTGroupForDefinitions()
     */
    protected function setADT(ilADT $a_adt): void
    {
        if (!$this->adt instanceof ilADT) {
            $this->adt = $a_adt;
        }
    }

    /**
     * Get field_id
     */
    public function getFieldId(): ?int
    {
        return $this->generic_data->id();
    }

    /**
     * Set record id
     */
    public function setRecordId(int $a_id): void
    {
        $this->generic_data->setRecordID($a_id);
    }

    /**
     * Get record id
     */
    public function getRecordId(): int
    {
        return $this->generic_data->getRecordID();
    }

    /**
     * Set import id
     */
    public function setImportId(string $a_id_string): void
    {
        $this->generic_data->setImportID(trim($a_id_string));
    }

    /**
     * Get import id
     */
    public function getImportId(): string
    {
        return $this->generic_data->getImportID();
    }

    /**
     * Set position
     */
    public function setPosition(int $a_pos): void
    {
        $this->generic_data->setPosition($a_pos);
    }

    /**
     * Get position
     */
    public function getPosition(): int
    {
        return $this->generic_data->getPosition();
    }

    /**
     * Get title
     */
    public function setTitle(string $a_title): void
    {
        $this->generic_data->setTitle(trim($a_title));
    }

    /**
     * Get title
     */
    public function getTitle(): string
    {
        return $this->generic_data->getTitle();
    }

    /**
     * Set description
     */
    public function setDescription(string $a_desc): void
    {
        $this->generic_data->setDescription(trim($a_desc));
    }

    /**
     * Get description
     */
    public function getDescription(): string
    {
        return $this->generic_data->getDescription();
    }

    /**
     * Is search supported at all
     */
    public function isSearchSupported(): bool
    {
        return true;
    }

    /**
     * Is search by filter supported
     */
    public function isFilterSupported(): bool
    {
        return true;
    }

    /**
     * Toggle searchable
     */
    public function setSearchable(bool $a_status): void
    {
        // see above
        if (!$this->isSearchSupported()) {
            $a_status = false;
        }
        $this->generic_data->setSearchable($a_status);
    }

    /**
     * Is searchable
     */
    public function isSearchable(): bool
    {
        return $this->generic_data->isSearchable();
    }

    /**
     * Toggle required
     */
    public function setRequired(bool $a_status): void
    {
        $this->generic_data->setRequired($a_status);
    }

    /**
     * Is required field
     */
    public function isRequired(): bool
    {
        return $this->generic_data->isRequired();
    }

    /**
     * Import (type-specific) field definition from DB
     */
    protected function importFieldDefinition(array $a_def): void
    {
    }

    /**
     * Get (type-specific) field definition
     */
    protected function getFieldDefinition(): array
    {
        return [];
    }

    /**
     * Parse properties for table gui
     */
    public function getFieldDefinitionForTableGUI(string $content_language): array
    {
        return [];
    }

    /**
     * Add custom input elements to definition form
     */
    protected function addCustomFieldToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled = false,
        string $language = ''
    ): void {
    }

    /**
     * Add input elements to definition form
     */
    public function addToFieldDefinitionForm(
        ilPropertyFormGUI $a_form,
        ilAdvancedMDPermissionHelper $a_permissions,
        string $language = ''
    ): void {
        global $DIC;
        $lng = $DIC['lng'];

        $perm = $a_permissions->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
            (string) $this->getFieldId(),
            array(
                array(ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_FIELD_TITLE
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_FIELD_DESCRIPTION
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_FIELD_PROPERTIES
                )
            )
        );

        // title
        $translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($this->getRecordId());

        $title = new ilTextInputGUI($lng->txt('title'), 'title');
        $title->setValue($this->getTitle());
        $title->setSize(20);
        $title->setMaxLength(70);
        $title->setRequired(true);
        if ($this->getFieldId()) {
            $translations->modifyTranslationInfoForTitle($this->getFieldId(), $a_form, $title, $language);
        } else {
            $title->setValue($this->getTitle());
        }

        $a_form->addItem($title);

        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_FIELD_TITLE]) {
            $title->setDisabled(true);
        }

        // desc
        $desc = new ilTextAreaInputGUI($lng->txt('description'), 'description');
        $desc->setValue($this->getDescription());
        $desc->setRows(3);
        $desc->setCols(50);
        if ($this->getFieldId()) {
            $translations->modifyTranslationInfoForDescription($this->getFieldId(), $a_form, $desc, $language);
        } else {
            $desc->setValue($this->getDescription());
        }

        $a_form->addItem($desc);

        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_FIELD_DESCRIPTION]) {
            $desc->setDisabled(true);
        }

        // searchable
        $check = new ilCheckboxInputGUI($lng->txt('md_adv_searchable'), 'searchable');
        $check->setChecked($this->isSearchable());
        $check->setValue("1");
        $a_form->addItem($check);

        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE] ||
            !$this->isSearchSupported()) {
            $check->setDisabled(true);
        }

        /* required
        $check = new ilCheckboxInputGUI($lng->txt('md_adv_required'), 'required');
        $check->setChecked($this->isRequired());
        $check->setValue(1);
        $a_form->addItem($check);
        */

        $this->addCustomFieldToDefinitionForm(
            $a_form,
            !$perm[ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_FIELD_PROPERTIES],
            $language
        );
    }

    /**
     * Import custom post values from definition form
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = ''): void
    {
        // type-specific
    }

    /**
     * Import post values from definition form
     */
    public function importDefinitionFormPostValues(
        ilPropertyFormGUI $a_form,
        ilAdvancedMDPermissionHelper $a_permissions,
        string $active_language
    ): void {
        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordID());
        $is_translation = (($active_language !== '') && ($active_language != $record->getDefaultLanguage()));
        if (!$a_form->getItemByPostVar("title")->getDisabled() && !$is_translation) {
            $this->setTitle($a_form->getInput("title"));
        }
        if (!$a_form->getItemByPostVar("description")->getDisabled() && !$is_translation) {
            $this->setDescription($a_form->getInput("description"));
        }
        if (!$a_form->getItemByPostVar("searchable")->getDisabled()) {
            $this->setSearchable((bool) $a_form->getInput("searchable"));
        }

        if ($a_permissions->hasPermission(
            ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
            (string) $this->getFieldId(),
            ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
            ilAdvancedMDPermissionHelper::SUBACTION_FIELD_PROPERTIES
        )) {
            $this->importCustomDefinitionFormPostValues($a_form, $active_language);
        }
    }

    public function importDefinitionFormPostValuesNeedsConfirmation(): bool
    {
        return false;
    }

    public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form): void
    {
    }

    public function prepareDefinitionFormConfirmation(ilPropertyFormGUI $a_form): void
    {
        $a_form->getItemByPostVar("title")->setDisabled(true);
        $a_form->getItemByPostVar("description")->setDisabled(true);
        $a_form->getItemByPostVar("searchable")->setDisabled(true);

        // checkboxes have no hidden on disabled
        if ($a_form->getInput("searchable")) {
            $hidden = new ilHiddenInputGUI("searchable");
            $hidden->setValue("1");
            $a_form->addItem($hidden);
        }

        $this->prepareCustomDefinitionFormConfirmation($a_form);
    }

    /**
     * Generate unique record id
     */
    public function generateImportId(int $a_field_id): string
    {
        return 'il_' . IL_INST_ID . '_adv_md_field_' . $a_field_id;
    }

    /**
     * Create new field entry
     */
    public function save(bool $keep_pos_and_import_id = false, bool $keep_import_id = false): void
    {
        if ($this->getFieldId()) {
            $this->update();
            return;
        }

        /*
         * This has to be set before update and creation here, the alternative
         * would be to set this in all children of this class, everywhere
         * where type-specific settings are set.
         */
        $this->generic_data->setFieldValues($this->getFieldDefinition());

        if ($keep_pos_and_import_id) {
            $field_id = $this->db_gateway->create($this->generic_data);
        } elseif ($keep_import_id) {
            $field_id = $this->db_gateway->createWithNewPosition($this->generic_data);
        } else {
            $field_id = $this->db_gateway->createFromScratch($this->generic_data);
        }

        $this->generic_data = $this->db_gateway->readByID($field_id);
    }

    /**
     * Update field entry
     */
    public function update(): void
    {
        if (!$this->getFieldId()) {
            $this->save();
            return;
        }

        /*
         * This has to be set before update and creation here, the alternative
         * would be to set this in all children of this class, everywhere
         * where type-specific settings are set.
         */
        $this->generic_data->setFieldValues($this->getFieldDefinition());

        $this->db_gateway->update($this->generic_data);
    }

    /**
     * Delete field entry
     */
    public function delete(): void
    {
        if (!$this->getFieldId()) {
            return;
        }

        // delete all values
        ilAdvancedMDValues::_deleteByFieldId($this->getFieldId(), $this->getADT());

        $this->db_gateway->delete($this->getFieldId());
    }

    /**
     * To Xml.
     * This method writes only the subset Field
     * Use class.ilAdvancedMDRecordXMLWriter to generate a complete xml presentation.
     */
    public function toXML(ilXmlWriter $a_writer): void
    {
        $a_writer->xmlStartTag('Field', array(
            'id' => $this->getImportId(),
            'searchable' => ($this->isSearchable() ? 'Yes' : 'No'),
            'fieldType' => self::getTypeString($this->getType())
        ));

        $a_writer->xmlElement('FieldTitle', null, $this->getTitle());
        $a_writer->xmlElement('FieldDescription', null, $this->getDescription());

        $translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($this->getRecordId());
        $a_writer->xmlStartTag('FieldTranslations');
        foreach ($translations->getTranslations($this->getFieldId()) as $translation) {
            $a_writer->xmlStartTag('FieldTranslation', ['language' => $translation->getLangKey()]);
            $a_writer->xmlElement(
                'FieldTranslationTitle',
                [],
                $translation->getTitle()
            );
            $a_writer->xmlElement(
                'FieldTranslationDescription',
                [],
                $translation->getDescription()
            );
            $a_writer->xmlEndTag('FieldTranslation');
        }
        $a_writer->xmlEndTag('FieldTranslations');
        $a_writer->xmlElement('FieldPosition', null, $this->getPosition());

        $this->addPropertiesToXML($a_writer);

        $a_writer->xmlEndTag('Field');
    }

    /**
     * Add (type-specific) properties to xml export
     */
    protected function addPropertiesToXML(ilXmlWriter $a_writer): void
    {
        // type-specific properties
    }

    /**
     * Import property from XML
     */
    public function importXMLProperty(string $a_key, string $a_value): void
    {
    }

    /**
     * Parse ADT value for xml (export)
     */
    abstract public function getValueForXML(ilADT $element): string;

    /**
     * Import value from xml
     * @param string $a_cdata
     */
    abstract public function importValueFromXML(string $a_cdata): void;

    /**
     * Import meta data from ECS
     */
    public function importFromECS(string $a_ecs_type, $a_value, string $a_sub_id): bool
    {
        return false;
    }

    /**
     * Prepare editor form elements
     */
    public function prepareElementForEditor(ilADTFormBridge $a_bridge): void
    {
        // type-specific
    }

    /**
     * Get value for search query parser
     * @param ilADTSearchBridge $a_adt_search
     * @return string
     * @todo check if string type is applicable
     */
    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search): string
    {
        return '';
    }

    public function getSearchValueSerialized(ilADTSearchBridge $a_adt_search): string
    {
        return $a_adt_search->getSerializedValue();
    }

    /**
     * Set value from search persistence
     */
    public function setSearchValueSerialized(ilADTSearchBridge $a_adt_search, $a_value): void
    {
        $a_adt_search->setSerializedValue($a_value);
    }

    /**
     * Add object-data needed for global search to AMD search results
     */
    protected function parseSearchObjects(array $a_records, array $a_object_types): array
    {
        $res = [];
        $obj_ids = [];
        foreach ($a_records as $record) {
            if ($record["sub_type"] == "-") {
                $obj_ids[] = $record["obj_id"];
            }
        }

        $sql = "SELECT obj_id,type" .
            " FROM object_data" .
            " WHERE " . $this->db->in("obj_id", $obj_ids, false, "integer") .
            " AND " . $this->db->in("type", $a_object_types, false, "text");
        $set = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($set)) {
            $res[] = $row;
        }
        return $res;
    }

    public function searchSubObjects(ilADTSearchBridge $a_adt_search, int $a_obj_id, string $sub_obj_type): array
    {
        $element_id = ilADTActiveRecordByType::SINGLE_COLUMN_NAME;

        // :TODO:
        if ($a_adt_search instanceof ilADTLocationSearchBridgeSingle) {
            $element_id = "loc";
        }

        $condition = $a_adt_search->getSQLCondition($element_id);
        if ($condition) {
            $objects = ilADTActiveRecordByType::find(
                "adv_md_values",
                $this->getADT()->getType(),
                $this->getFieldId(),
                $condition
            );
            if (sizeof($objects)) {
                $res = array();
                foreach ($objects as $item) {
                    if ($item["obj_id"] == $a_obj_id &&
                        $item["sub_type"] == $sub_obj_type) {
                        $res[] = $item["sub_id"];
                    }
                }
                return $res;
            }
        }

        return array();
    }

    /**
     * Search objects
     */
    public function searchObjects(
        ilADTSearchBridge $a_adt_search,
        ilQueryParser $a_parser,
        array $a_object_types,
        string $a_locate,
        string $a_search_type
    ): array {
        // search type only supported/needed for text
        $condition = $a_adt_search->getSQLCondition(ilADTActiveRecordByType::SINGLE_COLUMN_NAME);
        if ($condition) {
            $objects = ilADTActiveRecordByType::find(
                "adv_md_values",
                $this->getADT()->getType(),
                $this->getFieldId(),
                $condition,
                $a_locate
            );
            if (sizeof($objects)) {
                return $this->parseSearchObjects($objects, $a_object_types);
            }
        }
        return [];
    }

    /**
     * Get search string in lucene syntax
     * @param string | array
     * @return
     * @todo with php 8 support change parameter to union type
     */
    public function getLuceneSearchString($a_value)
    {
        return $a_value;
    }

    /**
     * Prepare search form elements
     */
    public function prepareElementForSearch(ilADTSearchBridge $a_bridge): void
    {
    }

    /**
     * Clone field definition
     */
    public function _clone(int $a_new_record_id): self
    {
        $class = get_class($this);
        $obj = new $class();
        $obj->setRecordId($a_new_record_id);
        $obj->setTitle($this->getTitle());
        $obj->setDescription($this->getDescription());
        $obj->setRequired($this->isRequired());
        $obj->setPosition($this->getPosition());
        $obj->setSearchable($this->isSearchable());
        $obj->importFieldDefinition($this->getFieldDefinition());
        $obj->save(true);

        return $obj;
    }
    //
    // complex options
    //

    public function hasComplexOptions(): bool
    {
        return false;
    }

    /**
     * @param object $a_parent_gui
     * @param string $parent_cmd
     * @return null
     */
    public function getComplexOptionsOverview(object $a_parent_gui, string $parent_cmd): ?string
    {
        return null;
    }
}
