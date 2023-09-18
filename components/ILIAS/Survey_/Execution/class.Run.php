<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Survey\Execution;

/**
 * Code data class
 * @author Alexander Killing <killing@leifos.de>
 */
class Run
{
    protected ?int $id = null;
    protected int $survey_id = 0;
    protected int $user_id = 0;
    protected string $code = "";
    protected bool $finished = false;
    protected int $tstamp = 0;
    protected int $lastpage = 0;
    protected int $appraisee_id = 0;

    public function __construct(
        int $survey_id,
        int $user_id
    ) {
        $this->survey_id = $survey_id;
        $this->user_id = $user_id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSurveyId(): int
    {
        return $this->survey_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTimestamp(): int
    {
        return $this->tstamp;
    }

    public function getFinished(): bool
    {
        return $this->finished;
    }

    public function getLastPage(): int
    {
        return $this->lastpage;
    }

    public function getAppraiseeId(): int
    {
        return $this->appraisee_id;
    }

    public function withId(int $id): self
    {
        $run = clone $this;
        $run->id = $id;
        return $run;
    }

    public function withSurveyId(int $id): self
    {
        $run = clone $this;
        $run->survey_id = $id;
        return $run;
    }

    public function withUserId(int $user_id): self
    {
        $run = clone $this;
        $run->user_id = $user_id;
        return $run;
    }

    public function withTimestamp(int $tstamp): self
    {
        $run = clone $this;
        $run->tstamp = $tstamp;
        return $run;
    }

    public function withCode(string $code): self
    {
        $run = clone $this;
        $run->code = $code;
        return $run;
    }

    public function withFinished(bool $finished): self
    {
        $run = clone $this;
        $run->finished = $finished;
        return $run;
    }

    public function withLastPage(int $last_page): self
    {
        $run = clone $this;
        $run->lastpage = $last_page;
        return $run;
    }

    public function withAppraiseeId(int $appr_id): self
    {
        $run = clone $this;
        $run->appraisee_id = $appr_id;
        return $run;
    }
}
