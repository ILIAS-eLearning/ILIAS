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

namespace ILIAS\MetaData\Repository\IdentifierHandler;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Manipulator\NullManipulator;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\BuilderInterface as PathBuilder;
use ILIAS\MetaData\Paths\NullBuilder as NullPathBuilder;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\NullPath;

class IdentifierHandlerTest extends TestCase
{
    protected function getSet(): SetInterface
    {
        return new class () extends NullSet {
            public array $prepared_changes = [];
        };
    }

    protected function getRessourceID(int $obj_id, int $sub_id, string $type): RessourceIDInterface
    {
        return new class ($obj_id, $sub_id, $type) extends NullRessourceID {
            public function __construct(
                protected int $obj_id,
                protected int $sub_id,
                protected string $type
            ) {
            }

            public function objID(): int
            {
                return $this->obj_id;
            }

            public function subID(): int
            {
                return $this->sub_id;
            }

            public function type(): string
            {
                return $this->type;
            }
        };
    }

    protected function getIdentifierHandler(): IdentifierHandler
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

        return new class ($manipulator, $path_factory) extends IdentifierHandler {
            protected function getInstallID(): string
            {
                return 'MockInstID';
            }
        };
    }

    public function testPrepareUpdateOfIdentifier(): void
    {
        $set = $this->getSet();
        $ressource_id = $this->getRessourceID(78, 983, 'TargetType');
        $identifier_handler = $this->getIdentifierHandler();

        $prepared_set = $identifier_handler->prepareUpdateOfIdentifier($set, $ressource_id);

        $expected_entry_changes = [
            'path' => '~start~%general%identifier{index:0}%entry',
            'values' => ['il_MockInstID_TargetType_983']
        ];
        $expected_catalog_changes = [
            'path' => '~start~%general%identifier{index:0}%catalog',
            'values' => ['ILIAS']
        ];
        $prepared_changes = $prepared_set->prepared_changes;
        $this->assertCount(2, $prepared_changes);
        $this->assertContains($expected_entry_changes, $prepared_changes);
        $this->assertContains($expected_catalog_changes, $prepared_changes);
    }

    public function testPrepareUpdateOfIdentifierForSubIDZero(): void
    {
        $set = $this->getSet();
        $ressource_id = $this->getRessourceID(78, 0, 'TargetType');
        $identifier_handler = $this->getIdentifierHandler();

        $prepared_set = $identifier_handler->prepareUpdateOfIdentifier($set, $ressource_id);

        $expected_entry_changes = [
            'path' => '~start~%general%identifier{index:0}%entry',
            'values' => ['il_MockInstID_TargetType_78']
        ];
        $expected_catalog_changes = [
            'path' => '~start~%general%identifier{index:0}%catalog',
            'values' => ['ILIAS']
        ];
        $prepared_changes = $prepared_set->prepared_changes;
        $this->assertCount(2, $prepared_changes);
        $this->assertContains($expected_entry_changes, $prepared_changes);
        $this->assertContains($expected_catalog_changes, $prepared_changes);
    }
}
