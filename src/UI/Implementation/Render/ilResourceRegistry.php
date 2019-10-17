<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Plumbing for ILIAS, tries to guess
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilResourceRegistry implements ResourceRegistry
{
    /**
     * @var	ilTemplate
     */
    protected $il_template;

    public function __construct(\ilTemplate $il_template)
    {
        $this->il_template = $il_template;
    }

    /**
     * @inheritdoc
     */
    public function register($name)
    {
        $path_parts = pathinfo($name);
        switch ($path_parts["extension"]) {
            case "js":
                $this->il_template->addJavaScript($name);
                break;
            case "css":
                $this->il_template->addCss($name);
                break;
            case "less":
                // Can be ignored, should be compiled into css
                break;
            default:
                throw new \InvalidArgumentException("Can't handle resource '$name'");
        }
    }
}
