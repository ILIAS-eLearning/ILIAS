<?php
namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Image\Image;

/**
 * Implementation of factory for modals
 *
 * @author Jesús López <lopez@leifos.com>
 */
class Factory implements Component\Card\Factory {

	public function card($title, $image){
		return new Card($title, $image);
	}

	public function custom($title, $image, $object_icon){
		return new Custom($title, $image, $object_icon);
	}
}
