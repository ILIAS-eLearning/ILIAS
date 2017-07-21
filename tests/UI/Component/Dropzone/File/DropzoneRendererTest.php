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
		$expectedHtml = '<div id="id_1" class="il-dropzone standard"><div class="dz-default dz-message"><span>drag_files_here</span></div></div><div class="text-center">- logic_or -</div><div class="il-dropzone-standard-select-files-wrapper text-center"><a href="" class="il-dropzone-standard-select-files">select_files_from_computer</a></div><div class="il-upload-file-list" data-upload-id="id_1"><li class="list-group-item il-upload-file-item il-upload-file-item-template hidden"><div class="filename"><!-- File name is inserted with javascript here --></div><div class="filesize small"><!-- File size is inserted with javascript here --></div><div class="btn-group" style="position: absolute; right: 16px; top: 16px"><button type="button" class="btn btn-default delete-file" aria-label="Remove File"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"     aria-valuemin="0"     aria-valuemax="100"></div></div><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></li><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><ul class="list-group il-upload-file-items"><!-- li from templates are cloned here with javascript --></ul></div>';

		// start test
		$standardDropzone = $this->getFactory()->standard('');

		$html = $this->normalizeHTML(
			$this->getDefaultRenderer()->render($standardDropzone)
		);

		$this->assertEquals($expectedHtml, $html);
	}


	/**
	 * should be rendered with the css class .standard and a span-tag with the passed in message inside the dropzone div.
	 */
	public function testRenderStandardDropzoneWithMessage() {

		// setup expected objects
		$expectedHtml = '<div id="id_1" class="il-dropzone standard"><div class="dz-default dz-message"><span>message</span></div></div><div class="text-center">- logic_or -</div><div class="il-dropzone-standard-select-files-wrapper text-center"><a href="" class="il-dropzone-standard-select-files">select_files_from_computer</a></div><div class="il-upload-file-list" data-upload-id="id_1"><li class="list-group-item il-upload-file-item il-upload-file-item-template hidden"><div class="filename"><!-- File name is inserted with javascript here --></div><div class="filesize small"><!-- File size is inserted with javascript here --></div><div class="btn-group" style="position: absolute; right: 16px; top: 16px"><button type="button" class="btn btn-default delete-file" aria-label="Remove File"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"     aria-valuemin="0"     aria-valuemax="100"></div></div><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></li><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><ul class="list-group il-upload-file-items"><!-- li from templates are cloned here with javascript --></ul></div>';

		// start test
		$standardDropzone = $this->getFactory()->standard('')
			->withMessage('message');

		$html = $this->normalizeHTML(
			$this->getDefaultRenderer()->render($standardDropzone)
		);

		$this->assertEquals($expectedHtml, $html);
	}


	/**
	 * A wrapper dropzone -----------------------------------------------------------------
	 */

	/**
	 * should be rendered with the css class .wrapper and all passed in ILIAS UI components inside the div.
	 */
	public function testRenderWrapperDropzone() {

		// setup expected objects
		$expectedHtml = '<div id="id_1" class="il-dropzone wrapper"><p>Pretty smart, isn\'t it?</p><p>Yeah, this is really smart.</p></div><div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_2"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">upload</h4></div><div class="modal-body"><div class="il-upload-file-list" data-upload-id="id_1"><li class="list-group-item il-upload-file-item il-upload-file-item-template hidden"><div class="filename"><!-- File name is inserted with javascript here --></div><div class="filesize small"><!-- File size is inserted with javascript here --></div><div class="btn-group" style="position: absolute; right: 16px; top: 16px"><button type="button" class="btn btn-default delete-file" aria-label="Remove File"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div><div class="progress" style="margin: 10px 0; display: none;"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0"     aria-valuemin="0"     aria-valuemax="100"></div></div><div class="file-error-message alert alert-danger" role="alert" style="display: none;"><!-- Error message for file is inserted with javascript here --></div><div class="file-success-message alert alert-success" role="alert" style="display: none;"><!-- Success message for file is inserted with javascript here --></div></li><div class="error-messages" style="display: none;"><div class="alert alert-danger" role="alert"><!-- General error messages are inserted here with javascript --></div></div><ul class="list-group il-upload-file-items"><!-- li from templates are cloned here with javascript --></ul></div></div><div class="modal-footer"><a class="btn btn-default btn-primary ilSubmitInactive disabled" data-action="" id="id_3">upload</a><a class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</a></div></div></div></div>';

		// start test
		$exampleTextQuestion = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Pretty smart, isn't it?</p>");
		$exampleTextAnswer = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Yeah, this is really smart.</p>");
		$wrapperDropzone = $this->getFactory()->wrapper('', [$exampleTextQuestion, $exampleTextAnswer]);

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