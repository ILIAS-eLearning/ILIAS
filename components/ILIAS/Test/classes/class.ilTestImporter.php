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

use ILIAS\TestQuestionPool\Import\TestQuestionsImportTrait;
use ILIAS\Test\TestDIC;
use ILIAS\Test\Logging\TestLogger;

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup components\ILIASLearningModule
 */
class ilTestImporter extends ilXmlImporter
{
    use TestQuestionsImportTrait;
    /**
     * @var array
     */
    public static $finallyProcessedTestsRegistry = [];

    private readonly TestLogger $logger;
    private readonly ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->logger = TestDIC::dic()['logging.logger'];
        $this->db = $DIC['ilDB'];

        parent::__construct();
    }

    /**
     * Import XML
     * @param string          $a_entity
     * @param string          $a_id
     * @param string          $a_xml
     * @param ilImportMapping $a_mapping
     * @return void
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilSaxParserException
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        if ($new_id = (int) $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) {
            // container content
            $new_obj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
            $new_obj->saveToDb(); // this generates test id first time
            $question_parent_obj_id = $new_obj->getId();

            ilSession::set('path_to_container_import_file', $this->getImportDirectory());
            list($importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromContainerImport(
                $this->getImportDirectory()
            );
            $selected_questions = [];
        } else {
            // single object
            $new_id = (int) $a_mapping->getMapping('components/ILIAS/Test', 'tst', 'new_id');
            $new_obj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $question_parent_obj_id = (int) (ilSession::get('tst_import_qst_parent') ?? $new_obj->getId());

            $selected_questions = ilSession::get('tst_import_selected_questions');
            list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile(
                ilSession::get('path_to_import_file')
            );
            ilSession::clear('tst_import_selected_questions');
        }

        $new_obj->loadFromDb();

        if (!file_exists($xmlfile)) {
            $this->logger->error(__METHOD__ . ': Cannot find xml definition: ' . $xmlfile);
            return;
        }
        if (!file_exists($qtifile)) {
            $this->logger->error(__METHOD__ . ': Cannot find xml definition: ' . $qtifile);
            return;
        }

        // start parsing of QTI files
        $qti_parser = new ilQTIParser(
            $importdir,
            $qtifile,
            ilQTIParser::IL_MO_PARSE_QTI,
            $question_parent_obj_id,
            $selected_questions
        );
        $qti_parser->setTestObject($new_obj);
        $qti_parser->startParsing();
        $new_obj = $qti_parser->getTestObject();

        // import page data
        $question_page_parser = new ilQuestionPageParser(
            $new_obj,
            $xmlfile,
            $importdir
        );
        $question_page_parser->setQuestionMapping($qti_parser->getImportMapping());
        $question_page_parser->startParsing();

        foreach ($qti_parser->getQuestionIdMapping() as $oldQuestionId => $newQuestionId) {
            $a_mapping->addMapping(
                "components/ILIAS/Taxonomy",
                "tax_item",
                "tst:quest:$oldQuestionId",
                (string) $newQuestionId
            );

            $a_mapping->addMapping(
                "components/ILIAS/Taxonomy",
                "tax_item_obj_id",
                "tst:quest:$oldQuestionId",
                (string) $new_obj->getId()
            );

            $a_mapping->addMapping(
                "components/ILIAS/Test",
                "quest",
                (string) $oldQuestionId,
                (string) $newQuestionId
            );
        }

        if ($new_obj->isRandomTest()) {
            $new_obj->questions = [];
            $this->importRandomQuestionSetConfig($new_obj, $xmlfile, $a_mapping);
        }


        $results_file_path = ilSession::get("tst_import_results_file");
        // import test results
        if ($results_file_path !== null && file_exists($results_file_path)) {
            $results = new ilTestResultsImportParser($results_file_path, $new_obj, $this->db, $this->logger);
            $results->setQuestionIdMapping($a_mapping->getMappingsOfEntity('components/ILIAS/Test', 'quest'));
            $results->setSrcPoolDefIdMapping($a_mapping->getMappingsOfEntity('components/ILIAS/Test', 'rnd_src_pool_def'));
            $results->startParsing();
        }

        $new_obj->saveToDb(); // this creates test_fi
        $new_obj->update(); // this saves ilObject data

        // import skill assignments
        $importedAssignmentList = $this->importQuestionSkillAssignments($a_mapping, $new_obj, $xmlfile);
        $this->importSkillLevelThresholds($a_mapping, $importedAssignmentList, $new_obj, $xmlfile);

        $a_mapping->addMapping("components/ILIAS/Test", "tst", (string) $a_id, (string) $new_obj->getId());
    }

    /**
     * Final processing
     * @param ilImportMapping $a_mapping
     * @return void
     */
    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $maps = $a_mapping->getMappingsOfEntity("components/ILIAS/Test", "tst");

        foreach ($maps as $old => $new) {
            if ($old == "new_id" || (int) $old <= 0) {
                continue;
            }

            if (isset(self::$finallyProcessedTestsRegistry[$new])) {
                continue;
            }

            $test_obj = ilObjectFactory::getInstanceByObjId((int) $new, false);
            if ($test_obj->isRandomTest()) {
                $this->finalRandomTestTaxonomyProcessing($a_mapping, (string) $old, $new, $test_obj);
            }

            self::$finallyProcessedTestsRegistry[$new] = true;
        }
    }

    protected function finalRandomTestTaxonomyProcessing(
        ilImportMapping $mapping,
        string $oldTstObjId,
        string $newTstObjId,
        ilObjTest $test_obj
    ): void {
        $new_tax_ids = $mapping->getMapping(
            'components/ILIAS/Taxonomy',
            'tax_usage_of_obj',
            $oldTstObjId
        );

        if ($new_tax_ids !== null) {
            $tax_ids = explode(":", $new_tax_ids);

            foreach ($tax_ids as $tid) {
                ilObjTaxonomy::saveUsage((int) $tid, (int) $newTstObjId);
            }
        }

        $srcPoolDefFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $test_obj
        );

        $srcPoolDefList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $test_obj,
            $srcPoolDefFactory
        );

        $srcPoolDefList->loadDefinitions();

        foreach ($srcPoolDefList as $definition) {
            // #21330
            if (!is_array($definition->getMappedTaxonomyFilter()) || 0 === count($definition->getMappedTaxonomyFilter())) {
                continue;
            }

            $definition->setMappedTaxonomyFilter(
                $this->getNewMappedTaxonomyFilter(
                    $mapping,
                    $definition->getMappedTaxonomyFilter()
                )
            );
            $definition->saveToDb();
        }
    }

    /**
     * @param ilImportMapping $mapping
     * @param  array $mappedFilter
     * @return array
     */
    protected function getNewMappedTaxonomyFilter(ilImportMapping $mapping, array $mappedFilter): array
    {
        $newMappedFilter = [];

        foreach ($mappedFilter as $taxId => $taxNodes) {
            $newTaxId = $mapping->getMapping(
                'components/ILIAS/Taxonomy',
                'tax',
                (string) $taxId
            );

            if (!$newTaxId) {
                continue;
            }

            $newMappedFilter[$newTaxId] = [];

            foreach ($taxNodes as $taxNodeId) {
                $newTaxNodeId = $mapping->getMapping(
                    'components/ILIAS/Taxonomy',
                    'tax_tree',
                    (string) $taxNodeId
                );

                if (!$newTaxNodeId) {
                    continue;
                }

                $newMappedFilter[$newTaxId][] = $newTaxNodeId;
            }
        }

        return $newMappedFilter;
    }

    protected function importRandomQuestionSetConfig(ilObjTest $test_obj, $xmlFile, $a_mapping)
    {
        $parser = new ilObjTestXMLParser($xmlFile);
        $parser->setTestOBJ($test_obj);
        $parser->setImportMapping($a_mapping);
        $parser->startParsing();
    }

    /**
     * @param ilImportMapping $mappingRegistry
     * @param ilObjTest $test_obj
     * @param string $xmlfile
     * @return ilAssQuestionSkillAssignmentList
     */
    protected function importQuestionSkillAssignments(ilImportMapping $mapping, ilObjTest $test_obj, $xmlFile): ilAssQuestionSkillAssignmentList
    {
        $parser = new ilAssQuestionSkillAssignmentXmlParser($xmlFile);
        $parser->startParsing();

        $importer = new ilAssQuestionSkillAssignmentImporter();
        $importer->setTargetParentObjId($test_obj->getId());
        $importer->setImportInstallationId((int) $this->getInstallId());
        $importer->setImportMappingRegistry($mapping);
        $importer->setImportMappingComponent('components/ILIAS/Test');
        $importer->setImportAssignmentList($parser->getAssignmentList());

        $importer->import();

        if ($importer->getFailedImportAssignmentList()->assignmentsExist()) {
            $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($test_obj->getId());
            $qsaImportFails->registerFailedImports($importer->getFailedImportAssignmentList());

            $test_obj->getObjectProperties()->storePropertyIsOnline(
                $test_obj->getObjectProperties()->getPropertyIsOnline()->withOffline()
            );
        }

        return $importer->getSuccessImportAssignmentList();
    }

    /**
     * @param ilImportMapping $mapping
     * @param ilAssQuestionSkillAssignmentList $assignmentList
     * @param ilObjTest $test_obj
     * @param $xmlFile
     */
    protected function importSkillLevelThresholds(ilImportMapping $mapping, ilAssQuestionSkillAssignmentList $assignmentList, ilObjTest $test_obj, $xmlFile)
    {
        $parser = new ilTestSkillLevelThresholdXmlParser($xmlFile);
        $parser->initSkillLevelThresholdImportList();
        $parser->startParsing();

        $importer = new ilTestSkillLevelThresholdImporter($this->db);
        $importer->setTargetTestId($test_obj->getTestId());
        $importer->setImportInstallationId((int) $this->getInstallId());
        $importer->setImportMappingRegistry($mapping);
        $importer->setImportedQuestionSkillAssignmentList($assignmentList);
        $importer->setImportThresholdList($parser->getSkillLevelThresholdImportList());
        $importer->import();

        if ($importer->getFailedThresholdImportSkillList()->skillsExist()) {
            $sltImportFails = new ilTestSkillLevelThresholdImportFails($test_obj->getId());
            $sltImportFails->registerFailedImports($importer->getFailedThresholdImportSkillList());

            $test_obj->setOfflineStatus(true);
        }
    }
}
