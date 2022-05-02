<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilServicesAuthenticationExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesAuthenticationExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
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
    protected function extractAfterLogin(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('user');
        $this->ilExtractedParams->setContextId(ilObjUser::_lookupId($parameters['username']));
    }
}
