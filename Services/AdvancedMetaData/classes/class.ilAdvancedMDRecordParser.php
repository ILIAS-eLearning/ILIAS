<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* SAX based XML parser for record import files
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAdvancedMetaData
*/

include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

class ilAdvancedMDRecordParser extends ilSaxParser
{
    const MODE_UPDATE = 1;
    const MODE_INSERT = 2;
    const MODE_UPDATE_VALIDATION = 3;
    const MODE_INSERT_VALIDATION = 4;
    
    private $mode;
    
    private $fields = array();
    
    private $is_error = false;
    private $error_msg = array();
    
    protected $context; // [array]
    
    protected $scopes = [];

    protected $translations = [];
    protected $translation_language = '';

    protected $field_translations = [];
    protected $field_translation_language = '';

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Constructor
     *
     * @access public
     * @param string xml file
     *
     */
    public function __construct($a_file)
    {
        parent::__construct($a_file, true);
        $this->log = ilLoggerFactory::getLogger('amet');
    }
    
    /**
     * set parsing mode
     *
     * @access public
     * @param int MODE_VALIDATION, MODE_UPDATE or MODE_INSERT
     *
     */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }
    
    /**
     * get mode
     *
     * @access public
     *
     */
    public function getMode()
    {
        return $this->mode;
    }
    
    
    /**
    * stores xml data in array
    *
    * @return bool success status
    * @access	private
    * @throws ilSaxParserException
    */
    public function startParsing()
    {
        parent::startParsing();
        if ($this->is_error) {
            include_once('./Services/Xml/exceptions/class.ilSaxParserException.php');
            throw new ilSaxParserException(implode('<br/>', $this->error_msg));
        }
    }
    
    /**
    * set event handlers
    *
    * @param	resource	reference to the xml parser
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }
    
    /**
     * Handler for start tags
     *
     * @access protected
     */
    protected function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
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
                    $scope->setRefId($parsed_id['id']);
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
                if (!$this->initRecordObject($a_attribs['id'])) {
                    $this->appendErrorMessage('Invalid attribute Id given for element "Record".');
                }
                $this->getCurrentRecord()->setActive($a_attribs['active']);
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
                if (!$this->initFieldObject($a_attribs['id'], $a_attribs['fieldType'])) {
                    $this->appendErrorMessage('Invalid attribute Id given for element "Record".');
                }
                $this->getCurrentField()->setImportId($a_attribs['id']);
                $this->getCurrentField()->setSearchable($a_attribs['searchable'] == 'Yes' ? true : false);
                break;
                
            case 'FieldTitle':
            case 'FieldDescription':
            case 'FieldPosition':
            case 'FieldValue':
                $this->field_value_id = $a_attribs['id'];
                break;
        }
    }
    
    /**
     * Handler for end tags
     *
     * @access protected
     */
    protected function handlerEndTag($a_xml_parser, $a_name)
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
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    protected function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }
    
    /**
     * Init record object
     *
     * @param string import id
     * @access private
     *
     */
    private function initRecordObject($a_id)
    {
        switch ($this->getMode()) {
            case self::MODE_INSERT:
            case self::MODE_INSERT_VALIDATION:
                $this->current_record = new ilAdvancedMDRecord(0);
                return true;
            
            default:
                $this->current_record = ilAdvancedMDRecord::_getInstanceByRecordId($this->extractRecordId($a_id));
                return true;
                break;
        }
    }
    
    /**
     * Init field definition object
     *
     * @access private
     * @param string import id
     *
     */
    private function initFieldObject($a_id, $a_type)
    {
        switch ($this->getMode()) {
            case self::MODE_INSERT:
            case self::MODE_INSERT_VALIDATION:
                $this->current_field = ilAdvancedMDFieldDefinition::getInstanceByTypeString($a_type);
                $this->fields[] = $this->current_field;
                return true;
            
            default:
                // ??? nonsense
                $this->current_field = ilAdvancedMDRecord::_getInstanceByFieldId($this->extractFieldId($a_id));
                return true;
                break;
        }
    }

    /**
     * @return ilAdvancedMDRecord
     */
    private function getCurrentRecord() : ilAdvancedMDRecord
    {
        return $this->current_record;
    }
    
    /**
     * get current field definition
     * @access private
     *
     */
    private function getCurrentField()
    {
        return $this->current_field;
    }
    
    /**
     * Extract id
     *
     * @access private
     * @param
     *
     */
    private function extractRecordId($a_id_string)
    {
        // first lookup import id
        if ($record_id = ilAdvancedMDRecord::_lookupRecordIdByImportId($a_id_string)) {
            $this->record_exists = true;
            return $record_id;
        }
        return 0;
    }
    
    
    
    /**
     *
     *
     * @access private
     * @param
     *
     */
    private function appendErrorMessage($a_msg)
    {
        $this->is_error = true;
        $this->error_msg[] = $a_msg;
    }
    
    /**
     * Store Record
     *
     * @access private
     * @param
     *
     */
    private function storeRecords()
    {
        switch ($this->getMode()) {
            case self::MODE_INSERT_VALIDATION:
            case self::MODE_UPDATE_VALIDATION:
                return true;
            
            case self::MODE_INSERT:
                // set local context
                if (is_array($this->context)) {
                    $this->getCurrentRecord()->setParentObject($this->context["obj_id"]);
                    $this->getCurrentRecord()->setAssignedObjectTypes(array(
                        array(
                            "obj_type" => $this->context["obj_type"],
                            "sub_type" => $this->context["sub_type"],
                            "optional" => false
                    )));
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
            (string) $this->getCurrentRecord()->getTitle(),
            (string) $this->getCurrentRecord()->getDescription()
        );

        foreach ($this->translations as $lang_key => $translation_info) {
            ilLoggerFactory::getLogger('root')->dump($translation_info, ilLogLevel::ERROR);
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
    
    public function setContext($a_obj_id, $a_obj_type, $a_sub_type = null)
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
    
    public function getRecordMap()
    {
        return $this->rec_map;
    }
}
