<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Implementation\Render\ilTemplateWrapper as UITemplateWrapper;

class Renderer extends AbstractComponentRenderer {

	const BLOCK_MAINBAR_ENTRIES = 'trigger_item';
	const BLOCK_MAINBAR_TOOLS = 'tool_trigger_item';
	const BLOCK_METABAR_ENTRIES = 'meta_element';

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof MainBar) {
			return $this->renderMainbar($component, $default_renderer);
		}
		if ($component instanceof MetaBar) {
			return $this->renderMetabar($component, $default_renderer);
		}
	}

	protected function renderMainbar(MainBar $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.mainbar.html", true, true);

		$active =  $component->getActive();
		$tools = $component->getToolEntries();

		$entry_signal = $component->getEntryClickSignal();
		$tools_signal = $component->getToolsClickSignal();
		$close_slates_signal = $component->getDisengageAllSignal();
		$tool_removal_signal = $component->getToolsRemovalSignal();

		$f = $this->getUIFactory();
		$btn_disengage = $f->button()->bulky($f->glyph()->back("#"), "close", "#")
			->withOnClick($close_slates_signal);
		$tpl->setVariable("CLOSE_SLATES", $default_renderer->render($btn_disengage));

		$this->renderTriggerButtonsAndSlates(
			$tpl, $default_renderer, $entry_signal,
			static::BLOCK_MAINBAR_ENTRIES,
			$component->getEntries(),
			$active
		);

		if (count($tools) > 0) {
			$tools_active = array_key_exists($active, $tools);

			$btn_tools = $component->getToolsButton()
				->withOnClick($tools_signal)
				->withEngagedState(false); //if a tool-entry is active, onLoadCode will "click" the button

			$btn_removetool = $f->button()->close()
				->withOnClick($tool_removal_signal);

			$tpl->setCurrentBlock("tools_trigger");
			$tpl->setVariable("BUTTON", $default_renderer->render($btn_tools));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("tool_removal");
			$tpl->setVariable("REMOVE_TOOL", $default_renderer->render($btn_removetool));
			$tpl->parseCurrentBlock();

			$this->renderTriggerButtonsAndSlates(
				$tpl, $default_renderer, $entry_signal,
				static::BLOCK_MAINBAR_TOOLS,
				$tools,
				$active
			);
		}

		$component = $component->withOnLoadCode(
			function($id) use ($entry_signal, $close_slates_signal, $tools_signal, $tool_removal_signal) {
				return "
					il.UI.maincontrols.mainbar.registerSignals(
						'{$id}',
						'{$entry_signal}',
						'{$close_slates_signal}',
						'{$tools_signal}',
						'{$tool_removal_signal}'
					);
				";
			}
		);

		if($active) {
			$component = $component->withAdditionalOnLoadCode(
				function($id) {
					return "il.UI.maincontrols.mainbar.initActive('{$id}');";
				}
			);
		}

		$id = $this->bindJavaScript($component);
		$tpl->setVariable('ID', $id);

		return $tpl->get();
	}

	protected function renderTriggerButtonsAndSlates(
		UITemplateWrapper $tpl,
		RendererInterface $default_renderer,
		Signal $entry_signal,
		string $block,
		array $entries,
		string $active = null,
		bool $slate_is_contained_in_entry = false
	) {
		foreach ($entries as $id=>$entry) {

			$engaged = (string)$id === $active;

			if($entry instanceof Slate) {
				$f = $this->getUIFactory();
				$secondary_signal = $entry->getToggleSignal();
				if($block === static::BLOCK_MAINBAR_TOOLS) {
					$secondary_signal = $entry->getShowSignal();
				}
				$button = $f->button()->bulky($entry->getSymbol(), $entry->getName(), '#')
					->withOnClick($entry_signal)
					->appendOnClick($secondary_signal)
					->withEngagedState($engaged);

				$slate = $entry;
				$slate = $slate->withEngaged(false); //init disengaged, onLoadCode will "click" the button

			} else {
				$button = $entry;
				$slate = null;
			}

			$tpl->setCurrentBlock($block);
			$tpl->setVariable("BUTTON", $default_renderer->render($button));
			if($slate && $slate_is_contained_in_entry) {
				$tpl->setVariable("SLATE", $default_renderer->render($slate));
			}
			$tpl->parseCurrentBlock();

			if($slate && $slate_is_contained_in_entry === false) {
				$tpl->setCurrentBlock("slate_item");
				$tpl->setVariable("SLATE", $default_renderer->render($slate));
				$tpl->parseCurrentBlock();
			}
		}
	}

	protected function renderMetabar(MetaBar $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.metabar.html", true, true);

		$entry_signal = $component->getEntryClickSignal();
		$active ='';
		$this->renderTriggerButtonsAndSlates(
			$tpl, $default_renderer, $entry_signal,
			static::BLOCK_METABAR_ENTRIES,
			$component->getEntries(),
			$active,
			true
		);

		$component = $component->withOnLoadCode(
			function($id) use ($entry_signal) {
				return "
					il.UI.maincontrols.metabar.registerSignals(
						'{$id}',
						'{$entry_signal}'
					);
				";
			}
		);
		$id = $this->bindJavaScript($component);
		$tpl->setVariable('ID', $id);
		return $tpl->get();
	}

	/**
	 * @inheritdoc
	 */
	public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/MainControls/mainbar.js');
		$registry->register('./src/UI/templates/js/MainControls/metabar.js');
	}

	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			MetaBar::class,
			MainBar::class
		);
	}

}
