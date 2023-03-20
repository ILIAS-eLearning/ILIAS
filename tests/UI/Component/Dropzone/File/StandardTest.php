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

use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Component\Button\Button;
use TestDefaultRenderer;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class StandardTest extends FileTestBase
{
    public function testRenderStandard(): void
    {
        $expected_title = 'test_title';
        $expected_msg = 'test_msg';
        $expected_url = 'test_url';

        $expected_html = $this->brutallyTrimHTML("
            <div id=\"id_2\" class=\"ui-dropzone \">
                <div class=\"modal fade il-modal-roundtrip\" tabindex=\"-1\" role=\"dialog\" id=\"id_1\">
                    <div class=\"modal-dialog\" role=\"document\" data-replace-marker=\"component\">
                        <div class=\"modal-content\">
                            <div class=\"modal-header\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"close\">
                                    <span aria-hidden=\"true\">&times;</span>
                                </button>
                                <span class=\"modal-title\">$expected_title
                                </span>
                            </div>
                            <div class=\"modal-body\">
                            </div>
                            <div class=\"modal-footer\">
                                <button class=\"btn btn-default\" data-dismiss=\"modal\" aria-label=\"close\">cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=\"ui-dropzone-container\">
                    <span class=\"ui-dropzone-message\">$expected_msg
                    </span>
                </div>
            </div>
        ");

        $dropzone = $this->factory->standard($expected_title, $expected_msg, $expected_url, $this->input);

        $html = $this->brutallyTrimHTML($this->getDefaultRenderer(null, [
            $this->input,
        ])->render($dropzone));

        $this->assertEquals($expected_html, $html);
    }

    public function testRenderStandardWithUploadButton(): void
    {
        $expected_button_html = 'test_button';

        $expected_html = $this->brutallyTrimHTML("
            <div id=\"id_2\" class=\"ui-dropzone \">
                <div class=\"modal fade il-modal-roundtrip\" tabindex=\"-1\" role=\"dialog\" id=\"id_1\">
                    <div class=\"modal-dialog\" role=\"document\" data-replace-marker=\"component\">
                        <div class=\"modal-content\">
                            <div class=\"modal-header\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"close\">
                                    <span aria-hidden=\"true\">&times;</span>
                                </button>
                                <span class=\"modal-title\">
                                </span>
                            </div>
                            <div class=\"modal-body\">
                            </div>
                            <div class=\"modal-footer\">
                                <button class=\"btn btn-default\" data-dismiss=\"modal\" aria-label=\"close\">cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=\"ui-dropzone-container\">
                    <span class=\"ui-dropzone-message\">
                    </span> $expected_button_html
                </div>
            </div>
        ");

        $button_mock = $this->createMock(Button::class);
        $button_mock->method('getCanonicalName')->willReturn($expected_button_html);
        $button_mock->method('withOnClick')->willReturnSelf();

        $dropzone = $this->factory->standard('', '', '', $this->input)->withUploadButton($button_mock);

        $html = $this->brutallyTrimHTML($this->getDefaultRenderer(null, [
            $button_mock,
            $this->input,
        ])->render($dropzone));

        $this->assertEquals($expected_html, $html);
    }
}
