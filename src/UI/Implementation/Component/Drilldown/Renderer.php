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

			$component = $component->withAdditionalOnLoadCode(function ($id) {
				return "il.UI.drilldown.init('$id');";
			});

			$tpl->setVariable('ENTRIES', $default_renderer->render($component->getEntries()));

		} else if ($component instanceof Drilldown\Level) {
			$tpl_name = "tpl.entry.html";
			$tpl = $this->getTemplate($tpl_name, true, true);

			$component = $component->withAdditionalOnLoadCode(function ($id) {
				return "";
			});

			$icon = $component->getIconOrGlyph();
			if(!is_null($icon)) {
				$tpl->setVariable('ICON', $default_renderer->render($icon));
			}
			$tpl->setVariable('LABEL', $component->getLabel());

			$entries = $component->getEntries();
			if(count($entries) > 0) {
				foreach ($entries as $entry) {
					if($entry instanceof Drilldown\Level) {
						$tpl->setCurrentBlock('level_entry');
						$tpl->setVariable('LEVEL_ENTRY', $default_renderer->render($entry));
						$tpl->parseCurrentBlock();
					} else {
						$tpl->setCurrentBlock('btn_entry');
						$tpl->setVariable('BTN_ENTRY', $default_renderer->render($entry));
						$tpl->parseCurrentBlock();

					}
				}
			}
		}

		$id = $this->bindJavaScript($component);
		$tpl->setVariable("ID", $id);


		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/Drilldown/drilldown.js');
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