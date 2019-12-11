<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User prompt dates
 *
 * @author @leifos.de
 * @ingroup
 */
class ilProfileUserPrompt
{
    /**
     * @var string
     */
    protected $last_prompt;	// timestamp

    /**
     * @var string
     */
    protected $first_login; // timestamp

    /**
     * @var int
     */
    protected $user_id;

    /**
     * Constructor
     */
    public function __construct($user_id, $last_prompt, $first_login)
    {
        $this->user_id = $user_id;
        $this->last_prompt = $last_prompt;
        $this->first_login = $first_login;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getLastPrompt()
    {
        return $this->last_prompt;
    }

    /**
     * @return string
     */
    public function getFirstLogin()
    {
        return $this->first_login;
    }
}
