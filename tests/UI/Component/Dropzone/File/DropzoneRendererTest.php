<?php

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

/**
 * Class FileDropzoneRendererTest
 *
 * @author  nmaerchy
 * @date    08.05.17
 * @version 0.0.1
 *
 */
class DropzoneRendererTest extends ILIAS_UI_TestBase {

	/**
	 * A standard dropzone ----------------------------------------------------------------
	 */

	/**
	 * should be rendered with the css class .standard and no content inside
	 * the dropzone div.
	 */
	public function testRenderStandardDropzone() {

		// setup expected objects
		$expectedHtml = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><a class="btn btn-link" href="" data-action=""  aria-label="delete_file" >remove</a></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-left dz-default dz-message">drag_files_here</span></div><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 il-dropzone-standard-select-files-wrapper text-right col-no-padding"><a class="btn btn-link" href="#" data-action="#"  >select_files_from_computer</a></div></div><div class="clearfix hidden-sm-up"></div></div></div>';

		// start test
		$standardDropzone = $this->getFactory()->standard('');

		$html = $this->normalizeHTML($this->getDefaultRenderer()->render($standardDropzone));

		$this->assertEquals($expectedHtml, $html);
	}


	/**
	 * should be rendered with the css class .standard and a span-tag with the passed in message
	 * inside the dropzone div.
	 */
	public function testRenderStandardDropzoneWithMessage() {

		// setup expected objects
		$expectedHtml = '<div id="id_1" class="il-dropzone-base"><div class="clearfix hidden-sm-up"></div><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><a class="btn btn-link" href="" data-action=""  aria-label="delete_file" >remove</a></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div><div class="container-fluid"><div class="il-dropzone standard clearfix row" data-upload-id="id_1"><div class="col-xs-12 col-md-9 col-sm-12 col-lg-9 col-no-padding"><span class="pull-left dz-default dz-message">message</span></div><div class="col-xs-12 col-md-3 col-sm-12 col-lg-3 il-dropzone-standard-select-files-wrapper text-right col-no-padding"><a class="btn btn-link" href="#" data-action="#"  >select_files_from_computer</a></div></div><div class="clearfix hidden-sm-up"></div></div></div>';

		// start test
		$standardDropzone = $this->getFactory()->standard('')->withMessage('message');

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
	public function testRenderWrapperDropzone() {
		// setup expected objects
		$expectedHtml = '<div id="id_1" class="il-dropzone-base"><div class="il-dropzone wrapper" data-upload-id="id_1"><p>Pretty smart, isn\'t it?</p><p>Yeah, this is really smart.</p></div><div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_2"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">upload</h4></div><div class="modal-body"><div class="il-upload-file-list" ><div class="container-fluid il-upload-file-items"><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><!-- rows from templates are cloned here with javascript --></div><!-- Templates --><div class="container-fluid" ><!-- hidden Template --><div class="il-upload-file-item il-upload-file-item-template clearfix row standard hidden"><div class="col-xs-12 col-no-padding"><!-- Display Filename--><span class="file-info filename">FILENAME<!-- File name is inserted with javascript here --></span><!-- Display Filesize--><span class="file-info filesize">100KB<!-- File size is inserted with javascript here --></span><!-- Dropdown with actions--><span class="pull-right remove"><!--<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu"><li><a class="btn btn-link" href="" data-action=""  aria-label="delete_file" >remove</a></li></ul></div>--><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span><!-- Progress Bar--><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"aria-valuemin="0"aria-valuemax="100"></div></div><!-- Error Messages --><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></div></div><!-- li from templates are cloned here with javascript --></div></div></div><div class="modal-footer"><a class="btn btn-default btn-primary ilSubmitInactive disabled" data-action="">upload</a><a class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</a></div></div></div></div></div>';

		// start test
		$exampleTextQuestion = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Pretty smart, isn't it?</p>");
		$exampleTextAnswer = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Yeah, this is really smart.</p>");
		$wrapperDropzone = $this->getFactory()->wrapper('', [
			$exampleTextQuestion,
			$exampleTextAnswer,
		]);

		$html = $this->normalizeHTML($this->getDefaultRenderer()->render($wrapperDropzone));

		$this->assertEquals($expectedHtml, $html);
	}


	public function getUIFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}


	public function normalizeHTML($html) {
		$html = trim(str_replace("\t", "", $html));

		return parent::normalizeHTML($html);
	}


	protected function getFactory() {
		return new \ILIAS\UI\Implementation\Component\Dropzone\File\Factory();
	}
}