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

namespace ILIAS\MetaData\Services;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Services\Reader\FactoryInterface as ReaderFactoryInterface;
use ILIAS\MetaData\Services\Manipulator\FactoryInterface as ManipulatorFactoryInterface;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Services\Reader\NullFactory as NullReaderFactory;
use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Reader\NullReader;
use ILIAS\MetaData\Services\Manipulator\NullFactory as NullManipulatorFactory;
use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Services\Manipulator\NullManipulator;
use ILIAS\MetaData\Paths\NullPath;

class ServicesTest extends TestCase
{
    protected function getServices(): Services
    {
        return new class () extends Services {
            public array $repositories = [];

            public function __construct()
            {
            }

            protected function repository(): RepositoryInterface
            {
                return $this->repositories[] = new class () extends NullRepository {
                    public array $deleted_md = [];

                    public function getMD(int $obj_id, int $sub_id, string $type): SetInterface
                    {
                        return new class ($obj_id, $sub_id, $type) extends NullSet {
                            public array $data;

                            public function __construct(int $obj_id, int $sub_id, string $type)
                            {
                                $this->data = [
                                    'obj_id' => $obj_id,
                                    'sub_id' => $sub_id,
                                    'type' => $type,
                                ];
                            }
                        };
                    }

                    public function getMDOnPath(
                        PathInterface $path,
                        int $obj_id,
                        int $sub_id,
                        string $type
                    ): SetInterface {
                        return new class ($path, $obj_id, $sub_id, $type) extends NullSet {
                            public array $data;

                            public function __construct(PathInterface $path, int $obj_id, int $sub_id, string $type)
                            {
                                $this->data = [
                                    'path' => $path,
                                    'obj_id' => $obj_id,
                                    'sub_id' => $sub_id,
                                    'type' => $type,
                                ];
                            }
                        };
                    }

                    public function deleteAllMD(int $obj_id, int $sub_id, string $type): void
                    {
                        $this->deleted_md[] = [
                            'obj_id' => $obj_id,
                            'sub_id' => $sub_id,
                            'type' => $type
                        ];
                    }
                };
            }

            protected function readerFactory(): ReaderFactoryInterface
            {
                return new class () extends NullReaderFactory {
                    public function get(SetInterface $set): ReaderInterface
                    {
                        return new class ($set) extends NullReader {
                            public function __construct(public SetInterface $set)
                            {
                            }
                        };
                    }
                };
            }

            protected function manipulatorFactory(): ManipulatorFactoryInterface
            {
                return new class () extends NullManipulatorFactory {
                    public function get(SetInterface $set): ManipulatorInterface
                    {
                        return new class ($set) extends NullManipulator {
                            public function __construct(public SetInterface $set)
                            {
                            }
                        };
                    }
                };
            }
        };
    }

    public function testRead(): void
    {
        $services = $this->getServices();
        $reader = $services->read(5, 17, 'type');

        $this->assertSame(
            ['obj_id' => 5, 'sub_id' => 17, 'type' => 'type'],
            $reader->set->data
        );
    }

    public function testReadWithPath(): void
    {
        $services = $this->getServices();
        $path = new NullPath();
        $reader = $services->read(5, 17, 'type', $path);

        $this->assertSame(
            ['path' => $path, 'obj_id' => 5, 'sub_id' => 17, 'type' => 'type'],
            $reader->set->data
        );
    }

    public function testReadWithSubIDZero(): void
    {
        $services = $this->getServices();
        $reader = $services->read(23, 0, 'type');

        $this->assertSame(
            ['obj_id' => 23, 'sub_id' => 23, 'type' => 'type'],
            $reader->set->data
        );
    }

    public function testManipulate(): void
    {
        $services = $this->getServices();
        $manipulator = $services->manipulate(5, 17, 'type');

        $this->assertSame(
            ['obj_id' => 5, 'sub_id' => 17, 'type' => 'type'],
            $manipulator->set->data
        );
    }

    public function testManipulateWithSubIDZero(): void
    {
        $services = $this->getServices();
        $manipulator = $services->manipulate(35, 0, 'type');

        $this->assertSame(
            ['obj_id' => 35, 'sub_id' => 35, 'type' => 'type'],
            $manipulator->set->data
        );
    }

    public function testDeleteAll(): void
    {
        $services = $this->getServices();
        $services->deleteAll(34, 90, 'type');

        $this->assertCount(1, $services->repositories);
        $this->assertCount(1, $services->repositories[0]->deleted_md);
        $this->assertSame(
            ['obj_id' => 34, 'sub_id' => 90, 'type' => 'type'],
            $services->repositories[0]->deleted_md[0]
        );
    }

    public function testDeleteAllWithSubIDZero(): void
    {
        $services = $this->getServices();
        $services->deleteAll(789, 0, 'type');

        $this->assertCount(1, $services->repositories);
        $this->assertCount(1, $services->repositories[0]->deleted_md);
        $this->assertSame(
            ['obj_id' => 789, 'sub_id' => 789, 'type' => 'type'],
            $services->repositories[0]->deleted_md[0]
        );
    }
}
