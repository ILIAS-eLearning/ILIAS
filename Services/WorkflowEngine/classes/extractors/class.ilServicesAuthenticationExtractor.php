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
 * Class ilServicesAuthenticationExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilServicesAuthenticationExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return ilExtractedParams
     */
    public function extract(string $event, array $parameters): ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('authentication');

        switch ($event) {
            case 'afterLogin':
                $this->extractAfterLogin($parameters);
                break;
                // case 'expiredSessionDetected': Can this be supported? No params... TODO: Add some thinking to it...
                // case 'reachedSessionPoolLimit': Can this be supported? No params... TODO: Add some thinking to it...
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractAfterLogin(array $parameters): void
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('user');
        $this->ilExtractedParams->setContextId(ilObjUser::_lookupId($parameters['username']));
    }
}
