<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilServicesUserExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesUserExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
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
    protected function extractUser($parameters)
    {
        $this->ilExtractedParams->setSubjectId($parameters['user_obj']->getId());
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }

    /**
     * @param array $parameters
     */
    protected function extractUserById($parameters)
    {
        $this->ilExtractedParams->setSubjectId($parameters['usr_id']);
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }
}
