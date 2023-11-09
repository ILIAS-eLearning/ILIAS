<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class for group reference export
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceExporter
 * @ingroup components\ILIASGroupReference
 */
class ilGroupReferenceExporter extends ilContainerReferenceExporter
{
    /**
     * Init xml writer
     * @param ilContainerReference $ref
     * @return ilContainerReferenceXmlWriter
     */
    protected function initWriter(ilContainerReference $ref): ilContainerReferenceXmlWriter
    {
        return new ilGroupReferenceXmlWriter($ref);
    }
}
