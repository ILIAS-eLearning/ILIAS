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
 * Blog Exercise
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBlogExercise
{
    protected ilObjUser $user;
    protected int $node_id;
    protected ilTree $tree;

    public function __construct(
        int $a_node_id
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->node_id = $a_node_id;
    }

    public function getAssignmentsOfBlog() : array
    {
        $user = $this->user;
        $node_id = $this->node_id;
        $tree = $this->tree;

        $assignments = [];

        $exercises = ilExSubmission::findUserFiles($user->getId(), $node_id);
        // #0022794
        if (count($exercises) === 0) {
            $exercises = ilExSubmission::findUserFiles($user->getId(), $node_id . ".sec");
        }
        if (count($exercises) === 0) {
            $exercises = ilExSubmission::findUserFiles($user->getId(), $node_id . ".zip");
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
