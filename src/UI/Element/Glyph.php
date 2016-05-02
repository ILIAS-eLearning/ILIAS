<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Element;

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
	const CARRET = "carret";
	const DRAG = "drag";
	const SEARCH = "search";
	const FILTER = "filter";
	const INFO = "info";
	const ENVELOPE = "envelope";

	/**
	 * Get the type of the glyph.
	 *
	 * @return	string	
	 */
	public function type();

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
	public function counters();

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