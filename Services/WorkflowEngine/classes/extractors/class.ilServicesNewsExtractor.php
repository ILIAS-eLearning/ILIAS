<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/extractors/class.ilBaseExtractor.php';

/**
 * Class ilServicesNewsExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesNewsExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return \ilExtractedParams
     */
    public function extract($event, $parameters)
    {
        $this->ilExtractedParams->setSubjectType('news');

        switch ($event) {
            case 'readNews':
            case 'unreadNews':
                $this->extractNews($parameters);
                break;
        }
        return $this->ilExtractedParams;
    }

    /**
     * @param array $parameters
     */
    protected function extractNews($parameters)
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('news_ids');
        $this->ilExtractedParams->setContextId(implode(',', $parameters['news_ids']));
    }
}
