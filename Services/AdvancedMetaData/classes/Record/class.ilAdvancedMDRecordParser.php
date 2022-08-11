<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * SAX based XML parser for record import files
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 * @todo    remove update mode completely
 */
class ilAdvancedMDRecordParser extends ilSaxParser
{
    public const MODE_UNDEFINED = 0;
    public const MODE_UPDATE = 1;
    public const MODE_INSERT = 2;
    // update is not supported anymore
    public const MODE_UPDATE_VALIDATION = 3;
    public const MODE_INSERT_VALIDATION = 4;

    private int $mode = self::MODE_UNDEFINED;

    private array $fields = [];

    private bool $is_error = false;
    private array $error_msg = [];
    private string $field_value_id = '';

    protected array $context;
    protected array $scopes = [];

    protected array $translations = [];
    protected string $translation_language = '';

    protected array $field_translations = [];
    protected string $field_translation_language = '';

    private array $rec_map;
    protected ?ilAdvancedMDRecord $current_record = null;
    protected ?ilAdvancedMDFieldDefinition $current_field = null;
    protected string $cdata = '';

    protected ilLogger $log;

    public function __construct(string $a_file)
    {
        parent::__construct($a_file, true);
        $this->log = ilLoggerFactory::getLogger('amet');
    }

    public function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function startParsing() : void
    {
        parent::startParsing();
        if ($this->is_error) {
            throw new ilSaxParserException(implode('<br/>', $this->error_msg));
        }
    }

    /**
     * set event handlers
     * @param resource    reference to the xml parser
     * @access    private
     */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * Handler for start tags
     * @access protected
     */
    protected function handlerBeginTag($a_xml_parser, $a_name, $a_attribs) : void
    {
        switch ($a_name) {
            case 'AdvancedMetaDataRecords':
                $this->is_error = false;
                $this->error_msg = array();
                // Nothing to do
                break;

            case 'Scope':
                $this->scopes = [];
                break;

            case 'ScopeEntry':
                $parsed_id = ilUtil::parseImportId($a_attribs['id']);
                if (
                    $parsed_id['inst_id'] == IL_INST_ID &&
                    ilObject::_exists($parsed_id['id'], true, $parsed_id['type'])
                ) {
                    $scope = new ilAdvancedMDRecordScope();
                    $scope->setRefId((int) $parsed_id['id']);
                    $this->scopes[] = $scope;
                }
                break;

            case 'Record':
                $this->fields = array();
                $this->current_field = null;
                $this->current_record = null;
                if (!strlen($a_attribs['id']) or !isset($a_attribs['active'])) {
                    $this->appendErrorMessage('Missing XML attribute for element "Record".');
                }
                if (!$this->initRecordObject((string) $a_attribs['id'])) {
                    $this->appendErrorMessage('Invalid attribute Id given for element "Record".');
                }
                $this->getCurrentRecord()->setActive((bool) $a_attribs['active']);
                $this->getCurrentRecord()->setImportId($a_attribs['id']);
                $this->getCurrentRecord()->setAssignedObjectTypes(array());

                if (isset($a_attribs['defaultLanguage'])) {
                    $language = (string) $a_attribs['defaultLanguage'];
                    if (ilLanguage::lookupId($language)) {
                        $this->getCurrentRecord()->setDefaultLanguage($language);
                    }
                } else {
                    $this->getCurrentRecord()->setDefaultLanguage($this->lng->getDefaultLanguage());
                }
                break;

            case 'RecordTranslations':
                $this->translations = [];
                $this->field_translations = [];
                $this->getCurrentRecord()->setDefaultLanguage(
                    $a_attribs['defaultLanguage'] ?? $this->getCurrentRecord()->getDefaultLanguage()
                );
                break;

            case 'RecordTranslation':
                $this->translation_language = $a_attribs['language'] ?? $this->lng->getDefaultLanguage();
                break;

            case 'FieldTranslations':
                $this->field_translations[$this->getCurrentField()->getImportId()] = [];
                break;

            case 'FieldTranslation':
                $this->field_translation_language = $a_attribs['language'] ?? $this->lng->getDefaultLanguage();
                break;

            case 'Title':
                break;

            case 'Field':
                if (!strlen($a_attribs['id']) or !isset($a_attribs['searchable']) or !isset($a_attribs['fieldType'])) {
                    $this->appendErrorMessage('Missing XML attribute for element "Field".');
                }
                if (!$this->initFieldObject((int) $a_attribs['id'], (string) $a_attribs['fieldType'])) {
                    $this->appendErrorMessage('Invalid attribute Id given for element "Record".');
                }
                $this->getCurrentField()->setImportId($a_attribs['id']);
                $this->getCurrentField()->setSearchable($a_attribs['searchable'] == 'Yes');
                break;

            case 'FieldTitle':
            case 'FieldDescription':
            case 'FieldPosition':
            case 'FieldValue':
                $this->field_value_id = (string) ($a_attribs['id'] ?? "");
                break;
        }
    }

    /**
     * Handler for end tags
     * @access protected
     */
    protected function handlerEndTag($a_xml_parser, $a_name) : void
    {
        switch ($a_name) {
            case 'AdvancedMetaDataRecords':
                break;

            case 'Record':
                $this->storeRecords();
                break;

            case 'Scope':
                $this->getCurrentRecord()->setScopes($this->scopes);
                break;

            case 'Title':
                $this->getCurrentRecord()->setTitle(trim($this->cdata));
                break;

            case 'Description':
                $this->getCurrentRecord()->setDescription(trim($this->cdata));
                break;

            case 'ObjectType':
                // #12980
                $parts = explode(":", trim($this->cdata));
                $this->getCurrentRecord()->appendAssignedObjectType($parts[0], $parts[1]);
                break;

            case 'Field':
                break;

            case 'RecordTranslationTitle':
                $this->translations[$this->translation_language]['title'] = trim($this->cdata);
                break;

            case 'RecordTranslationDescription':
                $this->translations[$this->translation_language]['description'] = trim($this->cdata);
                break;

            case 'FieldTranslationTitle':
                $this->field_translations[$this->getCurrentField()->getImportId()][$this->field_translation_language]['title'] = trim($this->cdata);
                break;

            case 'FieldTranslationDescription':
                $this->field_translations[$this->getCurrentField()->getImportId()][$this->field_translation_language]['description'] = trim($this->cdata);
                break;

            case 'FieldTitle':
                $this->getCurrentField()->setTitle(trim($this->cdata));
                break;

            case 'FieldDescription':
                $this->getCurrentField()->setDescription(trim($this->cdata));
                break;

            case 'FieldPosition':
                $this->getCurrentField()->setPosition((int) trim($this->cdata));
                break;

            case 'FieldValue':
                $this->getCurrentField()->importXMLProperty($this->field_value_id, trim($this->cdata));
                break;
        }
        $this->cdata = '';
    }

    /**
     * handler for character data
     * @param resource $a_xml_parser xml parser
     * @param string   $a_data       character data
     */
    protected function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }

    private function initRecordObject(string $a_id) : bool
    {
        switch ($this->getMode()) {
            case self::MODE_INSERT:
            case self::MODE_INSERT_VALIDATION:
                $this->current_record = new ilAdvancedMDRecord(0);
                return true;

            default:
                $this->current_record = ilAdvancedMDRecord::_getInstanceByRecordId($this->extractRecordId($a_id));
                return true;
        }
    }

    /**
     * Init field definition object
     */
    private function initFieldObject(int $a_id, string $a_type)
    {
        switch ($this->getMode()) {
            case self::MODE_INSERT:
            case self::MODE_INSERT_VALIDATION:
                $this->current_field = ilAdvancedMDFieldDefinition::getInstanceByTypeString($a_type);
                $this->fields[] = $this->current_field;
                return true;

            default:
                throw new InvalidArgumentException(
                    'Current parsing mode is not supported. Mode: ' . $this->getMode()
                );
        }
    }

    private function getCurrentRecord() : ?ilAdvancedMDRecord
    {
        return $this->current_record;
    }

    private function getCurrentField() : ?ilAdvancedMDFieldDefinition
    {
        return $this->current_field;
    }

    private function extractRecordId(string $a_id_string) : int
    {
        // first lookup import id
        if ($record_id = ilAdvancedMDRecord::_lookupRecordIdByImportId($a_id_string)) {
            return $record_id;
        }
        return 0;
    }

    private function appendErrorMessage(string $a_msg) : void
    {
        $this->is_error = true;
        $this->error_msg[] = $a_msg;
    }

    private function storeRecords() : void
    {
        switch ($this->getMode()) {
            case self::MODE_INSERT_VALIDATION:
            case self::MODE_UPDATE_VALIDATION:
                return;

            case self::MODE_INSERT:
                // set local context
                if (is_array($this->context)) {
                    $this->getCurrentRecord()->setParentObject($this->context["obj_id"]);
                    $this->getCurrentRecord()->setAssignedObjectTypes(array(
                        array(
                            "obj_type" => $this->context["obj_type"],
                            "sub_type" => $this->context["sub_type"],
                            "optional" => false
                        )
                    ));
                }

                $this->getCurrentRecord()->save();
                break;
        }
        foreach ($this->fields as $field) {
            $field->setRecordId($this->getCurrentRecord()->getRecordId());
            switch ($this->getMode()) {
                case self::MODE_INSERT:
                    $field->save();
                    foreach ($this->field_translations as $field_id => $field_info) {
                        if (strcmp($field_id, $field->getImportId()) !== 0) {
                            continue;
                        }
                        foreach ((array) $field_info as $language => $field_translation) {
                            $translation = new ilAdvancedMDFieldTranslation(
                                (int) $field->getFieldId(),
                                (string) $field_translation['title'],
                                (string) $field_translation['description'],
                                (string) $language
                            );
                            $translation->insert();
                        }
                    }

                    // see getRecordMap()
                    $this->log->debug("add to record map, rec id: " . $this->getCurrentRecord()->getRecordId() .
                        ", import id: " . $field->getImportId() . ", field id:" . $field->getFieldId());
                    $this->rec_map[$this->getCurrentRecord()->getRecordId()][$field->getImportId()] = $field->getFieldId();
                    break;
            }
        }
        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->getCurrentRecord()->getRecordId());
        $translations->addTranslationEntry($this->getCurrentRecord()->getDefaultLanguage(), true);
        $translations->updateTranslations(
            $this->getCurrentRecord()->getDefaultLanguage(),
            $this->getCurrentRecord()->getTitle(),
            $this->getCurrentRecord()->getDescription()
        );

        foreach ($this->translations as $lang_key => $translation_info) {
            if (!$translations->isConfigured($lang_key)) {
                $translations->addTranslationEntry($lang_key);
            }
            $translations->updateTranslations(
                $lang_key,
                (string) $translation_info['title'],
                (string) $translation_info['description']
            );
        }
    }

    public function setContext(int $a_obj_id, string $a_obj_type, ?string $a_sub_type = null) : void
    {
        if (!$a_sub_type) {
            $a_sub_type = "-";
        }

        $this->context = array(
            "obj_id" => $a_obj_id,
            "obj_type" => $a_obj_type,
            "sub_type" => $a_sub_type
        );
    }

    public function getRecordMap() : array
    {
        return $this->rec_map;
    }
}
