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

use ILIAS\Refinery\ConstraintViolationException;

/**
 * Default Mathjax configuration for the UI Framework
 */
class MathJaxDefaultConfig implements MathJaxConfig
{
    public function __construct(
        private readonly bool $mathjax_enabled,
    ) {
    }

    public function isMathJaxEnabled(): bool
    {
        return $this->mathjax_enabled;
    }

    public function getGlobalDisablingClass(): string
    {
        return 'tex2jax_ignore_global';
    }

    public function getPartialDisablingClass(): string
    {
        return 'tex2jax_ignore';
    }

    public function getPartialEnablingClass(): string
    {
        return 'tex2jax_process';
    }

    public function getResources(): array
    {
        return [
            'assets/js/mathjax.js'
        ];
    }

    public function getResourcesToExport(): array
    {
        $resources = [
            'assets/js/mathjax.js'
        ];

        // MathJax will dynamically load additional assets based on what is found on the page
        foreach (['tex-chtml-full.js', 'a11y', 'adaptors', 'input', 'output', 'sre', 'ui'] as $resource) {
            $resources[] = "./node_modules/mathjax/es5/" . $resource;
        }

        return $resources;
    }
}
