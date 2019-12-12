<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Internal service factory
 *
 * @author killing@leifos.de
 */
class ilExerciseInternalFactory
{
    /**
     * @var ilExerciseUIRequest
     */
    protected $request;

    /**
     * @var ilExerciseUI
     */
    protected $ui;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Internal business logic stuff
     * @return ilExerciseInternalService
     */
    public function service()
    {
        return new ilExerciseInternalService();
    }

    /**
     * Get request
     *
     * @return ilExerciseUIRequest
     */
    public function request($query_params = null, $post_data = null)
    {
        if ($query_params === null) {
            $query_params = $_GET;
        }
        if ($post_data === null) {
            $post_data = $_POST;
        }
        return new ilExerciseUIRequest($query_params, $post_data);
    }

    /**
     * Get ui
     *
     * @return ilExerciseUI
     */
    public function ui($query_params = null, $post_data = null)
    {
        return new ilExerciseUI($this->service(), $this->request($query_params, $post_data));
    }
}
