<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;

use ILIAS\UI\Component\Glyph as G;

class Factory implements \ILIAS\UI\Factory\Glyph {
	/**
	 * @inheritdoc
	 */
	public function up() {
		return new Glyph(G::UP);
	}

	/**
	 * @inheritdoc
	 */
	public function down() {
		return new Glyph(G::DOWN);
	}

	/**
	 * @inheritdoc
	 */
	public function add() {
		return new Glyph(G::ADD);
	}
	
	/**
	 * @inheritdoc
	 */
	public function remove() {
		return new Glyph(G::REMOVE);
	}

	/**
	 * @inheritdoc
	 */
	public function previous() {
		return new Glyph(G::PREVIOUS);
	}

	/**
	 * @inheritdoc
	 */
	public function next() {
		return new Glyph(G::NEXT);
	}

	/**
	 * @inheritdoc
	 */
	public function calendar() {
		return new Glyph(G::CALENDAR);
	}

	/**
	 * @inheritdoc
	 */
	public function close() {
		return new Glyph(G::CLOSE);
	}

	/**
	 * @inheritdoc
	 */
	public function attachment() {
		return new Glyph(G::ATTACHMENT);
	}

	/**
	 * @inheritdoc
	 */
	public function caret() {
		return new Glyph(G::CARET);
	}

	/**
	 * @inheritdoc
	 */
	public function drag() {
		return new Glyph(G::DRAG);
	}

	/**
	 * @inheritdoc
	 */
	public function search() {
		return new Glyph(G::SEARCH);
	}

	/**
	 * @inheritdoc
	 */
	public function filter() {
		return new Glyph(G::FILTER);
	}

	/**
	 * @inheritdoc
	 */
	public function info() {
		return new Glyph(G::INFO);
	}

	/**
	 * @inheritdoc
	 */
	public function envelope() {
		return new Glyph(G::ENVELOPE);
	}
}
