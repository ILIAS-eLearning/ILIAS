<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Drilldown;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Drilldown;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Drilldown\Drilldown) {
			$tpl_name = "tpl.drilldown.html";
			$tpl = $this->getTemplate($tpl_name, true, true);

		} else
		if ($component instanceof Drilldown\Level) {
			$tpl_name = "tpl.entry.html";
			$tpl = $this->getTemplate($tpl_name, true, true);

			$tpl->setVariable('ENTRY', $component->getLabel());
		}

		if(count($component->getEntries()) > 0) {
			$tpl->setVariable('ENTRIES', $default_renderer->render($component->getEntries()));
		}

		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Drilldown\Drilldown::class,
			Drilldown\Level::class
		);
	}
}