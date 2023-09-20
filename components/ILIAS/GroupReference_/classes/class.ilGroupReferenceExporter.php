<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Export_/classes/class.ilXmlExporter.php';
include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/ContainerReference_/classes/class.ilContainerReferenceExporter.php';

/**
 * Class for group reference export
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceExporter
 * @ingroup ModulesGroupReference
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
        include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/GroupReference_/classes/class.ilGroupReferenceXmlWriter.php';
        return new ilGroupReferenceXmlWriter($ref);
    }
}
