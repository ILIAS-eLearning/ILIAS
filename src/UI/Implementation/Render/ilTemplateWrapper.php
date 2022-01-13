<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ilGlobalTemplateInterface;
use ilTemplate;

/**
 * Wraps an ilTemplate to only provide smaller interface.
 */
class ilTemplateWrapper implements Template
{
    protected ilGlobalTemplateInterface $global_tpl;
    private ilTemplate $tpl;

    final public function __construct(ilGlobalTemplateInterface $global_tpl, ilTemplate $tpl)
    {
        $this->global_tpl = $global_tpl;
        $this->tpl = $tpl;
    }

    /**
     * @inheritdocs
     */
    public function setCurrentBlock(string $name) : bool
    {
        return $this->tpl->setCurrentBlock($name);
    }

    /**
     * @inheritdocs
     */
    public function parseCurrentBlock() : bool
    {
        return $this->tpl->parseCurrentBlock();
    }

    /**
     * @inheritdocs
     */
    public function touchBlock(string $name) : bool
    {
        return $this->tpl->touchBlock($name);
    }

    /**
     * @inheritdocs
     */
    public function setVariable(string $name, $value) : void
    {
        $this->tpl->setVariable($name, $value);
    }

    /**
     * @inheritdocs
     */
    public function get(string $block = null) : string
    {
        if ($block === null) {
            $block = "__global__";
        }
        return $this->tpl->get($block);
    }

    /**
     * @inheritdocs
     */
    public function addOnLoadCode(string $code) : void
    {
        $this->global_tpl->addOnLoadCode($code);
    }
}
