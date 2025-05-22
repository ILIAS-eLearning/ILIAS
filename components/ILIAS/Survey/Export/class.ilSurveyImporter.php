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


    public function init(): void
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
    public function setSurvey(ilObjSurvey $a_val): void
    {
        self::$survey = $a_val;
    }

    public function getSurvey(): ilObjSurvey
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
    ): void {
        if ($a_entity === "svy") {
            // Container import => test object already created
            if (!($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id))) {    // case ii, non container
                $new_id = $a_mapping->getMapping("components/ILIAS/Survey", "svy", 0);
            }
            /** @var ilObjSurvey $newObj */
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $this->setSurvey($newObj);


            [$xml_file] = $this->parseXmlFileNames();

            if (!file_exists($xml_file)) {
                $GLOBALS['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
                return;
            }
            $GLOBALS['ilLog']->write("getQuestionPoolID = " . $this->getImport()->getConfig("components/ILIAS/Survey")->getQuestionPoolID());

            $import = new SurveyImportParser(
                $this->getImport()->getConfig("components/ILIAS/Survey")->getQuestionPoolID(),
                $xml_file,
                true,
                $a_mapping
            );

            $import->setSurveyObject($newObj);
            $import->startParsing();

            $a_mapping->addMapping("components/ILIAS/Survey", "svy", (int) $a_id, $newObj->getId());
            $a_mapping->addMapping(
                "components/ILIAS/MetaData",
                "md",
                $a_id . ":0:svy",
                $newObj->getId() . ":0:svy"
            );
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
    protected function parseXmlFileNames(): array
    {
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $this->getImportDirectory());

        $basename = basename($this->getImportDirectory());
        $xml = $this->getImportDirectory() . '/' . $basename . '.xml';

        return array($xml);
    }
}
