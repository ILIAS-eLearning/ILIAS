<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcTutorRepository
{
    // Get exercise IDs of exercises a user is currently tutor (being notified)
    /**
     * @return int[]
     */
    public function getExerciseIdsBeingTutor(int $user_id) : array
    {
        return ilNotification::getActivatedNotifications(ilNotification::TYPE_EXERCISE_SUBMISSION, $user_id);
    }
}
