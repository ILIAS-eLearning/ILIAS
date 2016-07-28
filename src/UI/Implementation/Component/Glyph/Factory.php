<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Component\Glyph as G;

class Factory implements G\Factory {
	/**
	 * @inheritdoc
	 */
	public function settings($action = null) {
		return new Glyph(G\Glyph::SETTINGS, "settings", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function collapse($action = null) {
		return new Glyph(G\Glyph::COLLAPSE, "collapse_content", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function expand($action = null) {
		return new Glyph(G\Glyph::EXPAND, "expand_content", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function add($action = null) {
		return new Glyph(G\Glyph::ADD, "add", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function remove($action = null) {
		return new Glyph(G\Glyph::REMOVE, "remove", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function up($action = null) {
		return new Glyph(G\Glyph::UP, "up", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function down($action = null) {
		return new Glyph(G\Glyph::DOWN, "down", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function back($action = null) {
		return new Glyph(G\Glyph::BACK, "back", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function next($action = null) {
		return new Glyph(G\Glyph::NEXT, "next", $action);
	}


	/**
	 * @inheritdoc
	 */
	public function sortAscending($action = null) {
		return new Glyph(G\Glyph::SORT_ASCENDING, "sort_ascending", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function sortDescending($action = null) {
		return new Glyph(G\Glyph::SORT_DESCENDING, "sort_descending", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function sort($action = null) {
		return new Glyph(G\Glyph::SORT, "sort", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function user($action = null) {
		return new Glyph(G\Glyph::USER, "show_who_is_online", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function mail($action = null) {
		return new Glyph(G\Glyph::MAIL, "mail", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function notification($action = null) {
		return new Glyph(G\Glyph::NOTIFICATION, "notifications", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function tag($action = null) {
		return new Glyph(G\Glyph::TAG, "tags", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function note($action = null) {
		return new Glyph(G\Glyph::NOTE, "notes", $action);
	}

	/**
	 * @inheritdoc
	 */
	public function comment($action = null) {
		return new Glyph(G\Glyph::COMMENT, "comments", $action);
	}
}
