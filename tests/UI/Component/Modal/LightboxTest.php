<?php

require_once(__DIR__ . '/ModalBase.php');

use \ILIAS\UI\Component as C;

/**
 * Tests on implementation for the lightbox modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LightboxTest extends ModalBase {

	public function test_with_pages() {
		$page = $this->getLightboxPage();
		$pages = [$this->getLightboxPage(), $this->getLightboxPage()];
		$lightbox = $this->getModalFactory()->lightbox($page);
		$lightbox2 = $lightbox->withPages($pages);
		$this->assertEquals([$page], $lightbox->getPages());
		$this->assertEquals($pages, $lightbox2->getPages());
	}

	public function test_simple_rendering() {
		$image = $this->getUIFactory()->image()->responsive('src/fake/image.jpg', 'description');
		$lightbox = $this->getModalFactory()->lightbox($this->getUIFactory()->modal()->lightboxImagePage($image, 'title'));
		$this->assertHTMLEquals($this->getExpectedHTML(), $this->getDefaultRenderer()->render($lightbox));
	}

	protected function getLightboxPage() {
		return new LightboxMockPage();
	}

	protected function getExpectedHTML() {
		$expected = <<<EOT
<div class="modal fade il-modal-lightbox" tabindex="-1" role="dialog" id="id_1">
    <div class="modal-dialog" role="document">
        <div class="modal-content il-modal-lightbox-page">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">title</h4>
            </div>
            <div class="modal-body"><img src="src/fake/image.jpg" class="img-responsive" alt="description" /></div>
            <div class="il-modal-lightbox-description">
                description
            </div>
        </div>
        
    </div>
</div>
EOT;

		return $expected;
	}
}

class LightboxMockPage implements C\Modal\LightboxPage {

	public function getTitle() {
		return 'title';
	}

	public function getDescription() {
		return 'description';
	}

	public function getComponent() {
		return new ComponentDummy();
	}
}