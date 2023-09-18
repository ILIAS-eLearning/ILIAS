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

namespace ILIAS\MetaData\Elements\Structure;

use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;

class StructureFactory
{
    public function structure(
        DefinitionInterface $definition,
        StructureElement ...$sub_elements
    ): StructureElement {
        return $this->element(false, $definition, ...$sub_elements);
    }

    public function root(
        DefinitionInterface $definition,
        StructureElement ...$sub_elements
    ): StructureElementInterface {
        return $this->element(true, $definition, ...$sub_elements);
    }

    protected function element(
        bool $is_root,
        DefinitionInterface $definition,
        StructureElement ...$sub_elements
    ): StructureElement {
        return new StructureElement(
            $is_root,
            $definition,
            ...$sub_elements
        );
    }

    public function set(
        StructureElementInterface $root
    ): StructureSetInterface {
        return new StructureSet($root);
    }
}
