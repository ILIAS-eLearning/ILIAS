<?php
declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

class exQuestionPoolExporter extends ilXmlExporter
{

    public function getValidSchemaVersions($a_entity)
    {
        /* export schema versions code */
    }


    public function init()
    {
        /* question pool init code */
    }


    /**
     * @param string $a_entity
     * @param array  $a_schema_version
     * @param int    $a_id
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        /* question pool export code */
        /**
         * although e.g. the question pool does declare assessment questions
         * as a tail depency, it still is able to also provide the former qtixml,
         * that contains all questions as a single qti file.
         */
    }


    /**
     * @param string $a_entity
     * @param string $a_target_release
     * @param array  $a_ids
     *
     * @return array
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
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
                    'entity'    => 'qst',
                    'ids'       => $questionIds,
                );
            }

            return $deps;
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }
}