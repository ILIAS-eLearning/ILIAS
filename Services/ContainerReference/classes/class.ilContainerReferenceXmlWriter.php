<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
 * Class for container reference export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilContainerReferenceXmlWriter extends ilXmlWriter
{
    /**
     * @var ilSetting
     */
    protected $settings;

    const MODE_SOAP = 1;
    const MODE_EXPORT = 2;
    
    private $mode = self::MODE_SOAP;
    private $xml;
    private $ref;

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct(ilContainerReference $ref = null)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::__construct();
        $this->ref = $ref;
    }

    /**
     * Set export mode
     * @param int $a_mode
     */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    /**
     * get export mode
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get category object
     * @return ilContainerReference
     */
    public function getReference()
    {
        return $this->ref;
    }


    /**
     * Start wrting xml
     */
    public function export($a_with_header = true)
    {
        if ($this->getMode() == self::MODE_EXPORT) {
            if ($a_with_header) {
                $this->buildHeader();
            }
            $this->buildReference();
            $this->buildTarget();
            $this->buildTitle();
            $this->buildFooter();
        }
    }

    /**
     * get XML
     * @return string
     */
    public function getXml()
    {
        return $this->xmlDumpMem(false);
    }

    /**
     * Build xml header
     * @return bool
     */
    protected function buildHeader()
    {
        $ilSetting = $this->settings;

        $this->xmlSetDtdDef("<!DOCTYPE container reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_container_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS container reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();

        return true;
    }
    
    /**
     * Build target element
     */
    protected function buildTarget()
    {
        $this->xmlElement('Target', array('id' => $this->getReference()->getTargetId()));
    }
    
    /**
     * Build title element
     */
    protected function buildTitle()
    {
        $title = '';
        if ($this->getReference()->getTitleType() == ilContainerReference::TITLE_TYPE_CUSTOM) {
            $title = $this->getReference()->getTitle();
        }
        
        $this->xmlElement(
            'Title',
            array(
                    'type' => $this->getReference()->getTitleType()
                ),
            $title
        );
    }

    /**
     * Build category xml
     */
    protected function buildReference()
    {
        $this->xmlStartTag('ContainerReference');
    }
    
    /**
     * Add footer elements
     */
    protected function buildFooter()
    {
        $this->xmlEndTag('ContainerReference');
    }
}
