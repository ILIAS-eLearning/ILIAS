<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;

use ILIAS\UI\Implementation\AbstractRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component;

class Renderer extends AbstractRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component $component, RendererInterface $default_renderer) {
		if (!($component instanceof C\Glyph)) {
			throw new \LogicException(
				"Expected Glyph, found '".get_class($component)."' when rendering.");
		}

		$css_class = self::getCssClassFor($component->getType());
		return '<span class="glyphicon '.$css_class.'"></span>';
	}

	/**
	 * Get the css class used for a certain glyph type.
	 *
 	 * @param	mixed	$type
	 * @return	string
	 */
	static public function getCssClassFor($type) {
		switch($type) {
			case C\Glyph::UP:			return "glyphicon-chevron-up";
			case C\Glyph::DOWN:			return "glyphicon-chevron-down";
			case C\Glyph::ADD:			return "glyphicon-plus";
			case C\Glyph::REMOVE:		return "glyphicon-minus";
			case C\Glyph::PREVIOUS:		return "glyphicon-chevron-left";
			case C\Glyph::NEXT:			return "glyphicon-chevron-right";
			case C\Glyph::CALENDAR:		return "glyphicon-calendar";
			case C\Glyph::CLOSE:		return "glyphicon-remove";
			case C\Glyph::ATTACHMENT:	return "glyphicon-paperclip";
			// TODO: Don't know what this is for. SearchGUI uses it.
			case C\Glyph::CARET:		return "glyphicon-caret";
			case C\Glyph::DRAG:			return "glyphicon-share-alt";
			case C\Glyph::SEARCH:		return "glyphicon-search";
			case C\Glyph::FILTER:		return "glyphicon-filter";
			case C\Glyph::INFO:			return "glyphicon-info-sign";
			case C\Glyph::ENVELOPE:		return "glyphicon-envelope";
		}
		throw new \LogicException("Unknown glyph-type '$type'");
	}
}
