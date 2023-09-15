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

use ILIAS\MetaData\Structure\Dictionaries\Tags\TagInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagAssignmentInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagAssignment;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;

abstract class DictionaryInitiator
{
    protected PathFactoryInterface $path_factory;
    private StructureSetInterface $structure;

    /**
     * @var TagAssignmentInterface[]
     */
    private array $tag_assignments = [];

    public function __construct(
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->path_factory = $path_factory;
        $this->structure = $structure;
    }

    /**
     * When indices are added, the tag applies only
     * to copies of the element with those indices
     * (beginning with 0).
     */
    final protected function addTagToElement(
        TagInterface $tag,
        StructureElementInterface $element
    ): void {
        $this->tag_assignments[] = new TagAssignment(
            $this->path_factory->toElement($element),
            $tag
        );
    }

    /**
     * @return TagAssignmentInterface[]
     */
    final protected function getTagAssignments(): \Generator
    {
        yield from $this->tag_assignments;
    }

    final protected function getStructure(): StructureSetInterface
    {
        return $this->structure;
    }
}
