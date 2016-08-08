<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);
		$tpl = $this->getTemplate("tpl.card.html", true, true);

		if($component->getImage()){
			$tpl->setVariable("IMAGE",$default_renderer->render($component->getImage(),$default_renderer));
		}

		$tpl->setVariable("TITLE",$component->getTitle());

		if(is_array($component->getSections())){
			foreach($component->getSections() as $section){
				$tpl->setCurrentBlock("section");
				$tpl->setVariable("SECTION",$default_renderer->render($section,$default_renderer));
				$tpl->parseCurrentBlock();
			}
		}
		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array(Component\Card\Card::class);
	}
}
