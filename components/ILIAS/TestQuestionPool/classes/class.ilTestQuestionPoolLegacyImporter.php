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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;

class ilTestQuestionPoolLegacyImporter extends ilXmlImporter
{
    private ilObjQuestionPool $pool_obj;
    protected readonly ImportSessionRepository $session;
    protected readonly RequestDataCollector $request_data_collector;

    public function __construct()
    {
        parent::__construct();

        $local_dic = QuestionPoolDIC::dic();
        $this->session = $local_dic['exportimport.session'];
        $this->request_data_collector = $local_dic['request_data_collector'];
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $this->pool_obj = new ilObjQuestionPool(0, true);
        $this->pool_obj->setType('qpl');
        $this->pool_obj->setTitle('dummy');
        $this->pool_obj->setDescription('questionpool import');
        $this->pool_obj->create(true);
        $this->pool_obj->createReference();
        $this->pool_obj->putInTree($this->request_data_collector->getRefId());
        $this->pool_obj->setPermissions($this->request_data_collector->getRefId());

        $a_mapping->addMapping('components/ILIAS/TestQuestionPool', 'qpl', $a_id, (string) $this->pool_obj->getId());

        $context = $this->session->getContext();
        $import_base_dir = $context->get('import_base_dir');
        $xml_file = $context->get('xml_file');
        $context = $context->with('pool_obj_id', $this->pool_obj->getId());
        $this->session->setContext($context);

        $qpl_parser = new ilObjQuestionPoolXMLParser(
            $this->pool_obj,
            $xml_file
        );
        $qpl_parser->startParsing();

        // set another question pool name (if possible)
        $qpl_new = $this->request_data_collector->string('qpl_new');
        if ($qpl_new !== '') {
            $this->pool_obj->setTitle($qpl_new);
        }

        $this->pool_obj->update();
        $this->pool_obj->saveToDb();

        $qti_parser = new ilQTIParser(
            $import_base_dir,
            $context->get('qti_file'),
            ilQTIParser::IL_MO_PARSE_QTI,
            $this->pool_obj->getId(),
            $context->get('selected_questions')
        );
        $qti_parser->startParsing();

        $page_parser = new ilQuestionPageParser(
            $this->pool_obj,
            $xml_file,
            $import_base_dir
        );
        $page_parser->setQuestionMapping($qti_parser->getImportMapping());
        $page_parser->startParsing();

        foreach ($qti_parser->getImportMapping() as $k => $v) {
            $old_question_id = substr($k, strpos($k, 'qst_') + strlen('qst_'));
            $new_question_id = (string) $v['pool']; // yes, this is the new question id ^^

            $a_mapping->addMapping(
                'components/ILIAS/Taxonomy',
                'tax_item',
                "qpl:quest:{$old_question_id}",
                $new_question_id
            );

            $a_mapping->addMapping(
                'components/ILIAS/Taxonomy',
                'tax_item_obj_id',
                "qpl:quest:{$old_question_id}",
                (string) $this->pool_obj->getId()
            );

            $a_mapping->addMapping(
                'components/ILIAS/TestQuestionPool',
                'quest',
                $old_question_id,
                $new_question_id
            );
        }

        $this->importQuestionSkillAssignments($xml_file, $a_mapping, $this->pool_obj->getId());

        $a_mapping->addMapping(
            'components/ILIAS/MetaData',
            'md',
            "{$a_id}:0:qpl",
            "{$this->pool_obj->getId()}:0:qpl"
        );

        $this->pool_obj->saveToDb();
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $maps = $a_mapping->getMappingsOfEntity('components/ILIAS/TestQuestionPool', 'qpl');
        foreach ($maps as $old => $new) {
            if ($old !== 'new_id' && (int) $old > 0) {
                $new_tax_ids = $a_mapping->getMapping('components/ILIAS/Taxonomy', 'tax_usage_of_obj', (string) $old);
                if ($new_tax_ids !== null) {
                    $tax_ids = explode(':', $new_tax_ids);
                    foreach ($tax_ids as $tid) {
                        ilObjTaxonomy::saveUsage((int) $tid, (int) $new);
                    }
                }
            }
        }
    }

    protected function importQuestionSkillAssignments($xmlFile, ilImportMapping $mappingRegistry, $targetParentObjId): void
    {
        $parser = new ilAssQuestionSkillAssignmentXmlParser($xmlFile);
        $parser->startParsing();

        $importer = new ilAssQuestionSkillAssignmentImporter();
        $importer->setTargetParentObjId($targetParentObjId);
        $importer->setImportInstallationId($this->getInstallId());
        $importer->setImportMappingRegistry($mappingRegistry);
        $importer->setImportMappingComponent('components/ILIAS/TestQuestionPool');
        $importer->setImportAssignmentList($parser->getAssignmentList());

        $importer->import();

        if ($importer->getFailedImportAssignmentList()->assignmentsExist()) {
            $fails = new ilAssQuestionSkillAssignmentImportFails($targetParentObjId);
            $fails->registerFailedImports($importer->getFailedImportAssignmentList());

            $this->pool_obj->getObjectProperties()->storePropertyIsOnline($this->pool_obj->getObjectProperties()->getPropertyIsOnline()->withOffline());
        }
    }
}
