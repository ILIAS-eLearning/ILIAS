<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Dropzone\File;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class StandardTest extends FileTestBase
{
    public function testRenderStandard() : void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_5" class="ui-dropzone ">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="modal-title"></span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label for="id_4" class="control-label col-sm-3"></label>
                                        <div class="col-sm-9">
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
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span></div>
            </div>
        ');

        $dropzone = $this->factory->standard(
            $this->getUploadHandlerMock(),
            '#'
        );

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }

    public function testRenderStandardWithUploadButton() : void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_6" class="ui-dropzone ">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="modal-title"></span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label for="id_4" class="control-label col-sm-3"></label>
                                        <div class="col-sm-9">
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
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span><button class="btn btn-link" id="id_5">button_label</button></div>
            </div>
        ');

        $dropzone = $this->factory->standard(
            $this->getUploadHandlerMock(),
            '#'
        )->withUploadButton(
            $this->getUIFactory()->button()->shy('button_label', '#')
        );

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }

    public function testRenderStandardWithMetadata() : void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_6" class="ui-dropzone ">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="modal-title"></span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label for="id_5" class="control-label col-sm-3"></label>
                                        <div class="col-sm-9">
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
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span></div>
            </div>
        ');

        $dropzone = $this->factory->standard(
            $this->getUploadHandlerMock(),
            '#',
            $this->getFieldFactory()->text('test_input_1')
        );

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }

    public function testRenderStandardWithMessage() : void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_5" class="ui-dropzone ">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="modal-title"></span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label for="id_4" class="control-label col-sm-3"></label>
                                        <div class="col-sm-9">
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
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message">test_message</span></div>
            </div>
        ');

        $dropzone = $this->factory->standard(
            $this->getUploadHandlerMock(),
            '#'
        )->withMessage('test_message');

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }

    public function testRenderStandardWithTitle() : void
    {
        $expected_html = $this->brutallyTrimHTML('
            <div id="id_5" class="ui-dropzone ">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_1">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="modal-title">test_title</span></div>
                            <div class="modal-body">
                                <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
                                    <div class="il-standard-form-header clearfix">
                                        <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                                    </div>
                                    <div class="form-group row"><label for="id_4" class="control-label col-sm-3"></label>
                                        <div class="col-sm-9">
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
                            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button></div>
                        </div>
                    </div>
                </div>
                <div class="ui-dropzone-container"><span class="ui-dropzone-message"></span></div>
            </div>
        ');

        $dropzone = $this->factory->standard(
            $this->getUploadHandlerMock(),
            '#'
        )->withTitle('test_title');

        $this->assertEquals($expected_html, $this->getDropzoneHtml($dropzone));
    }
}