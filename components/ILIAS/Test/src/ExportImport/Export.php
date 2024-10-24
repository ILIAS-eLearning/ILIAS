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

namespace ILIAS\Test\ExportImport;

use ILIAS\Test\Logging\TestLogger;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\Language\Language;
use ILIAS\FileDelivery\Services as FileDeliveryServices;

/**
 * Export class for tests
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 *
 * @version $Id$
 *
 * @ingroup components\ILIASTest
 */
abstract class Export implements Exporter
{
    private string $export_dir;
    private string $subdir;
    private string $qti_filename;
    private string $filename;
    private string $resultsfile;

    private ?\ilXmlWriter $xml = null;

    protected bool $result_exporting_enabled = false;

    protected ?\ilTestParticipantList $forced_access_filtered_participant_list = null;

    protected string $inst_id;

    public function __construct(
        protected readonly Language $lng,
        protected readonly \ilDBInterface $db,
        protected readonly \ilBenchmark $bench,
        protected readonly TestLogger $logger,
        protected readonly \ilTree $tree,
        protected readonly \ilComponentRepository $component_repository,
        protected readonly GeneralQuestionPropertiesRepository $questionrepository,
        protected readonly FileDeliveryServices $file_delivery,
        protected readonly \ilObjTest $test_obj
    ) {
        $this->inst_id = (string) IL_INST_ID;

        $date = time();
        $this->export_dir = $test_obj->getExportDirectory();
        $this->subdir = "{$date}__{$this->inst_id}__tst_{$this->test_obj->getId()}";
        $this->filename = $this->subdir . '.xml';
        $this->resultsfile = "{$date}__{$this->inst_id}__results_{$this->test_obj->getId()}.xml";
        $this->qti_filename = "{$date}__{$this->inst_id}__qti_{$this->test_obj->getId()}.xml";
        $this->filename = $this->subdir . '.xml';
    }

    abstract protected function initXmlExport();
    abstract protected function getQuestionIds();
    abstract protected function populateQuestionSetConfigXml(\ilXmlWriter $xmlWriter);
    abstract protected function getQuestionsQtiXml();

    private function isResultExportingEnabled(): bool
    {
        return $this->result_exporting_enabled;
    }

    public function withResultExportingEnabled(bool $enable): self
    {
        $clone = clone $this;
        $clone->result_exporting_enabled = $enable;
        return $clone;
    }

    public function write(): ?string
    {
        $this->bench->start('TestExport', 'write');

        $this->initXmlExport();

        $this->xml = new \ilXmlWriter();

        // set dtd definition
        $this->xml->xmlSetDtdDef('<!DOCTYPE Test SYSTEM "http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd">');

        // set generated comment
        $this->xml->xmlSetGenCmt('Export of ILIAS Test '
            . "{$this->test_obj->getId()} of installation {$this->inst_id}");

        // set xml header
        $this->xml->xmlHeader();

        $this->xml->xmlStartTag('ContentObject', ['Type' => 'Test']);

        // create directories
        $this->test_obj->createExportDirectory();
        \ilFileUtils::makeDir($this->export_dir . '/' . $this->subdir);
        \ilFileUtils::makeDir($this->export_dir . '/' . $this->subdir . '/objects');

        $exp_log = new \ilLog(
            $this->test_obj->getExportDirectory(),
            'export.log'
        );
        $exp_log->delete();
        $exp_log->setLogFormat('');
        $exp_log->write(date('[y-m-d H:i:s] ') . 'Start Export');

        // write qti file
        $qti_file = fopen($this->export_dir . '/' . $this->subdir . '/' . $this->qti_filename, 'wb');
        fwrite($qti_file, $this->getQtiXml());
        fclose($qti_file);

        // get xml content
        $this->bench->start('TestExport', 'write_getXML');
        $this->test_obj->exportPagesXML(
            $this->xml,
            $this->inst_id,
            $this->export_dir . '/' . $this->subdir,
            $exp_log
        );
        $this->bench->stop('TestExport', 'write_getXML');

        $this->populateQuestionSetConfigXml($this->xml);

        $assignment_list = $this->buildQuestionSkillAssignmentList();
        $this->populateQuestionSkillAssignmentsXml($this->xml, $assignment_list, $this->getQuestionIds());
        $this->populateSkillLevelThresholdsXml($this->xml, $assignment_list);

        $this->xml->xmlEndTag('ContentObject');

        $this->bench->start('TestExport', 'write_dumpToFile');
        $this->xml->xmlDumpFile($this->export_dir . '/' . $this->subdir . '/' . $this->filename, false);
        $this->bench->stop('TestExport', 'write_dumpToFile');

        if ($this->isResultExportingEnabled()) {
            $resultwriter = new \ilTestResultsToXML($this->test_obj->getTestId(), $this->db, $this->test_obj->getAnonymity());
            $resultwriter->setIncludeRandomTestQuestionsEnabled($this->test_obj->isRandomTest());
            $this->bench->start('TestExport', 'write_results');
            $resultwriter->xmlDumpFile($this->export_dir . '/' . $this->subdir . '/' . $this->resultsfile, false);
            $this->bench->stop('TestExport', 'write_results');
        }

        // add media objects which were added with tiny mce
        $this->bench->start('QuestionpoolExport', 'write_saveAdditionalMobs');
        $this->exportXHTMLMediaObjects($this->export_dir . '/' . $this->subdir);
        $this->bench->stop('QuestionpoolExport', 'write_saveAdditionalMobs');

        // zip the file
        $this->bench->start('TestExport', 'write_zipFile');
        \ilFileUtils::zip(
            $this->export_dir . '/' . $this->subdir,
            $this->export_dir . '/' . $this->subdir . '.zip'
        );
        $this->bench->stop('TestExport', 'write_zipFile');

        // destroy writer object
        $this->xml = null;

        $exp_log->write(date('[y-m-d H:i:s] ') . 'Finished Export');
        $this->bench->stop('TestExport', 'write');

        return $this->export_dir . '/' . $this->subdir . '.zip';
    }

    public function deliver(): void
    {
        if (($path = $this->write()) === null) {
            return;
        }
        $this->file_delivery->legacyDelivery()->attached(
            $path,
            null,
            null,
            true
        );
        $this->file_delivery->deliver();
    }

    protected function getQtiXml()
    {
        $tst_qti_xml = $this->test_obj->toXML();
        $qst_qti_xml = $this->getQuestionsQtiXml();

        if (strpos($tst_qti_xml, '</section>') !== false) {
            $qti_xml = str_replace('</section>', "{$qst_qti_xml}</section>", $tst_qti_xml);
        } else {
            $qti_xml = str_replace("<section ident=\"1\"/>", "<section ident=\"1\">\n{$qst_qti_xml}</section>", $tst_qti_xml);
        }

        return $qti_xml;
    }

    protected function getQuestionQtiXml(int $question_id): string
    {
        $question_obj = \assQuestion::instantiateQuestion($question_id);
        $xml = $question_obj->toXML(false);

        // still neccessary? there is an include header flag!?
        $xml = preg_replace('/<questestinterop>/', '', $xml);
        $xml = preg_replace('/<\/questestinterop>/', '', $xml);

        return $xml;
    }

    public function exportXHTMLMediaObjects($a_export_dir): void
    {
        $mobs = \ilObjMediaObject::_getMobsOfObject('tst:html', $this->test_obj->getId());

        $intro_page_id = $this->test_obj->getMainSettings()->getIntroductionSettings()->getIntroductionPageId();
        if ($intro_page_id !== null) {
            $mobs += \ilObjMediaObject::_getMobsOfObject('tst:pg', $intro_page_id);
        }

        $concluding_remarks_page_id = $this->test_obj->getMainSettings()->getFinishingSettings()->getConcludingRemarksPageId();
        if ($concluding_remarks_page_id !== null) {
            $mobs += \ilObjMediaObject::_getMobsOfObject('tst:pg', $concluding_remarks_page_id);
        }

        foreach ($mobs as $mob) {
            if (\ilObjMediaObject::_exists($mob)) {
                $mob_obj = new \ilObjMediaObject($mob);
                $mob_obj->exportFiles($a_export_dir);
                unset($mob_obj);
            }
        }
        foreach ($this->getQuestionIds() as $question_id) {
            $mobs = \ilObjMediaObject::_getMobsOfObject('qpl:html', $question_id);
            foreach ($mobs as $mob) {
                if (\ilObjMediaObject::_exists($mob)) {
                    $mob_obj = new \ilObjMediaObject($mob);
                    $mob_obj->exportFiles($a_export_dir);
                    unset($mob_obj);
                }
            }
        }
    }

    protected function populateQuestionSkillAssignmentsXml(
        \ilXmlWriter $a_xml_writer,
        \ilAssQuestionSkillAssignmentList $assignment_list,
        array $questions
    ) {
        $skill_question_assignment_exporter = new \ilAssQuestionSkillAssignmentExporter();
        $skill_question_assignment_exporter->setXmlWriter($a_xml_writer);
        $skill_question_assignment_exporter->setQuestionIds($questions);
        $skill_question_assignment_exporter->setAssignmentList($assignment_list);
        $skill_question_assignment_exporter->export();
    }

    protected function populateSkillLevelThresholdsXml(
        \ilXmlWriter $a_xml_writer,
        \ilAssQuestionSkillAssignmentList $assignment_list
    ) {
        $threshold_list = new \ilTestSkillLevelThresholdList($this->db);
        $threshold_list->setTestId($this->test_obj->getTestId());
        $threshold_list->loadFromDb();

        $skill_level_threshold_exporter = new \ilTestSkillLevelThresholdExporter();
        $skill_level_threshold_exporter->setXmlWriter($a_xml_writer);
        $skill_level_threshold_exporter->setAssignmentList($assignment_list);
        $skill_level_threshold_exporter->setThresholdList($threshold_list);
        $skill_level_threshold_exporter->export();
    }

    protected function buildQuestionSkillAssignmentList(): \ilAssQuestionSkillAssignmentList
    {
        $assignment_list = new \ilAssQuestionSkillAssignmentList($this->db);
        $assignment_list->setParentObjId($this->test_obj->getId());
        $assignment_list->loadFromDb();
        $assignment_list->loadAdditionalSkillData();

        return $assignment_list;
    }
}
