<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Input\Container\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;

class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Filter\Standard) {
			return $this->renderStandard($component, $default_renderer);
		}

		throw new \LogicException("Cannot render: " . get_class($component));
	}

	/**
	 * Render standard filter
	 *
	 * @param Filter\Standard $component
	 * @param RendererInterface $default_renderer
	 * @return Template string
	 */
	protected function renderStandard(Filter\Standard $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.standard_filter.html", true, true);

		// JavaScript
		$component = $this->registerSignals($component);
		$id = $this->bindJavaScript($component);
		$tpl->setVariable('ID_FILTER', $id);

		// render expand and collapse
		$this->renderExpandAndCollapse($tpl, $component, $default_renderer);

		// render apply and reset buttons
		$this->renderApplyAndReset($tpl, $component, $default_renderer);

		// render toggle button
		$this->renderToggleButton($tpl, $component, $default_renderer);

		// render inputs
		$this->renderInputs($tpl, $component, $default_renderer);

		return $tpl->get();
	}

	/**
	 * @param Filter\Filter $filter
	 * @return Component\JavaScriptBindable
	 */
	protected function registerSignals(Filter\Filter $filter) {
		$update = $filter->getUpdateSignal();
		return $filter->withAdditionalOnLoadCode(function($id) use ($update) {
			$code =
				"$(document).on('{$update}', function(event, signalData) { il.UI.filter.onInputUpdate(event, signalData, '{$id}'); return false; });";
			return $code;
		});
	}

	/**
	 * Render expand/collapse section
	 *
	 * @param Template $tpl
	 * @param Filter\Standard $component
	 * @param RendererInterface $default_renderer
	 */
	protected function renderExpandAndCollapse(Template $tpl, Filter\Standard $component, RendererInterface $default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl->setCurrentBlock("action");
		$tpl->setVariable("ACTION_NAME", "expand");
		$tpl->setVariable("ACTION", $component->getExpandAction());
		$tpl->parseCurrentBlock();

		$opener_expand = $f->button()->bulky($f->symbol()->glyph()->expand(), $this->txt("filter"), "")
			->withAdditionalOnLoadCode(function ($id) {
				$code = "$('#$id').on('click', function(event) {
					il.UI.filter.onAjaxCmd(event, '$id', 'expand');
					event.preventDefault();
			});";
				return $code;
			});

		$tpl->setCurrentBlock("action");
		$tpl->setVariable("ACTION_NAME", "collapse");
		$tpl->setVariable("ACTION", $component->getCollapseAction());
		$tpl->parseCurrentBlock();

		$opener_collapse = $f->button()->bulky($f->symbol()->glyph()->collapse(), $this->txt("filter"), "")
			->withAdditionalOnLoadCode(function ($id) {
				$code = "$('#$id').on('click', function(event) {
					il.UI.filter.onAjaxCmd(event, '$id', 'collapse');
					event.preventDefault();
			});";
				return $code;
			});

		if ($component->isExpanded() == false) {
			$opener = [$opener_collapse, $opener_expand];
			$tpl->setVariable("OPENER", $default_renderer->render($opener));
			$tpl->setVariable("ARIA_EXPANDED", "'false'");
			$tpl->setVariable("INPUTS_ACTIVE_EXPANDED", "in");
		}
		else {
			$opener = [$opener_expand, $opener_collapse];
			$tpl->setVariable("OPENER", $default_renderer->render($opener));
			$tpl->setVariable("ARIA_EXPANDED", "'true'");
			$tpl->setVariable("INPUTS_EXPANDED", "in");
		}
	}

	/**
	 * Render apply and reset
	 *
	 * @param Template $tpl
	 * @param Filter\Standard $component
	 * @param RendererInterface $default_renderer
	 */
	protected function renderApplyAndReset(Template $tpl, Filter\Standard $component, RendererInterface$default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl->setCurrentBlock("action");
		$tpl->setVariable("ACTION_NAME", "apply");
		$tpl->setVariable("ACTION", $component->getApplyAction());
		$tpl->parseCurrentBlock();

		// render apply and reset buttons
		$apply = $f->button()->bulky($f->symbol()->glyph()->apply(), $this->txt("apply"), "");

		if (!$component->isActivated()) {
			$apply = $apply->withUnavailableAction();
			$reset = $f->button()->bulky($f->symbol()->glyph()->reset(), $this->txt("reset"), "")
			->withUnavailableAction();
		} else {

			$apply = $apply->withOnLoadCode(function ($id) {
				$code = "$('#$id').on('click', function(event) {
							il.UI.filter.onCmd(event, '$id', 'apply');
							return false; // stop event propagation
					});";
				return $code;
			});

			$reset = $f->button()->bulky($f->symbol()->glyph()->reset(), $this->txt("reset"), $component->getResetAction());
		}
		$tpl->setVariable("APPLY", $default_renderer->render($apply));
		$tpl->setVariable("RESET", $default_renderer->render($reset));
	}

	/**
	 * Render toggle button
	 *
	 * @param Template $tpl
	 * @param Filter\Standard $component
	 * @param RendererInterface $default_renderer
	 */
	protected function renderToggleButton(Template $tpl, Filter\Standard $component, RendererInterface$default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl->setCurrentBlock("action");
		$tpl->setVariable("ACTION_NAME", "toggleOn");
		$tpl->setVariable("ACTION", $component->getToggleOnAction());
		$tpl->parseCurrentBlock();

		$component->getToggleOnAction();
		$signal_generator = new SignalGenerator();
		$toggle_on_signal = $signal_generator->create();
		$toggle_on_action = $component->getToggleOnAction();
		$toggle = $f->button()->toggle("", $toggle_on_signal, $component->getToggleOffAction(), $component->isActivated())
			->withAdditionalOnLoadCode(function ($id) use ($toggle_on_signal, $toggle_on_action) {
				$code = "$(document).on('{$toggle_on_signal}',function(event) {
							il.UI.filter.onCmd(event, '$id', 'toggleOn');
							return false; // stop event propagation
				});";
				return $code;
			});

		$tpl->setVariable("TOGGLE", $default_renderer->render($toggle));
	}

	/**
	 * Render inputs
	 *
	 * @param Template $tpl
	 * @param Filter\Standard $component
	 * @param RendererInterface $default_renderer
	 */
	protected function renderInputs(Template $tpl, Filter\Standard $component, RendererInterface$default_renderer)
	{
		// pass information on what inputs should be initially rendered
		$is_input_rendered = $component->isInputRendered();
		foreach ($component->getInputs() as $k => $input)
		{
			$is_rendered = current($is_input_rendered);
			$tpl->setCurrentBlock("status");
			$tpl->setVariable("FIELD", $k);
			$tpl->setVariable("VALUE", (int) $is_rendered);
			$tpl->parseCurrentBlock();
			next($is_input_rendered);
		}

		// render inputs
		$input_group = $component->getInputGroup();
		if ($component->isActivated())
		{
			for ($i = 1; $i <= count($component->getInputs()); $i++)
			{
				$tpl->setCurrentBlock("active_inputs");
				$tpl->setVariable("ID", $i);
				$tpl->parseCurrentBlock();
			}
			if (count($component->getInputs()) > 0)
			{
				$tpl->setCurrentBlock("active_inputs_section");
				$tpl->parseCurrentBlock();
			}
			$tpl->touchBlock("enabled");
		} else {
			$tpl->touchBlock("disabled");
			$input_group = $input_group->withDisabled(true);
		}

		$input_group = $input_group->withOnUpdate($component->getUpdateSignal());

		$renderer = $default_renderer->withAdditionalContext($component);
		$tpl->setVariable("INPUTS", $renderer->render($input_group));
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Filter\Standard::class,
		);
	}
}
