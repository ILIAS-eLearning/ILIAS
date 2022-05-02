<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilServicesRepositoryExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilServicesRepositoryExtractor extends ilBaseExtractor
{
    /**
     * @param string $event
     * @param array  $parameters
     * @return \ilExtractedParams
     */
    public function extract(string $event, array $parameters) : ilExtractedParams
    {
        $this->ilExtractedParams->setSubjectType('repository');

        switch ($event) {
            case 'toTrash':
            case 'delete':
            case 'undelete':
                $this->extractWithoutUser($parameters);
                break;
        }

        return $this->ilExtractedParams;
    }
}
