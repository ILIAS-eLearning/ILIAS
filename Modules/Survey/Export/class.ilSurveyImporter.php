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
 * Importer class for files
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilSurveyImporter extends ilXmlImporter
{
    protected ilSurveyDataSet $ds;
    protected ilLogger $log;
    protected static ilObjSurvey $survey;
    protected ilLogger $svy_log;
    protected \ILIAS\SurveyQuestionPool\Export\ImportManager $spl_import_manager;

    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->log = $DIC["ilLog"];

        $this->spl_import_manager = $DIC->surveyQuestionPool()
            ->internal()
            ->domain()
            ->import();
    }


    public function init() : void
    {
        $this->ds = new ilSurveyDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImport($this);

        $this->svy_log = ilLoggerFactory::getLogger("svy");
    }


    /**
     * Set current survey object (being imported). This is done statically,
     * since the survey import uses multiple input files being processed for every survey
     * and all of these need the current survey object (ilSurveyImporter is intantiated multiple times)
     */
    public function setSurvey(ilObjSurvey $a_val) : void
    {
        self::$survey = $a_val;
    }

    public function getSurvey() : ilObjSurvey
    {
        return self::$survey;
    }

    /**
     * Import XML
     * @throws ilDatabaseException
     * @throws ilImportException
     * @throws ilObjectNotFoundException
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        if ($a_entity === "svy") {
            // Container import => test object already created
            if (!($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id))) {    // case ii, non container
                $new_id = $a_mapping->getMapping("Modules/Survey", "svy", 0);
            }
            /** @var ilObjSurvey $newObj */
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $this->setSurvey($newObj);


            [$xml_file] = $this->parseXmlFileNames();

            if (!file_exists($xml_file)) {
                $GLOBALS['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
                return;
            }
            $GLOBALS['ilLog']->write("getQuestionPoolID = " . $this->getImport()->getConfig("Modules/Survey")->getQuestionPoolID());

            $import = new SurveyImportParser(
                $this->getImport()->getConfig("Modules/Survey")->getQuestionPoolID(),
                $xml_file,
                true,
                $a_mapping
            );

            $import->setSurveyObject($newObj);
            $import->startParsing();


            // this is "written" by Services/Survey/classes/class.ilSurveyImportParser
            $mobs = $this->spl_import_manager->getMobs();
            if (count($mobs) > 0) {
                foreach ($mobs as $mob) {
                    $this->svy_log->debug("import mob xhtml, type: " . $mob["type"] . ", id: " . $mob["mob"]);

                    if (!isset($mob["type"])) {
                        $mob["type"] = "svy:html";
                    }

                    $importfile = dirname($xml_file) . "/" . $mob["uri"];
                    $this->svy_log->debug("import file: " . $importfile);

                    if (file_exists($importfile)) {
                        $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);

                        // survey mob
                        if ($mob["type"] === "svy:html") {
                            ilObjMediaObject::_saveUsage($media_object->getId(), "svy:html", $newObj->getId());
                            $this->svy_log->debug("old introduction: " . $newObj->getIntroduction());
                            $newObj->setIntroduction(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $newObj->getIntroduction()));
                            $newObj->setOutro(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $newObj->getOutro()));

                            $this->svy_log->debug("new introduction: " . $newObj->getIntroduction());
                        } elseif ($import->questions[$mob["id"]]) {
                            $new_qid = $import->questions[$mob["id"]];
                            ilObjMediaObject::_saveUsage($media_object->getId(), $mob["type"], $new_qid);
                            $new_question = SurveyQuestion::_instanciateQuestion($new_qid);
                            $qtext = $new_question->getQuestiontext();

                            $this->svy_log->debug("old question text: " . $qtext);

                            $qtext = ilRTE::_replaceMediaObjectImageSrc($qtext, 0);
                            $qtext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $qtext);
                            $qtext = ilRTE::_replaceMediaObjectImageSrc($qtext, 1);
                            $new_question->setQuestiontext($qtext);
                            $new_question->saveToDb();

                            $this->svy_log->debug("new question text: " . $qtext);

                            // also fix existing original in pool
                            if ($new_question->getOriginalId()) {
                                $pool_question = SurveyQuestion::_instanciateQuestion($new_question->getOriginalId());
                                $pool_question->setQuestiontext($qtext);
                                $pool_question->saveToDb();
                            }
                        }
                    } else {
                        $this->svy_log->error("Error: Could not open XHTML mob file for test introduction during test import. File $importfile does not exist!");
                    }
                }
                $newObj->setIntroduction(ilRTE::_replaceMediaObjectImageSrc($newObj->getIntroduction(), 1));
                $newObj->setOutro(ilRTE::_replaceMediaObjectImageSrc($newObj->getOutro(), 1));
                $newObj->saveToDb();
            }
            $a_mapping->addMapping("Modules/Survey", "svy", (int) $a_id, $newObj->getId());
        } else {
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $this->ds,
                $a_mapping
            );
        }
    }
    
    
    /**
     * Create qti and xml file name
     */
    protected function parseXmlFileNames() : array
    {
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $this->getImportDirectory());
        
        $basename = basename($this->getImportDirectory());
        $xml = $this->getImportDirectory() . '/' . $basename . '.xml';
        
        return array($xml);
    }
}
