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

namespace ILIAS\MetaData\Manipulator\ScaffoldProvider;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldFactoryInterface;
use ILIAS\MetaData\Elements\SetInterface;

class ScaffoldProvider implements ScaffoldProviderInterface
{
    protected ScaffoldFactoryInterface $scaffold_factory;
    protected PathFactoryInterface $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected StructureSetInterface $structure;

    public function __construct(
        ScaffoldFactoryInterface $scaffold_factory,
        PathFactoryInterface $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        StructureSetInterface $structure,
    ) {
        $this->scaffold_factory = $scaffold_factory;
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->structure = $structure;
    }

    /**
     * @return ElementInterface[]
     */
    public function getScaffoldsForElement(
        ElementInterface $element
    ): \Generator {
        $sub_names = [];
        foreach ($element->getSubElements() as $sub) {
            $sub_names[] = $sub->getDefinition()->name();
        }

        foreach ($this->getPossibleSubElementDefinitionsForElementInOrder($element) as $sub_definition) {
            if (
                !$sub_definition->unique() ||
                !in_array($sub_definition->name(), $sub_names)
            ) {
                yield $this->scaffold_factory->scaffold($sub_definition);
            }
        }
    }

    /**
     * @return string[]
     */
    public function getPossibleSubElementNamesForElementInOrder(
        ElementInterface $element
    ): \Generator {
        foreach ($this->getPossibleSubElementDefinitionsForElementInOrder($element) as $sub_definition) {
            yield $sub_definition->name();
        }
    }

    protected function getPossibleSubElementDefinitionsForElementInOrder(
        ElementInterface $element
    ): \Generator {
        $navigator = $this->navigator_factory->structureNavigator(
            $this->path_factory->toElement($element),
            $this->structure->getRoot()
        );
        $structure_element = $navigator->elementAtFinalStep();

        foreach ($structure_element->getSubElements() as $sub) {
            yield $sub->getDefinition();
        }
    }

    public function set(): SetInterface
    {
        return $this->scaffold_factory->set($this->structure->getRoot()->getDefinition());
    }
}
