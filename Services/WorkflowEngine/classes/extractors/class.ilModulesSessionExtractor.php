<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilModulesSessionExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesSessionExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('session');
        switch ($event) {
            case 'create':
            case 'update':
            case 'addToWaitingList':
            case 'delete':
                $this->extractWithUser($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }
}
