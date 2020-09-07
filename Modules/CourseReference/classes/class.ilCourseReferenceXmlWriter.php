<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/ContainerReference/classes/class.ilContainerReferenceXmlWriter.php';

/**
 * Class for container reference export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCourseReferenceXmlWriter extends ilContainerReferenceXmlWriter
{

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct(ilObjCourseReference $ref = null)
    {
        parent::__construct($ref);
    }

    /**
     * Build xml header
     * @global <type> $ilSetting
     * @return <type>
     */
    protected function buildHeader()
    {
        global $ilSetting;

        $this->xmlSetDtdDef("<!DOCTYPE course reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_course_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS course reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();

        return true;
    }
}
