<?php
/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\ViewControl
 */
class Renderer extends AbstractComponentRenderer
{

	/**
	 * @param Component\Component $component
	 * @param RendererInterface $default_renderer
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer)
	{
		$this->checkComponent($component);

		if ($component instanceof Component\ViewControl\Mode) {
			return $this->renderMode($component, $default_renderer);
		}
		return $this->renderSection($component, $default_renderer);

	}

	protected function renderMode(Component\ViewControl\Mode $component, RendererInterface $default_renderer)
	{
		$tpl = $this->getTemplate("tpl.mode.html", true, true);

		$active = $component->getActive();
		if($active == "") {
			$activate_first_item = true;
		}

		foreach ($component->getLabelledActions() as $label => $action)
		{
			$tpl->setCurrentBlock("view_control");

			$tpl->setVariable("LABEL", $label);
			$tpl->setVariable("HREF", $action);
			if($activate_first_item) {
				$tpl->setVariable("ACTIVE", "active");
				$activate_first_item = false;
			} else if($active == $label) {
				$tpl->setVariable("ACTIVE", "active");
			}
			else {
				$tpl->setVariable("ACTIVE", "");
			}

			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	protected function renderSection(Component\ViewControl\Section $component, RendererInterface $default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl = $this->getTemplate("tpl.section.html", true, true);
		
		$tpl->setVariable("PREVIOUS", $default_renderer->render($f->glyph()->back($component->getPreviousActions()->getAction())));
		$tpl->setVariable("BUTTON", $default_renderer->render($component->getSelectorButton()));
		$tpl->setVariable("NEXT", $default_renderer->render($f->glyph()->next($component->getNextActions()->getAction())));

		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\ViewControl\Mode::class,
			Component\ViewControl\Section::class
		);
	}

}