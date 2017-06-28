<?php
/**
 * Class JSDropzoneInitializer
 *
 * Generates the javascript code to initialize a dropzone.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    22.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\TriggeredSignalInterface;

class JSDropzoneInitializer {

	/**
	 * @var SimpleDropzone $dropzone
	 */
	private $dropzone;


	/**
	 * JSDropzoneInitializer constructor.
	 *
	 * @param SimpleDropzone $dropzone a wrapper class for dropzones
	 */
	public function __construct(SimpleDropzone $dropzone) { $this->dropzone = $dropzone; }


	/**
	 * Generates the javascript code to initialize a dropzone.
	 *
	 * @return string the generated code
	 */
	public function initDropzone() {
		$options = json_encode([
			'id' => $this->dropzone->getId(),
			'darkenedBackground' => $this->dropzone->isDarkenedBackground(),
			'registeredSignals' => $this->getRegisteredSignalIds(),
			'uploadId' => $this->dropzone->getUploadId(),
			'uploadUrl' => $this->dropzone->getUploadUrl(),
		]);
		return "il.UI.dropzone.initializeDropzone('{$this->dropzone->getType()}', JSON.parse('{$options}'));";
	}


	/**
	 * @return string the registered signals as comma separated string
	 */
	private function getRegisteredSignalIds() {
		return array_map(function($triggeredSignal) {
			/** @var $triggeredSignal TriggeredSignalInterface */
			return (string) $triggeredSignal->getSignal();
		}, $this->dropzone->getRegisteredSignals());
	}
}