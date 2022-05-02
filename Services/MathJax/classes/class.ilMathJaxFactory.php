<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\DI\Container;

/**
 * Factory for objects used by ilMathJax
 */
class ilMathJaxFactory
{
    protected ilGlobalTemplateInterface $template;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->template = $DIC->ui()->mainTemplate();
    }

    /**
     * Create an ilMathJaxServer object
     */
    public function server(ilMathJaxConfig $config) : ilMathJaxServer
    {
        return new ilMathJaxServer($config);
    }

    /**
     * Create an ilMathJaxImage object
     */
    public function image(string $a_tex, string $a_type, int $a_dpi) : ilMathJaxImage
    {
        return new ilMathJaxImage($a_tex, $a_type, $a_dpi);
    }

    /**
     * Get the global template
     */
    public function template() : ilGlobalTemplateInterface
    {
        return $this->template;
    }
}
