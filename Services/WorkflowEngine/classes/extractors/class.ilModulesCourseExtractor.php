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
 * Class ilModulesCourseExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilModulesCourseExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return ilExtractedParams
     */
    public function extract(string $event, array $parameters): ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('course');

        switch ($event) {
            case 'addParticipant':
            case 'deleteParticipant':
            case 'addSubscriber':
            case 'addToWaitingList':
                $this->extractWithUser($parameters);
                break;
            case 'create':
            case 'delete':
            case 'update':
                $this->extractWithoutUser($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }
}
