<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component;

/**
 * This describes how a glyph could be modified during construction of UI.
 */
interface Glyph extends \ILIAS\UI\Element {
	// Types of glyphs:
	const UP = "up";
	const DOWN = "down";
	const ADD = "add";
	const REMOVE = "remove";
	const PREVIOUS = "previous";
	const NEXT = "next";
	const CALENDAR = "calendar";
	const CLOSE = "close";
	const ATTACHMENT = "attachment";
	const CARET = "caret";
	const DRAG = "drag";
	const SEARCH = "search";
	const FILTER = "filter";
	const INFO = "info";
	const ENVELOPE = "envelope";

	/*static function is_valid_type($type) {
		static $types = array
			( self::UP
			, self::DOWN
			, self::ADD
			, self::REMOVE
			, self::PREVIOUS
			, self::NEXT
			, self::CALENDAR
			, self::CLOSE
			, self::ATTACHMENT
			, self::CARET
			, self::DRAG
			, self::SEARCH
			, self::FILTER
			, self::INFO
			, self::ENVELOPE
			);
		return in_array($type, $types);
	}*/

	/**
	 * Get the type of the glyph.
	 *
	 * @return	string	
	 */
	public function getType();

	/**
	 * Get a glyph like this, but with a new type.
	 *
	 * @param	string	$type	One of the glyph types.
	 * @return	Glyph
	 */
	public function withType($type);

	/**
	 * Get all counters attached to this glyph.
	 *
	 * @return  Counter[]
	 */
	public function getCounters();

	/**
	 * Get a glyph like this, but with a counter on it.
	 *
	 * If there already is a counter of the given counter type, replace that
	 * counter by the new one.
	 * 
	 * @param   Counter $counter
	 * @return  Glyph
	 */
	public function withCounter(Counter $counter);
}
