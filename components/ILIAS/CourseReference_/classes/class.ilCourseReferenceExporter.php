<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './components/ILIAS/Export_/classes/class.ilXmlExporter.php';
include_once './components/ILIAS/ContainerReference_/classes/class.ilContainerReferenceExporter.php';

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
        include_once './components/ILIAS/CourseReference_/classes/class.ilCourseReferenceXmlWriter.php';
        return new ilCourseReferenceXmlWriter($ref);
    }
}
