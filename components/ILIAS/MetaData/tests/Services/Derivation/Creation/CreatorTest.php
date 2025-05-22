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

namespace ILIAS\MetaData\Services\Derivation\Creation;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Services\Derivation\Creation\Creator;
use ILIAS\MetaData\Manipulator\NullManipulator;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullBuilder as NullPathBuilder;
use ILIAS\MetaData\Paths\BuilderInterface as PathBuilder;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\NullScaffoldProvider;

class CreatorTest extends TestCase
{
    protected function getCreator(): Creator
    {
        $manipulator = new class () extends NullManipulator {
            public function prepareCreateOrUpdate(
                SetInterface $set,
                PathInterface $path,
                string ...$values
            ): SetInterface {
                $set = clone $set;
                $set->prepared_changes[] = [
                    'path' => $path->toString(),
                    'values' => $values
                ];
                return $set;
            }

            public function prepareDelete(SetInterface $set, PathInterface $path): SetInterface
            {
                $set = clone $set;
                $set->prepared_changes[] = ['delete should not be prepared!'];
                return $set;
            }

            public function prepareForceCreate(
                SetInterface $set,
                PathInterface $path,
                string ...$values
            ): SetInterface {
                $set = clone $set;
                $set->prepared_changes[] = ['force create should not be prepared!'];
                return $set;
            }
        };

        $builder = new class () extends NullPathBuilder {
            protected string $path_string = '~start~';

            public function withNextStep(string $name, bool $add_as_first = false): PathBuilder
            {
                $builder = clone $this;
                if ($add_as_first) {
                    $name .= '[added as first]';
                }
                $builder->path_string .= '%' . $name;
                return $builder;
            }

            public function withAdditionalFilterAtCurrentStep(FilterType $type, string ...$values): PathBuilder
            {
                $builder = clone $this;
                $builder->path_string .= '{' . $type->value . ':' . implode('><', $values) . '}';
                return $builder;
            }

            public function get(): PathInterface
            {
                return new class ($this->path_string) extends NullPath {
                    public function __construct(protected string $path_string)
                    {
                    }

                    public function toString(): string
                    {
                        return $this->path_string;
                    }
                };
            }
        };

        $path_factory = new class ($builder) extends NullPathFactory {
            public function __construct(protected PathBuilder $builder)
            {
            }

            public function custom(): PathBuilder
            {
                return $this->builder;
            }
        };

        $scaffold_provider = new class () extends NullScaffoldProvider {
            public function set(): SetInterface
            {
                return new class () extends NullSet {
                    public array $prepared_changes = [];
                };
            }
        };

        return new Creator($manipulator, $path_factory, $scaffold_provider);
    }

    public function testCreateSet(): void
    {
        $creator = $this->getCreator();

        $set = $creator->createSet('some title');

        $expected_title_changes = [
            'path' => '~start~%general%title%string',
            'values' => ['some title']
        ];
        $prepared_changes = $set->prepared_changes;
        $this->assertCount(1, $prepared_changes);
        $this->assertContains($expected_title_changes, $prepared_changes);
    }

    public function testCreateSetWithEmptyTitle(): void
    {
        $creator = $this->getCreator();

        $set = $creator->createSet('');

        $expected_title_changes = [
            'path' => '~start~%general%title%string',
            'values' => ['some title']
        ];
        $prepared_changes = $set->prepared_changes;
        $this->assertCount(1, $prepared_changes);
        $this->assertSame('~start~%general%title%string', $prepared_changes[0]['path']);
        $this->assertCount(1, $prepared_changes[0]['values']);
        $this->assertNotSame('', $prepared_changes[0]['values'][0]);
    }

    public function testCreateSetWithLanguage(): void
    {
        $creator = $this->getCreator();

        $set = $creator->createSet('some title', '', 'tg');

        $expected_title_changes = [
            'path' => '~start~%general%title%string',
            'values' => ['some title']
        ];
        $expected_title_lang_changes = [
            'path' => '~start~%general%title%language',
            'values' => ['tg']
        ];
        $expected_lang_changes = [
            'path' => '~start~%general%language',
            'values' => ['tg']
        ];
        $prepared_changes = $set->prepared_changes;
        $this->assertCount(3, $prepared_changes);
        $this->assertContains($expected_title_changes, $prepared_changes);
        $this->assertContains($expected_title_lang_changes, $prepared_changes);
        $this->assertContains($expected_lang_changes, $prepared_changes);
    }

    public function testCreateSetWithDescription(): void
    {
        $creator = $this->getCreator();

        $set = $creator->createSet('some title', 'some description');

        $expected_title_changes = [
            'path' => '~start~%general%title%string',
            'values' => ['some title']
        ];
        $expected_description_changes = [
            'path' => '~start~%general%description%string',
            'values' => ['some description']
        ];
        $prepared_changes = $set->prepared_changes;
        $this->assertCount(2, $prepared_changes);
        $this->assertContains($expected_title_changes, $prepared_changes);
        $this->assertContains($expected_description_changes, $prepared_changes);
    }

    public function testCreateSetWithDescriptionAndLanguage(): void
    {
        $creator = $this->getCreator();

        $set = $creator->createSet('some title', 'some description', 'tg');

        $expected_title_changes = [
            'path' => '~start~%general%title%string',
            'values' => ['some title']
        ];
        $expected_title_lang_changes = [
            'path' => '~start~%general%title%language',
            'values' => ['tg']
        ];
        $expected_lang_changes = [
            'path' => '~start~%general%language',
            'values' => ['tg']
        ];
        $expected_description_changes = [
            'path' => '~start~%general%description%string',
            'values' => ['some description']
        ];
        $expected_description_lang_changes = [
            'path' => '~start~%general%description%language',
            'values' => ['tg']
        ];
        $prepared_changes = $set->prepared_changes;
        $this->assertCount(5, $prepared_changes);
        $this->assertContains($expected_title_changes, $prepared_changes);
        $this->assertContains($expected_title_lang_changes, $prepared_changes);
        $this->assertContains($expected_lang_changes, $prepared_changes);
        $this->assertContains($expected_description_changes, $prepared_changes);
        $this->assertContains($expected_description_lang_changes, $prepared_changes);
    }
}
