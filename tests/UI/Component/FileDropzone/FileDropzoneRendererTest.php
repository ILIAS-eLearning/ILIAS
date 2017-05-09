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
class FileDropzoneRendererTest extends ILIAS_UI_TestBase {

	/**
	 * A standard dropzone ----------------------------------------------------------------
	 */

	/**
	 * should be rendered with the css class .standard and no content inside the dropzone div.
	 */
	public function testRenderStandardDropzone() {

		$standardDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\Standard();

		// setup expected objects
		$expectedHtml = "<div id=\"id_1-darkend\"></div><div id=\"id_1\" class=\"il-file-dropzone standard\"></div>";

		// start test
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
		$expectedHtml = "<div id=\"id_1-darkend\"></div><div id=\"id_1\" class=\"il-file-dropzone wrapper\"><p>Pretty smart, isn't it?</p><p>Yeah, this is really smart.</p></div>";

		// start test
		$wrapperDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\Wrapper();
		$exampleTextQuestion = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Pretty smart, isn't it?</p>");
		$exampleTextAnswer = new \ILIAS\UI\Implementation\Component\Legacy\Legacy("<p>Yeah, this really smart.</p>");

		$wrapperDropzone = $wrapperDropzone->withContent(array($exampleTextQuestion, $exampleTextAnswer));

		$html = $this->normalizeHTML(
			$this->getDefaultRenderer()->render($wrapperDropzone)
		);

		$this->assertEquals($expectedHtml, $html);
	}
}