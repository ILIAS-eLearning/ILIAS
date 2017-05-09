<?php

/**
 * Class JavascriptHelperTest
 *
 * @author  nmaerchy
 * @date    09.05.17
 * @version 0.0.1
 *
 */
use ILIAS\UI\Implementation\Component\FileDropzone\JavascriptHelper;

class JavascriptHelperTest extends PHPUnit_Framework_TestCase {

	/**
	 * A JavascriptHelper --------------------------------------------
	 */

	/**
	 * should return the javascript code to enable the drop design.
	 */
	public function testEnableDropDesign() {

		// setup example objects.
		$dropzoneId = "dz-01";
		$darkendBackground = true;

		$simpleDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\SimpleDropzone();
		$simpleDropzone->setId($dropzoneId);
		$simpleDropzone->setDarkendBackground($darkendBackground);

		// setup expected objects
		$expectedJS = "il.UI.dropzone.enableDropDesign({\"id\": '{$dropzoneId}', \"darkendBackground\": '{$darkendBackground}'});";

		// start test
		$jsHelper = new JavascriptHelper($simpleDropzone);

		$this->assertEquals($expectedJS, $jsHelper->enableDropDesign());
	}


	/**
	 * should return the javascript code to disable the drop design.
	 */
	public function testDisableDropDesign() {

		// setup example objects.
		$dropzoneId = "dz-01";
		$darkendBackground = true;

		$simpleDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\SimpleDropzone();
		$simpleDropzone->setId($dropzoneId);
		$simpleDropzone->setDarkendBackground($darkendBackground);

		// setup expected objects
		$expectedJS = "il.UI.dropzone.disableDropDesign({\"id\": '{$dropzoneId}', \"darkendBackground\": '{$darkendBackground}'});";

		// start test
		$jsHelper = new JavascriptHelper($simpleDropzone);

		$this->assertEquals($expectedJS, $jsHelper->disableDropDesign());
	}
}
