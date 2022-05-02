<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilServicesMembershipExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesMembershipExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('membership');

        return $this->ilExtractedParams;
    }
}
