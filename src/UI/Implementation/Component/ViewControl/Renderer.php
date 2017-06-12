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

	const MODE_ARIA = "Mode View Controler";
	const MODE_ROLE = "group";

	/**
	 * @param Component\Component $component
	 * @param RendererInterface $default_renderer
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer)
	{

		$this->checkComponent($component);

		if ($component instanceof Component\ViewControl\Mode) {
			return $this->renderMode($component, $default_renderer);
		} else {
			//todo
		}

	}

	protected function renderMode(Component\ViewControl\Mode $component, RendererInterface $default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl = $this->getTemplate("tpl.mode.html", true, true);

		$active = $component->getActive();
		if($active == "") {
			$activate_first_item = true;
		}

		foreach ($component->getLabelledActions() as $label => $action)
		{
			$tpl->setVariable("ARIA", self::MODE_ARIA);
			$tpl->setVariable("ROLE", self::MODE_ROLE);

			$tpl->setCurrentBlock("view_control");

			//withCurrent vs withUnavailableAction
			if($activate_first_item) {
				$tpl->setVariable("BUTTON", $default_renderer->render($f->button()->standard($label, $action)->WithUnavailableAction()));
				$activate_first_item = false;
			} else if($active == $label) {
				$tpl->setVariable("BUTTON", $default_renderer->render($f->button()->standard($label, $action)->withUnavailableAction()));
			}
			else {
				$tpl->setVariable("BUTTON", $default_renderer->render($f->button()->standard($label, $action)));
			}
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return [Component\ViewControl\Mode::class];
	}

}