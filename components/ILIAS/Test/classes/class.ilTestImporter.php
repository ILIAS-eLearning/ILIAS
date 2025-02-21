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

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $results_file_path = null;
        if ($new_id = (int) $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) {
            // container content
            $new_obj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
            $new_obj->saveToDb();

            ilSession::set('path_to_container_import_file', $this->getImportDirectory());
            [$importdir, $xmlfile, $qtifile] = $this->buildImportDirectoriesFromContainerImport(
                $this->getImportDirectory()
            );
            $selected_questions = [];
        } else {
            // single object
            $new_id = (int) $a_mapping->getMapping('components/ILIAS/Test', 'tst', 'new_id');
            $new_obj = ilObjectFactory::getInstanceByObjId($new_id, false);

            $selected_questions = ilSession::get('tst_import_selected_questions') ?? [];
            [$subdir, $importdir, $xmlfile, $qtifile] = $this->buildImportDirectoriesFromImportFile(
                ilSession::get('path_to_import_file')
            );
            $results_file_path = $this->buildResultsFilePath($importdir, $subdir);
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
            $new_obj->getId(),
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

        $a_mapping = $this->addTaxonomyAndQuestionsMapping($qti_parser->getQuestionIdMapping(), $new_obj->getId(), $a_mapping);

        if ($new_obj->isRandomTest()) {
            $this->importRandomQuestionSetConfig($new_obj, $xmlfile, $a_mapping);
        }

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
        $a_mapping->addMapping(
            "components/ILIAS/MetaData",
            "md",
            $a_id . ":0:tst",
            $new_obj->getId() . ":0:tst"
        );
    }

    public function addTaxonomyAndQuestionsMapping(
        array $question_id_mapping,
        int $new_obj_id,
        ilImportMapping $mapping
    ): ilImportMapping {
        foreach ($question_id_mapping as $old_question_id => $new_question_id) {
            $mapping->addMapping(
                'components/ILIAS/Taxonomy',
                'tax_item',
                "tst:quest:{$old_question_id}",
                (string) $new_question_id
            );

            $mapping->addMapping(
                'components/ILIAS/Taxonomy',
                'tax_item_obj_id',
                "tst:quest:{$old_question_id}",
                (string) $new_obj_id
            );

            $mapping->addMapping(
                'components/ILIAS/Test',
                'quest',
                (string) $old_question_id,
                (string) $new_question_id
            );
        }

        return $mapping;
    }

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
        string $old_tst_obj_id,
        string $new_tst_obj_id,
        ilObjTest $test_obj
    ): void {
        $new_tax_ids = $mapping->getMapping(
            'components/ILIAS/Taxonomy',
            'tax_usage_of_obj',
            $old_tst_obj_id
        );

        if ($new_tax_ids !== null) {
            foreach (explode(':', $new_tax_ids) as $tax_id) {
                ilObjTaxonomy::saveUsage((int) $tax_id, (int) $new_tst_obj_id);
            }
        }

        $src_pool_def_list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $test_obj,
            new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                $this->db,
                $test_obj
            )
        );

        $src_pool_def_list->loadDefinitions();

        foreach ($src_pool_def_list as $definition) {
            $mapped_taxonomy_filter = $definition->getMappedTaxonomyFilter();
            if ($mapped_taxonomy_filter === []) {
                continue;
            }

            $definition->setMappedTaxonomyFilter(
                $this->getNewMappedTaxonomyFilter(
                    $mapping,
                    $mapped_taxonomy_filter
                )
            );
            $definition->saveToDb();
        }
    }

    protected function getNewMappedTaxonomyFilter(
        ilImportMapping $mapping,
        array $mapped_filter
    ): array {
        $new_mapped_filter = [];
        foreach ($mapped_filter as $tax_id => $tax_nodes) {
            $new_tax_id = $mapping->getMapping(
                'components/ILIAS/Taxonomy',
                'tax',
                (string) $tax_id
            );

            if ($new_tax_id === null) {
                continue;
            }

            $new_mapped_filter[$new_tax_id] = [];

            foreach ($tax_nodes as $tax_node_id) {
                $new_tax_node_id = $mapping->getMapping(
                    'components/ILIAS/Taxonomy',
                    'tax_tree',
                    (string) $tax_node_id
                );

                if ($new_tax_node_id === null) {
                    continue;
                }

                $new_mapped_filter[$new_tax_id][] = $new_tax_node_id;
            }
        }

        return $new_mapped_filter;
    }

    public function importRandomQuestionSetConfig(
        ilObjTest $test_obj,
        ?string $xml_file,
        \ilImportMapping $a_mapping
    ): void {
        $test_obj->questions = [];
        $parser = new ilObjTestXMLParser($xml_file);
        $parser->setTestOBJ($test_obj);
        $parser->setImportMapping($a_mapping);
        $parser->startParsing();
    }

    protected function importQuestionSkillAssignments(
        ilImportMapping $mapping,
        ilObjTest $test_obj,
        ?string $xml_file
    ): ilAssQuestionSkillAssignmentList {
        $parser = new ilAssQuestionSkillAssignmentXmlParser($xml_file);
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

    protected function importSkillLevelThresholds(
        ilImportMapping $mapping,
        ilAssQuestionSkillAssignmentList $assignment_list,
        ilObjTest $test_obj,
        ?string $xml_file
    ): void {
        $parser = new ilTestSkillLevelThresholdXmlParser($xml_file);
        $parser->initSkillLevelThresholdImportList();
        $parser->startParsing();

        $importer = new ilTestSkillLevelThresholdImporter($this->db);
        $importer->setTargetTestId($test_obj->getTestId());
        $importer->setImportInstallationId((int) $this->getInstallId());
        $importer->setImportMappingRegistry($mapping);
        $importer->setImportedQuestionSkillAssignmentList($assignment_list);
        $importer->setImportThresholdList($parser->getSkillLevelThresholdImportList());
        $importer->import();

        if ($importer->getFailedThresholdImportSkillList()->skillsExist()) {
            $sltImportFails = new ilTestSkillLevelThresholdImportFails($test_obj->getId());
            $sltImportFails->registerFailedImports($importer->getFailedThresholdImportSkillList());

            $test_obj->setOfflineStatus(true);
        }
    }
}
