<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button\Split;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Button\Split\Standard) {
			return $this->renderStandard($component, $default_renderer);
		} else {
			return $this->renderMonth($component, $default_renderer);
		}
	}

	protected function renderStandard(Component\Button\Split\Split $component, RendererInterface $default_renderer) {
		global $DIC;

		$tpl = $this->getTemplate("tpl.split_standard.html", true, true);

		$nr = 1;
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
		foreach ($component->getActionsAndLabels() as $label => $action)
		{
			if ($nr == 1)
			{
				$tpl->setVariable("DEFAULT_ACTION", $action);
				$tpl->setVariable("DEFAULT_LABEL", $label);
			}
			else
			{
				$tpl->setCurrentBlock("item");
				$tpl->setVariable("ITEM_ACTION", $action);
				$tpl->setVariable("ITEM_LABEL", $label);
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("items");
				$tpl->parseCurrentBlock();
			}
			$nr++;
		}
		if ($nr > 1)
		{
			$tpl->setCurrentBlock("menu_items");
			$tpl->parseCurrentBlock();
		}


		//$this->maybeRenderId($component, $tpl);
		return $tpl->get();
	}

	protected function renderMonth(Component\Button\Split\Split $component, RendererInterface $default_renderer) {
		global $DIC;

		$user = $DIC->user();

		$def = $component->getDefault();

		$tpl = $this->getTemplate("tpl.split_month.html", true, true);

		$tpl->setVariable("DEFAULT_LABEL", $def);

		include_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
		\ilCalendarUtil::initDateTimePicker();

		// weekStart is currently governed by locale and cannot be changed
/*
		$default = array(
			'locale' => $user->getLanguage()
		,'stepping' => 5
		,'useCurrent' => false
		//,'calendarWeeks' => true
		,'toolbarPlacement' => 'top'
			// ,'showTodayButton' => true
		,'showClear' => true
			// ,'showClose' => true
		,'keepInvalid' => true
		,'sideBySide' => true
			// ,'collapse' => false						
		,'format' => "mm-yyyy"
		,'viewMode' => "months"
		,'startView' => "months"
		,'minViewMode' => "months"
		);


		$tpl->addOnLoadCode('$("#'.$a_id.'").datetimepicker('.json_encode($config).')');
*/

		//$this->maybeRenderId($component, $tpl);
		return $tpl->get();
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array
		(Component\Button\Split\Standard::class
		, Component\Button\Split\Month::class
		);
	}
}
