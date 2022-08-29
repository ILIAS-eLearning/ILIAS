<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/ilCtrlPathTestBase.php';

/**
 * Class ilCtrlPathFactoryTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathFactoryTest extends ilCtrlPathTestBase
{
    /**
     * @var ilCtrlPathFactory
     */
    private ilCtrlPathFactory $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ilCtrlPathFactory($this->structure);
    }

    public function testPathFactoryFindSingleClassTargetPath(): void
    {
        $context = $this->createMock(ilCtrlContextInterface::class);
        $path = $this->factory->find($context, ilCtrlBaseClass1TestGUI::class);

        $this->assertEquals('0', $path->getCidPath());
        $this->assertInstanceOf(
            ilCtrlSingleClassPath::class,
            $path
        );
    }

    public function testPathFactoryFindArrayClassTargetPath(): void
    {
        $context = $this->createMock(ilCtrlContextInterface::class);
        $path = $this->factory->find($context, [
            ilCtrlBaseClass1TestGUI::class,
            ilCtrlCommandClass1TestGUI::class,
        ]);

        $this->assertEquals('0:2', $path->getCidPath());
        $this->assertInstanceOf(
            ilCtrlArrayClassPath::class,
            $path
        );
    }

    public function testPathFactoryNullPath(): void
    {
        $path = $this->factory->null();

        $this->assertNull($path->getCidPath());
        $this->assertInstanceOf(
            ilCtrlNullPath::class,
            $path
        );
    }

    public function testPathFactoryExistingPath(): void
    {
        $path = $this->factory->existing('foo');

        $this->assertEquals('foo', $path->getCidPath());
        $this->assertInstanceOf(
            ilCtrlExistingPath::class,
            $path
        );
    }
}
