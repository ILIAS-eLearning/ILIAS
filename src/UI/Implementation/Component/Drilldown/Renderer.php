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

		if ($component instanceof Drilldown\Menu) {
			$tpl_name = "tpl.drilldown.html";
			$tpl = $this->getTemplate($tpl_name, true, true);

			$component = $component->withAdditionalOnLoadCode(function ($id) {
				return "il.UI.drilldown.init('$id');";
			});

			$tpl->setVariable('ENTRIES', $default_renderer->render($component->getEntries()));

		} else if ($component instanceof Drilldown\Submenu) {
			$tpl_name = "tpl.entry.html";
			$tpl = $this->getTemplate($tpl_name, true, true);

			if($component->isInitiallyActive()) {
				$component = $component->withAdditionalOnLoadCode(function ($id) {
					return "$(document).ready(function(){ il.UI.drilldown.setActiveById('{$id}');});";
				});

			} else {
				$component = $component->withAdditionalOnLoadCode(function ($id) {
					return '';
				});
			}

			$icon = $component->getIconOrGlyph();
			$label = $component->getLabel();
			$button_factory = $this->getUIFactory()->button();
			if(is_null($icon)) {
				$entry_button = $button_factory->shy($label, '');
			} else {
				$entry_button = $button_factory->bulky($icon, $label, '');
			}

			$tpl->setVariable('ENTRY', $default_renderer->render($entry_button));

			$entries = $component->getEntries();
			if(count($entries) > 0) {
				foreach ($entries as $entry) {
					if($entry instanceof Drilldown\Level) {
						$entry_html = $default_renderer->render($entry);
					} else {
						$temp_tpl = $this->getTemplate('tpl.entry_wrapper.html', true, true);
						$temp_tpl->setVariable('ENTRY', $default_renderer->render($entry));
						$entry_html = $temp_tpl->get();
					}

					$tpl->setCurrentBlock('subentry');
					$tpl->setVariable('SUBENTRY', $entry_html);
					$tpl->parseCurrentBlock();
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
			Drilldown\Menu::class,
			Drilldown\Submenu::class
		);
	}
}