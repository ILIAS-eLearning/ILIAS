<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Interface for component renderers that need to enable or disable a latex processing by MathJax
 */
interface LatexAwareRenderer extends ComponentRenderer
{
    /**
     * Get the configuration of MathJax for the UI framework
     */
    public function getMathJaxConfig(): MathJaxConfig;

    /**
     * Add the configuration of MathJax for the UI framework
     */
    public function withMathJaxConfig(MathJaxConfig $config): self;

    /**
     * Register resources that are needed for latex rendering in the component
     *
     * The resources are added even if a latex rendering is not activated for the component
     * Checking this would require either
     *  - the ResourceRegistry available in render()
     *  - or dedicated components so that a withLatexEnabled() is not necessary
     *
     * The resources are provided as relative links to public assets by MathJaxUIConfig
     * The mathjax script will load further assets with relative paths
     * So the script has to be in a component directory under 'public' and not in the assets/js  directory
     */
    public function registerMathJaxResources(ResourceRegistry $registry): void;

    /**
     * Wrap the HTML content in an element that enables latex processing
     * This is called when latex is activated in the component
     */
    public function addLatexEnabling(string $content): string;

    /**
     * Wrap the HTML content in an element that disables latex processing
     * This is called when latex is deactivated in the component
     */
    public function addLatexDisabling(string $content): string;
}
