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

use ILIAS\Data\ReferenceId;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\SimpleXMLDeserializer;
use ILIAS\TestQuestionPool\ExportImport\QuestionPoolImporter;
use ILIAS\TestQuestionPool\QuestionPoolDIC;

/**
 * Importer class for question pools
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup components\ILIASLearningModule
 */

class ilTestQuestionPoolImporter extends ilXmlImporter
{
    protected readonly ImportSessionRepository $session;
    protected readonly QuestionPoolImporter $importer;

    public function __construct()
    {
        parent::__construct();

        $local_dic = QuestionPoolDIC::dic();
        $this->session = $local_dic['exportimport.session'];
        $this->importer = $local_dic['exportimport.importer'];
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $result = $this->importer->import(
            new SimpleXMLDeserializer()->open($a_xml),
            $a_mapping,
            new ReferenceId($a_mapping->getTargetId()),
            $this->session->getContext(),
        );
        $this->session->setContext($result);
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $this->finalizeQuestionPages($a_mapping);
        $this->finalizeTaxonomyUsage($a_mapping);
    }

    /**
     * Finalize the imported question pages by replacing the old question ids with the new question ids.
     */
    private function finalizeQuestionPages(ilImportMapping $a_mapping): void
    {
        $page_mappings = $a_mapping->getMappingsOfEntity('components/ILIAS/COPage', 'pg');

        foreach ($page_mappings as $old => $new) {
            if (!preg_match('/^qpl:(\d+)$/', $old, $old_matches)) {
                continue;
            }
            $old_question_id = $old_matches[1];

            if (!preg_match('/^qpl:(\d+)$/', $new, $new_matches)) {
                continue;
            }
            $new_question_id = $new_matches[1];

            $page = new ilAssQuestionPage((int) $new_question_id);
            $xml = preg_replace(
                '/il_\d+_qst_' . preg_quote($old_question_id, '/') . '\b/',
                "il__qst_{$new_question_id}",
                $page->getXMLContent()
            );
            if ($xml === null) {
                continue;
            }
            $page->setXMLContent($xml);

            $parent_obj_id = $a_mapping->getMapping(
                'components/ILIAS/TestQuestionPool',
                'question_assignment',
                $new_question_id
            );
            if ($parent_obj_id !== null) {
                $page->setParentId((int) $parent_obj_id);
            }

            $page->updateFromXML();
            unset($page);
        }
    }

    private function finalizeTaxonomyUsage(ilImportMapping $a_mapping): void
    {
        $qpl_mappings = $a_mapping->getMappingsOfEntity('components/ILIAS/TestQuestionPool', 'qpl');

        foreach ($qpl_mappings as $old => $new) {
            if ($old !== 'new_id' && (int) $old > 0) {
                $new_tax_ids = $a_mapping->getMapping(
                    'components/ILIAS/Taxonomy',
                    'tax_usage_of_obj',
                    (string) $old
                );

                if ($new_tax_ids !== null) {
                    $tax_ids = explode(':', $new_tax_ids);
                    foreach ($tax_ids as $tid) {
                        ilObjTaxonomy::saveUsage((int) $tid, (int) $new);
                    }
                }
            }
        }
    }
}
