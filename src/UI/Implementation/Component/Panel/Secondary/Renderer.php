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

		$sortation = $component->getSortation();
		if($sortation) {
			$tpl->setVariable("SORTATION", $default_renderer->render($sortation));
		}

		$pagination = $component->getPagination();
		if($pagination) {
			$tpl->setVariable("PAGINATION", $default_renderer->render($pagination));
		}

		$section = $component->getSection();
		if($section) {
			$tpl->setVariable("SECTION", $default_renderer->render($section));
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

		$sortation = $component->getSortation();
		if($sortation) {
			$tpl->setVariable("SORTATION", $default_renderer->render($sortation));
		}

		$pagination = $component->getPagination();
		if($pagination) {
			$tpl->setVariable("PAGINATION", $default_renderer->render($pagination));
		}

		$section = $component->getSection();
		if($section) {
			$tpl->setVariable("SECTION", $default_renderer->render($section));
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
