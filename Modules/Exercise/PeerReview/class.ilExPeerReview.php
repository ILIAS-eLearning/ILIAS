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

use ILIAS\Exercise\PeerReview\ExcPeerReviewDistribution;

/**
 * Exercise peer review
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExPeerReview
{
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected ilExAssignment$assignment;
    protected int $assignment_id;
    protected ilLogger $log;

    public function __construct(
        ilExAssignment $a_assignment
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->assignment = $a_assignment;
        $this->assignment_id = $a_assignment->getId();
        $this->log = ilLoggerFactory::getLogger("exc");
    }

    public function hasPeerReviewGroups(): bool
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT count(*) cnt" .
            " FROM exc_assignment_peer" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer"));
        $cnt = $ilDB->fetchAssoc($set);
        return (bool) $cnt["cnt"];
    }

    /**
     * @return int[]
     */
    protected function getValidPeerReviewUsers(): array
    {
        $ilDB = $this->db;

        $user_ids = array();

        // returned / assigned ?!
        $set = $ilDB->query("SELECT DISTINCT(user_id)" .
            " FROM exc_returned" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)");
        while ($row = $ilDB->fetchAssoc($set)) {
            $user_ids[] = (int) $row["user_id"];
        }

        return $user_ids;
    }

    public function initPeerReviews(): bool
    {
        $ilDB = $this->db;

        // see #22246
        if (!$this->assignment->afterDeadlineStrict()) {
            return false;
        }

        if (!$this->hasPeerReviewGroups()) {
            $user_ids = $this->getValidPeerReviewUsers();

            $distribution = new ExcPeerReviewDistribution($user_ids, $this->assignment->getPeerReviewMin());

            foreach ($user_ids as $rater_id) {
                foreach ($distribution->getPeersOfRater($rater_id) as $peer_id) {
                    $ilDB->manipulate("INSERT INTO exc_assignment_peer" .
                        " (ass_id, giver_id, peer_id)" .
                        " VALUES (" . $ilDB->quote($this->assignment_id, "integer") .
                        ", " . $ilDB->quote($rater_id, "integer") .
                        ", " . $ilDB->quote($peer_id, "integer") . ")");
                }
            }
        }
        return true;
    }

    /**
     * @return int[]|string[]
     */
    public function resetPeerReviews(): array
    {
        $ilDB = $this->db;

        $all = array();

        if ($this->hasPeerReviewGroups()) {
            foreach ($this->getAllPeerReviews(false) as $peer_id => $reviews) {
                foreach (array_keys($reviews) as $giver_id) {
                    $all[] = $giver_id;

                    foreach ($this->assignment->getPeerReviewCriteriaCatalogueItems() as $crit) {
                        $crit->setPeerReviewContext($this->assignment, $giver_id, $peer_id);
                        $crit->resetReview();
                    }
                }
            }

            // peer groups
            $ilDB->manipulate("DELETE FROM exc_assignment_peer" .
                " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer"));
        }

        return $all;
    }

    public function validatePeerReviewGroups(): ?array
    {
        if ($this->hasPeerReviewGroups()) {
            $all_exc = ilExerciseMembers::_getMembers($this->assignment->getExerciseId());
            $all_valid = $this->getValidPeerReviewUsers(); // only returned

            $peer_ids = $invalid_peer_ids = $invalid_giver_ids = $all_reviews = array();
            foreach ($this->getAllPeerReviews(false) as $peer_id => $reviews) {
                $peer_ids[] = $peer_id;

                if (!in_array($peer_id, $all_valid) ||
                    !in_array($peer_id, $all_exc)) {
                    $invalid_peer_ids[] = $peer_id;
                }
                foreach ($reviews as $giver_id => $valid) {
                    if (!in_array($giver_id, $all_valid) ||
                        !in_array($peer_id, $all_exc)) {
                        $invalid_giver_ids[] = $giver_id;
                    } else {
                        $all_reviews[$peer_id][$giver_id] = $valid;
                    }
                }
            }
            $invalid_giver_ids = array_unique($invalid_giver_ids);

            $missing_user_ids = array();
            foreach ($all_valid as $user_id) {
                // a missing peer is also a missing giver
                if (!in_array($user_id, $peer_ids)) {
                    $missing_user_ids[] = $user_id;
                }
            }

            $not_returned_ids = array();
            foreach ($all_exc as $user_id) {
                if (!in_array($user_id, $all_valid)) {
                    $not_returned_ids[] = $user_id;
                }
            }

            return array(
                "invalid" => (count($missing_user_ids) ||
                    count($invalid_peer_ids) ||
                    count($invalid_giver_ids)),
                "missing_user_ids" => $missing_user_ids,
                "not_returned_ids" => $not_returned_ids,
                "invalid_peer_ids" => $invalid_peer_ids,
                "invalid_giver_ids" => $invalid_giver_ids,
                "reviews" => $all_reviews);
        }

        return null;
    }

    public function getPeerReviewValues(
        int $a_giver_id,
        int $a_peer_id
    ): array {
        $peer = null;
        foreach ($this->getPeerReviewsByGiver($a_giver_id) as $item) {
            if ($item["peer_id"] == $a_peer_id) {
                $peer = $item;
            }
        }
        if (!$peer) {
            return [];
        }
        $data = $peer["pcomment"];
        if ($data) {
            $items = unserialize($data);
            if (!is_array($items)) {
                // v1 - pcomment == text
                $items = array("text" => $data);
            }
            return $items;
        }
        return [];
    }

    public function getPeerReviewsByGiver(int $a_user_id): array
    {
        $ilDB = $this->db;

        $res = array();

        if ($this->initPeerReviews()) {
            $idx = 0;
            $set = $ilDB->query("SELECT *" .
                " FROM exc_assignment_peer" .
                " WHERE giver_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND ass_id = " . $ilDB->quote($this->assignment_id, "integer") .
                " ORDER BY peer_id");
            while ($row = $ilDB->fetchAssoc($set)) {
                $row["seq"] = ++$idx;
                $res[] = $row;
            }
        }

        return $res;
    }

    public function getPeerMaskedId(
        int $a_giver_id,
        int $a_peer_id
    ): int {
        foreach ($this->getPeerReviewsByGiver($a_giver_id) as $peer) {
            if ($peer["peer_id"] == $a_peer_id) {
                return (int) $peer["seq"];
            }
        }
        return 0;
    }

    protected function validatePeerReview(array $a_data): bool
    {
        $all_empty = true;

        // see getPeerReviewValues()
        $values = null;
        $data = $a_data["pcomment"];
        if ($data) {
            try {
                $values = unserialize($data);
            } catch (Exception $e) {
            }
            if (!is_array($values)) {
                // v1 - pcomment == text
                $values = array("text" => $data);
            }
        }

        /* #18491 - values can be empty, text is optional (rating/file values are handled internally in criteria) */
        if (!$values) {
            return false;
        }

        foreach ($this->assignment->getPeerReviewCriteriaCatalogueItems() as $crit) {
            $crit_id = $crit->getId()
                ? $crit->getId()
                : $crit->getType();
            $crit->setPeerReviewContext(
                $this->assignment,
                $a_data["giver_id"],
                $a_data["peer_id"]
            );
            if (!$crit->validate($values[$crit_id] ?? null)) {
                return false;
            }
            if ($crit->hasValue($values[$crit_id] ?? null)) {
                $all_empty = false;
            }
        }

        return !$all_empty;
    }

    public function getPeerReviewsByPeerId(
        int $a_user_id,
        bool $a_only_valid = false
    ): array {
        $ilDB = $this->db;

        $res = array();

        $idx = 0;
        $set = $ilDB->query("SELECT *" .
            " FROM exc_assignment_peer" .
            " WHERE peer_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND ass_id = " . $ilDB->quote($this->assignment_id, "integer") .
            " ORDER BY peer_id");
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$a_only_valid ||
                $this->validatePeerReview($row)) {
                // this would be correct but rather senseless
                // $row["seq"] = $this->getPeerMaskedId($row["giver_id"], $a_user_id);
                $row["seq"] = ++$idx;
                $res[] = $row;
            }
        }

        return $res;
    }

    public function getAllPeerReviews(
        bool $a_only_valid = true
    ): array {
        $ilDB = $this->db;

        $res = array();

        $set = $ilDB->query("SELECT *" .
            " FROM exc_assignment_peer" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer") .
            " ORDER BY peer_id");
        while ($row = $ilDB->fetchAssoc($set)) {
            $valid = $this->validatePeerReview($row);
            if (!$a_only_valid ||
                $valid) {
                $res[$row["peer_id"]][$row["giver_id"]] = $valid;
            }
        }

        return $res;
    }

    public function hasPeerReviewAccess(
        int $a_peer_id
    ): bool {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $set = $ilDB->query("SELECT ass_id" .
            " FROM exc_assignment_peer" .
            " WHERE giver_id = " . $ilDB->quote($ilUser->getId(), "integer") .
            " AND peer_id = " . $ilDB->quote($a_peer_id, "integer") .
            " AND ass_id = " . $ilDB->quote($this->assignment_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return ((int) ($row["ass_id"] ?? 0) > 0);
    }

    public function updatePeerReviewTimestamp(
        int $a_peer_id
    ): void {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $ilDB->manipulate("UPDATE exc_assignment_peer" .
            " SET tstamp = " . $ilDB->quote(ilUtil::now(), "timestamp") .
            " WHERE giver_id = " . $ilDB->quote($ilUser->getId(), "integer") .
            " AND peer_id = " . $ilDB->quote($a_peer_id, "integer") .
            " AND ass_id = " . $ilDB->quote($this->assignment_id, "integer"));
    }

    public function updatePeerReview(
        int $a_peer_id,
        array $a_values
    ): void {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $data = [
            "pcomment" => serialize($a_values),
            "peer_id" => $a_peer_id,
            "giver_id" => $ilUser->getId()
        ];
        $valid = $this->validatePeerReview($data);

        $sql = "UPDATE exc_assignment_peer" .
            " SET tstamp = " . $ilDB->quote(ilUtil::now(), "timestamp") .
            ",pcomment  = " . $ilDB->quote(serialize($a_values), "text") .
            ",is_valid = " . $ilDB->quote((int) $valid, "integer") .
            " WHERE giver_id = " . $ilDB->quote($ilUser->getId(), "integer") .
            " AND peer_id = " . $ilDB->quote($a_peer_id, "integer") .
            " AND ass_id = " . $ilDB->quote($this->assignment_id, "integer");

        $ilDB->manipulate($sql);
    }

    public function countGivenFeedback(
        bool $a_validate = true,
        int $a_user_id = null
    ): int {
        $ilDB = $this->db;
        $ilUser = $this->user;

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }

        $cnt = 0;

        $set = $ilDB->query("SELECT *" .
            " FROM exc_assignment_peer" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer") .
            " AND giver_id = " . $ilDB->quote($a_user_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$a_validate ||
                $this->validatePeerReview($row)) {
                $cnt++;
            }
        }

        return $cnt;
    }

    protected function getMaxPossibleFeedbacks(): int
    {
        $ilDB = $this->db;

        // check if number of returned assignments is lower than assignment peer min
        $set = $ilDB->query("SELECT COUNT(DISTINCT(user_id)) cnt" .
            " FROM exc_returned" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer"));
        $cnt = $ilDB->fetchAssoc($set);
        $cnt = (int) $cnt["cnt"];
        return $cnt - 1;
    }

    public function getNumberOfMissingFeedbacksForReceived(): int
    {
        $max = $this->getMaxPossibleFeedbacks();

        // #16160 - forever alone
        if ($max === 0) {
            return 0;
        }

        // are all required or just 1?
        if (!$this->assignment->getPeerReviewSimpleUnlock()) {
            $needed = $this->assignment->getPeerReviewMin();
        } else {
            $needed = 1;
        }

        // there could be less participants than stated in the min required setting
        $min = min($max, $needed);

        return max(0, $min - $this->countGivenFeedback());
    }

    public function isFeedbackValidForPassed(int $a_user_id): bool
    {
        // peer feedback is not required for passing
        if ($this->assignment->getPeerReviewValid() == ilExAssignment::PEER_REVIEW_VALID_NONE) {
            return true;
        }

        // #16227 - no processing before reaching the peer review period
        if (!$this->assignment->afterDeadlineStrict()) {
            return false;
        }

        // forever alone - should be valid
        $max = $this->getMaxPossibleFeedbacks();
        if ($max === 0) {
            return true;
        }

        $no_of_feedbacks = $this->countGivenFeedback(true, $a_user_id);

        switch ($this->assignment->getPeerReviewValid()) {
            case ilExAssignment::PEER_REVIEW_VALID_ONE:
                return (bool) $no_of_feedbacks;

            case ilExAssignment::PEER_REVIEW_VALID_ALL:
                // there could be less participants than stated in the min required setting
                $min = min($max, $this->assignment->getPeerReviewMin());

                return (($min - $no_of_feedbacks) < 1);
        }
        return false;
    }

    public static function lookupGiversWithPendingFeedback(int $a_ass_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $user_ids = array();

        $set = $ilDB->query(
            "SELECT DISTINCT(giver_id) FROM exc_assignment_peer " .
            " WHERE ass_id = " . $ilDB->quote($a_ass_id, "integer") .
            " AND tstamp is NULL"
        );

        while ($row = $ilDB->fetchAssoc($set)) {
            $user_ids[] = $row["giver_id"];
        }

        return $user_ids;
    }
}
