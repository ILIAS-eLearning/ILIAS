<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\SimpleList;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\SimpleList
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var Component\Listing\SimpleList $component
		 */
		$this->checkComponent($component);

		$tpl = $this->getTemplate("tpl.simple_list.html", true, true);

		if(count($component->getItems())>0){
			$tpl->setVariable("TYPE",$component->getType());
			foreach($component->getItems() as $item){
				$tpl->setCurrentBlock("item");
				if(is_string($item)){
					$tpl->setVariable("ITEM", $item);
				}else{
					$tpl->setVariable("ITEM", $default_renderer->render($item));
				}
				$tpl->parseCurrentBlock();
			}
		}
		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return [Component\Listing\SimpleList::class];
	}
}
