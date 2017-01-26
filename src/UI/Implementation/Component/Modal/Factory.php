<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Modal as Modal;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Implementation of factory for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements Modal\Factory {

	/**
	 * @var Component\SignalGenerator
	 */
	protected $signal_generator;

	/**
	 * @param Component\SignalGenerator $signal_generator
	 */
	public function __construct(Component\SignalGenerator $signal_generator) {
		$this->signal_generator = $signal_generator;
	}


	/**
	 * @inheritdoc
	 */
	public function interruptive($title, $message, $form_action) {
		return new Interruptive($title, $message, $form_action, $this->signal_generator);
	}


	/**
	 * @inheritdoc
	 */
	public function interruptiveItem($id, $title, $description = '') {
		return new InterruptiveItem($id, $title, $description);
	}


	/**
	 * @inheritdoc
	 */
	public function roundtrip($title, $content) {
		return new RoundTrip($title, $content, $this->signal_generator);
	}


	/**
	 * @inheritdoc
	 */
	public function lightbox($pages) {
		return new Lightbox($pages, $this->signal_generator);
	}


	/**
	 * @inheritdoc
	 */
	public function lightboxImagePage(Component\Image\Image $image, $title, $description = '') {
		return new LightboxImagePage($image, $title, $description);
	}
}
