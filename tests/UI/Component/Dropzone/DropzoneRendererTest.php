<?php

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

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
	 * should be rendered with the css class .standard and no content inside the dropzone div.
	 */
	public function testRenderStandardDropzone() {

		// setup expected objects
		$expectedHtml = "<div id=\"id_1\" class=\"il-dropzone standard\"></div>";

		// start test
		$standardDropzone = new \ILIAS\UI\Implementation\Component\Dropzone\Standard();

		$html = $this->normalizeHTML(
			$this->getDefaultRenderer()->render($standardDropzone)
		);

		$this->assertEquals($expectedHtml, $html);
	}


	/**
	 * should be rendered with the css class .standard and a span-tag with the passed in message inside the dropzone div.
	 */
	public function testRenderStandardDropzoneWithDefaultMessage() {

		// setup expected objects
		$expectedHtml = "<div id=\"id_1\" class=\"il-dropzone standard\"><div class=\"dz-default dz-message\"><span>Drop files here to upload</span></div></div>";

		// start test
		$standardDropzone = new \ILIAS\UI\Implementation\Component\Dropzone\Standard();
		$standardDropzone = $standardDropzone->withMessage("Drop files here to upload");

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
		$expectedHtml = "<div id=\"id_1\" class=\"il-dropzone wrapper\"><p>Pretty smart, isn't it?</p><p>Yeah, this is really smart.</p></div>";

		// start test
		$exampleTextQuestion = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Pretty smart, isn't it?</p>");
		$exampleTextAnswer = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Yeah, this is really smart.</p>");
		$wrapperDropzone = new \ILIAS\UI\Implementation\Component\Dropzone\Wrapper(array($exampleTextQuestion, $exampleTextAnswer));

		$html = $this->normalizeHTML(
			$this->getDefaultRenderer()->render($wrapperDropzone)
		);

		$this->assertEquals($expectedHtml, $html);
	}


	public function normalizeHTML($html) {
		$html = trim(str_replace("\t", "", $html));
		return parent::normalizeHTML($html);
	}


}