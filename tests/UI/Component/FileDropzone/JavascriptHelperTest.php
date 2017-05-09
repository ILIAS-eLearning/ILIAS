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


	/**
	 * should return the javascript code to initialize a dropzone.
	 */
	public function testInitializeDropzone() {

		// setup example objects.
		$dropzoneId = "dz-01";
		$darkendBackground = true;

		$simpleDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\SimpleDropzone();
		$simpleDropzone->setId($dropzoneId);
		$simpleDropzone->setDarkendBackground($darkendBackground);

		// setup expected objects
		$expectedJS = "var $dropzoneId = new Dropzone(\"div#$dropzoneId\", {

				url: \"/\",
				autoProcessQueue: false,
				dictDefaultMessage: \"\",
				clickable: false,

		});
		$dropzoneId.previewsContainer = \"\"
		";

		// start test
		$jsHelper = new JavascriptHelper($simpleDropzone);

		$this->assertEquals($expectedJS, $jsHelper->initializeDropzone());
	}


	/**
	 * should generate the javascript code to trigger all signals of a dropzone.
	 */
	public function testTriggerSignals() {

		// setup example objects.
		$dropzoneId = "dz-01";

		$simpleDropzone = new \ILIAS\UI\Implementation\Component\FileDropzone\SimpleDropzone();
		$simpleDropzone->setId($dropzoneId);

		$signalGenerator = new \ILIAS\UI\Implementation\Component\SignalGenerator();

		$firstSignal = $signalGenerator->create();
		$secondSignal = $signalGenerator->create();

		$firstTriggeredSignal = new \ILIAS\UI\Implementation\Component\TriggeredSignal($firstSignal, "drop");
		$secondTriggeredSignal = new \ILIAS\UI\Implementation\Component\TriggeredSignal($secondSignal, "drop");

		// setup expected objects
		$expectedJS = "$('#{$dropzoneId}').trigger('{$firstSignal}', event);\n$('#{$dropzoneId}').trigger('{$secondSignal}', event);\n";

		// start test
		$jsHelper = new JavascriptHelper($simpleDropzone);

		$this->assertEquals($expectedJS, $jsHelper->triggerSignals(array($firstTriggeredSignal, $secondTriggeredSignal)));
	}
}
