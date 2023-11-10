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
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagAssignmentInterface;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Structure\Dictionaries\Tags\NullTagAssignment;
use ILIAS\MetaData\Structure\Dictionaries\Tags\TagInterface;
use ILIAS\MetaData\Structure\Dictionaries\Tags\NullTag;
use ILIAS\MetaData\Elements\Base\NullBaseElement;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class DictionaryTest extends TestCase
{
    protected function getTagAssignment(
        int $path_id,
        string $tag_name,
        int ...$indices
    ): TagAssignmentInterface {
        return new class ($path_id, $tag_name, $indices) extends NullTagAssignment {
            public function __construct(
                protected int $path_id,
                protected string $tag_name,
                protected array $indices
            ) {
            }

            public function tag(): TagInterface
            {
                return new class ($this->tag_name, $this->indices) extends NullTag {
                    public function __construct(
                        protected string $name,
                        protected array $indices
                    ) {
                    }

                    public function name()
                    {
                        return $this->name;
                    }

                    public function indices(): \Generator
                    {
                        yield from $this->indices;
                    }

                    public function isRestrictedToIndices(): bool
                    {
                        return !empty($this->indices);
                    }
                };
            }

            public function matchesPath(PathInterface $path): bool
            {
                return (string) $this->path_id === $path->toString();
            }
        };
    }

    protected function getDictionary(): Dictionary
    {
        $tag_assignments = [
            $this->getTagAssignment(0, 'tag 0'),
            $this->getTagAssignment(0, 'tag 0 index 0', 0),
            $this->getTagAssignment(0, 'tag 0 index 3', 3),
            $this->getTagAssignment(1, 'tag 1 index 1', 1),
            $this->getTagAssignment(1, 'tag 1 index -2', -2),
            $this->getTagAssignment(1, 'tag 1 index 0 and 1', 0, 1),
            $this->getTagAssignment(1, 'tag 1 index -3 and 1', -3, 1),
            $this->getTagAssignment(2, 'tag 2'),
            $this->getTagAssignment(2, 'tag 2 number 2')
        ];

        $path_factory = new class () extends NullFactory {
            public function toElement(
                BaseElementInterface $to,
                bool $leads_to_exactly_one = false
            ): PathInterface {
                return new class ($to->getMDID()) extends NullPath {
                    public function __construct(protected int $id)
                    {
                    }

                    public function toString(): string
                    {
                        return (string) $this->id;
                    }
                };
            }
        };

        return new class ($path_factory, ...$tag_assignments) extends Dictionary {
            public function __construct(
                PathFactoryInterface $path_factory,
                TagAssignmentInterface ...$tag_assignments
            ) {
                parent::__construct($path_factory, ...$tag_assignments);
            }
        };
    }

    protected function getElement(
        int $id,
        BaseElementInterface ...$subs
    ): BaseElementInterface {
        return new class ($id, $subs) extends NullBaseElement {
            protected ?BaseElementInterface $super = null;
            public function __construct(
                protected int $id,
                protected array $subs
            ) {
            }

            public function getDefinition(): DefinitionInterface
            {
                return new class ($this->id) extends NullDefinition {
                    public function __construct(protected int $id)
                    {
                    }

                    public function name(): string
                    {
                        return (string) $this->id;
                    }
                };
            }

            public function getSubElements(): \Generator
            {
                yield from $this->subs;
            }

            public function getSuperElement(): ?BaseElementInterface
            {
                return $this->super;
            }

            public function setSuperElement(BaseElementInterface $super): void
            {
                $this->super = $super;
            }

            public function getMDID(): int|NoID
            {
                return $this->id;
            }
        };
    }

    protected function getElements(): array
    {
        $el2 = $this->getElement(2);
        $el10 = $this->getElement(1);
        $el11 = $this->getElement(1);
        $el12 = $this->getElement(1);
        $el3 = $this->getElement(3);
        $el0 = $this->getElement(0, $el2, $el10, $el11, $el12, $el3);

        $el2->setSuperElement($el0);
        $el10->setSuperElement($el0);
        $el11->setSuperElement($el0);
        $el12->setSuperElement($el0);
        $el3->setSuperElement($el0);

        return [
            'el0' => $el0,
            'el2' => $el2,
            'el10' => $el10,
            'el11' => $el11,
            'el12' => $el12,
            'el3' => $el3
        ];
    }

    public function testTagForElement(): void
    {
        $dict = $this->getDictionary();
        $tags = $dict->tagsForElement($this->getElements()['el2']);

        $this->assertSame(
            'tag 2',
            $tags->current()->name()
        );
        $tags->next();
        $this->assertSame(
            'tag 2 number 2',
            $tags->current()->name()
        );
        $tags->next();
        $this->assertNull($tags->current());

        $tags = $dict->tagsForElement($this->getElements()['el3']);
        $this->assertNull($tags->current());
    }

    public function testTagForElementWithIndices(): void
    {
        $dict = $this->getDictionary();
        $els = $this->getElements();
        $tags10 = $dict->tagsForElement($this->getElements()['el10']);
        $tags11 = $dict->tagsForElement($this->getElements()['el11']);
        $tags12 = $dict->tagsForElement($this->getElements()['el12']);

        $this->assertSame(
            'tag 1 index 0 and 1',
            $tags10->current()->name()
        );
        $tags10->next();
        $this->assertNull($tags10->current());

        $this->assertSame(
            'tag 1 index 1',
            $tags11->current()->name()
        );
        $tags11->next();
        $this->assertSame(
            'tag 1 index 0 and 1',
            $tags11->current()->name()
        );
        $tags11->next();
        $this->assertSame(
            'tag 1 index -3 and 1',
            $tags11->current()->name()
        );
        $tags11->next();
        $this->assertNull($tags11->current());

        $this->assertNull($tags12->current());
    }

    public function testTagForElementAtRoot(): void
    {
        $dict = $this->getDictionary();
        $tags = $dict->tagsForElement($this->getElements()['el0']);

        $this->assertSame(
            'tag 0',
            $tags->current()->name()
        );
        $tags->next();
        $this->assertSame(
            'tag 0 index 0',
            $tags->current()->name()
        );
        $tags->next();
        $this->assertNull($tags->current());
    }
}
