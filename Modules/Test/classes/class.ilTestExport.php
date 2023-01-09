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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
/**
 * Export class for tests
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 *
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
abstract class ilTestExport
{
    private string $export_dir;
    private string $subdir;
    private string $qti_filename;
    /** @var  ilErrorHandling $err */
    public $err;			// error object

    /** @var  ilDBInterface $db */
    public $db;			// database object

    /** @var  ILIAS $ilias */
    public $ilias;			// ilias object

    /** @var  ilObjTest $test_obj */
    public $test_obj;		// test object

    public $inst_id;		// installation id
    public $mode;

    /** @var ilLanguage $lng */
    private $lng;

    private $resultsfile;

    protected $resultExportingEnabledForTestExport = false;

    /**
     * @var ilTestParticipantList
     */
    protected $forcedAccessFilteredParticipantList = null;

    /**
     * Constructor
     */
    public function __construct(&$a_test_obj, $a_mode = "xml")
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];

        $this->test_obj = &$a_test_obj;

        $this->err = &$ilErr;
        $this->ilias = &$ilias;
        $this->db = &$ilDB;
        $this->mode = $a_mode;
        $this->lng = &$lng;

        $this->inst_id = IL_INST_ID;

        $date = time();
        $this->export_dir = $this->test_obj->getExportDirectory();
        switch ($this->mode) {
            case "results":
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "tst__results_" . $this->test_obj->getId();
                break;
            case "aggregated":
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "test__aggregated__results_" . $this->test_obj->getId();
                break;
            default:
                $this->subdir = $date . "__" . $this->inst_id . "__" .
                    "tst" . "_" . $this->test_obj->getId();
                $this->filename = $this->subdir . ".xml";
                $this->resultsfile = $date . "__" . $this->inst_id . "__" .
                    "results" . "_" . $this->test_obj->getId() . ".xml";
                $this->qti_filename = $date . "__" . $this->inst_id . "__" .
                    "qti" . "_" . $this->test_obj->getId() . ".xml";
                break;
        }
        $this->filename = $this->subdir . "." . $this->getExtension();
    }

    /**
     * @return boolean
     */
    public function isResultExportingEnabledForTestExport(): bool
    {
        return $this->resultExportingEnabledForTestExport;
    }

    /**
     * @param boolean $resultExprtingEnabledForTestExport
     */
    public function setResultExportingEnabledForTestExport($resultExprtingEnabledForTestExport)
    {
        $this->resultExportingEnabledForTestExport = $resultExprtingEnabledForTestExport;
    }

    /**
     * @return ilTestParticipantList
     */
    public function getForcedAccessFilteredParticipantList(): ?ilTestParticipantList
    {
        return $this->forcedAccessFilteredParticipantList;
    }

    /**
     * @param ilTestParticipantList $forcedAccessFilteredParticipantList
     */
    public function setForcedAccessFilteredParticipantList(ilTestParticipantList $forcedAccessFilteredParticipantList)
    {
        $this->forcedAccessFilteredParticipantList = $forcedAccessFilteredParticipantList;
    }

    /**
     * @return ilTestParticipantList
     */
    public function getAccessFilteredParticipantList(): ?ilTestParticipantList
    {
        if ($this->getForcedAccessFilteredParticipantList() instanceof ilTestParticipantList) {
            return $this->getForcedAccessFilteredParticipantList();
        }

        return $this->test_obj->buildStatisticsAccessFilteredParticipantList();
    }

    public function getExtension(): string
    {
        switch ($this->mode) {
            case "results":
                return "csv";
                break;
            default:
                return "xml";
                break;
        }
    }

    public function getInstId()
    {
        return $this->inst_id;
    }


    /**
    *   build export file (complete zip file)
    *
    *   @access public
    *   @return
    */
    public function buildExportFile(): string
    {
        switch ($this->mode) {
            case "results":
                return $this->buildExportResultFile();
                break;
            default:
                return $this->buildExportFileXML();
                break;
        }
    }

    /**
    * build xml export file
    */
    public function buildExportResultFile(): string
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        $log = $DIC['log'];

        //get Log File
        $expDir = $this->test_obj->getExportDirectory();

        // make_directories
        $this->test_obj->createExportDirectory();
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        ilFileUtils::makeDir($this->export_dir);

        include_once './Services/Logging/classes/class.ilLog.php';
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Of Results");

        $data = (new ilCSVTestExport($this->test_obj, ilTestEvaluationData::FILTER_BY_NONE, '', false))->withAllResults()->getContent();
        $file = fopen($this->export_dir . "/" . $this->filename, "wb");
        fwrite($file, $data);
        fclose($file);

        $worksheet = (new ilExcelTestExport($this->test_obj, ilTestEvaluationData::FILTER_BY_NONE, '', true, false))
            ->withResultsPage()
            ->withAllUsersPages()
            ->withUserPages()
            ->getContent();
        $worksheet->writeToFile($this->export_dir . "/" . str_replace($this->getExtension(), "xlsx", $this->filename));
        // end
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export of Results");

        return $this->export_dir . "/" . $this->filename;
    }

    abstract protected function initXmlExport();

    abstract protected function getQuestionIds();

    /**
    * build xml export file
    */
    public function buildExportFileXML(): string
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        $ilBench->start("TestExport", "buildExportFile");

        $this->initXmlExport();

        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $this->xml = new ilXmlWriter();

        // set dtd definition
        $this->xml->xmlSetDtdDef("<!DOCTYPE Test SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd\">");

        // set generated comment
        $this->xml->xmlSetGenCmt("Export of ILIAS Test " .
            $this->test_obj->getId() . " of installation " . $this->inst_id . ".");

        // set xml header
        $this->xml->xmlHeader();

        $this->xml->xmlStartTag("ContentObject", array('Type' => 'Test'));

        // create directories
        $this->test_obj->createExportDirectory();
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

        // get Log File
        $expDir = $this->test_obj->getExportDirectory();
        include_once "./Services/Logging/classes/class.ilLog.php";
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // write qti file
        $qti_file = fopen($this->export_dir . "/" . $this->subdir . "/" . $this->qti_filename, "wb");
        fwrite($qti_file, $this->getQtiXml());
        fclose($qti_file);

        // get xml content
        $ilBench->start("TestExport", "buildExportFile_getXML");
        $this->test_obj->exportPagesXML(
            $this->xml,
            $this->inst_id,
            $this->export_dir . "/" . $this->subdir,
            $expLog
        );
        $ilBench->stop("TestExport", "buildExportFile_getXML");

        $this->populateQuestionSetConfigXml($this->xml);

        $assignmentList = $this->buildQuestionSkillAssignmentList();
        $this->populateQuestionSkillAssignmentsXml($this->xml, $assignmentList, $this->getQuestionIds());
        $this->populateSkillLevelThresholdsXml($this->xml, $assignmentList);

        $this->xml->xmlEndTag("ContentObject");

        // dump xml document to screen (only for debugging reasons)
        /*
        echo "<PRE>";
        echo htmlentities($this->xml->xmlDumpMem($format));
        echo "</PRE>";
        */

        // dump xml document to file
        $ilBench->start("TestExport", "buildExportFile_dumpToFile");
        $this->xml->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->filename, false);
        $ilBench->stop("TestExport", "buildExportFile_dumpToFile");

        if ($this->isResultExportingEnabledForTestExport() && @file_exists("./Modules/Test/classes/class.ilTestResultsToXML.php")) {
            // dump results xml document to file
            include_once "./Modules/Test/classes/class.ilTestResultsToXML.php";
            $resultwriter = new ilTestResultsToXML($this->test_obj->getTestId(), $this->test_obj->getAnonymity());
            $resultwriter->setIncludeRandomTestQuestionsEnabled($this->test_obj->isRandomTest());
            $ilBench->start("TestExport", "buildExportFile_results");
            $resultwriter->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->resultsfile, false);
            $ilBench->stop("TestExport", "buildExportFile_results");
        }

        // add media objects which were added with tiny mce
        $ilBench->start("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");
        $this->exportXHTMLMediaObjects($this->export_dir . "/" . $this->subdir);
        $ilBench->stop("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");

        // zip the file
        $ilBench->start("TestExport", "buildExportFile_zipFile");
        ilFileUtils::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );
        $ilBench->stop("TestExport", "buildExportFile_zipFile");

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");
        $ilBench->stop("TestExport", "buildExportFile");

        return $this->export_dir . "/" . $this->subdir . ".zip";
    }

    abstract protected function populateQuestionSetConfigXml(ilXmlWriter $xmlWriter);

    protected function getQtiXml()
    {
        $tstQtiXml = $this->test_obj->toXML();
        $qstQtiXml = $this->getQuestionsQtiXml();

        if (strpos($tstQtiXml, "</section>") !== false) {
            $qtiXml = str_replace("</section>", "$qstQtiXml</section>", $tstQtiXml);
        } else {
            $qtiXml = str_replace("<section ident=\"1\"/>", "<section ident=\"1\">\n$qstQtiXml</section>", $tstQtiXml);
        }

        return $qtiXml;
    }

    abstract protected function getQuestionsQtiXml();

    protected function getQuestionQtiXml($questionId)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $questionOBJ = assQuestion::_instantiateQuestion($questionId);
        $xml = $questionOBJ->toXML(false);

        // still neccessary? there is an include header flag!?
        $xml = preg_replace("/<questestinterop>/", "", $xml);
        $xml = preg_replace("/<\/questestinterop>/", "", $xml);

        return $xml;
    }

    public function exportXHTMLMediaObjects($a_export_dir)
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

        $mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->test_obj->getId());
        foreach ($mobs as $mob) {
            if (ilObjMediaObject::_exists($mob)) {
                $mob_obj = new ilObjMediaObject($mob);
                $mob_obj->exportFiles($a_export_dir);
                unset($mob_obj);
            }
        }
        foreach ($this->getQuestionIds() as $question_id) {
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
     * @param ilXmlWriter                      $a_xml_writer
     * @param ilAssQuestionSkillAssignmentList $assignmentList
     * @param                                  $questions
     */
    protected function populateQuestionSkillAssignmentsXml(ilXmlWriter $a_xml_writer, ilAssQuestionSkillAssignmentList $assignmentList, $questions)
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentExporter.php';
        $skillQuestionAssignmentExporter = new ilAssQuestionSkillAssignmentExporter();
        $skillQuestionAssignmentExporter->setXmlWriter($a_xml_writer);
        $skillQuestionAssignmentExporter->setQuestionIds($questions);
        $skillQuestionAssignmentExporter->setAssignmentList($assignmentList);
        $skillQuestionAssignmentExporter->export();
    }

    protected function populateSkillLevelThresholdsXml(ilXmlWriter $a_xml_writer, ilAssQuestionSkillAssignmentList $assignmentList)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
        $thresholdList = new ilTestSkillLevelThresholdList($ilDB);
        $thresholdList->setTestId($this->test_obj->getTestId());
        $thresholdList->loadFromDb();

        require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdExporter.php';
        $skillLevelThresholdExporter = new ilTestSkillLevelThresholdExporter();
        $skillLevelThresholdExporter->setXmlWriter($a_xml_writer);
        $skillLevelThresholdExporter->setAssignmentList($assignmentList);
        $skillLevelThresholdExporter->setThresholdList($thresholdList);
        $skillLevelThresholdExporter->export();
    }

    /**
     * @return ilAssQuestionSkillAssignmentList
     */
    protected function buildQuestionSkillAssignmentList(): ilAssQuestionSkillAssignmentList
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
        $assignmentList->setParentObjId($this->test_obj->getId());
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        return $assignmentList;
    }
}
