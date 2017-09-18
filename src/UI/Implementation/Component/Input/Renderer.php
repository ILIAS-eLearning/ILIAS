<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		$input_tpl = null;
		if ($component instanceof Component\Input\Text) {
			$input_tpl = $this->getTemplate("tpl.text.html", true, true);
		}else if($component instanceof Component\Input\Numeric){
			$input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
		} else{
			throw new \LogicException("Cannot render '".get_class($component)."'");
		}

		//TODO: How to solve this, Inputs will have a different frame depending on the
		// context...
		return $this->renderContext(
				$this->renderInput($input_tpl,$component, $default_renderer),
				$component,
				$default_renderer);
	}

	/**
	 * @param $input_html
	 * @param Component\Input\Input $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function renderContext($input_html, Component\Input\Input $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.context-form.html", true, true);
		$tpl->setVariable("NAME", $component->getName());
		$tpl->setVariable("LABEL", $component->getLabel());
		$tpl->setVariable("INPUT", $input_html);

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
	 * @param Template $tpl
	 * @param Component\Input\Input $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function renderInput(Template $tpl, Component\Input\Input $component, RendererInterface $default_renderer) {
		$tpl->setVariable("NAME", $component->getName());

		if ($component->getValue() !== null) {
			$tpl->setCurrentBlock("value");
			$tpl->setVariable("VALUE", $component->getValue());
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		( Component\Input\Text::class,Component\Input\Numeric::class
		);
	}
}
