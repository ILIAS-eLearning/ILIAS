<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Panel\Listing\Standard)
		{
			return $this->renderStandard($component, $default_renderer);
		}
	}

	protected function renderStandard(Component\Panel\Listing\Listing $component, RendererInterface $default_renderer) {
		global $DIC;


		$tpl = $this->getTemplate("tpl.listing_standard.html", true, true);

		$renderer = $DIC->ui()->renderer();

		$first = true;
		foreach ($component->getItems() as $item)
		{
			if ($item instanceof \ILIAS\UI\Component\Item\StandardItem)
			{
				$tpl->setCurrentBlock("item");
				$tpl->setVariable("ITEM", $renderer->render($item));
				$tpl->parseCurrentBlock();
			}

			if ($item instanceof Divider)
			{
				if (!$first)
				{
					$tpl->touchBlock("list_group");
				}
				$tpl->setCurrentBlock("divider");
				$tpl->setVariable("DIVIDER_LABEL", $item->getLabel());
				$tpl->parseCurrentBlock();
			}

			$first = false;
		}
		$tpl->touchBlock("list_group");

		$title = $component->getTitle();
		$tpl->setVariable("LIST_TITLE", $title);

		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		(Component\Panel\Listing\Standard::class
		);
	}
}
