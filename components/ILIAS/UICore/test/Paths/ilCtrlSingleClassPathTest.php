<?php

declare(strict_types=1);

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
 */

require_once __DIR__ . '/ilCtrlPathTestBase.php';

/**
 * Class ilCtrlSingleClassPathTest
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilCtrlSingleClassPathTest extends ilCtrlPathTestBase
{
    public function testSinglePathWithUnknownClass(): void
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

    public function testSinglePathWithoutBaseClass(): void
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

    public function testSinglePathWithContextBaseClass(): void
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

    public function testSinglePathWithProvidedBaseClass(): void
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

    public function testSinglePathWithSameTargetClass(): void
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

    public function testSinglePathWithEmptyTargetClassString(): void
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

    public function testSinglePathBaseclassPriority(): void
    {
        // mocked context that returns a cid-path containing a baseclass.
        $context = $this->createMock(ilCtrlContextInterface::class);
        $context
            ->method('getPath')
            ->willReturn($this->getPath('0:2')) // ilCtrlBaseClass1TestGUI -> ilCtrlCommandClass1TestGUI
        ;

        $path = new ilCtrlSingleClassPath(
            $this->structure,
            $context,
            ilCtrlBaseClass2TestGUI::class
        );

        // baseclasses should have the least priority, therefore the new cid-path
        // must not be '1' (the cid of ilCtrlBaseClass2TestGUI) but as described,
        // because the baseclass can also be called by ilCtrlCommandClass1TestGUI.
        $this->assertEquals('0:2:1', $path->getCidPath());
    }
}
