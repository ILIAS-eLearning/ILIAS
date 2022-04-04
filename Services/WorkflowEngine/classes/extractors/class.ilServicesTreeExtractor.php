<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilServicesTreeExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesTreeExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('tree');

        switch ($event) {
            case 'moveTree':
                $this->extractTree($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractTree(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('tree');
        $this->ilExtractedParams->setContextId($parameters['tree']);
    }
}
