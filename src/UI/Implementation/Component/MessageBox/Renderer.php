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

		if ($component->getType() == "failure") {
			$tpl->setCurrentBlock("failure_message");
			$tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());

			$buttons = $component->getButtons();
			if ($buttons) {
				$tpl->setVariable("BUTTONS", $DIC->ui()->renderer()->render($buttons));
			}

			$tpl->parseCurrentBlock();
		}
		if ($component->getType() == "success") {
			$tpl->setCurrentBlock("success_message");
			$tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());

			$buttons = $component->getButtons();
			if ($buttons) {
				$tpl->setVariable("BUTTONS", $DIC->ui()->renderer()->render($buttons));
			}

			$tpl->parseCurrentBlock();
		}
		if ($component->getType() == "info") {
			$tpl->setCurrentBlock("info_message");
			$tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());

			$buttons = $component->getButtons();
			if ($buttons) {
				$tpl->setVariable("BUTTONS", $DIC->ui()->renderer()->render($buttons));
			}

			$tpl->parseCurrentBlock();
		}
		if ($component->getType() == "confirmation") {
			$tpl->setCurrentBlock("confirmation_message");
			$tpl->setVariable("MESSAGE_TEXT", $component->getMessageText());

			$buttons = $component->getButtons();
			if ($buttons) {
				$tpl->setVariable("BUTTONS", $DIC->ui()->renderer()->render($buttons));
			}

			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	protected function getComponentInterfaceName() {
		return array(Component\MessageBox\MessageBox::class);
	}

}