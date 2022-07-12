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
 ********************************************************************
 */

/**
 * Hook-Class for exporting data-collections (used in SOAP-Class)
 * This Class avoids duplicated code by routing the request to the right place
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclContentImporter
{

    //const SOAP_FUNCTION_NAME = 'exportDataCollectionContent';

    const EXPORT_EXCEL = 'xlsx';
    protected int $max_imports = 100;
    protected array $supported_import_datatypes
        = array(
            ilDclDatatype::INPUTFORMAT_BOOLEAN,
            ilDclDatatype::INPUTFORMAT_NUMBER,
            ilDclDatatype::INPUTFORMAT_REFERENCE,
            ilDclDatatype::INPUTFORMAT_TEXT,
            ilDclDatatype::INPUTFORMAT_DATETIME,
            ilDclDatatype::INPUTFORMAT_PLUGIN,
            ilDclDataType::INPUTFORMAT_TEXT_SELECTION,
            ilDclDatatype::INPUTFORMAT_DATE_SELECTION,
        );
    protected array $warnings;
    /**
     * Ref-ID of DataCollection
     */
    protected int $ref_id;
    /**
     * Table-Id for export
     */
    protected int $table_id;

    protected ilObjDataCollection $dcl;
    /**
     * @var ilDclTable[]
     */
    protected array $tables;

    protected string $lng;

    public function __construct(int $ref_id, ?int $table_id = null)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->ref_id = $ref_id;
        $this->table_id = $table_id;

        $this->lng = $lng;

        $this->dcl = new ilObjDataCollection($ref_id);
        $this->tables = ($table_id) ? array($this->dcl->getTableById($table_id)) : $this->dcl->getTables();
    }

    /**
     * @throws ilException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws ilDateTimeException
     */
    public function import(string $file, bool $simulate = false) : array
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $this->warnings = array();
        try {
            $excel = new ilExcel();
            $excel->loadFromFile($file);
        } catch (Exception $e) {
            $this->warnings[] = $this->lng->txt("dcl_file_not_readable");
        }

        $sheet_count = $excel->getSheetCount();
        $excel->setActiveSheet(0);

        if ($sheet_count != count($this->tables)) {
            $this->warnings[] = $this->lng->txt('dcl_file_not_readable');
        }

        if (count($this->warnings)) {
            return array('line' => 0, 'warnings' => $this->warnings);
        }

        for ($sheet = 0; $sheet < $sheet_count; $sheet++) {
            $excel->setActiveSheet($sheet);
            $table = $this->tables[$sheet];

            // only 31 character-long table-titles are allowed
            $sheet_title = substr($table->getTitle(), 0, 31);
            if ($excel->getSheetTitle() != $sheet_title) {
                $this->warnings[] = $this->lng->txt('dcl_table_title_not_matching');
                continue;
            }

            $field_names = array();
            $sheet_data = $excel->getSheetAsArray();

            foreach ($sheet_data[0] as $column) {
                $field_names[] = $column;
            }
            $fields = $this->getImportFieldsFromTitles($table, $field_names);

            $records_failed = 0;
            for ($i = 2; $i <= count($sheet_data); $i++) {
                $record = new ilDclBaseRecordModel();
                $record->setOwner($ilUser->getId());
                $date_obj = new ilDateTime(time(), IL_CAL_UNIX);
                $record->setCreateDate($date_obj);
                $record->setTableId($table->getId());
                if (!$simulate) {
                    $record->doCreate();
                }
                $fields_failed = 0;
                foreach ($fields as $col => $field) {
                    try {
                        if ($field->isStandardField()) {
                            $record->setStandardFieldValueFromExcel($excel, $i, $col, $field);
                        } else {
                            $value = $record->getRecordFieldValueFromExcel($excel, $i, $col, $field);

                            if (is_array($value) && isset($value['warning'])) {
                                $this->warnings[] = $value['warning'];
                                $value = '';
                            }

                            $field->checkValidity($value, $record->getId());
                            if (!$simulate) {
                                $record->setRecordFieldValue($field->getId(), $value);
                            }
                        }
                    } catch (ilDclInputException $e) {
                        $fields_failed++;
                        $this->warnings[] = "(" . $i . ", " . ilDataCollectionImporter::getExcelCharForInteger($col + 1) . ") " . $e;
                    }
                }

                if ($fields_failed < count($fields)) {
                    $record_imported = true;
                } else {
                    $records_failed++;
                    $record_imported = false;
                }

                if (!$simulate) {
                    if (!$record_imported) { // if no fields have been filled, delete the record again
                        $record->doDelete(true); // omit notification
                    } else {
                        $record->doUpdate();
                    }
                }
                if (($i - 1) - $records_failed > $this->max_imports) {
                    $this->warnings[] = $this->lng->txt("dcl_max_import") . (count($sheet_data) - 1) . " > " . $this->max_imports;
                    break;
                }
            }
        }

        return array('line' => ($i - 2 < 0 ? 0 : $i - 2), 'warnings' => $this->warnings);
    }

    protected function checkImportType(ilDclBaseFieldModel $field) : bool
    {
        if (in_array($field->getDatatypeId(), $this->supported_import_datatypes)) {
            return true;
        } else {
            $this->warnings[] = $field->getTitle() . ": " . $this->lng->txt("dcl_not_supported_in_import");

            return false;
        }
    }

    /**
     * @param string[] $titles
     * @return ilDclBaseFieldModel[]
     */
    protected function getImportFieldsFromTitles(ilDclTable $table, array $titles) : array
    {
        $fields = $table->getRecordFields();
        $import_fields = array();
        foreach ($fields as $field) {
            if ($this->checkImportType($field)) {
                // the fields will add themselves to $import_fields (at the correct position) if their title is in $titles
                $field->checkTitlesForImport($titles, $import_fields);
            }
        }

        foreach ($titles as $key => $value) {
            $not_importable_titles = ilDclStandardField::_getNonImportableStandardFieldTitles();
            $importable_titles = ilDclStandardField::_getImportableStandardFieldTitle();
            foreach ($importable_titles as $identifier => $values) {
                if (in_array($value, $values)) {
                    $std_field = new ilDclStandardField();
                    $std_field->setId(substr($identifier, 4));
                    $import_fields[$key] = $std_field;
                    continue 2;
                }
            }
            if (in_array($value, $not_importable_titles)) {
                $this->warnings[] = "(1, " . ilDataCollectionImporter::getExcelCharForInteger($key) . ") \"" . $value . "\" " . $this->lng->txt("dcl_std_field_not_importable");
            } else {
                if (!isset($import_fields[$key])) {
                    $this->warnings[] = "(1, " . ilDataCollectionImporter::getExcelCharForInteger($key + 1) . ") \"" . $value . "\" " . $this->lng->txt("dcl_row_not_found");
                }
            }
        }

        return $import_fields;
    }
}
