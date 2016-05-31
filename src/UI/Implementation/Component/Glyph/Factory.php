<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Component\Glyph as G;

class Factory implements G\Factory {
	/**
	 * @inheritdoc
	 */
	public function up() {
		return new Glyph(G\Glyph::UP);
	}

	/**
	 * @inheritdoc
	 */
	public function down() {
		return new Glyph(G\Glyph::DOWN);
	}

	/**
	 * @inheritdoc
	 */
	public function add() {
		return new Glyph(G\Glyph::ADD);
	}
	
	/**
	 * @inheritdoc
	 */
	public function remove() {
		return new Glyph(G\Glyph::REMOVE);
	}

	/**
	 * @inheritdoc
	 */
	public function previous() {
		return new Glyph(G\Glyph::PREVIOUS);
	}

	/**
	 * @inheritdoc
	 */
	public function next() {
		return new Glyph(G\Glyph::NEXT);
	}

	/**
	 * @inheritdoc
	 */
	public function calendar() {
		return new Glyph(G\Glyph::CALENDAR);
	}

	/**
	 * @inheritdoc
	 */
	public function close() {
		return new Glyph(G\Glyph::CLOSE);
	}

	/**
	 * @inheritdoc
	 */
	public function attachment() {
		return new Glyph(G\Glyph::ATTACHMENT);
	}

	/**
	 * @inheritdoc
	 */
	public function caret() {
		return new Glyph(G\Glyph::CARET);
	}

	/**
	 * @inheritdoc
	 */
	public function drag() {
		return new Glyph(G\Glyph::DRAG);
	}

	/**
	 * @inheritdoc
	 */
	public function search() {
		return new Glyph(G\Glyph::SEARCH);
	}

	/**
	 * @inheritdoc
	 */
	public function filter() {
		return new Glyph(G\Glyph::FILTER);
	}

	/**
	 * @inheritdoc
	 */
	public function info() {
		return new Glyph(G\Glyph::INFO);
	}

	/**
	 * @inheritdoc
	 */
	public function envelope() {
		return new Glyph(G\Glyph::ENVELOPE);
	}
}
