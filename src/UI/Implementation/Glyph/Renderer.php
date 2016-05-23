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

		$tpl = $this->getTemplate("tpl.glyph.html", true, true);
		$tpl->touchBlock($component->getType());

		foreach ($component->getCounters() as $counter) {
			$tpl->setCurrentBlock("counter_".$counter->getType());
			$tpl->setVariable("NUMBER", $counter->getNumber());
			$tpl->parseCurrentBlock();
		}

		return trim(str_replace("\n", "", $tpl->get()));
	}
}
