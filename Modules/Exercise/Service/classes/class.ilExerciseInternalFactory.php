<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Internal service factory
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseInternalFactory
{
    protected ilExerciseUIRequest $request;
    protected ilExerciseUI $ui;
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    /**
     * Internal service (mostly bl)
     */
    public function service() : ilExerciseInternalService
    {
        return new ilExerciseInternalService();
    }



    /**
     * Get request wrapper. If dummy data is provided the usual http wrapper will
     * not be used.
     * @param null $query_params    dummy query params for testing
     * @param null $post_data       dummy post data for testing
     * @return ilExerciseUIRequest
     */
    public function request($query_params = null, $post_data = null) : ilExerciseUIRequest
    {
        return new ilExerciseUIRequest(
            $this->http,
            $this->refinery,
            $query_params,
            $post_data
        );
    }

    // get ui wrapper
    public function ui($query_params = null, $post_data = null) : ilExerciseUI
    {
        return new ilExerciseUI($this->service(), $this->request($query_params, $post_data));
    }
}
