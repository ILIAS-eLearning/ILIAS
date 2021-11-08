<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ilGlobalTemplateInterface;
use InvalidArgumentException;

/**
 * Plumbing for ILIAS, tries to guess
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilResourceRegistry implements ResourceRegistry
{
    protected ilGlobalTemplateInterface $il_template;

    public function __construct(ilGlobalTemplateInterface $il_template)
    {
        $this->il_template = $il_template;
    }

    /**
     * @inheritdoc
     */
    public function register(string $name) : void
    {
        $path_parts = pathinfo($name);
        switch ($path_parts["extension"]) {
            case "js":
                $this->il_template->addJavaScript($name, true, 1);
                break;
            case "css":
                $this->il_template->addCss($name);
                break;
            case "less":
                // Can be ignored, should be compiled into css
                break;
            default:
                throw new InvalidArgumentException("Can't handle resource '$name'");
        }
    }
}
