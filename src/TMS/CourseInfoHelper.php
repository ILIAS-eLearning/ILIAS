<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * Useful functions to process CourseInfo.
 */
trait CourseInfoHelper {
	/**
	 * Get components for the entity.
	 *
	 * @param	string		$component_type
	 * @return	Component[]
	 */
	abstract public function getComponentsOfType($component_type);

	/**
	 * Get information for a certain context ordered by priority.
	 *
	 * @param	mixed	$context	from CourseInfo
	 * @return	CourseInfo[]
	 */
	public function getCourseInfo($context) {
		$info = $this->getComponentsOfType(CourseInfo::class);

		$filter_by_context = function(CourseInfo $a) use ($context) {
			return $a->hasContext($context);
		};
		$info = array_filter($info, $filter_by_context);

		$sort_by_prio = function(CourseInfo $a, CourseInfo $b) {
			$a_prio = $a->getPriority();
			$b_prio = $b->getPriority();
			if ($a_prio < $b_prio) {
				return -1;
			}
			if ($a_prio > $b_prio) {
				return 1;
			}
			return 0;
		};
		usort($info, $sort_by_prio);

		return $info;
	}

	/**
	 * Unpacks CourseInfo to value.
	 *
	 * TODO: Test me!
	 *
	 * @param	CourseInfo[]	$info
	 * @return  string[]
	 */
	protected function unpackValue(array $info) {
		$ret = [];
		foreach ($info as $i) {
			$ret[] = $i->getValue();
		}
		return $ret;
	}

	/**
	 * Unpacks CourseInfo to label => value.
	 *
	 * TODO: Test me!
	 *
	 * @param	CourseInfo[]	$info
	 * @return  array<string,string>
	 */
	protected function unpackLabelAndValue(array $info) {
		$ret = [];
		foreach ($info as $i) {
			$ret[$i->getLabel()] = $i->getValue();
		}
		return $ret;
	}

	/**
	 * Unpacks CourseInfo to label => value, where value might be array and will be
	 * turned into an unordered listing.
	 *
	 * TODO: Test me!
	 *
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	CourseInfo[]	$info
	 * @return  array<string,string>
	 */
	protected function unpackLabelAndNestedValue(\ILIAS\UI\Factory $ui_factory, array $info) {
		$ui_factory = $this->getUIFactory();
		$ret = [];
		foreach ($info as $i) {
			$value = $i->getValue();
			if (is_array($value)) {
				$value = $ui_factory->listing()->unordered($value);
			}
			$ret[$i->getLabel()] = $value;
		}
		return $ret;
	}

	/**
	 * Unpacks CourseInfo to label => value, where value might be array and will be
	 * rendered as an unordered listing.
	 *
	 * TODO: Test me!
	 *
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	\ILIAS\UI\Renderer $ui_renderer
	 * @param	CourseInfo[]	$info
	 * @return  array<string,string>
	 */
	protected function unpackLabelAndNestedValueRendered(\ILIAS\UI\Factory $ui_factory, \ILIAS\UI\Renderer $ui_renderer, array $info) {
		$ui_factory = $this->getUIFactory();
		$ret = [];
		foreach ($info as $i) {
			$value = $i->getValue();
			if (is_array($value)) {
				$value = $ui_renderer->render($ui_factory->listing()->unordered($value));
			}
			$ret[$i->getLabel()] = $value;
		}
		return $ret;
	}

	/**
	 * Add course info to a form in a NonEditableInputGUI via unpackLabelAndNestedValueRendered.
	 *
	 * TODO: Test me!
	 *
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	\ILIAS\UI\Renderer $ui_renderer
	 * @param	CourseInfo[]	$info
	 * @param	\ilPropertyFormGUI $form
	 * @return  void
	 */
	protected function appendToForm(\ILIAS\UI\Factory $ui_factory, \ILIAS\UI\Renderer $ui_renderer, array $info, \ilPropertyFormGUI $form) {
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		$info = $this->unpackLabelAndNestedValueRendered($ui_factory, $ui_renderer, $info);

		foreach ($info as $key => $value) {
			$item = new \ilNonEditableValueGUI($key);
			$item->setValue($value);
			$form->addItem($item);
		}
	}
}
