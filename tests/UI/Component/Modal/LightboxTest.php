<?php

require_once(__DIR__ . '/ModalBase.php');

use \ILIAS\UI\Component as C;

/**
 * Tests on implementation for the lightbox modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LightboxTest extends ModalBase {

	public function test_get_single_page() {
		$page = $this->getLightboxPage();
		$lightbox = $this->getModalFactory()->lightbox($page);
		$this->assertEquals([$page], $lightbox->getPages());
	}

	public function test_get_multiple_page() {
		$pages = [$this->getLightboxPage(), $this->getLightboxPage()];
		$lightbox = $this->getModalFactory()->lightbox($pages);
		$this->assertEquals($pages, $lightbox->getPages());
	}

	public function test_simple_rendering() {
		$image = $this->getUIFactory()->image()->responsive('src/fake/image.jpg', 'description');
		$lightbox = $this->getModalFactory()->lightbox($this->getUIFactory()->modal()->lightboxImagePage($image, 'title'));
		$expected = $this->normalizeHTML($this->getExpectedHTML());
		$actual = $this->normalizeHTML($this->getDefaultRenderer()->render($lightbox));
		$this->assertEquals($expected, $actual);
	}

	protected function getLightboxPage() {
		return new LightboxMockPage();
	}

	protected function getExpectedHTML() {
		$expected = <<<EOT
<div class="modal fade il-modal-lightbox" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content il-modal-lightbox-page">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">title</h4>
			</div>
			<div class="modal-body">
				<div id="id_1_carousel" class="carousel slide" data-ride="carousel" data-interval="false">



					<div class="carousel-inner" role="listbox">

						<div class="item active" data-title="title">



<img src="src/fake/image.jpg" class="img-responsive" alt="description" />

							<div class="carousel-caption">
								description
							</div>
						</div>

					</div>



				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$(function() {
		$('#id_1').on('shown.bs.modal', function() {
			$('.modal-backdrop.in').css('opacity', '0.9');
		});
		$('#id_1_carousel').on('slid.bs.carousel', function() {
			var title = $(this).find('.carousel-inner .item.active').attr('data-title');
			$('#id_1').find('.modal-title').text(title);
		});
	});
</script>
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
