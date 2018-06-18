<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\MessageBox
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer)
	{
		global $DIC;
		/**
		 * @var Component\MessageBox\MessageBox $component
		 */
		$this->checkComponent($component);
		$tpl = $this->getTemplate("tpl.messagebox.html", true, true);


		$tpl->setCurrentBlock("message_box");

		$tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());
		$tpl->setVariable("ACC_TEXT", $this->txt($component->getType() . "_message"));

		$buttons = $component->getButtons();
		if ($buttons) {
			$tpl->setVariable("BUTTONS", $DIC->ui()->renderer()->render($buttons));
		}

		$tpl->touchBlock($component->getType() . "_class");

		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	protected function getComponentInterfaceName() {
		return array(Component\MessageBox\MessageBox::class);
	}

}