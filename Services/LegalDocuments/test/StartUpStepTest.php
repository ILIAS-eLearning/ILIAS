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

namespace ILIAS\LegalDocuments\test;

use ILIAS\LegalDocuments\Intercept;
use ILIAS\LegalDocuments\test\ContainerMock;
use ilCtrl;
use ILIAS\LegalDocuments\Conductor;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\StartUpStep;

require_once __DIR__ . '/ContainerMock.php';

class StartUpStepTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(StartUpStep::class, new StartUpStep($this->mock(ilCtrl::class), $this->mock(Conductor::class)));
    }

    public function testShouldStoreRequestTarget(): void
    {
        $instance = new StartUpStep($this->mock(ilCtrl::class), $this->mock(Conductor::class));
        $this->assertTrue($instance->shouldStoreRequestTarget());
    }

    public function testShouldInterceptRequest(): void
    {
        $instance = new StartUpStep($this->mock(ilCtrl::class), $this->mockTree(Conductor::class, ['intercepting' => [
            $this->mockTree(Intercept::class, ['intercept' => false]),
            $this->mockTree(Intercept::class, ['intercept' => true]),
        ]]));

        $this->assertTrue($instance->shouldInterceptRequest());
    }

    public function testExecute(): void
    {
        $ctrl = $this->mock(ilCtrl::class);
        $ctrl->expects(self::once())->method('setParameterByClass')->with('foo', 'id', 'baz');
        $ctrl->expects(self::once())->method('getLinkTargetByClass')->with(['foo'], 'bar')->willReturn('link');
        $ctrl->expects(self::once())->method('redirectToURL')->with('link');

        $instance = new StartUpStep($ctrl, $this->mockTree(Conductor::class, ['intercepting' => [
            $this->mockTree(Intercept::class, ['intercept' => false, 'id' => 'ho', 'target' => ['guiName' => 'dummy', 'guiPath' => ['dummy'], 'command' => 'hej']]),
            $this->mockTree(Intercept::class, ['intercept' => true, 'id' => 'baz', 'target' => ['guiName' => 'foo', 'guiPath' => ['foo'], 'command' => 'bar']]),
        ]]));

        $instance->execute();
    }

    public function testIsInFulfillment(): void
    {
        $ctrl = $this->mockTree(ilCtrl::class, ['getCmdClass' => 'foo']);

        $instance = new StartUpStep($ctrl, $this->mockTree(Conductor::class, ['intercepting' => [
            $this->mockTree(Intercept::class, ['intercept' => true, 'target' => ['guiName' => 'HEJ']]),
            $this->mockTree(Intercept::class, ['intercept' => true, 'target' => ['guiName' => 'FOO']]),
        ]]));

        $this->assertTrue($instance->isInFulfillment());
    }
}
