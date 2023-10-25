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

namespace ILIAS\MetaData\Services\Manipulator;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Manipulator\NullManipulator as NullInternalManipulator;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\SetInterface;

class ManipulatorTest extends TestCase
{
    protected function getPath(string $string): PathInterface
    {
        return new class ($string) extends NullPath {
            public function __construct(public string $string)
            {
            }
        };
    }

    protected function getManipulator(): Manipulator
    {
        $internal_manipulator = new class () extends NullInternalManipulator {
            public array $executed_actions = [];

            public function prepareCreateOrUpdate(
                SetInterface $set,
                PathInterface $path,
                string ...$values
            ): SetInterface {
                $cloned_set = clone $set;
                $cloned_set->actions[] = [
                    'action' => 'create or update',
                    'path' => $path->string,
                    'values' => $values
                ];
                return $cloned_set;
            }

            public function prepareForceCreate(
                SetInterface $set,
                PathInterface $path,
                string ...$values
            ): SetInterface {
                $cloned_set = clone $set;
                $cloned_set->actions[] = [
                    'action' => 'force create',
                    'path' => $path->string,
                    'values' => $values
                ];
                return $cloned_set;
            }

            public function prepareDelete(
                SetInterface $set,
                PathInterface $path
            ): SetInterface {
                $cloned_set = clone $set;
                $cloned_set->actions[] = [
                    'action' => 'delete',
                    'path' => $path->string
                ];
                return $cloned_set;
            }

            public function execute(SetInterface $set): void
            {
                $set->executed_actions = $set->actions;
            }
        };

        $set = new class () extends NullSet {
            public array $actions = [];
            public array $executed_actions = [];
        };

        return new class ($internal_manipulator, $set) extends Manipulator {
            public function exposeSet(): SetInterface
            {
                return $this->set;
            }
        };
    }

    public function testPrepareCreateOrUpdate(): void
    {
        $exp1 = [
            'action' => 'create or update',
            'path' => 'path 1',
            'values' => ['value 1']
        ];
        $exp2 = [
            'action' => 'create or update',
            'path' => 'path 2',
            'values' => ['value 20', 'value 21', 'value 22']
        ];
        $manipulator = $this->getManipulator();
        $manipulator1 = $manipulator->prepareCreateOrUpdate(
            $this->getPath($exp1['path']),
            ...$exp1['values']
        );
        $manipulator2 = $manipulator1->prepareCreateOrUpdate(
            $this->getPath($exp2['path']),
            ...$exp2['values']
        );


        $this->assertSame(
            [],
            $manipulator->exposeSet()->actions
        );
        $this->assertSame(
            [$exp1],
            $manipulator1->exposeSet()->actions
        );
        $this->assertSame(
            [$exp1, $exp2],
            $manipulator2->exposeSet()->actions
        );
    }

    public function testPrepareForce(): void
    {
        $exp1 = [
            'action' => 'force create',
            'path' => 'path 1',
            'values' => ['value 1']
        ];
        $exp2 = [
            'action' => 'force create',
            'path' => 'path 2',
            'values' => ['value 20', 'value 21', 'value 22']
        ];
        $manipulator = $this->getManipulator();
        $manipulator1 = $manipulator->prepareForceCreate(
            $this->getPath($exp1['path']),
            ...$exp1['values']
        );
        $manipulator2 = $manipulator1->prepareForceCreate(
            $this->getPath($exp2['path']),
            ...$exp2['values']
        );


        $this->assertSame(
            [],
            $manipulator->exposeSet()->actions
        );
        $this->assertSame(
            [$exp1],
            $manipulator1->exposeSet()->actions
        );
        $this->assertSame(
            [$exp1, $exp2],
            $manipulator2->exposeSet()->actions
        );
    }

    public function testPrepareDelete(): void
    {
        $exp1 = [
            'action' => 'delete',
            'path' => 'path 1'
        ];
        $exp2 = [
            'action' => 'delete',
            'path' => 'path 2'
        ];
        $manipulator = $this->getManipulator();
        $manipulator1 = $manipulator->prepareDelete($this->getPath($exp1['path']));
        $manipulator2 = $manipulator1->prepareDelete($this->getPath($exp2['path']));


        $this->assertSame(
            [],
            $manipulator->exposeSet()->actions
        );
        $this->assertSame(
            [$exp1],
            $manipulator1->exposeSet()->actions
        );
        $this->assertSame(
            [$exp1, $exp2],
            $manipulator2->exposeSet()->actions
        );
    }

    public function testExecute(): void
    {
        $exp1 = [
            'action' => 'delete',
            'path' => 'path 1'
        ];
        $exp2 = [
            'action' => 'force create',
            'path' => 'path 2',
            'values' => ['value 20', 'value 21', 'value 22']
        ];
        $exp3 = [
            'action' => 'delete',
            'path' => 'path 3'
        ];
        $exp4 = [
            'action' => 'create or update',
            'path' => 'path 4',
            'values' => []
        ];
        $exp5 = [
            'action' => 'force create',
            'path' => 'path 5',
            'values' => ['value 50', 'value 51', 'value 52']
        ];
        $exp6 = [
            'action' => 'delete',
            'path' => 'path 6'
        ];
        $exp7 = [
            'action' => 'create or update',
            'path' => 'path 7',
            'values' => ['value 7']
        ];

        $manipulator = $this->getManipulator()
            ->prepareDelete($this->getPath($exp1['path']))
            ->prepareForceCreate($this->getPath($exp2['path']), ...$exp2['values'])
            ->prepareDelete($this->getPath($exp3['path']))
            ->prepareCreateOrUpdate($this->getPath($exp4['path']), ...$exp4['values']);
        $manipulator->execute();
        $manipulator5 = $manipulator
            ->prepareForceCreate($this->getPath($exp5['path']), ...$exp5['values'])
            ->prepareDelete($this->getPath($exp6['path']))
            ->prepareCreateOrUpdate($this->getPath($exp7['path']), ...$exp7['values']);
        $manipulator5->execute();

        $this->assertSame(
            [$exp1, $exp2, $exp3, $exp4],
            $manipulator->exposeSet()->executed_actions
        );
        $this->assertSame(
            [$exp1, $exp2, $exp3, $exp4, $exp5, $exp6, $exp7],
            $manipulator5->exposeSet()->executed_actions
        );
    }
}
