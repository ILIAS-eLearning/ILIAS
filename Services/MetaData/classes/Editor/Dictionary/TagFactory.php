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

namespace ILIAS\MetaData\Editor\Dictionary;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class TagFactory
{
    protected PathFactoryInterface $path_factory;

    public function __construct(
        PathFactoryInterface $path_factory
    ) {
        $this->path_factory = $path_factory;
    }
    public function forElement(
        StructureElementInterface $element
    ): TagBuilder {
        return new TagBuilder($this->path_factory, $element);
    }
}
