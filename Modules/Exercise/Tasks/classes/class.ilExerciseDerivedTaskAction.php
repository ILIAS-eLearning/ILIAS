<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Action class for derived tasks, mostly getting user reponsibilities
 * by respecting permissions as well.
 *
 * @author @leifos.de
 * @ingroup
 */
class ilExerciseDerivedTaskAction
{
	/**
	 * @var ilExcMemberRepository
	 */
	protected $exc_mem_repo;

	/**
	 * @var ilExcAssMemberStateRepository
	 */
	protected $state_repo;

	/**
	 * Constructor
	 * @param ilExcMemberRepository $exc_mem_repo
	 */
	public function __construct(ilExcMemberRepository $exc_mem_repo, ilExcAssMemberStateRepository $state_repo,
								ilExcTutorRepository $tutor_repo)
	{
		$this->exc_mem_repo = $exc_mem_repo;
		$this->state_repo = $state_repo;
		$this->tutor_repo = $tutor_repo;
	}

	/**
	 * Get all open assignments of a user
	 *
	 * @param int $user_id
	 * @return ilExAssignment[]
	 */
	public function getOpenAssignmentsOfUser(int $user_id): array
	{
		$user_exc_ids = $this->exc_mem_repo->getExerciseIdsOfUser($user_id);
		$assignments = [];
		foreach ($this->state_repo->getSubmitableAssignmentIdsOfUser($user_exc_ids, $user_id) as $ass_id)
		{
			$assignments[] = new ilExAssignment($ass_id);
			// to do: permission check
		}
		return $assignments;
	}

	/**
	 * Get all open peer reviews of a user
	 *
	 * @param int $user_id
	 * @return ilExAssignment[]
	 */
	public function getOpenPeerReviewsOfUser(int $user_id): array
	{
		$user_exc_ids = $this->exc_mem_repo->getExerciseIdsOfUser($user_id);
		$assignments = [];
		foreach ($this->state_repo->getAssignmentIdsWithPeerFeedbackNeeded($user_exc_ids, $user_id) as $ass_id)
		{
			$assignments[] = new ilExAssignment($ass_id);
			// to do: permission check
		}
		return $assignments;
	}

	/**
	 * Get all open gradings of a user
	 *
	 * @param int $user_id
	 * @return ilExAssignment[]
	 */
	public function getOpenGradingsOfUser(int $user_id): array
	{
		$user_exc_ids = $this->tutor_repo->getExerciseIdsBeingTutor($user_id);
		$assignments = [];
		foreach ($this->state_repo->getAssignmentIdsWithGradingNeeded($user_exc_ids) as $ass_id => $open)
		{
			$assignments[] = new ilExAssignment($ass_id);
			// to do: permission check
		}
		return $assignments;
	}

}