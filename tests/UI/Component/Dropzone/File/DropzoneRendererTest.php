<?php

use ILIAS\Data\DataSize;

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Class FileDropzoneRendererTest
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 */
class DropzoneRendererTest extends ILIAS_UI_TestBase
{
    const STANDARD = "ILIAS\\UI\\Component\\Dropzone\\File\\Standard";
    const WRAPPER = "ILIAS\\UI\\Component\\Dropzone\\File\\Wrapper";


    public function test_implements_factory_interface()
    {
        $f = $this->dropzone();

        $this->assertInstanceOf(self::STANDARD, $f->standard(''));
        $this->assertInstanceOf(self::WRAPPER, $f->wrapper('', new I\Component\Legacy\Legacy('')));
    }


    /**
     * should be rendered with the css class .standard and no content inside
     * the dropzone div.
     */
    public function testRenderStandardDropzone()
    {

        // setup expected objects
        $expectedHtml = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 dz-default dz-message il-dropzone-standard-select-files-wrapper "> <!--col-no-padding--><a href="#" >select_files_from_computer</a></div><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-right dz-default dz-message">drag_files_here</span></div></div><div class="clearfix hidden-sm-up"></div></div></div>';

        // start test
        $standardDropzone = $this->dropzone()->standard('');

        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($standardDropzone));

        $this->assertEquals($expectedHtml, $html);
    }


    /**
     * should be rendered with the css class .standard and a span-tag with the passed in message
     * inside the dropzone div.
     */
    public function testRenderStandardDropzoneWithMessage()
    {

        // setup expected objects
        $expectedHtml = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 dz-default dz-message il-dropzone-standard-select-files-wrapper "> <!--col-no-padding--><a href="#" >select_files_from_computer</a></div><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-right dz-default dz-message">message</span></div></div><div class="clearfix hidden-sm-up"></div></div></div>';

        // start test
        $standardDropzone = $this->dropzone()->standard('')->withMessage('message');

        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($standardDropzone));

        $this->assertEquals($expectedHtml, $html);
    }


    /**
     * A wrapper dropzone -----------------------------------------------------------------
     */

    /**
     * should be rendered with the css class .wrapper and all passed in ILIAS UI components inside
     * the div.
     */
    public function testRenderWrapperDropzone()
    {
        // setup expected objects
        $expectedHtml = '<div id="id_1" class="il-dropzone-base"><div class="il-dropzone wrapper" data-upload-id="id_1"><p>Pretty smart, isn\'t it?</p><p>Yeah, this is really smart.</p></div><div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_2"><div class="modal-dialog" role="document" data-replace-marker="component"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">upload</h4></div><div class="modal-body"><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div></div><div class="modal-footer"><button class="btn btn-default btn-primary ilSubmitInactive disabled" data-action="">upload</button><a class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</a></div></div></div></div></div>';

        // start test
        $exampleTextQuestion = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Pretty smart, isn't it?</p>");
        $exampleTextAnswer = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Yeah, this is really smart.</p>");
        $wrapperDropzone = $this->dropzone()->wrapper('', [
            $exampleTextQuestion,
            $exampleTextAnswer,
        ]);

        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($wrapperDropzone));

        $this->assertEquals($expectedHtml, $html);
    }


    public function testRenderMetadata()
    {
        $with_user_defined_names_html = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><span class="file-info  toggle"><a class="glyph" aria-label="collapse_content"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></a><a class="glyph" aria-label="expand_content"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></a></span><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li><li><button class="btn btn-link" data-action=""  aria-label="edit_metadata" >edit_metadata</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div><br><div class="form-horizontal metadata" style="display: none;"><div class="form-group"><label class="col-sm-3 control-label">filename</label><div class="col-sm-9"><input type="text" class="form-control filename-input"></div></div></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 dz-default dz-message il-dropzone-standard-select-files-wrapper "> <!--col-no-padding--><a href="#" >select_files_from_computer</a></div><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-right dz-default dz-message">drag_files_here</span></div></div><div class="clearfix hidden-sm-up"></div></div></div>';
        $with_user_defined_names = $this->dropzone()
                                        ->standard('https://ilias.de/ilias.php')
                                        ->withUserDefinedFileNamesEnabled(true);
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($with_user_defined_names));
        $this->assertEquals($with_user_defined_names_html, $html);

        $with_user_defined_descriptions_html = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><span class="file-info  toggle"><a class="glyph" aria-label="collapse_content"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></a><a class="glyph" aria-label="expand_content"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></a></span><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li><li><button class="btn btn-link" data-action=""  aria-label="edit_metadata" >edit_metadata</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div><br><div class="form-horizontal metadata" style="display: none;"><div class="form-group"><label class="col-sm-3 control-label" for="description-input">description</label><div class="col-sm-9"><textarea class="form-control description-input" id="description-input" rows="3"></textarea></div></div></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 dz-default dz-message il-dropzone-standard-select-files-wrapper "> <!--col-no-padding--><a href="#" >select_files_from_computer</a></div><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-right dz-default dz-message">drag_files_here</span></div></div><div class="clearfix hidden-sm-up"></div></div></div>';
        $with_user_defined_descriptions = $this->dropzone()
                                               ->standard('https://ilias.de/ilias.php')
                                               ->withUserDefinedDescriptionEnabled(true);
        $html = $this->normalizeHTML($this->getDefaultRenderer()
                                          ->render($with_user_defined_descriptions));
        $this->assertEquals($with_user_defined_descriptions_html, $html);

        $with_user_defined_names_and_descriptions_html = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><span class="file-info  toggle"><a class="glyph" aria-label="collapse_content"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></a><a class="glyph" aria-label="expand_content"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></a></span><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li><li><button class="btn btn-link" data-action=""  aria-label="edit_metadata" >edit_metadata</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div><br><div class="form-horizontal metadata" style="display: none;"><div class="form-group"><label class="col-sm-3 control-label">filename</label><div class="col-sm-9"><input type="text" class="form-control filename-input"></div></div><div class="form-group"><label class="col-sm-3 control-label" for="description-input">description</label><div class="col-sm-9"><textarea class="form-control description-input" id="description-input" rows="3"></textarea></div></div></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 dz-default dz-message il-dropzone-standard-select-files-wrapper "> <!--col-no-padding--><a href="#" >select_files_from_computer</a></div><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-right dz-default dz-message">drag_files_here</span></div></div><div class="clearfix hidden-sm-up"></div></div></div>';
        $with_user_defined_names_and_descriptions = $this->dropzone()
                                                         ->standard('https://ilias.de/ilias.php')
                                                         ->withUserDefinedDescriptionEnabled(true)
                                                         ->withUserDefinedFileNamesEnabled(true);
        $html = $this->normalizeHTML($this->getDefaultRenderer()
                                          ->render($with_user_defined_names_and_descriptions));
        $this->assertEquals($with_user_defined_names_and_descriptions_html, $html);
    }


    public function testWithButton()
    {
        $expected_html = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><button class="btn btn-link" data-action=""  aria-label="delete_file" >remove</button></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 dz-default dz-message il-dropzone-standard-select-files-wrapper "> <!--col-no-padding--><a href="#" >select_files_from_computer</a></div><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-right dz-default dz-message">drag_files_here</span></div></div><div class="clearfix hidden-sm-up"></div></div><button class="btn btn-default ilSubmitInactive disabled"   data-action="#" id="id_2">Label</button></div>';
        $button = new I\Component\Button\Standard('Label', '#');
        $with_button = $this->dropzone()->standard('')->withUploadButton($button);
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($with_button));
        $this->assertEquals($expected_html, $html);
        $this->assertEquals($button, $with_button->getUploadButton());
    }


    public function testModifiers()
    {
        $url = 'https://ilias.de/123?test=8&lorem=ipsum';
        $message = 'Everything\'s fine here, just drop some files...';
        $filesize = new DataSize(1024, DataSize::KB);
        $file_types = array( 'pdf', 'docx' );
        $allowed_files = 5;
        $dropzone = $this->dropzone()
                         ->standard($url)
                         ->withMessage($message)
                         ->withUserDefinedFileNamesEnabled(true)
                         ->withUserDefinedDescriptionEnabled(true)
                         ->withAllowedFileTypes($file_types)
                         ->withFileSizeLimit($filesize)
                         ->withMaxFiles($allowed_files);

        $this->assertEquals($url, $dropzone->getUploadUrl());
        $this->assertEquals($message, $dropzone->getMessage());
        $this->assertTrue($dropzone->allowsUserDefinedFileNames());
        $this->assertTrue($dropzone->allowsUserDefinedFileDescriptions());
        $this->assertEquals($file_types, $dropzone->getAllowedFileTypes());
        $this->assertEquals($filesize, $dropzone->getFileSizeLimit());
        $this->assertEquals("1.024 KB", $dropzone->getFileSizeLimit()->__toString());
        $this->assertEquals($allowed_files, $dropzone->getMaxFiles());
    }

    public function getUIFactory()
    {
        return new \ILIAS\UI\Implementation\Factory(
            $this->createMock(C\Counter\Factory::class),
            new I\Component\Glyph\Factory(),
            new I\Component\Button\Factory(),
            $this->createMock(C\Listing\Factory::class),
            $this->createMock(C\Image\Factory::class),
            $this->createMock(C\Panel\Factory::class),
            new I\Component\Modal\Factory(
                new I\Component\SignalGenerator()
            ),
            $this->createMock(C\Dropzone\Factory::class),
            $this->createMock(C\Popover\Factory::class),
            $this->createMock(C\Divider\Factory::class),
            new I\Component\Link\Factory(),
            new I\Component\Dropdown\Factory(),
            $this->createMock(C\Item\Factory::class),
            $this->createMock(C\Icon\Factory::class),
            $this->createMock(C\ViewControl\Factory::class),
            $this->createMock(C\Chart\Factory::class),
            $this->createMock(C\Input\Factory::class),
            $this->createMock(C\Table\Factory::class),
            $this->createMock(C\MessageBox\Factory::class),
            $this->createMock(C\Card\Factory::class)
        );
    }

    public function normalizeHTML($html)
    {
        $html = trim(str_replace("\t", "", $html));

        return parent::normalizeHTML($html);
    }


    protected function dropzone()
    {
        return new I\Component\Dropzone\File\Factory();
    }
}
