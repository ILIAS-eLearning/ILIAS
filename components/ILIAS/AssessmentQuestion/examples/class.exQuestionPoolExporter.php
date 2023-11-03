<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
                    'component' => 'Services/AssessmentQuestion',
                    'entity' => 'qst',
                    'ids' => $questionIds
                );
            }

            return $deps;
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }
}
