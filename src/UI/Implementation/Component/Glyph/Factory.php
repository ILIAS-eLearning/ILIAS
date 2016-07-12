<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Component\Glyph as G;

class Factory implements G\Factory {
	/**
	 * @inheritdoc
	 */
	public function settings($action) {
		return new Glyph(G\Glyph::SETTINGS, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function collapse($action) {
		return new Glyph(G\Glyph::COLLAPSE, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function expand($action) {
		return new Glyph(G\Glyph::EXPAND, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function add($action) {
		return new Glyph(G\Glyph::ADD, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function remove($action) {
		return new Glyph(G\Glyph::REMOVE, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function up($action) {
		return new Glyph(G\Glyph::UP, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function down($action) {
		return new Glyph(G\Glyph::DOWN, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function back($action) {
		return new Glyph(G\Glyph::BACK, $action);
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
	public function sort($action) {
		return new Glyph(G\Glyph::SORT, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function user($action) {
		return new Glyph(G\Glyph::USER, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function mail($action) {
		return new Glyph(G\Glyph::MAIL, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function notification($action) {
		return new Glyph(G\Glyph::NOTIFICATION, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function tag($action) {
		return new Glyph(G\Glyph::TAG, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function note($action) {
		return new Glyph(G\Glyph::NOTE, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function comment($action) {
		return new Glyph(G\Glyph::COMMENT, $action);
	}
}
