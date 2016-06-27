<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Component\Glyph as G;

class Factory implements G\Factory {
	/**
	 * @inheritdoc
	 */
	public function up($action) {
		return new Glyph(G\Glyph::UP);
	}

	/**
	 * @inheritdoc
	 */
	public function down($action) {
		return new Glyph(G\Glyph::DOWN);
	}

	/**
	 * @inheritdoc
	 */
	public function add($action) {
		return new Glyph(G\Glyph::ADD);
	}
	
	/**
	 * @inheritdoc
	 */
	public function remove($action) {
		return new Glyph(G\Glyph::REMOVE);
	}

	/**
	 * @inheritdoc
	 */
	public function previous($action) {
		return new Glyph(G\Glyph::PREVIOUS);
	}

	/**
	 * @inheritdoc
	 */
	public function next($action) {
		return new Glyph(G\Glyph::NEXT, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function calendar($action) {
		return new Glyph(G\Glyph::CALENDAR, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function close($action) {
		return new Glyph(G\Glyph::CLOSE, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function attachment($action) {
		return new Glyph(G\Glyph::ATTACHMENT, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function caret($action) {
		return new Glyph(G\Glyph::CARET, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function drag($action) {
		return new Glyph(G\Glyph::DRAG, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function search($action) {
		return new Glyph(G\Glyph::SEARCH, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function filter($action) {
		return new Glyph(G\Glyph::FILTER, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function info($action) {
		return new Glyph(G\Glyph::INFO, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function envelope($action) {
		return new Glyph(G\Glyph::ENVELOPE, $action);
	}
}
