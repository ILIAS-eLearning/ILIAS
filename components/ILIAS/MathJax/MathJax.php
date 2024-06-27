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

namespace ILIAS;

use ILIAS\MathJax\MathJaxNodeModule;

class MathJax implements Component\Component
{
    /**
     * Configuration file - must be loaded first
     */
    public const CONFIG = 'config.js';

    /**
     * Mathjax Script - compiled bundle of mathjax components
     */
    public const SCRIPT = 'tex-svg.js';

    /**
     * directories of assest which are loaded by the MathJax script
     */
    public const ASSETS = [
        'a11y',
        'adaptors',
        'input',
        'output',
        'sre',
        'ui'
    ];

    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $contribute[\ILIAS\Setup\Agent::class] = fn() =>
            new \ilMathJaxSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );

        $contribute[Component\Resource\PublicAsset::class] = fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/MathJax/" . MathJax::CONFIG;
            }
            public function getTarget(): string
            {
                return "components/ILIAS/MathJax/config.js";
            }
        };

        $contribute[Component\Resource\PublicAsset::class] = fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "node_modules/mathjax/es5/" . MathJax::SCRIPT;
            }
            public function getTarget(): string
            {
                return "components/ILIAS/MathJax/script.js";
            }
        };


        foreach (self::ASSETS as $source) {
            $contribute[Component\Resource\PublicAsset::class] = fn() =>

            // Mathjax scripts load additional files from subdirectories with relative path
            // so put everything for Mathjax in a public directory of the component
            new class ($source) implements Component\Resource\PublicAsset {
                private string $source;
                public function __construct(string $source)
                {
                    $this->source = $source;
                }
                public function getSource(): string
                {
                    return "node_modules/mathjax/es5/" . $this->source;
                }
                public function getTarget(): string
                {
                    return "components/ILIAS/MathJax/" . $this->source;
                }
            };
        }
    }
}
