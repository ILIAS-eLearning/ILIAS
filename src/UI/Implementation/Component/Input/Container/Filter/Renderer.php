<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation as I;

class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Input\Container\Filter\Standard) {
			return $this->renderStandard($component, $default_renderer);
		}

		throw new \LogicException("Cannot render: " . get_class($component));
	}


	protected function renderStandard(Component\Input\Container\Filter\Standard $component, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate("tpl.standard_filter.html", true, true);


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

		$f = $this->getUIFactory();
		if ($component->isExpanded() == false) {

			if ($component->isActivated())
			{
				$opener_expand = $f->button()->bulky($f->glyph()->expand(), "Filter", "")
					->withOnLoadCode(function ($id) use ($component) {
						return "$('#{$id}').on('click', function(ev) {" . "	$('#{$id}').parents('form').attr('action', '" . $component->getExpandAction() . "').submit();" . "});";
					});
			} else {
				$opener_expand = $f->button()->bulky($f->glyph()->expand(), "Filter", $component->getExpandAction());
			}

			$tpl->setVariable("OPENER", $default_renderer->render($opener_expand));
			$tpl->touchBlock("collapsed");
		}
		else {

			if ($component->isActivated()) {
				$opener_collapse = $f->button()->bulky($f->glyph()->collapse(), "Filter", "")
					->withOnLoadCode(function ($id) use ($component) {
						return "$('#{$id}').on('click', function(ev) {" . "	$('#{$id}').parents('form').attr('action', '" . $component->getCollapseAction() . "').submit();" . "});";
					});
			} else {
				$opener_collapse = $f->button()->bulky($f->glyph()->collapse(), "Filter", $component->getCollapseAction());
			}

			$tpl->setVariable("OPENER", $default_renderer->render($opener_collapse));
			$tpl->touchBlock("expanded");
		}
		$tpl->setVariable("OPENER_TITLE", "Filter");
		//replace with Apply Glyph and use language variable
		$apply = $f->button()->bulky($f->glyph()->apply(), "Apply", "");

		if (!$component->isActivated()) {
			$apply = $apply->withUnavailableAction(true);
			$reset = $f->button()->bulky($f->glyph()->reset(), "Reset", "") //replace with Reset Glyph and use
			->withUnavailableAction(true);
		} else {
			$apply = $apply->withOnLoadCode(function ($id) use ($component) {
				return "$('#{$id}').on('click', function(ev) {" . "	$('#{$id}').parents('form').attr('action', '".$component->getApplyAction()."').submit();" . "});";
			});
			$reset = $f->button()->bulky($f->glyph()->reset(), "Reset", $component->getResetAction());
		}

		$component->getToggleOnAction();

		// toggle on signal
		$signal_generator = new I\Component\SignalGenerator();
		$toggle_on_signal = $signal_generator->create();
		$toggle_on_action = $component->getToggleOnAction();

		// toggle button
		$toggle = $f->button()->toggle("", $toggle_on_signal, $component->getToggleOffAction(), $component->isActivated())
			->withAdditionalOnLoadCode(function ($id) use ($toggle_on_signal, $toggle_on_action) {
				return "$(document).on('{$toggle_on_signal}',function(ev) {" . "	$('#{$id}').parents('form').attr('action', '$toggle_on_action').submit();" . "});";
			});

		$tpl->setVariable("APPLY", $default_renderer->render($apply));
		$tpl->setVariable("RESET", $default_renderer->render($reset));
		$tpl->setVariable("TOGGLE", $default_renderer->render($toggle));

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
			$input_group = $input_group->withLabel("disabled");
		}

		$renderer = $default_renderer->withAdditionalContext($component);
		$tpl->setVariable("INPUTS", $renderer->render($input_group));

		return $tpl->get();
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\Input\Container\Filter\Standard::class,
		);
	}
}
