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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Structure\NullStructureSet;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\NullTag;
use ILIAS\MetaData\Elements\Structure\NullStructureElement;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagAssignmentInterface;

class DictionaryInitiatorTest extends TestCase
{
    protected function getDictionaryInitiator(): DictionaryInitiator
    {
        return new class (new NullFactory(), new NullStructureSet()) extends DictionaryInitiator {
            public function __construct(
                PathFactoryInterface $path_factory,
                StructureSetInterface $structure
            ) {
                parent::__construct($path_factory, $structure);
            }

            public function exposeAddTagToElement(
                TagInterface $tag,
                StructureElementInterface $element
            ): void {
                parent::addTagToElement($tag, $element);
            }

            public function exposeGetTagAssignments(): \Generator
            {
                yield from parent::getTagAssignments();
            }
        };
    }

    public function testAddTagToElement(): void
    {
        $initiator = $this->getDictionaryInitiator();
        $initiator->exposeAddTagToElement(
            new NullTag(),
            new NullStructureElement()
        );
        $initiator->exposeAddTagToElement(
            new NullTag(),
            new NullStructureElement()
        );
        $assignments = $initiator->exposeGetTagAssignments();

        $this->assertInstanceOf(
            TagAssignmentInterface::class,
            $assignments->current()
        );
        $assignments->next();
        $this->assertInstanceOf(
            TagAssignmentInterface::class,
            $assignments->current()
        );
        $assignments->next();
        $this->assertNull($assignments->current());
    }
}
