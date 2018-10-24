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
		$opener = [$f->glyph()->collapse($component->getCollapseAction()), $f->glyph()->expand($component->getExpandAction())];
		$apply = $f->button()->bulky($f->glyph()->note(), "Apply", $component->getApplyAction()); //replace with Apply Glyph and use language variable
		$reset = $f->button()->bulky($f->glyph()->comment(), "Reset", $component->getResetAction()); //replace with Reset Glyph and use language variable
		//todo: Expand Filter when acitvated (only desktop, not mobile)
		$toggle = $f->button()->toggle("", $component->getToggleOnAction(), $component->getToggleOffAction(), $component->isActivated());

		$tpl->setVariable("DESCRIPTION", "Filter"); //replace with language variable
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
