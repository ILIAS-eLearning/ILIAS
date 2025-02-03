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

class exQuestionPoolExporter extends ilXmlExporter
{
    public function getValidSchemaVersions(string $a_entity): array
    {
        /* export schema versions code */
        return [];
    }

    public function init(): void
    {
        /* question pool init code */
    }

    /**
     * @param string $a_entity
     * @param string $a_schema_version
     * @param string $a_id
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        /* question pool export code */

        /**
         * although e.g. the question pool does declare assessment questions
         * as a tail depency, it still is able to also provide the former qtixml,
         * that contains all questions as a single qti file.
         */
        return '';
    }

    /**
     * @param string $a_entity
     * @param string $a_target_release
     * @param array  $a_ids
     * @return array
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        /**
         * when components use the assessment question service, they can declare questions
         * as a tail depency for their own export. the export service will address the
         * assessment question service to get all required question exported.
         *
         * simply determine the questionIds for the given entityIds and return them
         * in the shown depeny array structure.
         */

        if ($a_entity == 'qpl') {
            $deps = array();

            $questionIds = array(); // initialise with question ids that need to be exported

            if (count($questionIds)) {
                $deps[] = array(
                    'component' => 'components/ILIAS/AssessmentQuestion',
                    'entity' => 'qst',
                    'ids' => $questionIds
                );
            }

            return $deps;
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }
}
