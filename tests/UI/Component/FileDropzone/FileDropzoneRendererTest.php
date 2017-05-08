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

	public function testRenderStandardDropzone() {

		$standardDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\Standard();

		// setup expected objects
		$expectedHtml = "<div id=\"1\"></div><div id=\"2\" class=\"il-file-dropzone\"></div>";

		// start test
		$html = $this->normalizeHTML(
			$this->getDefaultRenderer()->render($standardDropzone)
		);

		$this->assertEquals($expectedHtml, $html);
	}
}