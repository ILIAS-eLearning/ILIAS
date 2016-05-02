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
	 * @return	string
	 */
	public function toHTMLString();

	/**
	 * Get to know which JS-files are required to render the component.
	 *
	 * This is an escape hatch to the current ILIAS template logic for UIs.
	 *
	 * @return	string[]	Paths to required javascripts.
	 */
	public function getRequiredJavascript();
}