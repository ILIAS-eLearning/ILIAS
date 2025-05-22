<?php

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

declare(strict_types=1);

namespace ILIAS\Exercise\Submission;

class Submission
{
    protected int $id;
    protected int $ass_id;
    protected int $user_id;
    protected int $team_id;
    protected string $title;
    protected string $text;
    protected string $rid;
    protected string $mimetype;
    protected string $timestamp;
    protected bool $late;

    public function __construct(
        int $id,
        int $ass_id,
        int $user_id,
        int $team_id,
        string $title,
        string $text,
        string $rid,
        string $mimetype,
        string $timestamp,
        bool $late
    ) {
        $this->id = $id;
        $this->ass_id = $ass_id;
        $this->user_id = $user_id;
        $this->team_id = $team_id;
        $this->title = $title;
        $this->text = $text;
        $this->rid = $rid;
        $this->mimetype = $mimetype;
        $this->timestamp = $timestamp;
        $this->late = $late;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAssId(): int
    {
        return $this->ass_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTeamId(): int
    {
        return $this->team_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getRid(): string
    {
        return $this->rid;
    }

    public function getMimetype(): string
    {
        return $this->mimetype;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function getTimestamp14(): string
    {
        $ts = $this->getTimestamp();
        return substr($ts, 0, 4) .
            substr($ts, 5, 2) . substr($ts, 8, 2) .
            substr($ts, 11, 2) . substr($ts, 14, 2) .
            substr($ts, 17, 2);
    }

    public function getLate(): bool
    {
        return $this->late;
    }
}
