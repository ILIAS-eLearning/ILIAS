<?php declare(strict_types=1);

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
 * Tests on implementation for the lightbox modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class LightboxTest extends ModalBase
{
    public function test_get_single_page() : void
    {
        $page = $this->getLightboxPage();
        $lightbox = $this->getModalFactory()->lightbox($page);
        $this->assertEquals([$page], $lightbox->getPages());
    }

    public function test_get_multiple_page() : void
    {
        $pages = [$this->getLightboxPage(), $this->getLightboxPage()];
        $lightbox = $this->getModalFactory()->lightbox($pages);
        $this->assertEquals($pages, $lightbox->getPages());
    }

    public function test_simple_image_page_rendering() : void
    {
        $image = new I\Component\Image\Image("responsive", 'src/fake/image.jpg', 'description');
        $lightbox = $this->getModalFactory()->lightbox($this->getModalFactory()->lightboxImagePage($image, 'title'));
        $expected = $this->normalizeHTML($this->getExpectedImagePageHTML());
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($lightbox));
        $this->assertEquals($expected, $actual);
    }

    public function test_simple_text_page_rendering() : void
    {
        $lightbox = $this->getModalFactory()->lightbox($this->getModalFactory()->lightboxTextPage('HelloWorld', 'title'));
        $expected = $this->normalizeHTML($this->getExpectedTextPageHTML());
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($lightbox));
        $this->assertEquals($expected, $actual);
    }

    public function test_different_page_type_rendering() : void
    {
        $image1 = new I\Component\Image\Image("responsive", 'src/fake/image.jpg', 'description');

        $pages = [
            $this->getModalFactory()->lightboxTextPage('HelloWorld', 'title'),
            $this->getModalFactory()->lightboxImagePage($image1, 'title'),
        ];

        $lightbox = $this->getModalFactory()->lightbox($pages);
        $expected = $this->normalizeHTML($this->getExpectedMixedPagesHTML());
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($lightbox));
        $this->assertEquals($expected, $actual);
    }

    protected function getLightboxPage() : LightboxMockPage
    {
        return new LightboxMockPage();
    }
    
    protected function getExpectedTextPageHTML() : string
    {
        return <<<EOT
<div class="modal fade il-modal-lightbox" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content il-modal-lightbox-page">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<span class="modal-title">title</span>
			</div>
			<div class="modal-body">
				<div id="id_1_carousel" class="carousel slide" data-ride="carousel" data-interval="false">



					<div class="carousel-inner" role="listbox">

						<div class="item active text-only" data-title="title">
HelloWorld
						</div>

					</div>



				</div>
			</div>
		</div>
	</div>
</div>
<script>
	window.setTimeout(function() {
		$('#id_1').on('shown.bs.modal', function() {
			$('.modal-backdrop.in').css('opacity', '0.9');
		});
		$('#id_1').on('show.bs.modal', function (e) {
			var elm = $(this).find('.carousel-inner .item.active').first();

			if (elm.hasClass('text-only')) {
				elm.closest('.carousel').addClass('text-only');
			} else {
				elm.closest('.carousel').removeClass('text-only');
			}
		});
		$('#id_1_carousel').on('slide.bs.carousel', function(e) {
			var elm = $(e.relatedTarget);

			if (elm.hasClass('text-only')) {
				elm.closest('.carousel').addClass('text-only');
			} else {
				elm.closest('.carousel').removeClass('text-only');
			}
		});
		$('#id_1_carousel').on('slid.bs.carousel', function() {
			var title = $(this).find('.carousel-inner .item.active').attr('data-title');
			$('#id_1').find('.modal-title').text(title);
		});
	}, 0);
</script>
EOT;
    }

    protected function getExpectedImagePageHTML() : string
    {
        return <<<EOT
<div class="modal fade il-modal-lightbox" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content il-modal-lightbox-page">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<span class="modal-title">title</span>
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
	window.setTimeout(function() {
		$('#id_1').on('shown.bs.modal', function() {
			$('.modal-backdrop.in').css('opacity', '0.9');
		});
		$('#id_1').on('show.bs.modal', function (e) {
			var elm = $(this).find('.carousel-inner .item.active').first();

			if (elm.hasClass('text-only')) {
				elm.closest('.carousel').addClass('text-only');
			} else {
				elm.closest('.carousel').removeClass('text-only');
			}
		});
		$('#id_1_carousel').on('slide.bs.carousel', function(e) {
			var elm = $(e.relatedTarget);

			if (elm.hasClass('text-only')) {
				elm.closest('.carousel').addClass('text-only');
			} else {
				elm.closest('.carousel').removeClass('text-only');
			}
		});
		$('#id_1_carousel').on('slid.bs.carousel', function() {
			var title = $(this).find('.carousel-inner .item.active').attr('data-title');
			$('#id_1').find('.modal-title').text(title);
		});
	}, 0);
</script>
EOT;
    }

    protected function getExpectedMixedPagesHTML() : string
    {
        return <<<EOT
<div class="modal fade il-modal-lightbox" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content il-modal-lightbox-page">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<span class="modal-title">title</span>
			</div>
			<div class="modal-body">
				<div id="id_1_carousel" class="carousel slide" data-ride="carousel" data-interval="false">


					<ol class="carousel-indicators">

					<li data-target="#id_1_carousel" data-slide-to="0" class="active"></li>
					
					<li data-target="#id_1_carousel" data-slide-to="1" class=""></li>
					
					</ol>


					<div class="carousel-inner" role="listbox">
					
						<div class="item active text-only" data-title="title">
HelloWorld
						</div>

						<div class="item" data-title="title">
						
						
						
						
						
<img src="src/fake/image.jpg" class="img-responsive" alt="description" />



							<div class="carousel-caption">
								description
							</div>
						</div>

					</div>
					
					
					<a class="left carousel-control" href="#id_1_carousel" role="button" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
					</a>
					<a class="right carousel-control" href="#id_1_carousel" role="button" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
					</a>
					
					
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	window.setTimeout(function() {
		$('#id_1').on('shown.bs.modal', function() {
			$('.modal-backdrop.in').css('opacity', '0.9');
		});
		$('#id_1').on('show.bs.modal', function (e) {
			var elm = $(this).find('.carousel-inner .item.active').first();

			if (elm.hasClass('text-only')) {
				elm.closest('.carousel').addClass('text-only');
			} else {
				elm.closest('.carousel').removeClass('text-only');
			}
		});
		$('#id_1_carousel').on('slide.bs.carousel', function(e) {
			var elm = $(e.relatedTarget);

			if (elm.hasClass('text-only')) {
				elm.closest('.carousel').addClass('text-only');
			} else {
				elm.closest('.carousel').removeClass('text-only');
			}
		});
		$('#id_1_carousel').on('slid.bs.carousel', function() {
			var title = $(this).find('.carousel-inner .item.active').attr('data-title');
			$('#id_1').find('.modal-title').text(title);
		});
	}, 0);
</script>
EOT;
    }
}

class LightboxMockPage implements C\Modal\LightboxPage
{
    public function getTitle() : string
    {
        return 'title';
    }

    public function getComponent() : C\Component
    {
        return new ComponentDummy();
    }
}
