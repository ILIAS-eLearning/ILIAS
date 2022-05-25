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
 * Class ilServicesUserExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilServicesUserExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('user');

        switch ($event) {
            case 'afterCreate':
            case 'afterUpdate':
                $this->extractUser($parameters);
                break;
            case 'deleteUser':
                $this->extractUserById($parameters);
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractUser(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId($parameters['user_obj']->getId());
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }

    /**
     * @param array $parameters
     */
    protected function extractUserById(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId($parameters['usr_id']);
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }
}
