<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Input\Container\Filter\Standard) {
			return $this->renderStandard($component, $default_renderer);
		}

		throw new \LogicException("Cannot render: " . get_class($component));
	}


	protected function renderStandard(Component\Input\Container\Filter\Standard $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.standard_filter.html", true, true);

		$f = $this->getUIFactory();
		$opener = [$f->glyph()->collapse(), $f->glyph()->expand()];
		//$apply_old = $f->glyph()->note("#");
		$apply = $f->button()->bulky($f->glyph()->note("#"), "Apply", "#");
		//reset_old = $f->glyph()->comment("#");
		$reset = $f->button()->bulky($f->glyph()->comment("#"), "Reset", "#");
		//Beim Aktivieren des Filters soll er ausgeklappt werden (nur Desktop, nicht Mobile)
		$toggle = $f->button()->toggle("", "#", "#");

		$tpl->setVariable("OPENER", $default_renderer->render($opener));
		$tpl->setVariable("APPLY", $default_renderer->render($apply));
		$tpl->setVariable("RESET", $default_renderer->render($reset));
		$tpl->setVariable("TOGGLE", $default_renderer->render($toggle));

		$renderer = $default_renderer->withAdditionalContext($component);
		$tpl->setVariable("INPUTS", $renderer->render($component->getInputGroup()));

		return $tpl->get();
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\Input\Container\Filter\Standard::class,
		);
	}
}
