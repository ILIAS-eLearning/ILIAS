<?php

require_once(__DIR__ . '/ModalBase.php');

/**
 * Tests on implementation for the roundtrip modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTripTest extends ModalBase {

	public function test_with_title() {
		$roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
		$roundtrip2 = $roundtrip->withTitle('myTitle2');
		$this->assertEquals('myTitle', $roundtrip->getTitle());
		$this->assertEquals('myTitle2', $roundtrip2->getTitle());
	}


	public function test_with_content() {
		$content = $this->getDummyComponent();
		$contents = [ $this->getDummyComponent(), $this->getDummyComponent() ];
		$roundtrip = $this->getModalFactory()->roundtrip('myTitle', $content);
		$roundtrip2 = $roundtrip->withContent($contents);
		$this->assertEquals([$content], $roundtrip->getContent());
		$this->assertEquals($contents, $roundtrip2->getContent());
	}


	public function test_with_action_buttons() {
		$roundtrip = $this->getModalFactory()->roundtrip('myTitle', $this->getDummyComponent());
		$action_buttons = [
			$this->getButtonFactory()->primary('Action 1', ''),
			$this->getButtonFactory()->standard('Action 2', ''),
		];
		$roundtrip2 = $roundtrip->withActionButtons($action_buttons);
		$this->assertEquals(0, count($roundtrip->getActionButtons()));
		$this->assertEquals(2, count($roundtrip2->getActionButtons()));
		$this->assertEquals($action_buttons, $roundtrip2->getActionButtons());
	}


	public function test_simple_rendering() {
		$roundtrip = $this->getModalFactory()->roundtrip('Title', $this->getUIFactory()->legacy('Content'))
			->withActionButtons([
				$this->getButtonFactory()->primary('Action 1', ''),
				$this->getButtonFactory()->standard('Action 2', ''),
			]);
		$this->assertHTMLEquals($this->getExpectedHTML(), $this->getDefaultRenderer()->render($roundtrip));
	}


	protected function getExpectedHTML() {
		$expected = <<<EOT
<div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Title</h4>
            </div>
            <div class="modal-body">                                Content                            </div>
            <div class="modal-footer">                
                <a class="btn btn-default btn-primary" href="" data-action="" id="id_2">Action 1</a>                
                <a class="btn btn-default" href="" data-action="" id="id_3">Action 2</a>                
                <a class="btn btn-default" href="" data-action="" id="id_4">cancel</a>
            </div>
        </div>
    </div>
</div>
EOT;

		return $expected;
	}
}