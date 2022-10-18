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

namespace ILIAS\Exercise;

use ILIAS\Repository;

/**
 * Exercise gui request wrapper. This class processes all
 * request parameters which are not handled by form classes already.
 * POST overwrites GET with the same name.
 * POST/GET parameters may be passed to the class for testing purposes.
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    /**
     * @return int[]
     */
    protected function getIds(): array
    {
        // "id" parameter used in team submission gui
        if ($this->isArray("id")) {
            return $this->intArray("id");
        } else {
            $team_id = $this->int("id");
            return ($team_id > 0)
                ? [$this->int("id")]
                : [];
        }
    }

    /**
     * note: shares "id" parameter with team ids
     * @return int[]
     */
    public function getAssignmentIds(): array
    {
        return $this->getIds();
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getAssId(): int
    {
        return $this->int("ass_id");
    }

    /**
     * @return int[]
     */
    public function getAssIds(): array
    {
        return $this->intArray("ass");
    }

    public function getAssIdGoto(): int
    {
        return $this->int("ass_id_goto");
    }

    /**
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function getExercise(): ?\ilObjExercise
    {
        if ($this->getRefId() > 0 && \ilObject::_lookupType($this->getRefId(), true) == "exc") {
            return new \ilObjExercise($this->getRefId());
        }
        return null;
    }

    /**
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function getAssignment(): ?\ilExAssignment
    {
        if ($this->getAssId() > 0) {
            return new \ilExAssignment($this->getAssId());
        }
        return null;
    }

    public function getAssType(): string
    {
        return $this->str("ass_type");
    }

    // also assignment type? see ilExAssignmentEditor
    public function getType(): int
    {
        return $this->int("type");
    }

    // criteria type
    public function getCriteriaType(): string
    {
        return $this->str("type");
    }

    /**
     * @return int[]
     */
    public function getSelectedAssignments(): array
    {
        return $this->intArray("sel_ass_ids");
    }

    /**
     * @return int[]
     */
    public function getListedAssignments(): array
    {
        return $this->intArray("listed_ass_ids");
    }

    //
    // User related
    //

    public function getMemberId(): int
    {
        return $this->int("member_id");
    }

    public function getMemberIds(): array
    {
        return $this->intArray("member_ids");
    }

    // can me merged with member id?
    public function getParticipantId(): int
    {
        return $this->int("part_id");
    }

    public function getUserId(): int
    {
        return $this->int("user_id");
    }

    public function getUserLogin(): string
    {
        return trim($this->str("user_login"));
    }

    /**
     * @return int[]
     */
    public function getSelectedParticipants(): array
    {
        return $this->intArray("sel_part_ids");
    }

    /**
     * @return int[]
     */
    public function getListedParticipants(): array
    {
        return $this->intArray("listed_part_ids");
    }

    /**
     * @return int[]
     */
    public function getGroupMembers(): array
    {
        return $this->arrayArray("grpt");
    }

    //
    // File related
    //

    public function getOldName(): string
    {
        return $this->str("old_name");
    }

    public function getNewName(): string
    {
        return $this->str("new_name");
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->arrayArray("file");
    }

    public function getFile(): string
    {
        return $this->str("file");
    }

    //
    // Individual deadline related
    //

    // sie ilExcIdl.js
    public function getDone(): bool
    {
        return (bool) $this->int("dn");
    }

    public function getIdlId(): string
    {
        return $this->str("idlid");   // may be comma separated
    }

    /**
     * @return string[]
     */
    public function getListedIdlIDs(): array
    {
        return $this->strArray("listed_idl_ids");
    }

    //
    // Table / Filter related
    //

    public function getOffset(): int
    {
        return $this->int("offset");
    }

    public function getSortOrder(): string
    {
        return $this->str("sort_order");
    }

    public function getSortBy(): string
    {
        return $this->str("sort_by");
    }

    public function getFilterStatus(): string
    {
        return trim($this->str("filter_status"));
    }

    public function getFilterFeedback(): string
    {
        return trim($this->str("filter_feedback"));
    }

    //
    // Workspace related
    //

    public function getSelectedWspObjId(): int
    {
        return $this->int("sel_wsp_obj");
    }

    //
    // Peer review related
    //

    public function getReviewGiverId(): int
    {
        $giver_peer_id = $this->str("fu");
        $parts = explode("__", $giver_peer_id);
        if (count($parts) > 1) {
            return (int) $parts[0];
        }
        return 0;
    }

    public function getReviewPeerId(): int
    {
        $giver_peer_id = $this->str("fu");
        $parts = explode("__", $giver_peer_id);
        if (count($parts) > 1) {
            return (int) $parts[1];
        }

        return 0;
    }

    public function getReviewCritId(): string
    {
        $giver_peer_id = $this->str("fu");
        $parts = explode("__", $giver_peer_id);
        if (isset($parts[2])) {
            return (string) $parts[2];
        }
        return "";
    }

    // different from "fu" parameter above!
    public function getPeerId(): int
    {
        return $this->int("peer_id");
    }

    // different from "fu" parameter above!
    public function getCritId(): string
    {
        return $this->str("crit_id");
    }

    // peer review files?
    public function getFileHash(): string
    {
        return trim($this->str("fuf"));
    }

    /**
     * @return int[]
     */
    public function getCatalogueIds(): array
    {
        return $this->getIds();
    }

    public function getCatalogueId(): int
    {
        return $this->int("cat_id");
    }

    /**
     * @return int[]
     */
    public function getCriteriaIds(): array
    {
        return $this->getIds();
    }


    //
    // Team related
    //

    /**
     * @return int[]
     */
    public function getTeamIds(): array
    {
        return $this->getIds();
    }

    //
    // Order / positions related
    //

    /**
     * @return int[]
     */
    public function getOrder(): array
    {
        return $this->intArray("order");
    }

    /**
     * @return int[]
     */
    public function getPositions(): array
    {
        return $this->intArray("pos");
    }

    //
    // Text related
    //

    public function getMinCharLimit(): int
    {
        return $this->int("min_char_limit");
    }

    //
    // Status / LP related
    //

    /**
     * @return string[]
     */
    public function getLearningComments(): array
    {
        return $this->strArray("lcomment");
    }

    /**
     * key might be ass_ids or user_ids!
     * @return string[]
     */
    public function getMarks(): array
    {
        return $this->strArray("mark");
    }

    /**
     * key might be ass_ids or user_ids!
     * @return string[]
     */
    public function getTutorNotices(): array
    {
        return $this->strArray("notice");
    }

    /**
     * key might be ass_ids or user_ids!
     * @return string[]
     */
    public function getStatus(): array
    {
        return $this->strArray("status");
    }

    public function getComment(): string
    {
        return $this->str("comment");
    }

    public function getRatingValue(): string
    {
        return $this->str("value");
    }

    /**
     * @return int[]
     */
    public function getSubmittedFileIds(): array
    {
        return $this->intArray("delivered");
    }

    public function getSubmittedFileId(): int
    {
        return $this->int("delivered");
    }

    public function getResourceObjectId(): int
    {
        return $this->int("item");
    }

    public function getBlogId(): int
    {
        return $this->int("blog_id");
    }

    public function getPortfolioId(): int
    {
        return $this->int("prtf_id");
    }

    public function getBackView(): int
    {
        return $this->int("vw");
    }
}
