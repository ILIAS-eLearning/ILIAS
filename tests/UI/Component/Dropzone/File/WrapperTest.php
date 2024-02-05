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

namespace ILIAS\Tests\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\Input\Field\Text;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class WrapperTest extends FileTestBase
{
    public function testRenderWrapper(): void
    {
        $expected_title = 'test_title';
        $expected_url = 'test_url';
        $expected_legacy_html = 'test_legacy_html';

        $expected_html = $this->brutallyTrimHTML(
            '
<div id="id_4" class="ui-dropzone ui-dropzone-wrapper">
	<div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
		<div class="modal-dialog" role="document" data-replace-marker="component">
			<div class="modal-content">
				<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button><h1 class="modal-title">' . $expected_title . ' </h1></div>
				<div class="modal-body">
					<form id="id_2" role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="' . $expected_url . '" method="post" novalidate="novalidate">File Field Input</form>
				</div>
				<div class="modal-footer"><button class="btn btn-default" id="id_3">save</button><button class="btn btn-default" data-dismiss="modal">cancel</button></div>
			</div>
		</div>
	</div>
	<div class="ui-dropzone-container"> ' . $expected_legacy_html . '</div>
</div>
        '
        );

        $legacy_mock = $this->createMock(Legacy::class);
        $legacy_mock->method('getCanonicalName')->willReturn($expected_legacy_html);

        $dropzone = $this->factory->wrapper($expected_title, $expected_url, $legacy_mock, $this->input);

        $html = $this->brutallyTrimHTML($this->getDefaultRenderer(null, [
            $legacy_mock,
            $this->input,
        ])->render($dropzone));

        $this->assertEquals($expected_html, $html);
    }

    public function testRenderWrapperWithAdditionalInputs(): void
    {
        $expected_button_html = md5(Text::class);

        $additional_input = $this->createMock(Text::class);
        $additional_input->method('getCanonicalName')->willReturn($expected_button_html);
        $additional_input->method('isRequired')->willReturn(false);
        $additional_input->method('withNameFrom')->willReturnSelf();

        $dropzone = $this->factory->standard('', '', '', $this->input, $additional_input);

        $html = $this->getDefaultRenderer(null, [
            $this->input,
            $additional_input,
        ])->render($dropzone);

        $this->assertTrue(str_contains($html, $expected_button_html));
    }
}
