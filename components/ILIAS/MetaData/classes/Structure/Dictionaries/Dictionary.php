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

namespace ILIAS\MetaData\Structure\Dictionaries;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagAssignmentInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\ElementInterface;

abstract class Dictionary
{
    protected PathFactoryInterface $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;

    /**
     * @var TagAssignmentInterface[]
     */
    private array $tag_assignments;

    public function __construct(
        PathFactoryInterface $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        TagAssignmentInterface ...$tag_assignments
    ) {
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->tag_assignments = $tag_assignments;
    }

    /**
     * If possible, takes into account the index of
     * elements when finding tags (beginning with 0).
     * @return TagInterface[]
     */
    public function tagsForElement(
        BaseElementInterface $element
    ): \Generator {
        $path = $this->path_factory->toElement($element);
        foreach ($this->getAssignmentsForElement($path) as $assignment) {
            $tag = $assignment->tag();
            if (!$this->doesIndexMatch($path, $element, $tag)) {
                continue;
            }
            yield $tag;
        }
    }

    /**
     * @return TagAssignmentInterface[]
     */
    protected function getAssignmentsForElement(
        PathInterface $path
    ): \Generator {
        foreach ($this->tag_assignments as $assignment) {
            if ($assignment->matchesPath($path)) {
                yield $assignment;
            }
        }
    }

    protected function doesIndexMatch(
        PathInterface $path,
        BaseElementInterface $element,
        TagInterface $tag
    ): bool {
        if (!($element instanceof ElementInterface)) {
            return true;
        }
        if (!$tag->isRestrictedToIndices()) {
            return true;
        }
        $index = $this->findIndexOfElement($path, $element);
        if (in_array($index, iterator_to_array($tag->indices()), true)) {
            return true;
        }
        return false;
    }

    protected function findIndexOfElement(
        PathInterface $path,
        ElementInterface $element
    ): int {
        $name = $element->getDefinition()->name();
        $navigator = $this->navigator_factory->navigator($path, $element);
        $index = 0;
        foreach ($navigator->elementsAtFinalStep() as $sibling_element) {
            if ($sibling_element === $element) {
                return $index;
            }
            $index++;
        }
        throw new \ilMDStructureException('Invalid metadata set');
    }
}
