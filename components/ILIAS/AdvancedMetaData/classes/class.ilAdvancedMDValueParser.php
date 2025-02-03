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
/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDValueParser implements ilSaxSubsetParser
{
    protected string $cdata = '';
    protected int $obj_id;
    protected array $values_records = array();
    protected array $values = array();
    protected ?ilAdvancedMDFieldDefinition $current_value = null;

    public function __construct(int $a_new_obj_id = 0)
    {
        $this->obj_id = $a_new_obj_id;
    }

    /**
     * Set object id (id of new created object)
     */
    public function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
    }

    /**
     * Save values
     * @access public
     */
    public function save(): bool
    {
        foreach ($this->values_records as $values_record) {
            $values_record->write();
        }
        return true;
    }

    /**
     * Start element handler
     * @access public
     * @param resource $a_xml_parser xml parser
     * @param string   $a_name       element name
     * @param array    $a_attribs    element attributes array
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        switch ($a_name) {
            case 'AdvancedMetaData':
                $this->values_records = ilAdvancedMDValues::getInstancesForObjectId($this->obj_id);
                foreach ($this->values_records as $values_record) {
                    // init ADTGroup before definitions to bind definitions to group
                    $values_record->getADTGroup();

                    foreach ($values_record->getDefinitions() as $def) {
                        $this->values[$def->getImportId()] = $def;
                    }
                }
                break;

            case 'Value':
                $this->initValue($a_attribs['id']);
                break;
        }
    }

    /**
     * End element handler
     * @access public
     * @param resource $a_xml_parser xml parser
     * @param string   $a_name       element name
     */
    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        switch ($a_name) {
            case 'AdvancedMetaData':
                break;

            case 'Value':
                $value = trim($this->cdata);
                if (is_object($this->current_value) && $value) {
                    $this->current_value->importValueFromXML($value);
                }
                break;
        }
        $this->cdata = '';
    }

    /**
     * Character data handler
     * @access public
     * @param resource $a_xml_parser xml parser
     * @param string   $a_data       character data
     */
    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }

    /**
     * init new value object
     */
    private function initValue(string $a_import_id): void
    {
        if (isset($this->values[$a_import_id])) {
            $this->current_value = $this->values[$a_import_id];
        } else {
            $this->current_value = null;
        }
    }
}
