<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/DataCollection/classes/class.ilObjDataCollectionAccess.php');
require_once('./Modules/DataCollection/classes/class.ilObjDataCollectionGUI.php');
require_once('./Modules/DataCollection/classes/Content/class.ilDclRecordListGUI.php');
require_once('./Modules/DataCollection/classes/Table/class.ilDclTable.php');
require_once('./Services/Export/classes/class.ilExport.php');

/**
 * Hook-Class for exporting data-collections (used in SOAP-Class)
 * This Class avoids duplicated code by routing the request to the right place
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclContentExporter
{
    const SOAP_FUNCTION_NAME = 'exportDataCollectionContent';
    const EXPORT_EXCEL = 'xlsx';
    const IN_PROGRESS_POSTFIX = '.prog';
    /**
     * @var int $ref_id Ref-ID of DataCollection
     */
    protected $ref_id;
    /**
     * @var int $table_id Table-Id for export
     */
    protected $table_id;
    /**
     * @var array $filter Array with filters
     */
    protected $filter;
    /**
     * @var ilObjDataCollection
     */
    protected $dcl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilDclTable
     */
    protected $table;


    public function __construct($ref_id, $table_id = null, $filter = array())
    {
        global $DIC;
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
     * The ilUtil::_sanitizeFilemame() does not clean enough
     *
     * @param $filename
     *
     * @return string
     */
    public function sanitizeFilename($filename)
    {
        $dangerous_filename_characters = array(" ", '"', "'", "&", "/", "\\", "?", "#", "`");

        return str_replace($dangerous_filename_characters, "_", iconv("utf-8", "ascii//TRANSLIT", $filename));
    }


    /**
     * Return export path
     *
     * @param $format
     *
     * @return string
     */
    public function getExportContentPath($format)
    {
        return ilExport::_getExportDirectory($this->dcl->getId(), $format, 'dcl') . '/';
    }


    /**
     * Fill a excel row
     *
     * @param ilDclTable           $table
     * @param ilExcel              $worksheet
     * @param ilDclBaseRecordModel $record
     * @param                      $row
     */
    protected function fillRowExcel(ilDclTable $table, ilExcel $worksheet, ilDclBaseRecordModel $record, $row)
    {
        $col = 0;
        foreach ($table->getFields() as $field) {
            if ($field->getExportable()) {
                $record->fillRecordFieldExcelExport($worksheet, $row, $col, $field->getId());
            }
        }
    }


    /**
     * Fill Excel header
     *
     * @param ilDclTable $table
     * @param ilExcel    $worksheet
     * @param            $row
     */
    protected function fillHeaderExcel(ilDclTable $table, ilExcel $worksheet, $row)
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
     *
     * @param $table
     * @param $worksheet
     * @param $row
     */
    protected function fillMetaExcel($table, $worksheet, $row)
    {
    }


    /**
     * Creates an export of a specific datacollection table
     *
     * @param string     $format
     * @param null       $filepath
     * @param bool|false $send
     *
     * @return null|string|void
     */
    public function export($format = self::EXPORT_EXCEL, $filepath = null, $send = false)
    {
        if (count($this->tables) == 0) {
            return;
        }

        if (empty($filepath)) {
            $filepath = $this->getExportContentPath($format);
            ilUtil::makeDirParents($filepath);

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
        switch ($format) {
            case self::EXPORT_EXCEL:
                require_once "./Services/Excel/classes/class.ilExcel.php";

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
                break;
        }

        if (file_exists($in_progress_file)) {
            unlink($in_progress_file);
        }

        if (!$data_available) {
            ilUtil::sendInfo($this->lng->txt('dcl_no_export_content_available'));

            return false;
        }

        if (!$fields_available) {
            global $ilCtrl;
            ilUtil::sendInfo(
                sprintf(
                    $this->lng->txt('dcl_no_export_fields_available'),
                    $ilCtrl->getLinkTargetByClass(array('ilDclTableListGUI', 'ilDclTableEditGUI', 'ilDclFieldListGUI'), 'listFields')
                )
            );

            return false;
        }

        if ($send) {
            $adapter->sendToClient($filename);
            exit;
        } else {
            $adapter->writeToFile($filepath);
        }
    }


    /**
     * Start Export async
     *
     * @param string $format
     * @param null   $filepath
     *
     * @return mixed
     * @throws ilDclException
     */
    public function exportAsync($format = self::EXPORT_EXCEL, $filepath = null)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $method = self::SOAP_FUNCTION_NAME;

        $soap_params = array($this->dcl->getRefId());
        array_push($soap_params, $this->table_id, $format, $filepath);

        $new_session_id = ilSession::_duplicate($_COOKIE[session_name()]);
        $client_id = $_COOKIE['ilClientId'];

        // Start cloning process using soap call
        include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

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
            require_once('./webservice/soap/include/inc.soap_functions.php');
            if (method_exists('ilSoapFunctions', $method)) {
                $res = ilSoapFunctions::$method($new_session_id . '::' . $client_id, $this->dcl->getRefId(), $this->table_id, $format, $filepath);
            } else {
                throw new ilDclException("SOAP call " . $method . " does not exists!");
            }
        }

        return $res;
    }
}
