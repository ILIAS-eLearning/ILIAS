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
 * Repository object assignment information
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExcRepoObjAssignmentInfo implements ilExcRepoObjAssignmentInfoInterface
{
    protected int  $id;

    /**
     * @var int[]
     */
    protected array $ref_ids;

    protected string $title;
    protected ilExAssignmentTypes $ass_types;
    protected bool $is_user_submission;
    protected string $exc_title;
    protected int $exc_id;

    /**
     * @param int[] $a_ref_ids
     */
    protected function __construct(
        int $a_assignment_id,
        string $a_assignment_title,
        array $a_ref_ids,
        bool $is_user_submission,
        int $a_exc_id,
        string $a_exc_title
    ) {
        $this->id = $a_assignment_id;
        $this->title = $a_assignment_title;
        $this->ref_ids = $a_ref_ids;
        $this->ass_types = ilExAssignmentTypes::getInstance();
        $this->is_user_submission = $is_user_submission;
        $this->exc_id = $a_exc_id;
        $this->exc_title = $a_exc_title;
    }


    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string[]
     */
    public function getLinks() : array
    {
        $links = [];
        foreach ($this->ref_ids as $ref_id) {
            $links[$ref_id] = ilLink::_getLink($ref_id, "exc", array(), "_" . $this->id);
        }
        return $links;
    }

    public function isUserSubmission() : bool
    {
        return $this->is_user_submission;
    }

    public function getExerciseId() : int
    {
        return $this->exc_id;
    }

    public function getExerciseTitle() : string
    {
        return $this->exc_title;
    }

    /**
     * @return int[]
     */
    public function getReadableRefIds() : array
    {
        return $this->ref_ids;
    }

    /**
     * Get all info objects for a ref id of an repo object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     * @return ilExcRepoObjAssignmentInfo[]
     * @throws ilExcUnknownAssignmentTypeException
     */
    public static function getInfo(int $a_ref_id, int $a_user_id) : array
    {
        global $DIC;

        $access = $DIC->access();

        $ass_types = ilExAssignmentTypes::getInstance();

        $repos_ass_type_ids = $ass_types->getIdsForSubmissionType(ilExSubmission::TYPE_REPO_OBJECT);
        $submissions = ilExSubmission::getSubmissionsForFilename($a_ref_id, $repos_ass_type_ids);

        $ass_info = array();
        foreach ($submissions as $s) {
            //$ass_type = $ass_types->getById($s["type"]);

            // @todo note: this currently only works, if submissions are assigned to the team (like team wikis)
            // get team of user
            $team = ilExAssignmentTeam::getInstanceByUserId($s["ass_id"], $a_user_id);
            $is_user_submission = $team->getId() > 0 && $team->getId() == $s["team_id"];

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
