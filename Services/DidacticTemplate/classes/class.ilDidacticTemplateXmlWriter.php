<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for a single didactic template
 * @author   Stefan Meyer <meyer@leifos.com>
 * @defgroup ServicesDidacticTemplate
 */
class ilDidacticTemplateXmlWriter extends ilXmlWriter
{
    private ilDidacticTemplateSetting $tpl;

    /**
     * Constructor
     */
    public function __construct(int $a_tpl_id)
    {
        parent::__construct();
        $this->tpl = new ilDidacticTemplateSetting($a_tpl_id);
    }

    /**
     * Get template setting
     */
    public function getSetting() : ilDidacticTemplateSetting
    {
        return $this->tpl;
    }

    /**
     * Write xml
     */
    public function write() : void
    {
        $this->xmlHeader();
        $this->xmlStartTag('didacticTemplateDefinition');

        // add definition setting
        $this->getSetting()->toXml($this);
        $this->xmlEndTag('didacticTemplateDefinition');
    }
}
