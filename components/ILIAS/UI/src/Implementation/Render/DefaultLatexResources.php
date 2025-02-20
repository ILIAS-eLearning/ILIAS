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

class DefaultLatexResources implements LatexResources
{
    public function toRegister(): array
    {
        return ['assets/js/mathjax.js'];
    }

    public function toProvide(): array
    {
        $resources = [
            'js/MathJax/mathjax.js' => 'assets/js/mathjax.js'
        ];

        foreach (['tex-chtml-full.js', 'a11y', 'adaptors', 'input', 'output', 'sre', 'ui',] as $asset) {
            $resources['node_modules/mathjax/es5/' . $asset] = 'node_modules/mathjax/es5/' .  $asset;
        }

        return $resources;
    }
}