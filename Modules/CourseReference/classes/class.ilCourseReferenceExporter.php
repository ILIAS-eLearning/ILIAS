<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';
include_once './Services/ContainerReference/classes/class.ilContainerReferenceExporter.php';

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
     * @return ilCategoryXmlWriter
     */
    protected function initWriter(ilContainerReference $ref)
    {
        include_once './Modules/CourseReference/classes/class.ilCourseReferenceXmlWriter.php';
        return new ilCourseReferenceXmlWriter($ref);
    }
}
