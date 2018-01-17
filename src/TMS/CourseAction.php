<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
interface CourseAction extends Component {
	const CONTEXT_SEARCH = 1;
	const CONTEXT_USER_BOOKING = 2;
	const CONTEXT_EMPLOYEE_BOOKING = 3;
	const CONTEXT_EDU_BIO = 4;
	const CONTEXT_EMPOYEE_EDU_BIO = 5;
	const CONTEXT_MY_TRAININGS = 6;
	const CONTEXT_MY_ADMIN_TRAININGS = 7;
	const CONTEXT_SUPERIOR_SEARCH = 8;

	/**
	 * Get the owner of this action
	 *
	 * @return \ilObject
	 */
	public function getOwner();

	/**
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the action will be displayed in later position
	 *
	 * @return int
	 */
	public function getPriority();

	/**
	 * Check if the info is relevant in the given context.
	 *
	 * @param mixed 	$context from the list of contexts in this class
	 *
	 * @return bool
	 */
	public function hasContext($context);

	/**
	 * Checks the action is allowed for user
	 *
	 * @param int 	$usr_id 	Id of user the action is requested for
	 *
	 * @return bool
	 */
	public function isAllowedFor($usr_id);

	/**
	 * Get the link for the ui control
	 *
	 * @param \ilCtrl 	$ctrl
	 * @param int 	$usr_id
	 *
	 * @return string
	 */
	public function getLink(\ilCtrl $ctrl, $usr_id);

	/**
	 * Get the label for the ui control
	 *
	 * @return string
	 */
	public function getLabel();
}