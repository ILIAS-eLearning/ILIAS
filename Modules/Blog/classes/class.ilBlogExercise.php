<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Blog Exercise
 *
 * @author <killing@leifos.com>
 */
class ilBlogExercise
{
    /**
     * @var ilObjUser
     */
    protected $user;

    protected $node_id; // [int]

    /**
     * @var ilTree
     */
    protected $tree;

    public function __construct($a_node_id)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->node_id = $a_node_id;
    }

    /**
     * @return array
     */
    public function getAssignmentsOfBlog()
    {
        $user = $this->user;
        $node_id = $this->node_id;
        $tree = $this->tree;

        $assignments = [];

        $exercises = ilExSubmission::findUserFiles($user->getId(), $node_id);
        // #0022794
        if (!$exercises)
        {
            $exercises = ilExSubmission::findUserFiles($user->getId(), $node_id.".sec");
        }
        if($exercises) {
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
