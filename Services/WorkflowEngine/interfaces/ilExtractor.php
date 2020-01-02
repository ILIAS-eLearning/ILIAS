<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilExtractor
 */
interface ilExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return ilExtractedParams
     */
    public function extract($event, $parameters);
}
