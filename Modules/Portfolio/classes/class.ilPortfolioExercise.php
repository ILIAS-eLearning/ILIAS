<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise info for portfolios
 *
 * @author <killing@leifos.com>
 */
class ilPortfolioExercise
{
    protected $user_id; // [int]
    protected $obj_id; // [int]

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * ilPortfolioExercise constructor.
     * @param int $a_user_id
     * @param int $a_obj_id
     */
    public function __construct(int $a_user_id, int $a_obj_id)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->user_id = $a_user_id;
        $this->obj_id = $a_obj_id;
    }


    /**
     * @return array
     */
    public function getAssignmentsOfPortfolio(): array
    {
        $user_id = $this->user_id;
        $obj_id = $this->obj_id;
        $tree = $this->tree;

        $assignments = [];

        $exercises = ilExSubmission::findUserFiles($user_id, $obj_id);
        // #0022794
        if (!$exercises) {
            $exercises = ilExSubmission::findUserFiles($user_id, $obj_id . ".sec");
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