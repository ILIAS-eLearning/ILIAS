<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var Component\Image\Image $component
		 */
		$this->checkComponent($component);
		$tpl = $this->getTemplate("tpl.image.html", true, true);

		$tpl->setCurrentBlock($component->getType());
		$tpl->setVariable("SOURCE",$component->getSource());
		$tpl->setVariable("ALT",$component->getAlt());
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return [Component\Image\Image::class];
	}
}
