<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ilGlobalTemplateInterface;
use ilTemplate;

/**
 * Factory for wrapped ilTemplates.
 */
class ilTemplateWrapperFactory implements TemplateFactory
{
    protected ilGlobalTemplateInterface $global_tpl;

    public function __construct(ilGlobalTemplateInterface $global_tpl)
    {
        $this->global_tpl = $global_tpl;
    }

    /**
     * @inheritdocs
     */
    public function getTemplate(string $path, bool $purge_unfilled_vars, bool $purge_unused_blocks) : Template
    {
        $tpl = new ilTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
        return new ilTemplateWrapper($this->global_tpl, $tpl);
    }
}
