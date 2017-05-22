<?php
/**
 * Class JSDropzoneInitializer
 *
 * @author  nmaerchy
 * @date    22.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone
 */

namespace ILIAS\UI\Implementation\Component\Dropzone;

class JSDropzoneInitializer {

	/**
	 * @var SimpleDropzone $dropzone
	 */
	private $dropzone;


	/**
	 * JSDropzoneInitializer constructor.
	 *
	 * @param SimpleDropzone $dropzone
	 */
	public function __construct(SimpleDropzone $dropzone) { $this->dropzone = $dropzone; }

	public function initDropzone() {

		$darkenedBackground = $this->dropzone->isDarkenedBackground()? "true" : "false";

		return "
		
			il.UI.dropzone.initializeDropzone(\"{$this->dropzone->getType()}\", {
			
				\"id\": \"{$this->dropzone->getId()}\",
				\"darkenedBackground\": {$darkenedBackground},
				\"registeredSignals\": [{$this->getRegisteredSignals()}]
			});
		";

	}

	private function getRegisteredSignals() {

		$registeredSignalList = array();

		foreach ($this->dropzone->getRegisteredSignals() as $registeredSignal) {

			$signal = $registeredSignal->getSignal();
			array_push($registeredSignalList, $signal);
		}

		return implode(",", $registeredSignalList);
	}
}