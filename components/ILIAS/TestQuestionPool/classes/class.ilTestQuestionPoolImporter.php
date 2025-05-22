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
use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;

/**
 * Importer class for question pools
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup components\ILIASLearningModule
 */

class ilTestQuestionPoolImporter extends ilXmlImporter
{
    use TestQuestionsImportTrait;

    private ilObjQuestionPool $pool_obj;
    protected readonly RequestDataCollector $request_data_collector;

    public function __construct()
    {
        parent::__construct();

        $local_dic = QuestionPoolDIC::dic();
        $this->request_data_collector = $local_dic['request_data_collector'];
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        global $DIC;
        // Container import => pool object already created
        if (($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) !== null) {
            $new_obj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
            $new_obj->getObjectProperties()->storePropertyIsOnline($new_obj->getObjectProperties()->getPropertyIsOnline()->withOffline()); // sets Question pools to always online

            $selected_questions = [];
            [$importdir, $xmlfile, $qtifile] = $this->buildImportDirectoriesFromContainerImport(
                $this->getImportDirectory()
            );
        } elseif (($new_id = $a_mapping->getMapping('components/ILIAS/TestQuestionPool', 'qpl', 'new_id')) !== null) {
            $new_obj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);

            $selected_questions = ilSession::get('qpl_import_selected_questions');
            [$subdir, $importdir, $xmlfile, $qtifile] = $this->buildImportDirectoriesFromImportFile(
                ilSession::get('path_to_import_file')
            );
            ilSession::clear('qpl_import_selected_questions');
        } else {
            // Shouldn't happen
            $DIC['ilLog']->write(__METHOD__ . ': non container and no tax mapping, perhaps old qpl export');
            return;
        }

        if (!file_exists($xmlfile)) {
            $DIC['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xmlfile);
            return;
        }
        if (!file_exists($qtifile)) {
            $DIC['ilLog']->write(__METHOD__ . ': Cannot find qti definition: ' . $qtifile);
            return;
        }

        $this->pool_obj = $new_obj;

        $new_obj->fromXML($xmlfile);

        $qpl_new = $this->request_data_collector->string('qpl_new');

        // set another question pool name (if possible)
        if ($qpl_new !== '') {
            $new_obj->setTitle($qpl_new);
        }

        $new_obj->update();
        $new_obj->saveToDb();

        // FIXME: Copied from ilObjQuestionPoolGUI::importVerifiedFileObject
        // TODO: move all logic to ilObjQuestionPoolGUI::importVerifiedFile and call
        // this method from ilObjQuestionPoolGUI and ilTestImporter

        $DIC['ilLog']->write(__METHOD__ . ': xml file: ' . $xmlfile . ', qti file:' . $qtifile);

        $qtiParser = new ilQTIParser(
            $importdir,
            $qtifile,
            ilQTIParser::IL_MO_PARSE_QTI,
            $new_obj->getId(),
            $selected_questions
        );
        $qtiParser->startParsing();

        $questionPageParser = new ilQuestionPageParser(
            $new_obj,
            $xmlfile,
            $importdir
        );
        $questionPageParser->setQuestionMapping($qtiParser->getImportMapping());
        $questionPageParser->startParsing();

        foreach ($qtiParser->getImportMapping() as $k => $v) {
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
                (string) $new_obj->getId()
            );

            $a_mapping->addMapping(
                'components/ILIAS/TestQuestionPool',
                'quest',
                $old_question_id,
                $new_question_id
            );
        }

        $this->importQuestionSkillAssignments($xmlfile, $a_mapping, $new_obj->getId());

        $a_mapping->addMapping('components/ILIAS/TestQuestionPool', 'qpl', $a_id, (string) $new_obj->getId());
        $a_mapping->addMapping(
            'components/ILIAS/MetaData',
            'md',
            $a_id . ':0:qpl',
            $new_obj->getId() . ':0:qpl'
        );


        $new_obj->saveToDb();
    }

    /**
     * Final processing
     * @param ilImportMapping $a_mapping
     * @return void
     */
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
            $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($targetParentObjId);
            $qsaImportFails->registerFailedImports($importer->getFailedImportAssignmentList());

            $this->pool_obj->getObjectProperties()->storePropertyIsOnline($this->pool_obj->getObjectProperties()->getPropertyIsOnline()->withOffline());
        }
    }
}
