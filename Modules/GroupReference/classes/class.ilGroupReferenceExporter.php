<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';
include_once './Services/ContainerReference/classes/class.ilContainerReferenceExporter.php';

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
     * @return ilGroupReferenceXmlWriter
     */
    protected function initWriter(ilContainerReference $ref)
    {
        include_once './Modules/GroupReference/classes/class.ilGroupReferenceXmlWriter.php';
        return new ilGroupReferenceXmlWriter($ref);
    }
}
