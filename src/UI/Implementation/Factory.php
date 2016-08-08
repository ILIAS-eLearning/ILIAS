<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

// TODO: This might cache the created factories.
class Factory implements \ILIAS\UI\Factory {
	/**
	 * @inheritdoc
	 */
	public function counter() {
		return new Component\Counter\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function glyph() {
		return new Component\Glyph\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function button() {
		return new Component\Button\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function card($title,\ILIAS\UI\Component\Image\Image $image = null){
		return new Component\Card\Card($title,$image);
	}

	/**
	 * @inheritdoc
	 */
	public function deck(array $cards){
		return new Component\Deck\Deck($cards, Component\Deck\Deck::SIZE_S);
	}
}
