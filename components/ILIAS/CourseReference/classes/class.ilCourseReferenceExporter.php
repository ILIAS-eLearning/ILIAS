<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCourseReferenceExporter extends ilContainerReferenceExporter
{
    /**
     * Init xml writer
     * @param ilContainerReference $ref
     * @return ilContainerReferenceXmlWriter
     */
    protected function initWriter(ilContainerReference $ref): ilContainerReferenceXmlWriter
    {
        return new ilCourseReferenceXmlWriter($ref);
    }
}
