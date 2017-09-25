<?php
/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\ViewControl
 */
class Renderer extends AbstractComponentRenderer
{
	const MODE_ROLE = "group";

	/**
	 * @param Component\Component $component
	 * @param RendererInterface $default_renderer
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer)
	{
		$this->checkComponent($component);

		if ($component instanceof Component\ViewControl\Mode) {
			return $this->renderMode($component, $default_renderer);
		}
		if ($component instanceof Component\ViewControl\Section) {
			return $this->renderSection($component, $default_renderer);
		}
		if ($component instanceof Component\ViewControl\Sortation) {
			return $this->renderSortation($component, $default_renderer);
		}
	}

	protected function renderMode(Component\ViewControl\Mode $component, RendererInterface $default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl = $this->getTemplate("tpl.mode.html", true, true);

		$active = $component->getActive();
		if($active == "") {
			$activate_first_item = true;
		}

		foreach ($component->getLabelledActions() as $label => $action)
		{
			$tpl->setVariable("ARIA", $this->txt($component->getAriaLabel()));
			$tpl->setVariable("ROLE", self::MODE_ROLE);

			$tpl->setCurrentBlock("view_control");

			//At this point we don't have an specific text for the button aria label. component->getAriaLabel gets the main viewcontrol aria label.
			if($activate_first_item) {
				$tpl->setVariable("BUTTON", $default_renderer->render($f->button()->standard($label, $action)->WithUnavailableAction()->withAriaLabel($label)->withAriaChecked()));
				$activate_first_item = false;
			} else if($active == $label) {
				$tpl->setVariable("BUTTON", $default_renderer->render($f->button()->standard($label, $action)->withUnavailableAction()->withAriaLabel($label)->withAriaChecked()));
			}
			else {
				$tpl->setVariable("BUTTON", $default_renderer->render($f->button()->standard($label, $action)->withAriaLabel($label)));
			}
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	protected function renderSection(Component\ViewControl\Section $component, RendererInterface $default_renderer)
	{
		$f = $this->getUIFactory();

		$tpl = $this->getTemplate("tpl.section.html", true, true);

		// render middle button
		$tpl->setVariable("BUTTON", $default_renderer->render($component->getSelectorButton()));

		// previous button
		$this->renderSectionButton($component->getPreviousActions(), $tpl, "prev");

		// next button
		$this->renderSectionButton($component->getNextActions(), $tpl, "next");

		return $tpl->get();
	}

	/**
	 * @param Component\Button\Button $component button
	 * @param $tpl
	 * @param string $type
	 */
	protected function renderSectionButton(Component\Button\Button $component, $tpl, $type)
	{
		$uptype = strtoupper($type);

		$action = $component->getAction();
		$tpl->setVariable($uptype."_ACTION", $action);
		if ($component->isActive())
		{
			$tpl->setCurrentBlock($type."_with_href");
			$tpl->setVariable($uptype."_HREF", $action);
			$tpl->parseCurrentBlock();
		} else {
			$tpl->touchBlock($type."_disabled");
		}
		$this->maybeRenderId($component, $tpl, $type."_with_id", $uptype."_ID");
	}


	protected function renderSortation(Component\ViewControl\Sortation $component, RendererInterface $default_renderer) {
		$f = new \ILIAS\UI\Implementation\Factory(
			new \ILIAS\UI\Implementation\Component\SignalGenerator()
		);

		$tpl = $this->getTemplate("tpl.sortation.html", true, true);

		$component = $component->withResetSignals();
		$triggeredSignals = $component->getTriggeredSignals();
		if($triggeredSignals) {

			$signal_select = $component->getSelectSignal();
			$signal = $triggeredSignals[0]->getSignal();
			$options = json_encode($signal->getOptions());

			$component = $component->withOnLoadCode(function($id) use ($signal_select, $signal, $options) {
				return "
				$(document).on('{$signal_select}', function(event, signalData) {

					var triggerer = signalData.triggerer[0], 			//the shy-button within the dropdown
						param = triggerer.getAttribute('data-action'), 	//the sortation-value
						id = '{$id}',									//id of sortation control to be used
																		//as triggerer for others
						sortation = $('#' + id),
						dd = sortation.find('.dropdown-toggle')			//the dropdown
						;

					//close dropdown and set current value
					dd.dropdown('toggle');
					dd.contents()[0].data = signalData.triggerer.contents()[0].data  + ' ';

					var options = JSON.parse('{$options}');
					options.sortation = param;

					//trigger sort-signal
					sortation.trigger('{$signal}',
						{
							'id' : '{$signal}', 'event' : 'sort',
							'triggerer' : sortation,
							'options' : options
						}
					);
					return false;
				});
				";

			});
			//maybeRenderId does not return id
			$id = $this->bindJavaScript($component);
			$tpl->setVariable('ID', $id);
		}

		//setup entries
		$options = $component->getOptions();
		$init_label = $component->getLabel();
		$items = array();
		foreach ($options as $val => $label) {
			if($triggeredSignals) {
				$shy = $f->button()->shy($label, $val)->withOnClick($signal_select);
			} else {
				$url = $component->getTargetURL();
				$url .= (strpos($url, '?') === false) ?  '?' : '&';
				$url .= $component->getParameterName() .'=' .$val;
				$shy = $f->button()->shy($label, $url);
			}
			$items[] = $shy;
		}

		$dd = $f->dropdown()->standard($items)
			->withLabel($init_label);

		$tpl->setVariable('SORTATION_DROPDOWN', $default_renderer->render($dd));
	    return $tpl->get();
	}


	protected function maybeRenderId(Component\Component $component, $tpl, $block, $template_var) {
		$id = $this->bindJavaScript($component);
		if ($id !== null) {
			$tpl->setCurrentBlock($block);
			$tpl->setVariable($template_var, $id);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\ViewControl\Mode::class,
			Component\ViewControl\Section::class,
			Component\ViewControl\Sortation::class
		);

	}

}
