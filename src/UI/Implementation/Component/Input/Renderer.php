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
		$id = $this->bindJavaScript($component);

		$tpl = $this->getTemplate("tpl.text.html", true, true);
		$tpl->setVariable("ID", $id);
		$tpl->setVariable("LABEL", $component->getLabel());

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
