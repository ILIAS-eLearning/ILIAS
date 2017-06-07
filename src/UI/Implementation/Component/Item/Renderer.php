<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Item\AppointmentItem)
		{
			return $this->renderAppointment($component, $default_renderer);
		}
	}

	protected function renderAppointment(Component\Item\AppointmentItem $component, RendererInterface $default_renderer) {

		$tpl = $this->getTemplate("tpl.item_standard.html", true, true);

		// description
		$desc = $component->getDescription();
		if (trim($desc) != "")
		{
			$tpl->setCurrentBlock("desc");
			$tpl->setVariable("DESC", $desc);
			$tpl->parseCurrentBlock();
		}

		// actions
		$actions = $component->getActions();
		if (count($actions) > 0)
		{
			foreach ($actions as $lab => $act)
			{
				$tpl->setCurrentBlock("action_item");
				$tpl->setVariable("ACTION_HREF", $act);
				$tpl->setVariable("ACTION_LABEL", $lab);
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("actions");
			$tpl->parseCurrentBlock();
		}

		// properties
		$props = $component->getProperties();
		if (count($props) > 0)
		{
			$cnt = 0;
			foreach ($props as $name => $value)
			{
				$cnt++;
				if ($cnt % 2 == 1)
				{
					$tpl->setCurrentBlock("property_row");
					$tpl->setVariable("PROP_NAME_A", $name);
					$tpl->setVariable("PROP_VAL_A", $value);
				}
				else
				{
					$tpl->setVariable("PROP_NAME_B", $name);
					$tpl->setVariable("PROP_VAL_B", $value);
					$tpl->parseCurrentBlock();
				}
			}
			if ($cnt % 2 == 1)
			{
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("properties");
			$tpl->parseCurrentBlock();
		}

		$title = $component->getTitle();

		$tpl->setVariable("TITLE", $title);

		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		(Component\Item\AppointmentItem::class
		, Component\Button\Standard::class
		);
	}
}
