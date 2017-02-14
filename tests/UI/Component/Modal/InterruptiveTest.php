<?php

require_once(__DIR__ . '/ModalBase.php');

use \ILIAS\UI\Component as C;

/**
 * Tests on implementation for the interruptive modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class InterruptiveTest extends ModalBase {

	public function test_with_title() {
		$interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
		$interruptive2 = $interruptive->withTitle('myTitle2');
		$this->assertEquals('myTitle', $interruptive->getTitle());
		$this->assertEquals('myTitle2', $interruptive2->getTitle());
	}

	public function test_with_message() {
		$interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
		$interruptive2 = $interruptive->withMessage('myMessage2');
		$this->assertEquals('myMessage', $interruptive->getMessage());
		$this->assertEquals('myMessage2', $interruptive2->getMessage());
	}

	public function test_with_form_action() {
		$interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
		$interruptive2 = $interruptive->withFormAction('myFormAction2');
		$this->assertEquals('myFormAction', $interruptive->getFormAction());
		$this->assertEquals('myFormAction2', $interruptive2->getFormAction());
	}

	public function test_with_affected_items() {
		$interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
		$items = [$this->getInterruptiveItem(), $this->getInterruptiveItem()];
		$interruptive2 = $interruptive->withAffectedItems($items);
		$this->assertEquals(0, count($interruptive->getAffectedItems()));
		$this->assertEquals($items, $interruptive2->getAffectedItems());
	}

	public function test_simple_rendering() {
		$interruptive = $this->getModalFactory()->interruptive('Title', 'Message', '');
		$this->assertHTMLEquals($this->getExpectedHTML(), $this->getDefaultRenderer()->render($interruptive));
	}

	protected function getInterruptiveItem() {
		return new InterruptiveItemMock();
	}

	protected function getExpectedHTML() {
		$expected = <<<EOT
<div class="modal fade il-modal-interruptive" tabindex="-1" role="dialog" id="id_1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Title</h4>
            </div>
            <div class="modal-body">
                <div class="il-modal-interruptive-message">
                    Message
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-default btn-primary" href="" data-action="" id="id_2">delete</a>
                <a class="btn btn-default" href="" data-action="" id="id_3">cancel</a>
            </div>
        </div>
    </div>
</div>
EOT;

		return $expected;
	}

}

class InterruptiveItemMock implements C\Modal\InterruptiveItem {

	public function getId() {
		return 1;
	}

	public function getTitle() {
		return 'title';
	}

	public function getDescription() {
		return 'description';
	}
}
