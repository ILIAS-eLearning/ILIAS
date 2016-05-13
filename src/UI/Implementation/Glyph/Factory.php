<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;

use ILIAS\UI\Component\Glyph as G;

class Factory implements \ILIAS\UI\Factory\Glyph {
	/**
	 * @inheritdoc
	 */
	public function up() {
		return new Glyph(G::UP, array());
	}

	/**
	 * @inheritdoc
	 */
	public function down() {
		return new Glyph(G::DOWN, array());
	}

	/**
	 * @inheritdoc
	 */
	public function add() {
		return new Glyph(G::ADD, array());
	}
	
	/**
	 * @inheritdoc
	 */
	public function remove() {
		return new Glyph(G::REMOVE, array());
	}

	/**
	 * @inheritdoc
	 */
	public function previous() {
		return new Glyph(G::PREVIOUS, array());
	}

	/**
	 * @inheritdoc
	 */
	public function next() {
		return new Glyph(G::NEXT, array());
	}

	/**
	 * @inheritdoc
	 */
	public function calendar() {
		return new Glyph(G::CALENDAR, array());
	}

	/**
	 * @inheritdoc
	 */
	public function close() {
		return new Glyph(G::CLOSE, array());
	}

	/**
	 * @inheritdoc
	 */
	public function attachment() {
		return new Glyph(G::ATTACHMENT, array());
	}

	/**
	 * @inheritdoc
	 */
	public function caret() {
		return new Glyph(G::CARET, array());
	}

	/**
	 * @inheritdoc
	 */
	public function drag() {
		return new Glyph(G::DRAG, array());
	}

	/**
	 * @inheritdoc
	 */
	public function search() {
		return new Glyph(G::SEARCH, array());
	}

	/**
	 * @inheritdoc
	 */
	public function filter() {
		return new Glyph(G::FILTER, array());
	}

	/**
	 * @inheritdoc
	 */
	public function info() {
		return new Glyph(G::INFO, array());
	}

	/**
	 * @inheritdoc
	 */
	public function envelope() {
		return new Glyph(G::ENVELOPE, array());
	}
}