<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */


namespace ILIAS\UI\Implementation\Component\Listing\DescriptiveList;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\DescriptiveList
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var Component\Listing\DescriptiveList $component
		 */
		$this->checkComponent($component);

		$tpl = $this->getTemplate("tpl.descriptive_list.html", true, true);

		foreach($component->getItems() as $key => $item){
			if(is_string($item)){
                $content = $item;
            }else{
                $content = $default_renderer->render($item);
            }

			if(trim($content) != ""){
				$tpl->setCurrentBlock("item");
				$tpl->setVariable("DESCRIPTION",$key);
				$tpl->setVariable("CONTENT",$content);
				$tpl->parseCurrentBlock();
			}
		}

		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return [Component\Listing\DescriptiveList::class];
	}
}
