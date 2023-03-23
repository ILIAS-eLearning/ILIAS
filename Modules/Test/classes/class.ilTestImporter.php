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
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilTestImporter extends ilXmlImporter
{
    /**
     * @var array
     */
    public static $finallyProcessedTestsRegistry = array();

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

        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            // container content
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            ilSession::set('tst_import_subdir', $this->getImportPackageName());
            $newObj->saveToDb(); // this generates test id first time
            $questionParentObjId = $newObj->getId();
        } else {
            // single object
            $new_id = $a_mapping->getMapping('Modules/Test', 'tst', 'new_id');
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);

            $questionParentObjId = ilSession::get('tst_import_qst_parent') ?? $newObj->getId();
        }

        $newObj->loadFromDb();

        list($xml_file, $qti_file) = $this->parseXmlFileNames();

        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if (!@file_exists($xml_file)) {
            $DIC['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
            return;
        }
        if (!@file_exists($qti_file)) {
            $DIC['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $qti_file);
            return;
        }

        /* @var ilObjTest $newObj */

        // FIXME: Copied from ilObjTestGUI::importVerifiedFileObject
        // TODO: move all logic to ilObjTest::importVerifiedFile and call
        // this method from ilObjTestGUI and ilTestImporter
        $newObj->mark_schema->flush();

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
                $newQuestionId
            );

            $a_mapping->addMapping(
                "Services/Taxonomy",
                "tax_item_obj_id",
                "tst:quest:$oldQuestionId",
                $newObj->getId()
            );

            $a_mapping->addMapping(
                "Modules/Test",
                "quest",
                $oldQuestionId,
                $newQuestionId
            );
        }

        if ($newObj->isRandomTest()) {
            $newObj->questions = array();
            $this->importRandomQuestionSetConfig($newObj, $xml_file, $a_mapping);
        }

        // import test results
        if (@file_exists(ilSession::get("tst_import_results_file"))) {
            $results = new ilTestResultsImportParser(ilSession::get("tst_import_results_file"), $newObj);
            $results->setQuestionIdMapping($a_mapping->getMappingsOfEntity('Modules/Test', 'quest'));
            $results->setSrcPoolDefIdMapping($a_mapping->getMappingsOfEntity('Modules/Test', 'rnd_src_pool_def'));
            $results->startParsing();
        }

        $newObj->saveToDb(); // this creates test_fi
        $newObj->update(); // this saves ilObject data

        // import skill assignments
        $importedAssignmentList = $this->importQuestionSkillAssignments($a_mapping, $newObj, $xml_file);
        $this->importSkillLevelThresholds($a_mapping, $importedAssignmentList, $newObj, $xml_file);

        $a_mapping->addMapping("Modules/Test", "tst", $a_id, $newObj->getId());
    }

    /**
     * Final processing
     * @param ilImportMapping $a_mapping
     * @return void
     */
    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $maps = $a_mapping->getMappingsOfEntity("Modules/Test", "tst");

        foreach ($maps as $old => $new) {
            if ($old == "new_id" || (int) $old <= 0) {
                continue;
            }

            if (isset(self::$finallyProcessedTestsRegistry[$new])) {
                continue;
            }

            /* @var ilObjTest $testOBJ */
            $testOBJ = ilObjectFactory::getInstanceByObjId($new, false);
            if ($testOBJ->isRandomTest()) {
                $this->finalRandomTestTaxonomyProcessing($a_mapping, $old, $new, $testOBJ);
            }

            self::$finallyProcessedTestsRegistry[$new] = true;
        }
    }

    protected function finalRandomTestTaxonomyProcessing(ilImportMapping $mapping, $oldTstObjId, $newTstObjId, ilObjTest $testOBJ)
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

        // update all source pool definition's tax/taxNode ids with new mapped id
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilDB = $DIC['ilDB'];

        $srcPoolDefFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $ilDB,
            $testOBJ
        );

        $srcPoolDefList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $ilDB,
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
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC['ilLog']->write(__METHOD__ . ': ' . $this->getImportDirectory());

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
        $importer->setImportInstallationId($this->getInstallId());
        $importer->setImportMappingRegistry($mapping);
        $importer->setImportMappingComponent('Modules/Test');
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

        $importer = new ilTestSkillLevelThresholdImporter();
        $importer->setTargetTestId($testOBJ->getTestId());
        $importer->setImportInstallationId($this->getInstallId());
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
