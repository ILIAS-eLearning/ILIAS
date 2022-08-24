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
 * Class ilModulesOrgUnitExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilModulesOrgUnitExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return ilExtractedParams
     */
    public function extract(string $event, array $parameters): ilExtractedParams
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
    protected function extractWithUser(array $parameters): void
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('usr_id');
        $this->ilExtractedParams->setContextId($parameters['user_id']); // usr_id in many other places
    }
}
