<?php

use ILIAS\DI\Container;

/**
 * Factory for objects used by ilMathJax
 */
class ilMathJaxFactory
{
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
     * @return ilGlobalTemplateInterface
     */
    public function template() : ilGlobalTemplateInterface
    {
        /** @var Container $DIC */
        global $DIC;
        return $DIC->ui()->mainTemplate();
    }
}