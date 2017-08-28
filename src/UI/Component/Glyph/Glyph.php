<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Glyph;

use \ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\Clickable;

/**
 * This describes how a glyph could be modified during construction of UI.
 */
interface Glyph extends \ILIAS\UI\Component\Component, \ILIAS\UI\Component\JavaScriptBindable, Clickable {
	// Types of glyphs:
	const SETTINGS = "settings";
	const EXPAND = "expand";
	const COLLAPSE = "collapse";
	const ADD = "add";
	const REMOVE = "remove";
	const UP = "up";
	const DOWN = "down";
	const BACK = "back";
	const NEXT = "next";
	const SORT_ASCENDING = "sortAscending";
	const SORT_DESCENDING = "sortDescending";
	const SORT = "sort";
	const USER = "user";
	const MAIL = "mail";
	const NOTIFICATION = "notification";
	const TAG = "tag";
	const NOTE = "note";
	const COMMENT = "comment";
	const BRIEFCASE = "briefcase";


	/**
	 * Get the type of the glyph.
	 *
	 * @return	string
	 */
	public function getType();

	/**
	 * Get the action on the glyph.
	 *
	 * @return	string|null
	 */
	public function getAction();

	/**
	 * Get all counters attached to this glyph.
	 *
	 * @return	Counter[]
	 */
	public function getCounters();

	/**
	 * Get a glyph like this, but with a counter on it.
	 *
	 * If there already is a counter of the given counter type, replace that
	 * counter by the new one.
	 *
	 * @param	Counter $counter
	 * @return	Glyph
	 */
	public function withCounter(Counter $counter);


	/**
	 * Returns whether the Glyph is highlighted.
	 *
	 * @return bool
	 */
	public function isHighlighted();

	/**
	 * Get a Glyph like this with a highlight.
	 *
	 * @param bool|true $highlighted
	 * @return mixed
	 */
	public function withHighlight();
}
