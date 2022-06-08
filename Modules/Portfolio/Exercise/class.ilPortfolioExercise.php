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
 * Exercise info for portfolios
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioExercise
{
    protected int $user_id;
    protected int $obj_id;
    protected ilTree $tree;

    public function __construct(
        int $a_user_id,
        int $a_obj_id
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->user_id = $a_user_id;
        $this->obj_id = $a_obj_id;
    }

    public function getAssignmentsOfPortfolio() : array
    {
        $user_id = $this->user_id;
        $obj_id = $this->obj_id;
        $tree = $this->tree;

        $assignments = [];

        $exercises = ilExSubmission::findUserFiles($user_id, $obj_id);
        // #0022794
        if (count($exercises) === 0) {
            $exercises = ilExSubmission::findUserFiles($user_id, $obj_id . ".sec");
        }
        if (count($exercises) === 0) {
            $exercises = ilExSubmission::findUserFiles($user_id, $obj_id . ".zip");
        }
        if ($exercises) {
            foreach ($exercises as $exercise) {
                // #9988
                $active_ref = false;
                foreach (ilObject::_getAllReferences($exercise["obj_id"]) as $ref_id) {
                    if (!$tree->isSaved($ref_id)) {
                        $active_ref = true;
                        break;
                    }
                }
                if ($active_ref) {
                    $assignments[] = [
                        "exc_id" => $exercise["obj_id"],
                        "ass_id" => $exercise["ass_id"]
                    ];
                }
            }
        }
        return $assignments;
    }
}
