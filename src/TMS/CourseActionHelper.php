<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * Useful functions to process CourseAction.
 */
trait CourseActionHelper {
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
	 * @param	mixed	$context	from CourseAction
	 * @return	CourseAction[]
	 */
	public function getCourseAction($context) {
		$info = $this->getComponentsOfType(CourseAction::class);

		$filter_by_context = function(CourseAction $a) use ($context) {
			return $a->hasContext($context);
		};
		$info = array_filter($info, $filter_by_context);

		$sort_by_prio = function(CourseAction $a, CourseAction $b) {
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
}
