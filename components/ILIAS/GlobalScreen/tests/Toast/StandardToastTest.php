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

use ILIAS\GlobalScreen\Scope\Toast\Factory\StandardToastItem;
use ILIAS\GlobalScreen\Scope\Toast\Factory\ToastAction;

require_once(__DIR__ . "/BaseToastSetUp.php");

class StandardToastTest extends BaseToastSetUp
{
    public function testStandardToast()
    {
        $id = $this->createMock(\ILIAS\GlobalScreen\Identification\IdentificationInterface::class);

        $standard_toast = $this->factory->standard(
            $id,
            'Toast Title'
        );

        $this->assertInstanceOf(StandardToastItem::class, $standard_toast);
        $this->assertEquals('Toast Title', $standard_toast->getTitle());
        $this->assertEquals([], $standard_toast->getAllToastActions());
        $this->assertCount(0, $standard_toast->getAllToastActions());
        $this->assertCount(0, $standard_toast->getAdditionalToastActions());

        $handle = function () {
            return true;
        };

        $standard_toast = $standard_toast->withShownCallable($handle);
        $this->assertCount(1, $standard_toast->getAllToastActions());
        $this->assertCount(0, $standard_toast->getAdditionalToastActions());

        $standard_toast = $standard_toast->withClosedCallable($handle);
        $this->assertCount(2, $standard_toast->getAllToastActions());
        $this->assertCount(0, $standard_toast->getAdditionalToastActions());

        $standard_toast = $standard_toast->withVanishedCallable($handle);
        $this->assertCount(3, $standard_toast->getAllToastActions());
        $this->assertCount(0, $standard_toast->getAdditionalToastActions());

        $standard_toast = $standard_toast->withAdditionToastAction($this->factory->action('one', 'One', $handle));
        $this->assertCount(4, $standard_toast->getAllToastActions());
        $this->assertCount(1, $standard_toast->getAdditionalToastActions());

        $standard_toast = $standard_toast->withAdditionToastAction($this->factory->action('two', 'Two', $handle));
        $this->assertCount(5, $standard_toast->getAllToastActions());
        $this->assertCount(2, $standard_toast->getAdditionalToastActions());

        // double add
        $this->expectException(\InvalidArgumentException::class);
        $standard_toast = $standard_toast->withAdditionToastAction($this->factory->action('two', 'Two', $handle));
    }

    public function reservedActionsProvider(): array
    {
        $action = function () {
            return true;
        };

        return [
            [new ToastAction('shown', 'shown', $action)],
            [new ToastAction('closed', 'closed', $action)],
            [new ToastAction('vanished', 'vanished', $action)],
        ];
    }

    /**
     * @dataProvider reservedActionsProvider
     */
    public function testReservedActions(ToastAction $action): void
    {
        $id = $this->createMock(\ILIAS\GlobalScreen\Identification\IdentificationInterface::class);

        $standard_toast = $this->factory->standard(
            $id,
            'Toast Title'
        );

        $this->expectException(\InvalidArgumentException::class);
        $standard_toast = $standard_toast->withAdditionToastAction($action);
    }
}
