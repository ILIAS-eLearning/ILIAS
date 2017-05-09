<?php
/**
 * Class JavascriptHelper
 *
 * Helper class to create often used javascript commands a dropzone will need.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.1
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
		 */
		return "var {$this->simpleDropzone->getId()} = new Dropzone(\"div#{$this->simpleDropzone->getId()}\", {

				url: \"/\",
				autoProcessQueue: false,
				dictDefaultMessage: \"\",
				clickable: false,

		});";
	}


	/**
	 * Generates the javascript function to enable the drop design of a dropzone.
	 * The function looks like:
	 *      function(event) {...}
	 *
	 * @return string the generated code
	 */
	public function enableDropDesign() {
		return "function(event) {
			il.UI.dropzone.enableDropDesign({\"id\": '{$this->simpleDropzone->getId()}', \"darkendBackground\": '{$this->simpleDropzone->isDarkendBackground()}'});
		}";
	}

	/**
	 * Generates the javascript function to disable the drop design of a dropzone.
	 * The function looks like:
	 *      function(event) {...}
	 *
	 * @return string the generated code
	 */
	public function disableDropDesign() {
		return "function(event) {
			il.UI.dropzone.disableDropDesign({\"id\": '{$this->simpleDropzone->getId()}', \"darkendBackground\": '{$this->simpleDropzone->isDarkendBackground()}'});
		}";
	}

	/**
	 * Generates the javascript function to trigger all passed in signals.
	 * The function looks like:
	 *      function(event) {...}
	 *
	 * @param TriggeredSignal[] $signalList a list of signals to trigger
	 *
	 * @return string the generated code
	 */
	public function triggerSignals(array $signalList) {

		$jsCode = "function(event) {";
		foreach ($signalList as $triggeredSignal) {
			/**
			 * @var \ILIAS\UI\Implementation\Component\Signal $signal
			 */
			$signal = $triggeredSignal->getSignal();
			$jsCode .= "$('#{$this->simpleDropzone->getId()}').trigger('{$signal}', event);";
		}

		$jsCode .= "}";

		return $jsCode;
	}


	/**
	 * @return string the id of the dropzone used in the javascript code.
	 */
	public function getJSDropzone() {
		return $this->simpleDropzone->getId();
	}
}