<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Code;

/**
 * Code data class
 * @author Alexander Killing <killing@leifos.de>
 */
class Code
{
    /**
     * @var string
     */
    protected $code = "";

    /**
     * @var string
     */
    protected $user_key = null;

    /**
     * @var string
     */
    protected $email = "";

    /**
     * @var string
     */
    protected $last_name = "";

    /**
     * @var string
     */
    protected $first_name = "";

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $user_id = 0;

    /**
     * @var int
     */
    protected $survey_id = 0;

    /**
     * @var int
     */
    protected $tstamp = 0;

    /**
     * @var int
     */
    protected $sent = 0;

    /**
     * Constructor
     */
    public function __construct(
        string $code
    ) {
        $this->code = $code;
    }

    public function getCode() : string
    {
        return $this->code;
    }

    public function getFirstName() : string
    {
        return $this->first_name;
    }

    public function getLastName() : string
    {
        return $this->last_name;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getUserKey() : string
    {
        return $this->user_key;
    }

    public function getSurveyId() : int
    {
        return $this->survey_id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getSent() : int
    {
        return $this->sent;
    }

    public function getTimestamp() : int
    {
        return $this->tstamp;
    }

    public function withId(int $id) : self
    {
        $code = clone $this;
        $code->id = $id;
        return $code;
    }

    public function withSurveyId(int $id) : self
    {
        $code = clone $this;
        $code->survey_id = $id;
        return $code;
    }

    public function withUserKey(string $user_key) : self
    {
        $code = clone $this;
        $code->user_key = $user_key;
        return $code;
    }

    public function withUserId(int $user_id) : self
    {
        $code = clone $this;
        $code->user_id = $user_id;
        return $code;
    }

    public function withTimestamp(int $tstamp) : self
    {
        $code = clone $this;
        $code->tstamp = $tstamp;
        return $code;
    }

    public function withSent(int $sent) : self
    {
        $code = clone $this;
        $code->sent = $sent;
        return $code;
    }

    public function withEmail(string $email) : self
    {
        $code = clone $this;
        $code->email = $email;
        return $code;
    }

    public function withFirstName(string $first_name) : self
    {
        $code = clone $this;
        $code->first_name = $first_name;
        return $code;
    }

    public function withLastName(string $last_name) : self
    {
        $code = clone $this;
        $code->last_name = $last_name;
        return $code;
    }
}
