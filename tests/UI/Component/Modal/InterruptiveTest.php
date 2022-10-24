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
 *
 *********************************************************************/

require_once(__DIR__ . '/ModalBase.php');

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Tests on implementation for the interruptive modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class InterruptiveTest extends ModalBase
{
    public function test_get_title(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertEquals('myTitle', $interruptive->getTitle());
    }

    public function test_get_message(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertEquals('myMessage', $interruptive->getMessage());
    }

    public function test_get_form_action(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertEquals('myFormAction', $interruptive->getFormAction());
    }

    public function test_get_affected_items(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $items = [$this->getInterruptiveItem(), $this->getInterruptiveItem()];
        $interruptive = $interruptive->withAffectedItems($items);
        $this->assertEquals($items, $interruptive->getAffectedItems());
    }

    public function test_with_form_action(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $interruptive2 = $interruptive->withFormAction('myFormAction2');
        $this->assertEquals('myFormAction', $interruptive->getFormAction());
        $this->assertEquals('myFormAction2', $interruptive2->getFormAction());
    }

    public function test_with_affected_items(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $items = [$this->getInterruptiveItem(), $this->getInterruptiveItem()];
        $interruptive2 = $interruptive->withAffectedItems($items);
        $this->assertEquals(0, count($interruptive->getAffectedItems()));
        $this->assertEquals($items, $interruptive2->getAffectedItems());
    }

    public function test_simple_rendering(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('Title', 'Message', 'myAction.php');
        $expected = $this->normalizeHTML($this->getExpectedHTML());
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($interruptive));
        $this->assertEquals($expected, $actual);
    }

    protected function getInterruptiveItem(): InterruptiveItemMock
    {
        return new InterruptiveItemMock();
    }

    protected function getExpectedHTML(): string
    {
        $expected = <<<EOT
<div class="modal fade il-modal-interruptive" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog" role="document">
		<form action="myAction.php" method="POST">
			<div class="modal-content">
				<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true"></span></button><span class="modal-title">Title</span>
				</div>
				<div class="modal-body">
					<div class="alert alert-warning il-modal-interruptive-message" role="alert">Message</div>
				</div>
				<div class="modal-footer">
					<input type="submit" class="btn btn-primary" value="delete" name="cmd[delete]">
					<button class="btn btn-default" data-dismiss="modal">cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>
EOT;
        return $expected;
    }


    public function testLabels(): void
    {
        $action_label = 'actionlabel';
        $cancel_label = 'cancellabel';
        $interruptive = $this->getModalFactory()->interruptive('Title', 'Message', 'someaction')
            ->withActionButtonLabel($action_label)
            ->withCancelButtonLabel($cancel_label);

        $this->assertEquals(
            $action_label,
            $interruptive->getActionButtonLabel()
        );
        $this->assertEquals(
            $cancel_label,
            $interruptive->getCancelButtonLabel()
        );
    }
}

class InterruptiveItemMock implements C\Modal\InterruptiveItem
{
    public function getId(): string
    {
        return '1';
    }

    public function getTitle(): string
    {
        return 'title';
    }

    public function getDescription(): string
    {
        return 'description';
    }

    public function getIcon(): C\Image\Image
    {
        return new I\Component\Image\Image(C\Image\Image::STANDARD, '', '');
    }
}
