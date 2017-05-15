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
 * @version 0.0.4
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

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

	public function enableAutoDesign() {
		return "il.UI.dropzone.enableAutoDesign()";
	}

	public function enableDragHover() {
		return "$(this).addClass(\"drag-hover\");";
	}

	public function disableDragHover() {
		return "$(this).removeClass(\"drag-hover\");";
	}


	/**
	 * Generates the javascript code to enable the darkend background for dropzones.
	 *
	 * @return string the generated code
	 */
	public function enableDarkendDesign() {
		return "il.UI.dropzone.enableDarkendDesign();";
	}

	public function enableDefaultDesign() {
		return "il.UI.dropzone.enableDefaultDesign();";
	}

	public function configureDarkendDesign() {
		return "il.UI.dropzone.setDarkendDesign({$this->simpleDropzone->isDarkendBackground()})";
	}

	/**
	 * Generates the javascript code to disable all css highlighting for dropzones.
	 *
	 * @return string the generated code
	 */
	public function disableDesign() {
		return "il.UI.dropzone.disableDesign();";
	}

	/**
	 * Generates the javascript code to trigger all registered signals of a dropzone.
	 * The result of this method needs a javascript variable "event".
	 *
	 * e.g. javascript code
	 * function(event) { JavascriptHelper#triggerRegisteredSignals }
	 *
	 * @return string the generated code
	 */
	public function triggerRegisteredSignals() {

		$jsCode = "";
		foreach ($this->simpleDropzone->getRegisteredSignals() as $triggeredSignal) {
			/**
			 * @var \ILIAS\UI\Implementation\Component\Signal $signal
			 */
			$signal = $triggeredSignal->getSignal();
			$jsCode .= "$('#{$this->simpleDropzone->getId()}').trigger('{$signal}', event);\n";
		}
		return $jsCode;
	}


	/**
	 * Wraps the id used in the javascript into a jQuery object.
	 * e.g. $("#dropzoneId")
	 *
	 * @return string the jQuery object of the dropzone used in the javascript code.
	 */
	public function getJSDropzone() {
		return "$(\"#{$this->simpleDropzone->getId()}\")";
	}
}