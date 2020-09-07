<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilModulesGroupExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesGroupExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
    {
        $this->ilExtractedParams->setSubjectType('group');

        switch ($event) {
            case 'addParticipant':
            case 'deleteParticipant':
            case 'addSubscriber':
            case 'addToWaitingList':
                $this->extractWithUser($parameters);
                break;
            case 'create':
            case 'update':
            case 'delete':
                $this->extractWithoutUser($parameters);
        }

        return $this->ilExtractedParams;
    }
}
