<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExAssignmentTypeInterface
{
	/**
	 * Is assignment type active?
	 *
	 * @return bool
	 */
	function isActive();

	/**
	 * Uses teams
	 *
	 * @return bool
	 */
	function usesTeams();

	/**
	 * Uses file upload
	 *
	 * @return bool
	 */
	function usesFileUpload();

	/**
	 * Get title of type
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get submission type
	 *
	 * @return string
	 */
	public function getSubmissionType();

	/**
	 * Get submission type
	 *
	 * @return string
	 */
	public function isSubmissionAssignedToTeam();

}