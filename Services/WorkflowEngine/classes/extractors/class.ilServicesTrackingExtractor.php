<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilServicesTrackingExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesTrackingExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
    {
        //$this->ilExtractedParams->setSubjectType('tracking'); See below what we do here different from other impl.

        switch ($event) {
            case 'updateStatus':
                $this->extractTracking($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractTracking($parameters)
    {
        $this->ilExtractedParams->setSubjectType('tracking_' . $parameters['status']);
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('usr_id');
        $this->ilExtractedParams->setContextId($parameters['usr_id']);
    }
}
