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

/**
 * Adapter for advanced metadata service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterAdvMDAdapter
{
    protected array $types = ["crs", "cat", "grp", "sess"];
    protected array $supported_types = [
        ilAdvancedMDFieldDefinition::TYPE_SELECT,
        ilAdvancedMDFieldDefinition::TYPE_TEXT,
        ilAdvancedMDFieldDefinition::TYPE_INTEGER,
        ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI,
    ];
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
    }

    /**
     * Get active record sets
     *
     * @return ilAdvancedMDRecord[]
     */
    public function getAvailableRecordSets() : array
    {
        $records = [];
        foreach ($this->types as $type) {
            foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType($type) as $record_obj) {
                if ($record_obj->isActive() && $record_obj->getParentObject() === 0) {
                    $records[] = $record_obj;
                }
            }
        }
        return $records;
    }

    /**
     * Get fields
     * @return ilAdvancedMDFieldDefinition[]
     */
    public function getFields(int $a_record_id) : array
    {
        $fields = array_filter(ilAdvancedMDFieldDefinition::getInstancesByRecordId($a_record_id), function ($f) {
            /** @var ilAdvancedMDFieldDefinition $f */
            return in_array($f->getType(), $this->supported_types, true);
        });
        return $fields;
    }

    /**
     * Get name for filter
     * @throws ilException
     */
    public function getTitle(int $record_id, int $filter_id) : string
    {
        $lng = $this->lng;

        if ($record_id === 0) {
            return $lng->txt("cont_std_filter_title_" . $filter_id);
        }

        $field = ilAdvancedMDFieldDefinition::getInstance($filter_id);
        return $field->getTitle();
    }

    /**
     * @throws ilException
     */
    public function getAdvType(int $filter_id) : string
    {
        $field = ilAdvancedMDFieldDefinition::getInstance($filter_id);
        return (string) $field->getType();
    }

    public function getOptions(int $filter_id) : array
    {
        $field = ilAdvancedMDFieldDefinition::getInstance($filter_id);
        return $field->getOptions();
    }
}
