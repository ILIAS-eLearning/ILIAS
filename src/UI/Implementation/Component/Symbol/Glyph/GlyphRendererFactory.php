<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;
use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;

class GlyphRendererFactory extends Render\DefaultRendererFactory {
	const USE_BUTTON_CONTEXT_FOR = [
		'BulkyButton',
		'BulkyLink'
	];

	public function getRendererInContext(Component\Component $component, array $contexts) {
		if( count(array_intersect(self::USE_BUTTON_CONTEXT_FOR, $contexts)) > 0) {
			return new ButtonContextRenderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
		}
		return new Renderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
	}
}
