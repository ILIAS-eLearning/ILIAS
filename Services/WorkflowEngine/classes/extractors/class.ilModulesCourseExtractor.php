<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilModulesCourseExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesCourseExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
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
