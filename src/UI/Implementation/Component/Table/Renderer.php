<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {

		$this->checkComponent($component);

		if ($component instanceof Component\Table\Presentation) {
				return $this->renderPresentationTable($component, $default_renderer);
		}

		if ($component instanceof Component\Table\PresentationRow) {
				return $this->renderPresentationRow($component, $default_renderer);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\Table\PresentationRow::class,
			Component\Table\Presentation::class
		);
	}

	/**
	 * @param Component\Table\Presentation $component
	 * @param RendererInterface $default_renderer
	 * @return mixed
	 */
	protected function renderPresentationTable(Component\Table\Presentation $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.presentationtable.html", true, true);

		foreach ($component->getRows() as $row) {
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROW", $default_renderer->render($row));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @param Component\Table\Presentation $component
	 * @param RendererInterface $default_renderer
	 * @return mixed
	 */
	protected function renderPresentationRow(Component\Table\PresentationRow $component, RendererInterface $default_renderer) {
		$data = $component->getData();

		$tpl = $this->getTemplate("tpl.presentationrow.html", true, true);
		$tpl->setVariable("TITLE", $data[$component->getTitleField()]);
		$tpl->setVariable("SUBTITLE", $data[$component->getSubtitleField()]);


		foreach ($component->getButtons() as $button) {
			$tpl->setCurrentBlock("button");
			$tpl->setVariable("BUTTON", $default_renderer->render($button));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

}
