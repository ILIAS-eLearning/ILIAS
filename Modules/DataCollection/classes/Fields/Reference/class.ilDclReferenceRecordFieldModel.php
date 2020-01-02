<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordFieldModel.php';
require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordModel.php';
require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseFieldModel.php';

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclReferenceRecordFieldModel extends ilDclBaseRecordFieldModel
{

    /**
     * @var int
     */
    protected $dcl_obj_id;


    /**
     * @param ilDclBaseRecordModel $record
     * @param ilDclBaseFieldModel  $field
     */
    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);
        $dclTable = ilDclCache::getTableCache($this->getField()->getTableId());
        $this->dcl_obj_id = $dclTable->getObjId();
    }


    /**
     * @return int|string
     */
    public function getExportValue()
    {
        $value = $this->getValue();
        if ($value) {
            if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                foreach ($value as $val) {
                    if ($val) {
                        $ref_rec = ilDclCache::getRecordCache($val);
                        $ref_record_field = $ref_rec->getRecordField($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
                        if ($ref_record_field) {
                            $exp_value = $ref_record_field->getExportValue();
                            $names[] = is_array($exp_value) ? array_shift($exp_value) : $exp_value;
                        }
                    }
                }

                return implode('; ', $names);
            } else {
                $ref_rec = ilDclCache::getRecordCache($this->getValue());
                $ref_record_field = $ref_rec->getRecordField($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));

                $exp_value = "";
                if ($ref_record_field) {
                    $exp_value = $ref_record_field->getExportValue();
                }

                return (is_array($exp_value) ? array_shift($exp_value) : $exp_value);
            }
        } else {
            return "";
        }
    }


    public function getValueFromExcel($excel, $row, $col)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $value = parent::getValueFromExcel($excel, $row, $col);
        $old = $value;
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $value = $this->getReferencesFromString($value);
            $has_value = count($value);
        } else {
            $value = $this->getReferenceFromValue($value);
            $has_value = $value;
        }

        if (!$has_value && $old) {
            $warning = "(" . $row . ", " . ilDataCollectionImporter::getExcelCharForInteger($col + 1) . ") " . $lng->txt("dcl_no_such_reference") . " "
                . $old;

            return array('warning' => $warning);
        }

        return $value;
    }


    /**
     * This method tries to get as many valid references out of a string separated by commata. This is problematic as a string value could contain commata itself.
     * It is optimized to work with an exported list from this DataCollection. And works fine in most cases. Only areference list with the values "hello" and "hello, world"
     * Will mess with it.
     *
     * @param $stringValues string
     *
     * @return int[]
     */
    protected function getReferencesFromString($stringValues)
    {
        $delimiter = strpos($stringValues, '; ') ? '; ' : ', ';
        $slicedStrings = explode($delimiter, $stringValues);
        $slicedReferences = array();
        $resolved = 0;
        for ($i = 0; $i < count($slicedStrings); $i++) {
            //try to find a reference since the last resolved value separated by a comma.
            // $i = 1; $resolved = 0; $string = "hello, world, gaga" -> try to match "hello, world".
            $searchString = implode(array_slice($slicedStrings, $resolved, $i - $resolved + 1));
            if ($ref = $this->getReferenceFromValue($searchString)) {
                $slicedReferences[] = $ref;
                $resolved = $i;
                continue;
            }

            //try to find a reference with the current index.
            // $i = 1; $resolved = 0; $string = "hello, world, gaga" -> try to match "world".
            $searchString = $slicedStrings[$i];
            if ($ref = $this->getReferenceFromValue($searchString)) {
                $slicedReferences[] = $ref;
                $resolved = $i;
                continue;
            }
        }

        return $slicedReferences;
    }


    /**
     * @param $field ilDclBaseFieldModel
     * @param $value
     *
     * @return int
     */
    public function getReferenceFromValue($value)
    {
        $field = ilDclCache::getFieldCache($this->getField()->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
        $table = ilDclCache::getTableCache($field->getTableId());
        $record_id = 0;
        foreach ($table->getRecords() as $record) {
            $record_value = $record->getRecordField($field->getId())->getExportValue();
            // in case of a url-field
            if (is_array($record_value) && !is_array($value)) {
                $record_value = array_shift($record_value);
            }
            if ($record_value == $value) {
                $record_id = $record->getId();
            }
        }

        return $record_id;
    }


    public function afterClone()
    {
        $field_clone = ilDclCache::getCloneOf($this->getField()->getId(), ilDclCache::TYPE_FIELD);
        $record_clone = ilDclCache::getCloneOf($this->getRecord()->getId(), ilDclCache::TYPE_RECORD);

        if ($field_clone && $record_clone) {
            $record_field_clone = ilDclCache::getRecordFieldCache($record_clone, $field_clone);
            $clone_reference = $record_field_clone->getValue();
            $reference_record = ilDclCache::getCloneOf($clone_reference, ilDclCache::TYPE_RECORD);
            if ($reference_record) {
                $this->setValue($reference_record->getId()); // reference fields store the id of the reference's record as their value
                $this->doUpdate();
            }
        }
    }
}
