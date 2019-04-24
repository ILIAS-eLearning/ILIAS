<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tutor repository class.
 *
 * There is no explicit tutor concept in exercises yet. On the one hand, every user with "write" permission
 * can manage an exercise. Since this includes all system administrators this is not a well defined group of
 * users.
 *
 * This class defines tutors currently as the ones who get notifications about new submissions, which is a
 * smaller, but well defined group.
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
class ilExcTutorRepository
{
	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	/**
	 * Get exercise IDs of exercises a user is currently tutor (being notified)
	 *
	 * @param int $user_id
	 * @return int[]
	 */
	public function getExerciseIdsBeingTutor(int $user_id): array
	{
		return ilNotification::getActivatedNotifications(ilNotification::TYPE_EXERCISE_SUBMISSION, $user_id);
	}


}