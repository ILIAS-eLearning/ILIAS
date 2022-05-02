<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
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
    protected function extractNews(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId(0);
        $this->ilExtractedParams->setContextType('news_ids');
        $this->ilExtractedParams->setContextId(implode(',', $parameters['news_ids']));
    }
}
