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

abstract class Dictionary
{
    protected PathFactoryInterface $path_factory;

    /**
     * @var TagAssignmentInterface[]
     */
    private array $tag_assignments;

    public function __construct(
        PathFactoryInterface $path_factory,
        TagAssignmentInterface ...$tag_assignments
    ) {
        $this->path_factory = $path_factory;
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
        foreach ($this->getAssignmentsForElement($element) as $assignment) {
            $tag = $assignment->tag();
            if (!$this->doesIndexMatch($element, $tag)) {
                continue;
            }
            yield $tag;
        }
    }

    /**
     * @return TagAssignmentInterface[]
     */
    protected function getAssignmentsForElement(
        BaseElementInterface $element
    ): \Generator {
        $path = $this->path_factory->toElement($element);
        foreach ($this->tag_assignments as $assignment) {
            if ($assignment->matchesPath($path)) {
                yield $assignment;
            }
        }
    }

    protected function doesIndexMatch(
        BaseElementInterface $element,
        TagInterface $tag
    ): bool {
        if ($element instanceof StructureElementInterface) {
            return true;
        }
        if (!$tag->isRestrictedToIndices()) {
            return true;
        }
        $index = $this->findIndexOfElement($element);
        if (in_array($index, iterator_to_array($tag->indices()), true)) {
            return true;
        }
        return false;
    }

    protected function findIndexOfElement(
        BaseElementInterface $element
    ): int {
        if (!($super = $element->getSuperElement())) {
            return 0;
        }

        $name = $element->getDefinition()->name();
        $index = 0;
        foreach ($super->getSubElements() as $sub) {
            if ($sub->getDefinition()->name() !== $name) {
                continue;
            }
            if ($sub === $element) {
                return $index;
            }
            $index++;
        }
        throw new \ilMDStructureException('Invalid metadata set');
    }
}
