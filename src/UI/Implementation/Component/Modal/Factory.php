<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Modal as Modal;

/**
 * Implementation of factory for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements Modal\Factory {

	/**
	 * @inheritdoc
	 */
	public function interruptive($title, $message, $form_action) {
		return new Interruptive($title, $message, $form_action);
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
		return new RoundTrip($title, $content);
	}


	/**
	 * @inheritdoc
	 */
	public function lightbox($pages) {
		return new Lightbox($pages);
	}


	/**
	 * @inheritdoc
	 */
	public function lightboxImagePage(Component\Image\Image $image, $title, $description = '') {
		return new LightboxImagePage($image, $title, $description);
	}
}
