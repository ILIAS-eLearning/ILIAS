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
	 *
	 */
	protected function renderPresentationTable(Component\Table\Presentation $component, RendererInterface $default_renderer) {

		return 'table';
	}

	protected function renderPresentationRow(Component\Table\PresentationRow $component, RendererInterface $default_renderer) {
		return 'row';

	}

}
