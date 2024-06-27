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

namespace ILIAS\MathJax;

use ILIAS\Component\Resource\PublicAsset;
use ILIAS\Component\Resource\ComponentJS;
use ILIAS\Component\Resource\ComponentCSS;

/**
 * Some distributable file created by npm.
 * Adapted for MathJax to support directories in target locaion
 *
 * see \ILIAS\Component\Resource\NodeModule
 */
class MathJaxNodeModule implements PublicAsset
{
    public const REGEXP_SOURCE = '%^(((\w|.)+(/(\w|.)+)*\.\w{2,4}))$%';
    public const TARGET = "mathjax";

    /**
     * @param string $source  path relative to node_modules
     */
    public function __construct(
        protected string $source
    ) {
        if (!preg_match(self::REGEXP_SOURCE, $this->source)) {
            throw new \InvalidArgumentException(
                "'{$this->source}' is not a valid source path for a public asset."
            );
        }
    }

    public function getSource(): string
    {
        return "node_modules/{$this->source}";
    }

    public function getTarget(): string
    {
        return "mathjax/{$this->source}";
    }
}
