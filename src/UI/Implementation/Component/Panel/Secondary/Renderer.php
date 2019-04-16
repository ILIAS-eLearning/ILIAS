<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;

class Renderer extends AbstractComponentRenderer
{
	/**
	 * @inheritdoc
	 */
	public function render(C\Component $component, RendererInterface $default_renderer)
	{
		$this->checkComponent($component);

		if ($component instanceof C\Panel\Secondary\Listing)
		{
			return $this->renderListing($component, $default_renderer);
		}
		else if ($component instanceof C\Panel\Secondary\Legacy)
		{
			return $this->renderLegacy($component, $default_renderer);
		}

	}

	protected function renderListing(C\Panel\Secondary\Listing $component, RendererInterface $default_renderer)
	{
		$tpl = $this->getTemplate("tpl.secondary.html", true, true);

		$tpl->setVariable("TITLE", $component->getTitle());

		$actions = $component->getActions();
		if ($actions) {
			$tpl->setVariable("ACTIONS", $default_renderer->render($actions));
		}

		$view_controls = $component->getViewControls();

		if($view_controls) {
			foreach ($view_controls as $view_control) {
				$tpl->setCurrentBlock("view_controls");
				$tpl->setVariable("VIEW_CONTROL", $default_renderer->render($view_control));
				$tpl->parseCurrentBlock();
			}
		}

		foreach ($component->getItemGroups() as $group)
		{
			if ($group instanceof C\Item\Group)
			{
				$tpl->setCurrentBlock("group");
				$tpl->setVariable("ITEM_GROUP", $default_renderer->render($group));
				$tpl->parseCurrentBlock();
			}
		}

		return $tpl->get();
	}

	protected function renderLegacy(C\Panel\Secondary\Legacy $component, RendererInterface $default_renderer)
	{
		$tpl = $this->getTemplate("tpl.secondary.html", true, true);

		$tpl->setVariable("TITLE", $component->getTitle());

		$actions = $component->getActions();
		if ($actions) {
			$tpl->setVariable("ACTIONS", $default_renderer->render($actions));
		}

		$view_controls = $component->getViewControls();

		if($view_controls) {
			foreach ($view_controls as $view_control) {
				$tpl->setCurrentBlock("view_controls");
				$tpl->setVariable("VIEW_CONTROL", $default_renderer->render($view_control));
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setVariable("BODY_LEGACY", $default_renderer->render($component->getLegacyComponent()));

		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() : array
	{
		return array (C\Panel\Secondary\Listing::class, C\Panel\Secondary\Secondary::class);
	}
}
