<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Exercise gui request wrapper. This class processes all
 * request parameters which are not handled by form classes already.
 * POST overwrites GET with the same name.
 * POST/GET parameters may be passed to the class for testing purposes.
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIRequest
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;
    protected ?array $passed_query_params;
    protected ?array $passed_post_data;

    /**
     * Query params and post data parameters are used for testing. If none of these is
     * provided the usual http service wrapper is used to determine the request data.
     * @param HTTP\Services    $http
     * @param Refinery\Factory $refinery
     * @param array|null       $passed_query_params
     * @param array|null       $passed_post_data
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->passed_post_data = $passed_post_data;
        $this->passed_query_params = $passed_query_params;
    }

    // get integer parameter kindly
    protected function int(string $key) : int
    {
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->get($key, $t) ?? 0);
    }

    // get integer array kindly
    protected function intArray($key) : array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to int
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, (int) $v];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    // get string parameter kindly
    protected function str($key) : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return \ilUtil::stripSlashes((string) ($this->get($key, $t) ?? ""));
    }

    // get string array kindly
    protected function strArray($key) : array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to string
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, \ilUtil::stripSlashes((string) $v)];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    /**
     * Check if parameter is an array
     */
    protected function isArray(string $key) : bool
    {
        if ($this->passed_query_params === null && $this->passed_post_data === null) {
            $no_transform = $this->refinery->custom()->transformation(function ($v) {
                return $v;
            });
            $w = $this->http->wrapper();
            if ($w->post()->has($key)) {
                return is_array($w->post()->retrieve($key, $no_transform));
            }
            if ($w->query()->has($key)) {
                return is_array($w->query()->retrieve($key, $no_transform));
            }
        }
        if (isset($this->passed_post_data[$key])) {
            return is_array($this->passed_post_data[$key]);
        }
        if (isset($this->passed_query_params[$key])) {
            return is_array($this->passed_query_params[$key]);
        }
        return false;
    }

    /**
     * Get passed parameter, if not data passed, get key from http request
     * @param string                  $key
     * @param Refinery\Transformation $t
     * @return mixed|null
     */
    protected function get(string $key, Refinery\Transformation $t)
    {
        if ($this->passed_query_params === null && $this->passed_post_data === null) {
            $w = $this->http->wrapper();
            if ($w->post()->has($key)) {
                return $w->post()->retrieve($key, $t);
            }
            if ($w->query()->has($key)) {
                return $w->query()->retrieve($key, $t);
            }
        }
        if (isset($this->passed_post_data[$key])) {
            return $t->transform($this->passed_post_data[$key]);
        }
        if (isset($this->passed_query_params[$key])) {
            return $t->transform($this->passed_query_params[$key]);
        }
        return null;
    }

    //
    // General exercise and assignment related
    //

    /**
     * @return int[]
     */
    protected function getIds() : array
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
    public function getAssignmentIds() : array
    {
        return $this->getIds();
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getAssId() : int
    {
        return $this->int("ass_id");
    }

    /**
     * @return int[]
     */
    public function getAssIds() : array
    {
        return $this->intArray("ass");
    }

    public function getAssIdGoto() : int
    {
        return $this->int("ass_id_goto");
    }

    /**
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function getExercise() : ?\ilObjExercise
    {
        if ($this->getRefId() > 0 && \ilObject::_lookupType($this->getRefId(), true) == "exc") {
            return new \ilObjExercise($this->getRefId());
        }
        return null;
    }

    /**
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function getAssignment() : ?\ilExAssignment
    {
        if ($this->getAssId() > 0) {
            return new \ilExAssignment($this->getAssId());
        }
        return null;
    }

    public function getAssType() : string
    {
        return $this->str("ass_type");
    }

    // also assignment type? see ilExAssignmentEditor
    public function getType() : int
    {
        return $this->int("type");
    }

    /**
     * @return int[]
     */
    public function getSelectedAssignments() : array
    {
        return $this->intArray("sel_ass_ids");
    }

    /**
     * @return int[]
     */
    public function getListedAssignments() : array
    {
        return $this->intArray("listed_ass_ids");
    }

    //
    // User related
    //

    public function getMemberId() : int
    {
        return $this->int("member_id");
    }

    // can me merged with member id?
    public function getParticipantId() : int
    {
        return $this->int("part_id");
    }

    public function getUserId() : int
    {
        return $this->int("user_id");
    }

    public function getUserLogin() : string
    {
        return trim($this->str("user_login"));
    }

    /**
     * @return int[]
     */
    public function getSelectedParticipants() : array
    {
        return $this->intArray("sel_part_ids");
    }

    /**
     * @return int[]
     */
    public function getListedParticipants() : array
    {
        return $this->intArray("listed_part_ids");
    }

    /**
     * @return int[]
     */
    public function getGroupMembers() : array
    {
        return $this->intArray("grpt");
    }

    //
    // File related
    //

    public function getOldName() : string
    {
        return $this->str("old_name");
    }

    public function getNewName() : string
    {
        return $this->str("new_name");
    }

    /**
     * @return string[]
     */
    public function getFiles() : array
    {
        return $this->strArray("file");
    }

    public function getFile() : string
    {
        return $this->str("file");
    }

    //
    // Individual deadline related
    //

    // sie ilExcIdl.js
    public function getDone() : bool
    {
        return (bool) $this->int("dn");
    }

    public function getIdlId() : string
    {
        return $this->str("idlid");   // may be comma separated
    }

    /**
     * @return string[]
     */
    public function getListedIdlIDs() : array
    {
        return $this->strArray("listed_idl_ids");
    }

    //
    // Table / Filter related
    //

    public function getOffset() : int
    {
        return $this->int("offset");
    }

    public function getSortOrder() : string
    {
        return $this->str("sort_order");
    }

    public function getSortBy() : string
    {
        return $this->str("sort_by");
    }

    public function getFilterStatus() : string
    {
        return trim($this->str("requested_filter_status"));
    }

    public function getFilterFeedback() : string
    {
        return trim($this->str("requested_filter_feedback"));
    }

    //
    // Workspace related
    //

    public function getSelectedWspObjId() : int
    {
        return $this->int("sel_wsp_obj");
    }

    //
    // Peer review related
    //

    public function getReviewGiverId() : int
    {
        $giver_peer_id = $this->str("fu");
        $parts = explode("__", $giver_peer_id);
        if (count($parts) > 1) {
            return (int) $parts[0];
        }
        return 0;
    }

    public function getReviewPeerId() : int
    {
        $giver_peer_id = $this->str("fu");
        $parts = explode("__", $giver_peer_id);
        if (count($parts) > 1) {
            return (int) $parts[1];
        }

        return 0;
    }

    public function getReviewCritId() : string
    {
        $giver_peer_id = $this->str("fu");
        $parts = explode("__", $giver_peer_id);
        if (isset($parts[2])) {
            return (string) $parts[2];
        }
        return "";
    }

    // different from "fu" parameter above!
    public function getPeerId() : int
    {
        return $this->int("peer_id");
    }

    // different from "fu" parameter above!
    public function getCritId() : string
    {
        return $this->str("crit_id");
    }

    // peer review files?
    public function getFileHash() : string
    {
        return trim($this->str("fuf"));
    }

    /**
     * @return int[]
     */
    public function getCatalogueIds() : array
    {
        return $this->getIds();
    }

    public function getCatalogueId() : int
    {
        return $this->int("cat_id");
    }

    /**
     * @return int[]
     */
    public function getCriteriaIds() : array
    {
        return $this->getIds();
    }


    //
    // Team related
    //

    /**
     * @return int[]
     */
    public function getTeamIds() : array
    {
        return $this->getIds();
    }

    //
    // Order / positions related
    //

    /**
     * @return int[]
     */
    public function getOrder() : array
    {
        return $this->intArray("order");
    }

    /**
     * @return int[]
     */
    public function getPositions() : array
    {
        return $this->intArray("pos");
    }

    //
    // Text related
    //

    public function getMinCharLimit() : int
    {
        return $this->int("min_char_limit");
    }

    //
    // Status / LP related
    //

    /**
     * @return string[]
     */
    public function getLearningComments() : array
    {
        return $this->strArray("lcomment");
    }

    /**
     * key might be ass_ids or user_ids!
     * @return string[]
     */
    public function getMarks() : array
    {
        return $this->strArray("mark");
    }

    /**
     * key might be ass_ids or user_ids!
     * @return string[]
     */
    public function getTutorNotices() : array
    {
        return $this->strArray("notice");
    }

    /**
     * key might be ass_ids or user_ids!
     * @return string[]
     */
    public function getStatus() : array
    {
        return $this->strArray("status");
    }

    public function getComment() : string
    {
        return $this->str("comment");
    }

    public function getRatingValue() : string
    {
        return $this->str("value");
    }

    /**
     * @return int[]
     */
    public function getSubmittedFileIds() : array
    {
        return $this->intArray("delivered");
    }

    public function getSubmittedFileId() : int
    {
        return $this->int("delivered");
    }

    public function getResourceObjectId() : int
    {
        return $this->int("item");
    }

    public function getBlogId() : int
    {
        return $this->int("blog_id");
    }

    public function getPortfolioId() : int
    {
        return $this->int("prtf_id");
    }

    public function getBackView() : int
    {
        return $this->int("vw");
    }
}
