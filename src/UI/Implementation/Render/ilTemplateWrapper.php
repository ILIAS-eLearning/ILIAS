<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
