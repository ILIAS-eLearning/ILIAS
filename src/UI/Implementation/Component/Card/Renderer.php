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
		/**
		 * @var Component\Card\Card $component
		 */
		$this->checkComponent($component);
		$tpl = $this->getTemplate("tpl.card.html", true, true);

		if($component->getImage()){
			//$tpl->setVariable("IMAGE",$default_renderer->render($component->getImage(),$default_renderer));
		}

		if($component->isHighlighted()) {
			$tpl->touchBlock("highlight");
		} else {
			$tpl->touchBlock("no_highlight");
		}

		if($component->getTitleAction()) {
			$tpl->setCurrentBlock("title_action_begin");
			$tpl->setVariable("HREF",$component->getTitleAction());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("TITLE",$component->getTitle());

		if($component->getTitleAction()) {
			$tpl->touchBlock("title_action_end");
		}

		if(is_array($component->getSections())){
			foreach($component->getSections() as $section){
				$tpl->setCurrentBlock("section");
				$tpl->setVariable("SECTION",$default_renderer->render($section,$default_renderer));
				$tpl->parseCurrentBlock();
			}
		}

		if($component instanceof Component\Card\RepositoryObject)
		{
			if($component->getObjectIcon());
			{
				$tpl->setCurrentBlock("action");
				$tpl->setVariable("OBJECT_ICON",$default_renderer->render($component->getObjectIcon(),$default_renderer));
				$tpl->parseCurrentBlock();
			}
			if($component->getProgressMeter())
			{
				$tpl->setCurrentBlock("progress");
				$tpl->setVariable("PROGRESS_ICON",$default_renderer->render($component->getProgress()));
				$tpl->parseCurrentBlock();
			}
			if($component->getCertificate())
			{
				$tpl->setCurrentBlock("progress");
				$tpl->setVariable("PROGRESS_ICON",$default_renderer->render($component->getCertificate()));
				$tpl->parseCurrentBlock();
			}
			if($component->getActions())
			{
				$tpl->setCurrentBlock("actions");
				$tpl->setVariable("DROPDOWN", $default_renderer->render($component->getActions()));
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
