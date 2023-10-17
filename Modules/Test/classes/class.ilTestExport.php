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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

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
    private string $filename;
    private string $resultsfile;

    private ?ilXmlWriter $xml;
    private ilLanguage $lng;

    protected bool $resultExportingEnabledForTestExport = false;

    protected ?ilTestParticipantList $forcedAccessFilteredParticipantList = null;
    protected ilBenchmark $bench;

    protected ilErrorHandling $err;
    protected ilDBInterface $db;
    protected ILIAS $ilias;

    protected string $inst_id;

    public function __construct(
        public ilObjTest $test_obj,
        public string $mode = "xml"
    ) {
        global $DIC;
        $this->err = $DIC['ilErr'];
        $this->ilias = $DIC['ilias'];
        $this->db = $DIC['ilDB'];
        $this->lng = $DIC['lng'];
        $this->bench = $DIC['ilBench'];

        $this->inst_id = (string) IL_INST_ID;

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

    public function isResultExportingEnabledForTestExport(): bool
    {
        return $this->resultExportingEnabledForTestExport;
    }

    public function setResultExportingEnabledForTestExport(bool $resultExprtingEnabledForTestExport): void
    {
        $this->resultExportingEnabledForTestExport = $resultExprtingEnabledForTestExport;
    }

    public function getForcedAccessFilteredParticipantList(): ?ilTestParticipantList
    {
        return $this->forcedAccessFilteredParticipantList;
    }

    public function setForcedAccessFilteredParticipantList(ilTestParticipantList $forcedAccessFilteredParticipantList): void
    {
        $this->forcedAccessFilteredParticipantList = $forcedAccessFilteredParticipantList;
    }

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

    public function buildExportResultFile(): string
    {
        //get Log File
        $expDir = $this->test_obj->getExportDirectory();

        // make_directories
        $this->test_obj->createExportDirectory();
        ilFileUtils::makeDir($this->export_dir);

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
            ->withUserPages()
            ->getContent();
        $worksheet->writeToFile($this->export_dir . "/" . str_replace($this->getExtension(), "xlsx", $this->filename));
        // end
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export of Results");

        return $this->export_dir . "/" . $this->filename;
    }

    abstract protected function initXmlExport();

    abstract protected function getQuestionIds();

    public function buildExportFileXML(): string
    {
        $this->bench->start("TestExport", "buildExportFile");

        $this->initXmlExport();

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
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir);
        ilFileUtils::makeDir($this->export_dir . "/" . $this->subdir . "/objects");

        // get Log File
        $expDir = $this->test_obj->getExportDirectory();
        $expLog = new ilLog($expDir, "export.log");
        $expLog->delete();
        $expLog->setLogFormat("");
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export");

        // write qti file
        $qti_file = fopen($this->export_dir . "/" . $this->subdir . "/" . $this->qti_filename, "wb");
        fwrite($qti_file, $this->getQtiXml());
        fclose($qti_file);

        // get xml content
        $this->bench->start("TestExport", "buildExportFile_getXML");
        $this->test_obj->exportPagesXML(
            $this->xml,
            $this->inst_id,
            $this->export_dir . "/" . $this->subdir,
            $expLog
        );
        $this->bench->stop("TestExport", "buildExportFile_getXML");

        $this->populateQuestionSetConfigXml($this->xml);

        $assignmentList = $this->buildQuestionSkillAssignmentList();
        $this->populateQuestionSkillAssignmentsXml($this->xml, $assignmentList, $this->getQuestionIds());
        $this->populateSkillLevelThresholdsXml($this->xml, $assignmentList);

        $this->xml->xmlEndTag("ContentObject");

        $this->bench->start("TestExport", "buildExportFile_dumpToFile");
        $this->xml->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->filename, false);
        $this->bench->stop("TestExport", "buildExportFile_dumpToFile");

        if ($this->isResultExportingEnabledForTestExport()) {
            $resultwriter = new ilTestResultsToXML($this->test_obj->getTestId(), $this->db, $this->test_obj->getAnonymity());
            $resultwriter->setIncludeRandomTestQuestionsEnabled($this->test_obj->isRandomTest());
            $this->bench->start("TestExport", "buildExportFile_results");
            $resultwriter->xmlDumpFile($this->export_dir . "/" . $this->subdir . "/" . $this->resultsfile, false);
            $this->bench->stop("TestExport", "buildExportFile_results");
        }

        // add media objects which were added with tiny mce
        $this->bench->start("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");
        $this->exportXHTMLMediaObjects($this->export_dir . "/" . $this->subdir);
        $this->bench->stop("QuestionpoolExport", "buildExportFile_saveAdditionalMobs");

        // zip the file
        $this->bench->start("TestExport", "buildExportFile_zipFile");
        ilFileUtils::zip(
            $this->export_dir . "/" . $this->subdir,
            $this->export_dir . "/" . $this->subdir . ".zip"
        );
        $this->bench->stop("TestExport", "buildExportFile_zipFile");

        // destroy writer object
        $this->xml = null;

        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export");
        $this->bench->stop("TestExport", "buildExportFile");

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

    protected function getQuestionQtiXml($questionId): string
    {
        $questionOBJ = assQuestion::instantiateQuestion($questionId);
        $xml = $questionOBJ->toXML(false);

        // still neccessary? there is an include header flag!?
        $xml = preg_replace("/<questestinterop>/", "", $xml);
        $xml = preg_replace("/<\/questestinterop>/", "", $xml);

        return $xml;
    }

    public function exportXHTMLMediaObjects($a_export_dir): void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->test_obj->getId());

        $intro_page_id = $this->test_obj->getMainSettings()->getIntroductionSettings()->getIntroductionPageId();
        if ($intro_page_id !== null) {
            $mobs += ilObjMediaObject::_getMobsOfObject("tst:pg", $intro_page_id);
        }

        $concluding_remarks_page_id = $this->test_obj->getMainSettings()->getFinishingSettings()->getConcludingRemarksPageId();
        if ($concluding_remarks_page_id !== null) {
            $mobs += ilObjMediaObject::_getMobsOfObject("tst:pg", $concluding_remarks_page_id);
        }

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

    protected function populateQuestionSkillAssignmentsXml(
        ilXmlWriter $a_xml_writer,
        ilAssQuestionSkillAssignmentList $assignmentList,
        array $questions
    ) {
        $skillQuestionAssignmentExporter = new ilAssQuestionSkillAssignmentExporter();
        $skillQuestionAssignmentExporter->setXmlWriter($a_xml_writer);
        $skillQuestionAssignmentExporter->setQuestionIds($questions);
        $skillQuestionAssignmentExporter->setAssignmentList($assignmentList);
        $skillQuestionAssignmentExporter->export();
    }

    protected function populateSkillLevelThresholdsXml(
        ilXmlWriter $a_xml_writer,
        ilAssQuestionSkillAssignmentList $assignmentList
    ) {
        $thresholdList = new ilTestSkillLevelThresholdList($this->db);
        $thresholdList->setTestId($this->test_obj->getTestId());
        $thresholdList->loadFromDb();

        $skillLevelThresholdExporter = new ilTestSkillLevelThresholdExporter();
        $skillLevelThresholdExporter->setXmlWriter($a_xml_writer);
        $skillLevelThresholdExporter->setAssignmentList($assignmentList);
        $skillLevelThresholdExporter->setThresholdList($thresholdList);
        $skillLevelThresholdExporter->export();
    }

    protected function buildQuestionSkillAssignmentList(): ilAssQuestionSkillAssignmentList
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->test_obj->getId());
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        return $assignmentList;
    }
}
