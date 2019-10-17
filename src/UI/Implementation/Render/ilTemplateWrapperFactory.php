<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Factory for wrapped ilTemplates.
 */
class ilTemplateWrapperFactory implements TemplateFactory
{
    /**
     * @var	\ilTemplate
     */
    protected $global_tpl;

    public function __construct(\ilTemplate $global_tpl)
    {
        $this->global_tpl = $global_tpl;
    }

    /**
     * @inheritdocs
     */
    public function getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks)
    {
        $tpl = new \ilTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
        return new ilTemplateWrapper($this->global_tpl, $tpl);
    }
}
