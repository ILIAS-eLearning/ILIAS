<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use \Psr\Http\Message\ServerRequestInterface;

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

    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
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
        return new ilExerciseUIRequest(
            $this->http,
            $this->refinery,
            $query_params,
            $post_data
        );
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
