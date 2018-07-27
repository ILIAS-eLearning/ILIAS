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
		$tpl = $this->getTemplate("tpl.standard.html", true, true);

		$tpl->setVariable("URL", $component->getPostURL());

		$f = $this->getUIFactory();
		$submit_button = $f->button()->standard($this->txt("save"), "#");

		$tpl->setVariable("BUTTONS_TOP", $default_renderer->render($submit_button));
		$tpl->setVariable("BUTTONS_BOTTOM", $default_renderer->render($submit_button));

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
