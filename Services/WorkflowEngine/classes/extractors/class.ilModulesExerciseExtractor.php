<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilModulesExerciseExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesExerciseExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('exercise');

        switch ($event) {
            case 'createAssignment':
            case 'updateAssignment':
            case 'deleteAssignment':
            case 'delete':
                $this->extractWithoutUser($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }
}
