<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesSurvey
 */
class ilSurveyImporter extends ilXmlImporter
{
    /**
     * @var Logger
     */
    protected $log;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->log = $DIC["ilLog"];
    }

    /**
     * @var ilObjSurvey
     */
    protected static $survey;

    /**
     * @var ilLogger
     */
    protected $svy_log;

    /**
     * Init
     *
     * @param
     * @return
     */
    public function init()
    {
        include_once("./Modules/Survey/classes/class.ilSurveyDataSet.php");
        $this->ds = new ilSurveyDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImport($this);

        $this->svy_log = ilLoggerFactory::getLogger("svy");
    }


    /**
     * Set current survey object (being imported). This is done statically,
     * since the survey import uses multiple input files being processed for every survey
     * and all of these need the current survey object (ilSurveyImporter is intantiated multiple times)
     *
     * @param ilObjSurvey $a_val survey
     */
    public function setSurvey(ilObjSurvey $a_val)
    {
        self::$survey = $a_val;
    }

    /**
     * Get current survey object
     *
     * @return ilObjSurvey survey
     */
    public function getSurvey()
    {
        return self::$survey;
    }

    /**
     * Import XML
     *
     * @param $a_entity
     * @param $a_id
     * @param $a_xml
     * @param ilImportMapping $a_mapping
     * @return bool|string
     * @throws ilDatabaseException
     * @throws ilImportException
     * @throws ilObjectNotFoundException
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        if ($a_entity == "svy") {
            // Container import => test object already created
            if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
                $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            #$newObj->setImportDirectory(dirname(rtrim($this->getImportDirectory(),'/')));
            } else {    // case ii, non container
                $new_id = $a_mapping->getMapping("Modules/Survey", "svy", 0);
                $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            }
            $this->setSurvey($newObj);

            include_once "./Services/Survey/classes/class.SurveyImportParser.php";

            list($xml_file) = $this->parseXmlFileNames();

            if (!@file_exists($xml_file)) {
                $GLOBALS['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
                return false;
            }
            $GLOBALS['ilLog']->write("getQuestionPoolID = " . $this->getImport()->getConfig("Modules/Survey")->getQuestionPoolID());

            $import = new SurveyImportParser($this->getImport()->getConfig("Modules/Survey")->getQuestionPoolID(), $xml_file, true, $a_mapping);

            $import->setSurveyObject($newObj);
            $import->startParsing();

            $this->svy_log->debug("is array import_mob_xml: -" . is_array($_SESSION["import_mob_xhtml"]) . "-");

            // this is "written" by Services/Survey/classes/class.ilSurveyImportParser
            if (is_array($_SESSION["import_mob_xhtml"])) {
                include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
                include_once "./Services/RTE/classes/class.ilRTE.php";
                include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
                foreach ($_SESSION["import_mob_xhtml"] as $mob) {
                    $this->svy_log->debug("import mob xhtml, type: " . $mob["type"] . ", id: " . $mob["mob"]);

                    if (!$mob["type"]) {
                        $mob["type"] = "svy:html";
                    }

                    $importfile = dirname($xml_file) . "/" . $mob["uri"];
                    $this->svy_log->debug("import file: " . $importfile);

                    if (file_exists($importfile)) {
                        $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);

                        // survey mob
                        if ($mob["type"] == "svy:html") {
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
            $a_mapping->addMapping("Modules/Survey", "svy", (int) $a_id, (int) $newObj->getId());
            return $newObj->getId();
        } else {
            include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $this->ds,
                $a_mapping
            );
        }

        return true;
    }
    
    
    /**
     * Create qti and xml file name
     * @return array
     */
    protected function parseXmlFileNames()
    {
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $this->getImportDirectory());
        
        $basename = basename($this->getImportDirectory());
        $xml = $this->getImportDirectory() . '/' . $basename . '.xml';
        
        return array($xml);
    }
}
