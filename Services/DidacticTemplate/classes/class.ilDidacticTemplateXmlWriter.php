<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Xml/classes/class.ilXmlWriter.php';

/**
 * Settings for a single didactic template
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @defgroup ServicesDidacticTemplate
 */
class ilDidacticTemplateXmlWriter extends ilXmlWriter
{
    private $tpl = null;

    /**
     * Constructor
     */
    public function __construct($a_tpl_id)
    {
        parent::__construct();

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';
        $this->tpl = new ilDidacticTemplateSetting($a_tpl_id);
    }

    /**
     * Get template setting
     * @return ilDidacticTemplateSetting
     */
    public function getSetting()
    {
        return $this->tpl;
    }

    /**
     * Write xml
     */
    public function write()
    {
        $this->xmlHeader();
        $this->xmlStartTag('didacticTemplateDefinition');

        // add definition setting
        $this->getSetting()->toXml($this);

        $this->xmlEndTag('didacticTemplateDefinition');
    }
}
