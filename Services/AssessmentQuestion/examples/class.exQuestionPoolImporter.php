<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class exQuestionPoolImporter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class exQuestionPoolImporter extends ilXmlImporter
{
    /**
     * @param string          $a_entity
     * @param string          $a_id
     * @param string          $a_xml
     * @param ilImportMapping $a_mapping
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        /**
         * here consumers can regularly process their own import stuff.
         *
         * although the assessment questions are imported by declared tail depencies,
         * any consumer component can import any overall qti xml file, that was added
         * to the export by the consumer itself.
         */
    }

    /**
     * Final processing
     * @param ilImportMapping $a_mapping
     * @return void
     */
    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $maps = $a_mapping->getMappingsOfEntity("Modules/TestQuestionPool", "qpl");

        foreach ($maps as $old => $new) {
            if ($old != "new_id" && (int) $old > 0) {
                $newQstIds = $a_mapping->getMapping("Services/AssessmentQuestion", "qst", $old);

                if ($newQstIds !== false) {
                    $qstIds = explode(":", $newQstIds);
                    foreach ($qstIds as $qId) {
                        $qstInstance = $DIC->question()->getQuestionInstance($qId);
                        $qstInstance->setParentId($new);
                        $qstInstance->save();
                    }
                }

                $qstMappings = $a_mapping->getMappingsOfEntity('Services/AssessmentQuestion', 'qst');

                foreach ($qstMappings as $oldQstId => $newQstId) {
                    // process all question ids within the consumer component database,
                    // look for the old qst id and map to the new qst id
                }
            }
        }
    }
}
