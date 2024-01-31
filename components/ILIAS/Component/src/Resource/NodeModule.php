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

namespace ILIAS\Component\Resource;

/**
 * Some distributable file created by npm.
 */
class NodeModule implements PublicAsset
{
    public const REGEXP_SOURCE = '%^(((\w|.)+(/(\w|.)+)*\.\w{2,4}))$%';

    /**
     * @param $component this belongs to
     * @param $target path relative to the ILIAS public directory, filename of resource will be appended. Use one dot for toplevel.
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
        $source = explode("/", $this->source);
        return ComponentJS::JS_TARGET . "/" . array_pop($source);
    }
}
