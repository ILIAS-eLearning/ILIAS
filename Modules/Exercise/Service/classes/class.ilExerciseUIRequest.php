<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise ui request
 *
 * @author killing@leifos.de
 */
class ilExerciseUIRequest
{
    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * @var int
     */
    protected $requested_ass_id;

    /**
     * @var int
     */
    protected $requested_member_id;

    /**
     * @var ilExAssignment|null
     */
    protected $ass = null;

    /**
     * @var ilObjExercise
     */
    protected $exc = null;

    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;

    protected ?array $passed_query_params;
    protected ?array $passed_post_data;

    /**
     * Constructor
     */
    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
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

    /**
     * @param $key
     * @return int
     */
    protected function int($key) : int
    {
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->get($key, $t) ?? 0);
    }

    /**
     * @param $key
     * @return string
     */
    protected function str($key) : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return (string) ($this->get($key, $t) ?? "");
    }

    /**
     * Get passed parameter, if not data passed, get key from http request
     * @param $key
     * @param $t
     * @return mixed|null
     */
    protected function get($key, $t)
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


    /**
     * @return int
     */
    public function getRequestedRefId() : int
    {
        return $this->requested_ref_id;
    }

    /**
     * @return int
     */
    public function getRequestedAssId() : int
    {
        return $this->requested_ass_id;
    }

    /**
     * @return int
     */
    public function getRequestedMemberId() : int
    {
        return $this->requested_member_id;
    }

    /**
     * @return ilObjExercise|null
     */
    public function getRequestedExercise()
    {
        return $this->exc;
    }

    /**
     * @return ilExAssignment|null
     */
    public function getRequestedAssignment()
    {
        return $this->ass;
    }
}
