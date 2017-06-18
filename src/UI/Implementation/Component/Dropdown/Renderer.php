<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		return $this->renderDropdown($component, $default_renderer);
	}

	protected function renderDropdown(Component\Dropdown\Dropdown $component, RendererInterface $default_renderer) {

		// get template
		$tpl_name = "tpl.standard.html";
		$tpl = $this->getTemplate($tpl_name, true, true);

		// render items
		$items = $component->getItems();
		if (count($items) == 0) {
			return "";
		}
		$this->renderItems($items, $tpl);

		// render trigger button
		$label = $component->getLabel();
		if ($label !== null) {
			$tpl->setVariable("LABEL", $component->getLabel());
		}

		$this->maybeRenderId($component, $tpl, "with_id", "ID");
		return $tpl->get();
	}

	/**
	 * @param array $items
	 * @param ilTemplate $tpl
	 */
	protected function renderItems($items, $tpl)
	{
		/* needs rewrite
		foreach ($items as $item)
		{
			$this->maybeRenderId($item, $tpl, "with_item_id", "ITEM_ID");

			$label = $item->getLabel();
			$action = $item->getAction();

			$tpl->setCurrentBlock("item");
			$tpl->setVariable("ACTION", $action);
			$tpl->setVariable("ITEM_HREF", $action);

			if ($label !== null) {
				$tpl->setVariable("ITEM_LABEL", $label);
			}
			$tpl->parseCurrentBlock();
		}
		*/
	}


	protected function maybeRenderId(Component\Component $component, $tpl, $block, $template_var) {
		$id = $this->bindJavaScript($component);
		// Check if the component is acting as triggerer
		if ($component instanceof Component\Triggerer && count($component->getTriggeredSignals())) {
			$id = ($id === null) ? $this->createId() : $id;
			$this->triggerRegisteredSignals($component, $id);
		}
		if ($id !== null) {
			$tpl->setCurrentBlock($block);
			$tpl->setVariable($template_var, $id);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		(Component\Dropdown\Standard::class
		);
	}
}
