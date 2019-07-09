<?php

declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer)
	{
		$this->checkComponent($component);

		if ($component instanceof Component\Link\Standard) {
			return $this->renderStandard($component, $default_renderer);
		}
		if ($component instanceof Component\Link\Bulky) {
			return $this->renderBulky($component, $default_renderer);
		}
	}

	protected function setStandardVars(
		string $tpl_name,
		Component\Link\Link $component
	): Template {
		$tpl = $this->getTemplate($tpl_name, true, true);
		$action = $component->getAction();
		$label = $component->getLabel();
		if ($component->getOpenInNewViewport())
		{
			$tpl->touchBlock("open_in_new_viewport");
		}
		$tpl->setVariable("LABEL", $label);
		$tpl->setVariable("HREF", $action);
		return $tpl;
	}

	protected function renderStandard(
		Component\Link\Standard $component,
		RendererInterface $default_renderer
	): string {
		$tpl_name = "tpl.standard.html";
		$tpl = $this->setStandardVars($tpl_name, $component);
		return $tpl->get();
	}

	protected function renderBulky(
		Component\Link\Bulky $component,
		RendererInterface $default_renderer
	): string {
		$tpl_name = "tpl.bulky.html";
		$tpl = $this->setStandardVars($tpl_name, $component);
		$tpl->setVariable("SYMBOL", $default_renderer->render($component->getSymbol()));
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return [
			Component\Link\Standard::class,
			Component\Link\Bulky::class
		];
	}
}
