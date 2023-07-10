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

namespace ILIAS\ResourceStorage\Flavours;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FitToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Flavour\Definition\ToGreyScale;
use ILIAS\ResourceStorage\Flavour\Engine\Engine;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;
use ILIAS\ResourceStorage\Flavour\Engine\NoEngine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropSquare;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\ExtractPages;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\FitSquare;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\MakeGreyScale;
use ILIAS\ResourceStorage\Flavour\Machine\Factory;
use ILIAS\ResourceStorage\Flavour\Machine\NullMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FlavourMachineTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
require_once __DIR__ . '/../AbstractBaseTest.php';
require_once __DIR__ . '/DummyDefinition.php';
require_once __DIR__ . '/DummyMachine.php';
require_once __DIR__ . '/BrokenDummyMachine.php';
require_once __DIR__ . '/SVGDummyMachine.php';

class FlavourMachineTest extends AbstractBaseTest
{
    /**
     * @var ImagickEngine|MockObject
     */
    private $imagick_mock;
    /**
     * @var GDEngine|MockObject
     */
    private $gd_mock;
    /**
     * @var \ILIAS\ResourceStorage\Flavour\Engine\Factory|MockObject
     */
    private \PHPUnit\Framework\MockObject\MockObject $engine_factory_mock;

    private array $engine_mocks = [];

    protected function setUp(): void
    {
        $this->engine_factory_mock = $this->createMock(\ILIAS\ResourceStorage\Flavour\Engine\Factory::class);
    }

    public function testFactory(): void
    {
        $factory = new Factory($this->engine_factory_mock, [
            \stdClass::class => \stdClass::class,
            BrokenDummyMachine::class => BrokenDummyMachine::class
        ]);

        // Not a machine
        $definition = $this->createMock(FlavourDefinition::class);
        $definition->expects($this->once())->method('getFlavourMachineId')->willReturn(\stdClass::class);

        $null_machine = $factory->get($definition);
        $this->assertInstanceOf(NullMachine::class, $null_machine);
        $this->assertEquals('Machine stdClass does not implement FlavourMachine', $null_machine->getReason());
        $this->assertEquals('null_machine', $null_machine->getId());
        $this->assertEquals(NoEngine::class, $null_machine->dependsOnEngine());

        // Broken machine
        $definition = $this->createMock(FlavourDefinition::class);
        $definition->expects($this->once())->method('getFlavourMachineId')->willReturn(BrokenDummyMachine::class);
        $null_machine = $factory->get($definition);
        $this->assertInstanceOf(NullMachine::class, $null_machine);
        $this->assertEquals(
            'Could not instantiate machine ILIAS\ResourceStorage\Flavours\BrokenDummyMachine',
            $null_machine->getReason()
        );
        $this->assertEquals('null_machine', $null_machine->getId());
        $this->assertEquals(NoEngine::class, $null_machine->dependsOnEngine());
    }

    public function definitionsToMachines(): array
    {
        return [
            [new PagesToExtract(true), ExtractPages::class, ImagickEngine::class],
            [new CropToSquare(), CropSquare::class, GDEngine::class],
            [new FitToSquare(), FitSquare::class, GDEngine::class],
            [new ToGreyScale(), MakeGreyScale::class, GDEngine::class],
        ];
    }


    /**
     * @dataProvider definitionsToMachines
     */
    public function testDefaultMachines(FlavourDefinition $d, string $machine): void
    {
        $factory = new Factory($this->engine_factory_mock);
        $this->engine_factory_mock->expects($this->exactly(1))
            ->method('get')
            ->willReturn(new NoEngine());

        $machine_instance = $factory->get($d);
        $this->assertInstanceOf($machine, $machine_instance);
        $machine_instance_second_get = $factory->get($d);
        $this->assertSame($machine_instance, $machine_instance_second_get);
    }

    public function machinesToEngines(): array
    {
        return [
            [ExtractPages::class, ImagickEngine::class],
            [CropSquare::class, GDEngine::class],
            [FitSquare::class, GDEngine::class],
            [MakeGreyScale::class, GDEngine::class],
        ];
    }

    /**
     * @dataProvider machinesToEngines
     */
    public function testDefaultMachineEngines(string $machine, string $engine): void
    {
        $factory = new \ILIAS\ResourceStorage\Flavour\Engine\Factory();
        $engin_instance = $factory->get(new $machine());
        $this->assertInstanceOf($engine, $engin_instance);
    }

    /**
     * @dataProvider definitionsToMachines
     */
    public function testNullMachineFallback(FlavourDefinition $d, string $machine, string $engine): void
    {
        $factory = new Factory($this->engine_factory_mock);

        $engine_mock = $this->createMock(Engine::class);

        $this->engine_factory_mock->expects($this->once())
            ->method('get')
            ->willReturn($engine_mock);

        $engine_mock->expects($this->once())
            ->method('isRunning')
            ->willReturn(false);

        $machine_instance = $factory->get($d);
        $this->assertInstanceOf(NullMachine::class, $machine_instance);
        $this->assertEquals(
            "Machine $machine depends on engine $engine which is not running or available.",
            $machine_instance->getReason()
        );
    }


    public function testMachineResult(): void
    {
        $svg_stream = Streams::ofString(
            '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 155 155"><defs><style>.cls-1{fill:red;}</style></defs><g><g><rect class="cls-1" x="3" y="3" width="150" height="150"/><path d="M151.14,6V151.14H6V6H151.14m6-6H0V157.14H157.14V0h0Z"/></g></g></svg>'
        );
        $machine = new SVGDummyMachine();
        ;
        $definition = $this->createSVGColorChangeDefinition('red', 'blue');
        $file_info = new FileInformation();

        $result = iterator_to_array($machine->processStream($file_info, $svg_stream, $definition));
        $this->assertCount(1, $result);
        $result_one = $result[0];
        $this->assertInstanceOf(Result::class, $result_one);
        $this->assertEquals($definition, $result_one->getDefinition());
        $this->assertInstanceOf(FileStream::class, $result_one->getStream());
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 155 155"><defs><style>.cls-1{fill:blue;}</style></defs><g><g><rect class="cls-1" x="3" y="3" width="150" height="150"/><path d="M151.14,6V151.14H6V6H151.14m6-6H0V157.14H157.14V0h0Z"/></g></g></svg>',
            (string)$result_one->getStream()
        );
    }

    private function createSVGColorChangeDefinition(string $color, string $to_color): FlavourDefinition
    {
        return new class ($color, $to_color) extends DummyDefinition {
            private string $color;
            private string $to_color;

            public function __construct(string $color, string $to_color)
            {
                $this->color = $color;
                $this->to_color = $to_color;
                parent::__construct(
                    'svg_color_changer',
                    'svg_color_changing_machine'
                );
            }

            public function getColor(): string
            {
                return $this->color;
            }

            public function getToColor(): string
            {
                return $this->to_color;
            }
        };
    }
}
