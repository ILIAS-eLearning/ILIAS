<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/ilCtrlPathTestBase.php';

/**
 * Class ilCtrlArrayClassPathTest
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayClassPathTest extends ilCtrlPathTestBase
{
    public function testArrayPathWithEmptyTargets(): void
    {
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $this->createMock(ilCtrlContextInterface::class),
            []
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage(ilCtrlArrayClassPath::class . '::getCidPathByArray must be provided with a list of classes.');
        throw $path->getException();
    }

    public function testArrayPathWithUnknownTargetClass(): void
    {
        $invalid_class = ilCtrlInvalidGuiClass::class;
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $this->createMock(ilCtrlContextInterface::class),
            [$invalid_class]
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Class '$invalid_class' was not found in the control structure, try `composer du` to read artifacts.");
        throw $path->getException();
    }

    public function testArrayPathWithUnrelatedTargets(): void
    {
        $parent_class = ilCtrlBaseClass2TestGUI::class;
        $child_class = ilCtrlCommandClass2TestGUI::class;
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $this->createMock(ilCtrlContextInterface::class),
            [$parent_class, $child_class]
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Class '$child_class' is not a child of '$parent_class'.");
        throw $path->getException();
    }

    public function testArrayPathWithoutBaseClass(): void
    {
        // mocked context that will return path without cid-path.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath(null));

        $first_target_class = ilCtrlCommandClass1TestGUI::class;
        $second_target_class = ilCtrlCommandClass2TestGUI::class;
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $context,
            [$first_target_class, $second_target_class]
        );

        $this->assertNotNull($path->getException());
        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Class '$first_target_class' is not a baseclass and the current context doesn't have one either.");
        throw $path->getException();
    }

    public function testArrayPathWithProvidedBaseClass(): void
    {
        // mocked context that will return path without cid-path.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath(null));

        $base_class = ilCtrlBaseClass2TestGUI::class;
        $first_target_class = ilCtrlCommandClass1TestGUI::class;
        $second_target_class = ilCtrlCommandClass2TestGUI::class;
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $context,
            [$base_class, $first_target_class, $second_target_class]
        );

        $this->assertEquals('1:2:3', $path->getCidPath());
    }

    public function testArrayPathWithContextBaseClass(): void
    {
        // mocked context that will return path with base-class.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath('1:4'));

        $first_target_class = ilCtrlCommandClass1TestGUI::class;
        $second_target_class = ilCtrlCommandClass2TestGUI::class;
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $context,
            [$first_target_class, $second_target_class]
        );

        $this->assertEquals('1:2:3', $path->getCidPath());
    }

    public function testArrayPathWithContextAndProvidedBaseClass(): void
    {
        // mocked context that will return path with first base-class
        // that can call command class 1-
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath('0:2'));

        $new_base_class = ilCtrlBaseClass2TestGUI::class;
        $first_target_class = ilCtrlCommandClass1TestGUI::class;
        $second_target_class = ilCtrlCommandClass2TestGUI::class;
        $path = new ilCtrlArrayClassPath(
            $this->structure,
            $context,
            [$new_base_class, $first_target_class, $second_target_class]
        );

        $this->assertEquals('1:2:3', $path->getCidPath());
    }
}
