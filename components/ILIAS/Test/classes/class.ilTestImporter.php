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

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup components\ILIASLearningModule
 */
class ilTestImporter extends ilXmlImporter
{
    /**
     * @var array
     */
    public static $finallyProcessedTestsRegistry = [];

    private ilLogger $log;
    private ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC['ilLog'];
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
        ilObjTest::_setImportDirectory($this->getImportDirectoryContainer());

        if ($new_id = (int) $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            // container content
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            ilSession::set('tst_import_subdir', $this->getImportPackageName());
            $newObj->saveToDb(); // this generates test id first time
            $questionParentObjId = $newObj->getId();
        } else {
            // single object
            $new_id = (int) $a_mapping->getMapping('components/ILIAS/Test', 'tst', 'new_id');
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);

            $questionParentObjId = ilSession::get('tst_import_qst_parent') ?? $newObj->getId();
        }

        $newObj->loadFromDb();

        list($xml_file, $qti_file) = $this->parseXmlFileNames();

        if (!@file_exists($xml_file)) {
            $this->log->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
            return;
        }
        if (!@file_exists($qti_file)) {
            $this->log->write(__METHOD__ . ': Cannot find xml definition: ' . $qti_file);
            return;
        }

        /* @var ilObjTest $newObj */

        // FIXME: Copied from ilObjTestGUI::importVerifiedFileObject
        // TODO: move all logic to ilObjTest::importVerifiedFile and call
        // this method from ilObjTestGUI and ilTestImporter
        $newObj->getMarkSchema()->flush();

        $idents = ilSession::get('tst_import_idents');

        // start parsing of QTI files
        $qtiParser = new ilQTIParser($qti_file, ilQTIParser::IL_MO_PARSE_QTI, $questionParentObjId, $idents);
        $qtiParser->setTestObject($newObj);
        $qtiParser->startParsing();
        $newObj = $qtiParser->getTestObject();

        // import page data
        $questionPageParser = new ilQuestionPageParser($newObj, $xml_file, basename($this->getImportDirectory()));
        $questionPageParser->setQuestionMapping($qtiParser->getImportMapping());
        $questionPageParser->startParsing();

        foreach ($qtiParser->getQuestionIdMapping() as $oldQuestionId => $newQuestionId) {
            $a_mapping->addMapping(
                "Services/Taxonomy",
                "tax_item",
                "tst:quest:$oldQuestionId",
                (string) $newQuestionId
            );

            $a_mapping->addMapping(
                "Services/Taxonomy",
                "tax_item_obj_id",
                "tst:quest:$oldQuestionId",
                (string) $newObj->getId()
            );

            $a_mapping->addMapping(
                "components/ILIAS/Test",
                "quest",
                (string) $oldQuestionId,
                (string) $newQuestionId
            );
        }

        if ($newObj->isRandomTest()) {
            $newObj->questions = array();
            $this->importRandomQuestionSetConfig($newObj, $xml_file, $a_mapping);
        }

        // import test results
        if (@file_exists(ilSession::get("tst_import_results_file"))) {
            $results = new ilTestResultsImportParser(ilSession::get("tst_import_results_file"), $newObj, $this->db, $this->log);
            $results->setQuestionIdMapping($a_mapping->getMappingsOfEntity('components/ILIAS/Test', 'quest'));
            $results->setSrcPoolDefIdMapping($a_mapping->getMappingsOfEntity('components/ILIAS/Test', 'rnd_src_pool_def'));
            $results->startParsing();
        }

        $newObj->saveToDb(); // this creates test_fi
        $newObj->update(); // this saves ilObject data

        // import skill assignments
        $importedAssignmentList = $this->importQuestionSkillAssignments($a_mapping, $newObj, $xml_file);
        $this->importSkillLevelThresholds($a_mapping, $importedAssignmentList, $newObj, $xml_file);

        $a_mapping->addMapping("components/ILIAS/Test", "tst", (string) $a_id, (string) $newObj->getId());
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

            /* @var ilObjTest $testOBJ */
            $testOBJ = ilObjectFactory::getInstanceByObjId((int) $new, false);
            if ($testOBJ->isRandomTest()) {
                $this->finalRandomTestTaxonomyProcessing($a_mapping, (string) $old, $new, $testOBJ);
            }

            self::$finallyProcessedTestsRegistry[$new] = true;
        }
    }

    protected function finalRandomTestTaxonomyProcessing(ilImportMapping $mapping, string $oldTstObjId, string $newTstObjId, ilObjTest $testOBJ)
    {
        $new_tax_ids = $mapping->getMapping(
            'Services/Taxonomy',
            'tax_usage_of_obj',
            $oldTstObjId
        );

        if ($new_tax_ids !== false) {
            $tax_ids = explode(":", $new_tax_ids);

            foreach ($tax_ids as $tid) {
                ilObjTaxonomy::saveUsage((int) $tid, (int) $newTstObjId);
            }
        }

        $srcPoolDefFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $testOBJ
        );

        $srcPoolDefList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $testOBJ,
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
        $newMappedFilter = array();

        foreach ($mappedFilter as $taxId => $taxNodes) {
            $newTaxId = $mapping->getMapping(
                'Services/Taxonomy',
                'tax',
                $taxId
            );

            if (!$newTaxId) {
                continue;
            }

            $newMappedFilter[$newTaxId] = array();

            foreach ($taxNodes as $taxNodeId) {
                $newTaxNodeId = $mapping->getMapping(
                    'Services/Taxonomy',
                    'tax_tree',
                    $taxNodeId
                );

                if (!$newTaxNodeId) {
                    continue;
                }

                $newMappedFilter[$newTaxId][] = $newTaxNodeId;
            }
        }

        return $newMappedFilter;
    }

    /**
     * Create qti and xml file name
     * @return array
     */
    protected function parseXmlFileNames(): array
    {
        $this->log->write(__METHOD__ . ': ' . $this->getImportDirectory());

        $basename = basename($this->getImportDirectory());

        $xml = $this->getImportDirectory() . '/' . $basename . '.xml';
        $qti = $this->getImportDirectory() . '/' . preg_replace('/test|tst/', 'qti', $basename) . '.xml';

        return array($xml,$qti);
    }

    private function getImportDirectoryContainer(): string
    {
        $dir = $this->getImportDirectory();
        $dir = dirname($dir);
        return $dir;
    }

    private function getImportPackageName(): string
    {
        $dir = $this->getImportDirectory();
        $name = basename($dir);
        return $name;
    }

    protected function importRandomQuestionSetConfig(ilObjTest $testOBJ, $xmlFile, $a_mapping)
    {
        $parser = new ilObjTestXMLParser($xmlFile);
        $parser->setTestOBJ($testOBJ);
        $parser->setImportMapping($a_mapping);
        $parser->startParsing();
    }

    /**
     * @param ilImportMapping $mappingRegistry
     * @param ilObjTest $testOBJ
     * @param string $xmlfile
     * @return ilAssQuestionSkillAssignmentList
     */
    protected function importQuestionSkillAssignments(ilImportMapping $mapping, ilObjTest $testOBJ, $xmlFile): ilAssQuestionSkillAssignmentList
    {
        $parser = new ilAssQuestionSkillAssignmentXmlParser($xmlFile);
        $parser->startParsing();

        $importer = new ilAssQuestionSkillAssignmentImporter();
        $importer->setTargetParentObjId($testOBJ->getId());
        $importer->setImportInstallationId((int) $this->getInstallId());
        $importer->setImportMappingRegistry($mapping);
        $importer->setImportMappingComponent('components/ILIAS/Test');
        $importer->setImportAssignmentList($parser->getAssignmentList());

        $importer->import();

        if ($importer->getFailedImportAssignmentList()->assignmentsExist()) {
            $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($testOBJ->getId());
            $qsaImportFails->registerFailedImports($importer->getFailedImportAssignmentList());

            $testOBJ->setOnline(false);
        }

        return $importer->getSuccessImportAssignmentList();
    }

    /**
     * @param ilImportMapping $mapping
     * @param ilAssQuestionSkillAssignmentList $assignmentList
     * @param ilObjTest $testOBJ
     * @param $xmlFile
     */
    protected function importSkillLevelThresholds(ilImportMapping $mapping, ilAssQuestionSkillAssignmentList $assignmentList, ilObjTest $testOBJ, $xmlFile)
    {
        $parser = new ilTestSkillLevelThresholdXmlParser($xmlFile);
        $parser->startParsing();

        $importer = new ilTestSkillLevelThresholdImporter($this->db);
        $importer->setTargetTestId($testOBJ->getTestId());
        $importer->setImportInstallationId((int) $this->getInstallId());
        $importer->setImportMappingRegistry($mapping);
        $importer->setImportedQuestionSkillAssignmentList($assignmentList);
        $importer->setImportThresholdList($parser->getSkillLevelThresholdImportList());
        $importer->import();

        if ($importer->getFailedThresholdImportSkillList()->skillsExist()) {
            $sltImportFails = new ilTestSkillLevelThresholdImportFails($testOBJ->getId());
            $sltImportFails->registerFailedImports($importer->getFailedThresholdImportSkillList());

            $testOBJ->setOfflineStatus(true);
        }
    }
}
