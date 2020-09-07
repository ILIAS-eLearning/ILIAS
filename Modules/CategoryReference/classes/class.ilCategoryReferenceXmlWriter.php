<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/ContainerReference/classes/class.ilContainerReferenceXmlWriter.php';

/**
 * Class for container reference export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCategoryReferenceXmlWriter extends ilContainerReferenceXmlWriter
{

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct(ilObjCategoryReference $ref = null)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::__construct($ref);
    }

    /**
     * Build xml header
     * @global <type> $ilSetting
     * @return <type>
     */
    protected function buildHeader()
    {
        $ilSetting = $this->settings;

        $this->xmlSetDtdDef("<!DOCTYPE category reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_category_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS category reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();

        return true;
    }
}
