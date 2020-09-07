<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilModulesOrgUnitExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesOrgUnitExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
    {
        $this->ilExtractedParams->setSubjectType('orgunit');

        switch ($event) {
            case 'assignUsersToEmployeeRole':
            case 'assignUsersToSuperiorRole':
            case 'deassignUserFromEmployeeRole':
            case 'deassignUserFromSuperiorRole':
            case 'assignUserToLocalRole':
            case 'deassignUserFromLocalRole':
                $this->extractWithUser($parameters);
                break;
            case 'initDefaultRoles':
            case 'delete':
                $this->extractWithoutUser($parameters);
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractWithUser($parameters)
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('usr_id');
        $this->ilExtractedParams->setContextId($parameters['user_id']); // usr_id in many other places
    }
}
