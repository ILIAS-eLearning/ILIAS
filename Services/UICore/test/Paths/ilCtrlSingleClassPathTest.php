<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/ilCtrlPathTestBase.php';

/**
 * Class ilCtrlSingleClassPathTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSingleClassPathTest extends ilCtrlPathTestBase
{
    public function testSinglePathWithUnknownClass() : void
    {
        $invalid_class = ilCtrlInvalidGuiClass::class;
        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $this->createMock(ilCtrlContextInterface::class),
            $invalid_class
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Class '$invalid_class' was not found in the control structure, try `composer du` to read artifacts.");
        throw $path->getException();
    }

    public function testSinglePathWithoutBaseClass() : void
    {
        // mocked context that returns no cid-path.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath(null))
        ;

        $target_class = ilCtrlCommandClass1TestGUI::class;
        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $context,
            $target_class
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("ilCtrl cannot find a path for '$target_class' that reaches ''");
        throw $path->getException();
    }

    public function testSinglePathWithContextBaseClass() : void
    {
        // mocked context that returns a cid-path containing a baseclass.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath('0:4'))
        ;

        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $context,
            ilCtrlCommandClass1TestGUI::class
        );

        $this->assertEquals('0:2', $path->getCidPath());
    }

    public function testSinglePathWithProvidedBaseClass() : void
    {
        // mocked context that returns no cid-path
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath(null))
        ;

        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $context,
            ilCtrlBaseClass1TestGUI::class
        );

        $this->assertEquals('0', $path->getCidPath());
    }

    public function testSinglePathWithSameTargetClass() : void
    {
        // mocked context that returns a cid-path that already
        // contains the target class.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath('1:2:3'))
        ;

        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $context,
            ilCtrlCommandClass2TestGUI::class
        );

        $this->assertEquals('1:2:3', $path->getCidPath());
    }

    public function testSinglePathWithEmptyTargetClassString() : void
    {
        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $this->createMock(ilCtrlContextInterface::class),
            ''
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Class '' was not found in the control structure, try `composer du` to read artifacts.");
        throw $path->getException();
    }
}