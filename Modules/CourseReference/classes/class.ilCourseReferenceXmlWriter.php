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
     * Start writing xml
     */
    public function export(bool $a_with_header = true): void
    {
        if ($this->getMode() == self::MODE_EXPORT) {
            if ($a_with_header) {
                $this->buildHeader();
            }
            $this->buildReference();
            $this->buildTarget();
            $this->buildTitle();
            $this->buildCourseSettings();
            $this->buildFooter();
        }
    }

    /**
     * Add member update setting
     */
    protected function buildCourseSettings()
    {
        $this->xmlElement('MemberUpdate', [], $this->getReference()->isMemberUpdateEnabled() ? 1 : 0);
    }


    /**
     * Build xml header
     * @return void
     *@global <type> $ilSetting
     */
    protected function buildHeader(): void
    {
        global $ilSetting;

        $this->xmlSetDtdDef("<!DOCTYPE course reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_course_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS course reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();
    }
}
