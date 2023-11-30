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
* Export class for questionpools
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
*
* @version $Id$
*
* @ingroup components\ILIASTestQuestionPool
*/
class ilQuestionpoolExport
{
    public ilErrorHandling $err;			// error object
    public ilDBInterface $db;			// database object
    public ILIAS $ilias;			// ilias object
    public string $inst_id;		// installation id
    public ilLanguage $lng;

    private string $export_dir = '';
    private string $subdir = '';
    private string $filename = '';
    private string $zipfilename = '';
    private string $qti_filename = '';
    private ilXmlWriter $xml;

    /**
    * Constructor
    * @access	public
    */
    public function __construct(
        protected ilObjQuestionPool $qpl_obj,
        protected string $mode = "xml",
        protected ?array $questions = null
    ) {
        global $DIC;
        $this->err = $DIC['ilErr'];
        $this->db = $DIC['ilDB'];
        $this->ilias = $DIC['ilias'];
        $this->lng = $DIC['lng'];

        if (!is_array($this->questions)) {
            $this->questions = $this->qpl_obj->getAllQuestionIds();
        }

        $this->inst_id = IL_INST_ID;
        $date = time();

        $this->qpl_obj->createExportDirectory();
        switch ($this->mode) {
            case "xml":
                $this->export_dir = $this->qpl_obj->getExportDirectory('xml');
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "qpl" . "_" . $this->qpl_obj->getId();
                $this->filename = $this->subdir . ".xml";
                $this->qti_filename = $date . "__" . $this->inst_id . "__" .
                    "qti" . "_" . $this->qpl_obj->getId() . ".xml";
                break;
            case "xlsx":
                $this->export_dir = $this->qpl_obj->getExportDirectory('xlsx');
                $this->filename = $date . "__" . $this->inst_id . "__" .
                    "qpl" . "_" . $this->qpl_obj->getId() . ".xlsx";
                $this->zipfilename = $date . "__" . $this->inst_id . "__" .
                    "qpl" . "_" . $this->qpl_obj->getId() . ".zip";
                break;
            default:
                $this->export_dir = $this->qpl_obj->getExportDirectory('zip');
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "qpl" . "_" . $this->qpl_obj->getId();
                $this->filename = $this->subdir . ".xml";
                $this->qti_filename = $date . "__" . $this->inst_id . "__" .
                    "qti" . "_" . $this->qpl_obj->getId() . ".xml";
                break;
        }
    }

    public function getInstId()
    {
        return $this->inst_id;
    }


    /**
    *   build export file (complete zip file)
    */
    public function buildExportFile(): string
    {
        switch ($this->mode) {
            case "xlsx":
                return $this->buildExportFileXLSX();
            case "xml":
            default:
                return $this->buildExportFileXML();
        }
    }

    /**
    * build xml export file
    */
    public function buildExportFileXML(): string
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        $ilBench->start("QuestionpoolExport", "buildExportFile");

        $this->xml = new ilXmlWriter();
        $this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");
        $this->xml->xmlSetGenCmt("Export of ILIAS Test Questionpool " .
            $this->qpl_obj->getId() . " of installation " . $this->inst_id);
        $this->xml->xmlHeader();

        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

        $expDir = $this->qpl_obj->getExportDirectory();
        ilFileUtils::makeDirParents($expDir);

        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        $qti_file = fopen($this->export_dir . "/" . $this->subdir . "/" . $this->qti_filename, "w");
        fwrite($qti_file, $this->qpl_obj->questionsToXML($this->questions));
        fclose($qti_file);

        $ilBench->start("QuestionpoolExport", "buildExportFile_getXML");
        $this->qpl_obj->objectToXmlWriter(
            $this->xml,
            $this->inst_id,
            $this->export_dir . "/" . $this->subdir,
            $expLog,
            $this->questions
        );
        $ilBench->stop("QuestionpoolExport", "buildExportFile_getXML");

        $ilBench->start("QuestionpoolExport", "buildExportFile_dumpToFile");
        $this->xml->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->filename, false);
        $ilBench->stop("QuestionpoolExport", "buildExportFile_dumpToFile");

        $ilBench->start("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");
        $this->exportXHTMLMediaObjects($this->export_dir . "/" . $this->subdir);
        $ilBench->stop("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");

        $ilBench->start("QuestionpoolExport", "buildExportFile_zipFile");
        ilFileUtils::zip($this->export_dir . "/" . $this->subdir, $this->export_dir . "/" . $this->subdir . ".zip");

        $ilBench->stop("QuestionpoolExport", "buildExportFile_zipFile");

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");
        $ilBench->stop("QuestionpoolExport", "buildExportFile");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }

    public function exportXHTMLMediaObjects($a_export_dir): void
    {
        foreach ($this->questions as $question_id) {
            $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
            foreach ($mobs as $mob) {
                if (ilObjMediaObject::_exists($mob)) {
                    $mob_obj = new ilObjMediaObject($mob);
                    $mob_obj->exportFiles($a_export_dir);
                    unset($mob_obj);
                }
            }
        }
    }

    /**
    * build xml export file
    */
    protected function buildExportFileXLSX(): string
    {
        $worksheet = new ilAssExcelFormatHelper();
        $worksheet->addSheet('Sheet 1');
        $row = 1;
        $col = 0;

        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt("title"));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt("description"));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt("question_type"));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt("author"));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col++) . $row, $this->lng->txt("create_date"));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col) . $row, $this->lng->txt("last_update"));

        $col = 0;
        $row++;
        $questions = $this->qpl_obj->getQuestionList();
        foreach ($questions as $question) {
            $worksheet->setCell($row, $col++, $question["title"]);
            $worksheet->setCell($row, $col++, $question["description"]);
            $worksheet->setCell($row, $col++, $this->lng->txt($question["type_tag"]));
            $worksheet->setCell($row, $col++, $question["author"]);
            $created = new ilDate($question["created"], IL_CAL_UNIX);
            $worksheet->setCell($row, $col++, $created);
            $updated = new ilDate($question["tstamp"], IL_CAL_UNIX);
            $worksheet->setCell($row, $col++, $updated);
            $col = 0;
            $row++;
        }

        $excelfile = $this->export_dir . '/' . $this->filename;
        $worksheet->writeToFile($excelfile);
        return $excelfile;
    }
}
