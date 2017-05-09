<?php
/**
 * Class JavascriptHelper
 *
 * Helper class to create often used javascript commands a dropzone will need.
 * The javascript uses the dropzone.js library
 * @see http://www.dropzonejs.com/#configuration
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Implementation\Component\TriggeredSignal;

class JavascriptHelper {

	/**
	 * @var SimpleDropzone $simpleDropzone
	 */
	private $simpleDropzone;


	/**
	 * JavascriptHelper constructor.
	 *
	 * @param SimpleDropzone $simpleDropzone
	 */
	public function __construct(SimpleDropzone $simpleDropzone) {
		$this->simpleDropzone = $simpleDropzone;
	}


	/**
	 * Generates the javascript code to initialize a dropzone.
	 *
	 * @return string the generated code
	 */
	public function initializeDropzone() {
		/*
		 * the url parameter is required by the library,
		 * so we set autoProcessQueue to false to prevent the upload to the url
		 *
		 * The previewsContainer option needs to be empty exclusive, otherwise previews of the files will be displayed.
		 * @see hhttp://www.dropzonejs.com/#configuration
		 */
		return "var {$this->simpleDropzone->getId()} = new Dropzone(\"div#{$this->simpleDropzone->getId()}\", {

				url: \"/\",
				autoProcessQueue: false,
				dictDefaultMessage: \"\",
				clickable: false,

		});
		{$this->simpleDropzone->getId()}.previewsContainer = \"\"
		";
	}


	/**
	 * Generates the javascript code to enable the drop design of a dropzone.
	 *
	 * @return string the generated code
	 */
	public function enableDropDesign() {
		return "il.UI.dropzone.enableDropDesign({\"id\": '{$this->simpleDropzone->getId()}', \"darkendBackground\": '{$this->simpleDropzone->isDarkendBackground()}'});";
	}

	/**
	 * Generates the javascript code to disable the drop design of a dropzone.
	 *
	 * @return string the generated code
	 */
	public function disableDropDesign() {
		return "il.UI.dropzone.disableDropDesign({\"id\": '{$this->simpleDropzone->getId()}', \"darkendBackground\": '{$this->simpleDropzone->isDarkendBackground()}'});";
	}

	/**
	 * Generates the javascript code to trigger all passed in signals.
	 * The result of this method needs to be wrapped by the {@link JavascriptHelper#wrapToJSEventFunction}
	 * to avoid javascript syntax errors.
	 *
	 * @param TriggeredSignal[] $signalList a list of signals to trigger
	 *
	 * @return string the generated code
	 */
	public function triggerSignals(array $signalList) {

		$jsCode = "";
		foreach ($signalList as $triggeredSignal) {
			/**
			 * @var \ILIAS\UI\Implementation\Component\Signal $signal
			 */
			$signal = $triggeredSignal->getSignal();
			$jsCode .= "$('#{$this->simpleDropzone->getId()}').trigger('{$signal}', event);\n";
		}
		return $jsCode;
	}


	/**
	 * Wraps the passed in javascript code to a javascript event function.
	 *
	 * e.g. function(event) {...}
	 *
	 * @param string $javascriptCode the javascript code to wrap
	 *
	 * @return string the wrapped javascript code
	 */
	public function wrapToJSEventFunction($javascriptCode) {
		return "function(event) {" . $javascriptCode . "}";
	}


	/**
	 * @return string the id of the dropzone used in the javascript code.
	 */
	public function getJSDropzone() {
		return $this->simpleDropzone->getId();
	}
}