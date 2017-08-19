<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		$tpl = $this->getTemplate("tpl.glyph.html", true, true);

		$action = $component->getAction();
		if ($action !== null) {
			$tpl->setCurrentBlock("with_action");
			$tpl->setVariable("ACTION", $component->getAction());
			$tpl->parseCurrentBlock();
		}

		if ($component->isHighlighted()) {
			$tpl->touchBlock("highlighted");
		}

		$tpl->setVariable("LABEL", $this->txt($component->getAriaLabel()));

		$id = $this->bindJavaScript($component);

		$tpl->touchBlock($component->getType());

		if ($id !== null) {
			$tpl->setCurrentBlock("with_id");
			$tpl->setVariable("ID", $id);
			$tpl->parseCurrentBlock();
		}

		$largest_counter = 0;
		foreach ($component->getCounters() as $counter) {
			if($largest_counter < $counter->getNumber()){
				$largest_counter = $counter->getNumber();
			}
			$n = "counter_".$counter->getType();
			$tpl->setCurrentBlock($n);
			$tpl->setVariable(strtoupper($n), $default_renderer->render($counter));
			$tpl->parseCurrentBlock();
		}

		if($largest_counter){
			$tpl->setCurrentBlock("counter_spacer");
			$tpl->setVariable("COUNTER_SPACER",$largest_counter);
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array(Component\Glyph\Glyph::class);
	}
}
