<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/RepoObjectAssignment/interfaces/interface.ilExcRepoObjAssignmentInfoInterface.php");

/**
 * Repository object assignment information
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExcRepoObjAssignmentInfo implements ilExcRepoObjAssignmentInfoInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int[]
     */
    protected $ref_ids;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var ilExAssignmentTypes
     */
    protected $ass_types;

    /**
     * @var bool
     */
    protected $is_user_submission;

    /**
     * @var string
     */
    protected $exc_title;

    /**
     * @var int
     */
    protected $exc_id;

    /**
     * Constructor
     *
     * @param int $a_assignment_id
     * @param int[] $a_ref_ids ref ids
     */
    protected function __construct(
        $a_assignment_id,
        $a_assignment_title,
        $a_ref_ids,
        $is_user_submission,
        $a_exc_id,
        $a_exc_title
    ) {
        $this->id = $a_assignment_id;
        $this->title = $a_assignment_title;
        $this->ref_ids = $a_ref_ids;
        $this->ass_types = ilExAssignmentTypes::getInstance();
        $this->is_user_submission = $is_user_submission;
        $this->exc_id = $a_exc_id;
        $this->exc_title = $a_exc_title;
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = [];
        foreach ($this->ref_ids as $ref_id) {
            $links[$ref_id] = ilLink::_getLink($ref_id, "exc", array(), "_" . $this->id);
        }
        return $links;
    }

    /**
     * @inheritdoc
     */
    public function isUserSubmission()
    {
        return $this->is_user_submission;
    }

    /**
     * @inheritdoc
     */
    public function getExerciseId()
    {
        return $this->exc_id;
    }

    /**
     * @inheritdoc
     */
    public function getExerciseTitle()
    {
        return $this->exc_title;
    }

    /**
     * @inheritdoc
     */
    public function getReadableRefIds()
    {
        return $this->ref_ids;
    }


    /**
     * Get all info objects for a ref id of an repo object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     * @return ilExcRepoObjAssignmentInfo[]
     */
    public static function getInfo($a_ref_id, $a_user_id)
    {
        global $DIC;

        $access = $DIC->access();

        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssignmentTypes.php");
        $ass_types = ilExAssignmentTypes::getInstance();

        $repos_ass_type_ids = $ass_types->getIdsForSubmissionType(ilExSubmission::TYPE_REPO_OBJECT);
        include_once("./Modules/Exercise/classes/class.ilExSubmission.php");
        $submissions = ilExSubmission::getSubmissionsForFilename($a_ref_id, $repos_ass_type_ids);

        $ass_info = array();
        foreach ($submissions as $s) {
            $ass_type = $ass_types->getById($s["type"]);

            // @todo note: this currently only works, if submissions are assigned to the team (like team wikis)
            // get team of user
            include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
            $team = ilExAssignmentTeam::getInstanceByUserId($s["ass_id"], $a_user_id);
            $is_user_submission = ($team->getId() > 0 && $team->getId() == $s["team_id"])
                ? true
                : false;


            // determine all readable ref ids of the exercise
            $ref_ids = ilObject::_getAllReferences($s["exc_id"]);
            $readable_ref_ids = array();
            foreach ($ref_ids as $ref_id) {
                if ($a_user_id > 0 && !$access->checkAccessOfUser($a_user_id, "read", "", $ref_id)) {
                    continue;
                }
                $readable_ref_ids[] = $ref_id;
            }
            $ass_info[] = new self(
                $s["ass_id"],
                $s["title"],
                $readable_ref_ids,
                $is_user_submission,
                $s["exc_id"],
                ilObject::_lookupTitle($s["exc_id"])
            );
        }
        return $ass_info;
    }
}
