<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

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
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
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
    protected function extractAfterLogin($parameters)
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('user');
        $this->ilExtractedParams->setContextId(ilObjUser::_lookupId($parameters['username']));
    }
}
