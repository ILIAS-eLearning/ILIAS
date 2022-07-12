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
class ilDclContentExporter
{
    const SOAP_FUNCTION_NAME = 'exportDataCollectionContent';
    const EXPORT_EXCEL = 'xlsx';
    const IN_PROGRESS_POSTFIX = '.prog';
    /**
     * Ref-ID of DataCollection
     */
    protected int $ref_id;
    /**
     * Table-Id for export
     */
    protected ?int $table_id;
    /**
     * Array with filters
     */
    protected array $filter;

    protected ilObjDataCollection $dcl;

    protected ilLanguage $lng;

    protected ilDclTable $table;
    private ilGlobalTemplateInterface $main_tpl;
    protected array $tables;

    public function __construct(int $ref_id, ?int $table_id, array $filter = array())
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC['lng'];

        $this->ref_id = $ref_id;
        $this->table_id = $table_id;
        $this->filter = $filter;

        $this->dcl = new ilObjDataCollection($ref_id);
        $this->tables = ($table_id) ? array($this->dcl->getTableById($table_id)) : $this->dcl->getTables();

        $lng->loadLanguageModule('dcl');
        $this->lng = $lng;
    }

    /**
     * Sanitize the given filename
     * The ilUtil::_sanitizeFilename() does not clean enough
     */
    public function sanitizeFilename(string $filename) : string
    {
        $dangerous_filename_characters = array(" ", '"', "'", "&", "/", "\\", "?", "#", "`");

        return str_replace($dangerous_filename_characters, "_", iconv("utf-8", "ascii//TRANSLIT", $filename));
    }

    /**
     * Return export path
     */
    public function getExportContentPath(string $format) : string
    {
        return ilExport::_getExportDirectory($this->dcl->getId(), $format, 'dcl') . '/';
    }

    /**
     * Fill a excel row
     */
    protected function fillRowExcel(
        ilDclTable $table,
        ilExcel $worksheet,
        ilDclBaseRecordModel $record,
        int $row
    ) : void {
        $col = 0;
        foreach ($table->getFields() as $field) {
            if ($field->getExportable()) {
                $record->fillRecordFieldExcelExport($worksheet, $row, $col, $field->getId());
            }
        }
    }

    /**
     * Fill Excel header
     */
    protected function fillHeaderExcel(ilDclTable $table, ilExcel $worksheet, int $row) : void
    {
        $col = 0;

        foreach ($table->getFields() as $field) {
            if ($field->getExportable()) {
                $field->fillHeaderExcel($worksheet, $row, $col);
            }
        }
    }

    /**
     * Fill Excel meta-data
     */
    protected function fillMetaExcel(string $table, ilExcel $worksheet, int $row) : void
    {
    }

    /**
     * Creates an export of a specific data collection table
     * @return bool|void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception|\PhpOffice\PhpSpreadsheet\Exception
     */
    public function export(string $format = self::EXPORT_EXCEL, string $filepath = null, bool $send = false)
    {
        if (count($this->tables) == 0) {
            return;
        }

        if (empty($filepath)) {
            $filepath = $this->getExportContentPath($format);
            ilFileUtils::makeDirParents($filepath);

            $basename = (isset($this->table_id)) ? $this->tables[0]->getTitle() : 'complete';
            $filename = time() . '__' . $basename . "_" . date("Y-m-d_H-i");

            $filepath .= $this->sanitizeFilename($filename);
        } else {
            $filename = pathinfo($filepath, PATHINFO_FILENAME);
        }

        $in_progress_file = $filepath . self::IN_PROGRESS_POSTFIX;
        file_put_contents($in_progress_file, "");

        $data_available = false;
        $fields_available = false;
        if ($format == self::EXPORT_EXCEL) {
            $adapter = new ilExcel();
            foreach ($this->tables as $table) {
                ilDclCache::resetCache();

                $list = $table->getPartialRecords(null, null, null, 0, $this->filter);
                $data_available = $data_available || ($list['total'] > 0);
                $fields_available = $fields_available || (count($table->getExportableFields()) > 0);
                if ($list['total'] > 0 && count($table->getExportableFields()) > 0) {
                    // only 31 character-long table-titles are allowed
                    $title = substr($table->getTitle(), 0, 31);
                    $adapter->addSheet($title);
                    $row = 1;

                    $this->fillMetaExcel($table, $adapter, $row);

                    // #14813
                    $pre = $row;
                    $this->fillHeaderExcel($table, $adapter, $row);
                    if ($pre == $row) {
                        $row++;
                    }

                    foreach ($list['records'] as $set) {
                        $this->fillRowExcel($table, $adapter, $set, $row);
                        $row++; // #14760
                    }

                    $data_available = true;
                }
            }
        }

        if (file_exists($in_progress_file)) {
            unlink($in_progress_file);
        }

        if (!$data_available) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('dcl_no_export_content_available'));

            return false;
        }

        if (!$fields_available) {
            global $ilCtrl;
            $this->main_tpl->setOnScreenMessage('info', sprintf(
                $this->lng->txt('dcl_no_export_fields_available'),
                $ilCtrl->getLinkTargetByClass(array('ilDclTableListGUI', 'ilDclTableEditGUI', 'ilDclFieldListGUI'),
                    'listFields')
            ));
            return false;
        }

        if ($send) {
            $adapter->sendToClient($filename);
        } else {
            $adapter->writeToFile($filepath);
        }
        return true;
    }

    /**
     * Start Export async
     * @return mixed
     * @throws ilDclException
     */
    public function exportAsync(string $format = self::EXPORT_EXCEL, string $filepath = null)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $method = self::SOAP_FUNCTION_NAME;

        $soap_params = array($this->dcl->getRefId());
        array_push($soap_params, $this->table_id, $format, $filepath);

        $new_session_id = ilSession::_duplicate($_COOKIE[session_name()]);
        $client_id = $_COOKIE['ilClientId'];

        // Start cloning process using soap call
        $soap_client = new ilSoapClient();
        $soap_client->setResponseTimeout(5);
        $soap_client->enableWSDL(true);

        $ilLog->write(__METHOD__ . ': Trying to call Soap client...');

        array_unshift($soap_params, $new_session_id . '::' . $client_id);

        if ($soap_client->init()) {
            $ilLog->info('Calling soap ' . $method . ' method with params ' . print_r($soap_params, true));
            $res = $soap_client->call($method, $soap_params);
        } else {
            $ilLog->warning('SOAP clone call failed. Calling clone method manually');
            if (method_exists('ilSoapFunctions', $method)) {
                $res = ilSoapFunctions::$method($new_session_id . '::' . $client_id, $this->dcl->getRefId(),
                    $this->table_id, $format, $filepath);
            } else {
                throw new ilDclException("SOAP call " . $method . " does not exists!");
            }
        }

        return $res;
    }
}
