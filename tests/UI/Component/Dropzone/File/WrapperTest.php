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

namespace ILIAS\Tests\UI\Component\Dropzone\File;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class WrapperTest extends FileTestBase
{
    public function testRenderWrapper(): void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_6" class="ui-dropzone ui-dropzone-wrapper">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button><span class="modal-title"></span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label class="control-label col-sm-4 col-md-3 col-lg-2"></label>
                                        <div class="col-sm-8 col-md-9 col-lg-10">
                                            <div id="id_4" class="ui-input-file">
                                                <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                                                <div class="ui-input-file-input-dropzone"><button class="btn btn-link" data-action="#" id="id_3">select_files_from_computer</button><span class="ui-input-file-input-error-msg" data-dz-error-msg></span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="il-standard-form-footer clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span>
                    <p>test_content</p>
                </div>
            </div>
        ');

        $dropzone = $this->factory->wrapper(
            $this->getUploadHandlerMock(),
            '#',
            $this->getUIFactory()->legacy('<p>test_content</p>')
        );

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }

    public function testRenderWrapperWithMetadata(): void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_7" class="ui-dropzone ui-dropzone-wrapper">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button><span class="modal-title"></span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label class="control-label col-sm-4 col-md-3 col-lg-2"></label>
                                        <div class="col-sm-8 col-md-9 col-lg-10">
                                            <div id="id_5" class="ui-input-file">
                                                <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                                                <div class="ui-input-file-input-dropzone"><button class="btn btn-link" data-action="#" id="id_4">select_files_from_computer</button><span class="ui-input-file-input-error-msg" data-dz-error-msg></span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="il-standard-form-footer clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span>
                    <p>test_content</p>
                </div>
            </div>
        ');

        $dropzone = $this->factory->wrapper(
            $this->getUploadHandlerMock(),
            '#',
            $this->getUIFactory()->legacy('<p>test_content</p>'),
            $this->getFieldFactory()->text('test_input_1')
        );

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }

    public function testRenderWrapperWithTitle(): void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_6" class="ui-dropzone ui-dropzone-wrapper">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button><span class="modal-title">test_title</span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label class="control-label col-sm-4 col-md-3 col-lg-2"></label>
                                        <div class="col-sm-8 col-md-9 col-lg-10">
                                            <div id="id_4" class="ui-input-file">
                                                <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                                                <div class="ui-input-file-input-dropzone"><button class="btn btn-link" data-action="#" id="id_3">select_files_from_computer</button><span class="ui-input-file-input-error-msg" data-dz-error-msg></span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="il-standard-form-footer clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span>
                    <p>test_content</p>
                </div>
            </div>
        ');

        $dropzone = $this->factory->wrapper(
            $this->getUploadHandlerMock(),
            '#',
            $this->getUIFactory()->legacy('<p>test_content</p>')
        )->withTitle('test_title');

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }
}
