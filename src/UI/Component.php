<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * A component is the most general form of an entity in the UI. Every entity
 * is a component. 
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface Component {
	/**
	 * Render element to an HTML string.
	 *
	 * This is an escape hatch to the current ILIAS template logic for UIs.
	 *
	 * The uncommon underscore_case is a reminder for the fact, that
	 * to_html_string should vanish from our code by bubbling up in the call
	 * chain.
	 *
	 * TODO: Explain this...
	 * 
	 * @return	string
	 */
	public function to_html_string();

	/**
	 * Get to know which JS-files are required to render the component.
	 *
	 * This is an escape hatch to the current ILIAS template logic for UIs.
	 *
	 * The uncommon underscore_case is a reminder for the fact, that
	 * get_required_javascript should vanish from our code by bubbling up
	 * in the call chain.
	 *
	 * TODO: Explain this...
	 *
	 * @return	string[]	Paths to required javascripts.
	 */
	public function get_required_javascript();
}