<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Exercise ui request
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseUIRequest
{
    protected int $requested_ref_id;
    protected int $requested_ass_id;
    protected int $requested_member_id;
    protected ?ilExAssignment $ass = null;
    protected ?ilObjExercise $exc = null;
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
     * @throws ilExcUnknownAssignmentTypeException
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
        $this->requested_member_id = $this->int("member_id");

        if ($this->getRequestedAssId() > 0) {
            $this->ass = new ilExAssignment($this->getRequestedAssId());
        }
        if ($this->getRequestedRefId() > 0 && ilObject::_lookupType($this->getRequestedRefId(), true) == "exc") {
            $this->exc = new ilObjExercise($this->getRequestedRefId());
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

    public function getRequestedMemberId() : int
    {
        return $this->requested_member_id;
    }

    public function getRequestedExercise() : ?ilObjExercise
    {
        return $this->exc;
    }

    public function getRequestedAssignment() : ?ilExAssignment
    {
        return $this->ass;
    }
}
