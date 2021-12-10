<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Execution;

/**
 * Code data class
 * @author Alexander Killing <killing@leifos.de>
 */
class Run
{
    /**
     * @var int
     */
    protected $id = "";

    /**
     * @var int
     */
    protected $survey_id = 0;

    /**
     * @var int
     */
    protected $user_id = 0;

    /**
     * @var string
     */
    protected $code = "";

    /**
     * @var bool
     */
    protected $finished = false;

    /**
     * @var int
     */
    protected $tstamp = 0;

    /**
     * @var int
     */
    protected $lastpage = 0;

    /**
     * @var int
     */
    protected $appraisee_id = 0;

    /**
     * Constructor
     */
    public function __construct(
        int $survey_id,
        int $user_id
    ) {
        $this->survey_id = $survey_id;
        $this->user_id = $user_id;
    }

    public function getCode() : string
    {
        return $this->code;
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

    public function getTimestamp() : int
    {
        return $this->tstamp;
    }

    public function getFinished() : bool
    {
        return $this->finished;
    }

    public function getLastPage() : int
    {
        return $this->lastpage;
    }

    public function getAppraiseeId() : int
    {
        return $this->appraisee_id;
    }

    public function withId(int $id) : self
    {
        $run = clone $this;
        $run->id = $id;
        return $run;
    }

    public function withSurveyId(int $id) : self
    {
        $run = clone $this;
        $run->survey_id = $id;
        return $run;
    }

    public function withUserId(int $user_id) : self
    {
        $run = clone $this;
        $run->user_id = $user_id;
        return $run;
    }

    public function withTimestamp(int $tstamp) : self
    {
        $run = clone $this;
        $run->tstamp = $tstamp;
        return $run;
    }

    public function withCode(string $code) : self
    {
        $run = clone $this;
        $run->code = $code;
        return $run;
    }

    public function withFinished(bool $finished) : self
    {
        $run = clone $this;
        $run->finished = $finished;
        return $run;
    }

    public function withLastPage(int $last_page) : self
    {
        $run = clone $this;
        $run->lastpage = $last_page;
        return $run;
    }

    public function withAppraiseeId(int $appr_id) : self
    {
        $run = clone $this;
        $run->appraisee_id = $appr_id;
        return $run;
    }
}
