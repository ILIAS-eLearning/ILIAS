<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

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
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
    {
        $this->ilExtractedParams->setSubjectType('session');
        switch ($event) {
            case 'create':
            case 'update':
            case 'delete':
                $this->extractWithUser($parameters);
                break;
            case 'addToWaitingList':
                $this->extractWithUser($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }
}
