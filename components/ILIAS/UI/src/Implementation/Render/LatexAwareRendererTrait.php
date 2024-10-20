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

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\LatexAwareRenderer;
use ILIAS\UI\Implementation\Render\MathJaxConfig;

/**
 * Functions to support latex rendering in a UI component
 */
trait LatexAwareRendererTrait
{
    protected MathJaxConfig $mathjax_config;

    /**
     * Get the configuration of MathJax for the UI framework
     */
    public function getMathJaxConfig(): MathJaxConfig
    {
        return $this->mathjax_config;
    }

    /**
     * Add the configuration of MathJax for the UI framework
     */
    public function withMathJaxConfig(MathJaxConfig $config): self
    {
        $clone = clone $this;
        $clone->mathjax_config = $config;
        return $clone;
    }

    /**
     * @see LatexAwareRenderer::registerResources()
    */
    public function registerMathJaxResources(ResourceRegistry $registry): void
    {
        if ($this->getMathJaxConfig()->isMathJaxEnabled()) {
            foreach ($this->getMathJaxConfig()->getResources() as $resource) {
                $registry->register($resource);
            }
        }
    }

    /**
     * @see LatexAwareRenderer::addLatexEnabling()
     */
    public function addLatexEnabling(string $content): string
    {
        if ($this->getMathJaxConfig()->isMathJaxEnabled()) {
            $class = $this->getMathJaxConfig()->getPartialEnablingClass();
            return '<div style="display: inherit;" class="' . $class . '">' . $content . '</div>';
        }
        return $content;
    }

    /**
     * @see LatexAwareRenderer::addLatexDisabling()
     */
    public function addLatexDisabling(string $content): string
    {
        if ($this->getMathJaxConfig()->isMathJaxEnabled()) {
            $class = $this->getMathJaxConfig()->getPartialDisablingClass();
            return '<div style="display: inherit;" class="' . $class . '">' . $content . '</div>';
        }
        return $content;
    }
}
