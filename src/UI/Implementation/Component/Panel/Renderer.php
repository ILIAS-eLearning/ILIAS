<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var Component\Panel\Panel $component
		 */
		$this->checkComponent($component);

		if ($component instanceof Component\Panel\Standard) {
			/**
			 * @var Component\Panel\Standard $component
			 */
			return $this->render_standard($component, $default_renderer);
		} else if($component instanceof Component\Panel\Sub) {
			/**
			 * @var Component\Panel\Sub $component
			 */
			return $this->render_sub($component, $default_renderer);
		}
		/**
		 * @var Component\Panel\Report $component
		 */
		return $this->render_report($component, $default_renderer);
	}

	/**
	 * @param Component\Panel\Standard $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function render_standard(Component\Panel\Standard $component, RendererInterface $default_renderer)
	{
		$tpl = $this->getTemplate("tpl.panel.html", true, true);

		$content = "";

		$tpl->setVariable("TITLE",  $component->getTitle());

		foreach($component->getContent() as $item){
			$content .= $default_renderer->render($item);
		}
		$tpl->setVariable("BODY",  $content);

		return $tpl->get();
	}

	/**
	 * @param Component\Panel\Sub $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function render_sub(Component\Panel\Sub $component, RendererInterface $default_renderer)
	{
		$tpl = $this->getTemplate("tpl.sub.html", true, true);

		$content = "";

		$tpl->setVariable("TITLE",  $component->getTitle());

		foreach($component->getContent() as $item){
			$content .= $default_renderer->render($item);
		}

		if($component->getCard()){
			$tpl->setCurrentBlock("with_card");
			$tpl->setVariable("BODY",  $content);
			$tpl->setVariable("CARD",  $default_renderer->render($component->getCard()));
			$tpl->parseCurrentBlock();
		}else{
			$tpl->setCurrentBlock("no_card");
			$tpl->setVariable("BODY",  $content);
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @param Component\Panel\Report $component
	 * @param RendererInterface $default_renderer
	 * @return string
	 */
	protected function render_report(Component\Panel\Report $component, RendererInterface $default_renderer)
	{
		$tpl = $this->getTemplate("tpl.panel.html", true, true);

		$content = "";

		$tpl->setVariable("TITLE",  $component->getTitle());

		foreach($component->getSubPanels() as $sub){
			$content .= $default_renderer->render($sub);
		}
		$tpl->setVariable("BODY",  $content);

		return $tpl->get();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return [Component\Panel\Panel::class];
	}
}
