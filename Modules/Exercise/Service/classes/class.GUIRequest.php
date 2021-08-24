<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Exercise gui request wrapper
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIRequest
{
    protected int $requested_ref_id;
    protected int $requested_ass_id;
    protected int $requested_member_id;
    protected int $requested_part_id;       // check if this can be merged with member id
    protected string $requested_ass_type;
    protected string $requested_old_name;
    protected bool $done;                   // see ilExcIdl.js
    protected string $requested_idl_id;
    protected int $requested_user_id;
    protected string $requested_sort_order;
    protected string $requested_sort_by;
    protected int $requested_offset;
    protected int $requested_ass_id_goto;
    protected int $selected_wsp_obj_id;
    protected ?\ilExAssignment $ass = null;
    protected ?\ilObjExercise $exc = null;
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

        $this->requested_ref_id = $this->int("ref_id");
        $this->requested_ass_id = $this->int("ass_id");
        $this->requested_ass_id_goto = $this->int("ass_id_goto");
        $this->requested_ass_type = $this->str("ass_type");
        $this->requested_member_id = $this->int("member_id");
        $this->requested_part_id = $this->int("part_id");
        $this->requested_ass_type = $this->str("old_name");
        $this->requested_user_id = $this->int("user_id");
        $this->requested_sort_order = $this->str("sort_order");
        $this->requested_sort_by = $this->str("sort_by");
        $this->requested_offset = $this->int("offset");
        $this->done = (bool) $this->int("dn");
        $this->requested_idl_id = $this->str("idlid");   // may be comma separated
        $this->selected_wsp_obj_id = $this->int("sel_wsp_obj");

        if ($this->getRequestedAssId() > 0) {
            $this->ass = new \ilExAssignment($this->getRequestedAssId());
        }
        if ($this->getRequestedRefId() > 0 && \ilObject::_lookupType($this->getRequestedRefId(), true) == "exc") {
            $this->exc = new \ilObjExercise($this->getRequestedRefId());
        }
    }

    // get integer parameter kindly
    protected function int($key) : int
    {
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->get($key, $t) ?? 0);
    }

    // get string parameter kindly
    protected function str($key) : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return (string) ($this->get($key, $t) ?? "");
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
            return $this->passed_post_data[$key];
        }
        if (isset($this->passed_query_params[$key])) {
            return $this->passed_query_params[$key];
        }
        return null;
    }

    public function getRequestedRefId() : int
    {
        return $this->requested_ref_id;
    }

    public function getRequestedAssId() : int
    {
        return $this->requested_ass_id;
    }

    public function getRequestedAssIdGoto() : int
    {
        return $this->requested_ass_id_goto;
    }

    public function getRequestedMemberId() : int
    {
        return $this->requested_member_id;
    }

    public function getRequestedParticipantId() : int
    {
        return $this->requested_part_id;
    }

    public function getRequestedExercise() : ?\ilObjExercise
    {
        return $this->exc;
    }

    public function getRequestedAssignment() : ?\ilExAssignment
    {
        return $this->ass;
    }

    public function getRequestedAssType() : string
    {
        return $this->requested_ass_type;
    }

    public function getRequestedOldName() : string
    {
        return $this->requested_old_name;
    }

    public function getDone() : bool
    {
        return $this->done;
    }

    public function getRequestedIdlId() : bool
    {
        return $this->requested_idl_id;
    }

    public function getRequestedUserId() : int
    {
        return $this->requested_user_id;
    }

    public function getRequestedOffset() : int
    {
        return $this->requested_offset;
    }

    public function getRequestedSortOrder() : string
    {
        return $this->requested_sort_order;
    }

    public function getRequestedSortBy() : string
    {
        return $this->requested_sort_by;
    }

    public function getSelectedWspObjId() : string
    {
        return $this->selected_wsp_obj_id;
    }
}
