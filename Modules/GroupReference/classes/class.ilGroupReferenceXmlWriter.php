<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/ContainerReference/classes/class.ilContainerReferenceXmlWriter.php';

/**
 * Class for container reference export
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceXmlWriter
 *
 * @ingroup ModulesGroupReference
 */
class ilGroupReferenceXmlWriter extends ilContainerReferenceXmlWriter
{

    /**
     * ilGroupReferenceXmlWriter constructor.
     * @param ilObjGroupReference|null $ref
     */
    public function __construct(ilObjGroupReference $ref = null)
    {
        parent::__construct($ref);
    }

    /**
     * Build xml header
     * @global ilSetting $ilSetting
     * @return bool
     */
    protected function buildHeader()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->xmlSetDtdDef("<!DOCTYPE course reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_course_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS course reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();

        return true;
    }
}
