<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Button\Close) {
			return $this->render_close();
		}
		else {
			return $this->render_button($component, $default_renderer);
		}
	}

	protected function render_button(Component\Component $component, RendererInterface $default_renderer) {
		if ($component instanceof Component\Button\Primary) {
			$tpl_name = "tpl.primary.html";
		}
		if ($component instanceof Component\Button\Standard) {
			$tpl_name = "tpl.standard.html";
		}
		
		$tpl = $this->getTemplate($tpl_name, true, true);
		$tpl->setVariable("ACTION", $component->getAction());
		$label = $component->getLabel();
		if ($label !== null) {
			$tpl->setVariable("LABEL", $component->getLabel());
		}
		$glyph = $component->getGlyph();
		if ($glyph !== null) {
			$tpl->setVariable("GLYPH", $default_renderer->render($glyph));
		}

		if (!$component->isActive()) {
			$tpl->setVariable("DISABLED", "disabled");
		}

		return $tpl->get();
	}

	protected function render_close() {
		$tpl = $this->getTemplate("tpl.close.html", false, false);
		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array
			( Component\Button\Primary::class
			, Component\Button\Standard::class
			, Component\Button\Close::class
			);
	}
}
