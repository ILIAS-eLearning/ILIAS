<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilServicesObjectExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesObjectExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
    {
        $this->ilExtractedParams->setSubjectType('object');

        switch ($event) {
            case 'create':
            case 'update':
                $this->extractObject($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractObject($parameters)
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType($parameters['obj_type']);
        $this->ilExtractedParams->setContextId(0);
    }
}
