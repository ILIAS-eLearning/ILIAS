<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Input\Text) {
			return $this->renderText($component, $default_renderer);
		}

		throw new \LogicException("Cannot render '".get_class($component)."'");
	}

	protected function renderText(Component\Input\Text $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.text.html", true, true);
		$tpl->setVariable("NAME", $component->getName());
		$tpl->setVariable("LABEL", $component->getLabel());

		if ($component->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $component->getValue());
			$tpl->parseCurrentBlock();
		}

		if ($component->getByline() !== null) {
			$tpl->setCurrentBlock("byline");
			$tpl->setVariable("BYLINE", $component->getByline());
			$tpl->parseCurrentBlock();
		}

		if ($component->getError() !== null) {
			$tpl->setCurrentBlock("error");
			$tpl->setVariable("ERROR", $component->getError());
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		( Component\Input\Text::class
		);
	}
}
