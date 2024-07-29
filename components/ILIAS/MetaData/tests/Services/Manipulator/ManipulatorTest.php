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
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Repository\NullRepository;

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

    protected function getManipulator(bool $throw_exception = false): Manipulator
    {
        $internal_manipulator = new class ($throw_exception) extends NullInternalManipulator {
            public array $executed_actions = [];

            public function __construct(protected bool $throw_exception)
            {
            }

            public function prepareCreateOrUpdate(
                SetInterface $set,
                PathInterface $path,
                string ...$values
            ): SetInterface {
                if ($this->throw_exception) {
                    throw new \ilMDPathException('failed');
                }

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
                if ($this->throw_exception) {
                    throw new \ilMDPathException('failed');
                }

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
        };

        $repository = new class ($throw_exception) extends NullRepository {
            public array $executed_actions = [];

            public function __construct(protected bool $throw_exception)
            {
            }

            public function manipulateMD(SetInterface $set): void
            {
                if ($this->throw_exception) {
                    throw new \ilMDRepositoryException('failed');
                }

                $this->executed_actions[] = $set->actions;
            }
        };

        $set = new class () extends NullSet {
            public array $actions = [];
        };

        return new class ($internal_manipulator, $repository, $set) extends Manipulator {
            public function exposeSet(): SetInterface
            {
                return $this->set;
            }

            public function exposeRepository(): RepositoryInterface
            {
                return $this->repository;
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

    public function testPrepareCreateOrUpdateException(): void
    {
        $manipulator = $this->getManipulator(true);

        $this->expectException(\ilMDServicesException::class);
        $manipulator->prepareCreateOrUpdate($this->getPath('path'));
    }

    public function testPrepareForceCreate(): void
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

    public function testPrepareForceCreateException(): void
    {
        $manipulator = $this->getManipulator(true);

        $this->expectException(\ilMDServicesException::class);
        $manipulator->prepareForceCreate($this->getPath('path'));
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
        $manipulator = $manipulator
            ->prepareForceCreate($this->getPath($exp5['path']), ...$exp5['values'])
            ->prepareDelete($this->getPath($exp6['path']))
            ->prepareCreateOrUpdate($this->getPath($exp7['path']), ...$exp7['values']);
        $manipulator->execute();

        $executed_actions = $manipulator->exposeRepository()->executed_actions;
        $this->assertCount(2, $executed_actions);
        $this->assertSame(
            [$exp1, $exp2, $exp3, $exp4],
            $executed_actions[0]
        );
        $this->assertSame(
            [$exp1, $exp2, $exp3, $exp4, $exp5, $exp6, $exp7],
            $executed_actions[1]
        );
    }

    public function testExecuteException(): void
    {
        $manipulator = $this->getManipulator(true);

        $this->expectException(\ilMDServicesException::class);
        $manipulator->execute();
    }
}
