<?php
/**
 * Class JavascriptHelper
 *
 * Helper class to generate the javascript code needed for dropzones.
 * The generated javascript code uses the jQuery dragster plugin.
 * @see https://github.com/catmanjan/jquery-dragster
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.6
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone
 */

namespace ILIAS\UI\Implementation\Component\Dropzone;

class JavascriptHelper {

	/**
	 * @var SimpleDropzone $simpleDropzone
	 */
	private $simpleDropzone;


	/**
	 * JavascriptHelper constructor.
	 *
	 * @param SimpleDropzone $simpleDropzone A wrapper class for dropzone components.
	 */
	public function __construct(SimpleDropzone $simpleDropzone) {
		$this->simpleDropzone = $simpleDropzone;
	}


	/**
	 * Creates the javascript code to configure a Standard Dropzone.
	 *
	 * @return string The generated Javascript code.
	 */
	public function initializeStandardDropzone() {

		return "
		
			{$this->configureDarkenedDesign()}
			
			{$this->getJSDropzone()}.dragster({
			
				enter: function(dragsterEvent, event) {
					{$this->addDropzoneHover()}
					{$this->enableHighlightDesign()}
				},
				leave: function(dragsterEvent, event) {
					{$this->removeDropzoneHover()}
					{$this->disableHighlightDesign()}
				},
				drop: function(dragsterEvent, event) {
					{$this->removeDropzoneHover()}
					{$this->disableHighlightDesign()}
					{$this->triggerRegisteredSignals()}
				}
			});
		";

	}


	/**
	 * Creates the javascript code to configure a Wrapper Dropzone.
	 * The generated code adds also a listener on the html document.
	 *
	 * @return string The generated code.
	 */
	public function initializeWrapperDropzone() {
		return "
		
			{$this->configureDarkenedDesign()}
		
			$(document).dragster({
			
				enter: function(dragsterEvent, event) {
					{$this->enableHighlightDesign()}
				},
				leave: function(dragsterEvent, event) {
					{$this->disableHighlightDesign()}
				},
				drop: function(dragsterEvent, event) {
					{$this->disableHighlightDesign()}
				}
			
			});
			
			
			{$this->getJSDropzone()}.dragster({
			
				enter: function(dragsterEvent, event) {
					dragsterEvent.stopImmediatePropagation();
					{$this->addDropzoneHover()}
				},
				leave: function(dragsterEvent, event) {
					dragsterEvent.stopImmediatePropagation();
					{$this->removeDropzoneHover()}
				},
				drop: function(dragsterEvent, event) {
					{$this->removeDropzoneHover()}
					{$this->disableHighlightDesign()}
					{$this->triggerRegisteredSignals()}
				}
			
			});
		";
	}


	/**
	 * Generates the javascript code to enable the highlight design, depending
	 * on the {@link $this->simpleDropzone->isUseAutoHighlight} return value.
	 *
	 * @return string The javascript code to enable the highlight design.
	 */
	private function enableHighlightDesign() {
		if ($this->simpleDropzone->isUseAutoHighlight()) {
			return "il.UI.dropzone.enableAutoDesign()";
		}
		return "il.UI.dropzone.enableHighlightDesign({$this->simpleDropzone->isDarkenedBackground()});";
	}

	/**
	 * Generates the javascript code to disable all css highlighting for dropzones.
	 *
	 * @return string The javascript code to disable all css highlighting for dropzones.
	 */
	private function disableHighlightDesign() {
		return "il.UI.dropzone.disableHighlightDesign();";
	}

	/**
	 * @return string The javascript code to enable drag hover style.
	 */
	private function addDropzoneHover() {
		return "$(this).addClass(\"drag-hover\");";
	}


	/**
	 * @return string The javascript code to disable drag hover style.
	 */
	private function removeDropzoneHover() {
		return "$(this).removeClass(\"drag-hover\");";
	}


	/**
	 * @return string The javascript code to configure the darkend background.
	 */
	private function configureDarkenedDesign() {
		return "il.UI.dropzone.setDarkenedDesign({$this->simpleDropzone->isDarkenedBackground()})";
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
	private function triggerRegisteredSignals() {

		$jsCode = "";
		foreach ($this->simpleDropzone->getRegisteredSignals() as $triggeredSignal) {
			/**
			 * @var \ILIAS\UI\Implementation\Component\Signal $signal
			 */
			$signal = $triggeredSignal->getSignal();
			$jsCode .= "{$this->getJSDropzone()}.trigger('{$signal}', event);\n";
		}
		return $jsCode;
	}


	/**
	 * Wraps the id used in the javascript into a jQuery object.
	 * e.g. $("#dropzoneId")
	 *
	 * @return string the jQuery object of the dropzone used in the javascript code.
	 */
	private function getJSDropzone() {
		return "$(\"#{$this->simpleDropzone->getId()}\")";
	}
}