<?php

namespace CaT\Plugins\TalentAssessment\Observator;

interface DB {
	/**
	 * get all observator for $role_id
	 *
	 * @param 	int 	$role_id
	 *
	 * @return array[]
	 */
	public function selectAssignedFor($role_id);

	/**
	 * create local role template
	 *
	 * @param string 	$template_title
	 * @param string 	$tpl_description
	 */
	public function createLocalRoleTemplate($tpl_title, $tpl_description);

	/**
	 * get obj id of role template
	 *
	 * @return int
	 */
	public function getRoltId();
}