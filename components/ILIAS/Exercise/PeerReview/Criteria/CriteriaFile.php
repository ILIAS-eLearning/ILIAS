<?php

declare(strict_types=1);

namespace ILIAS\Exercise\PeerReview\Criteria;

class CriteriaFile
{
    public function __construct(
        protected int $ass_id,
        protected int $giver_id,
        protected int $peer_id,
        protected int $criteria_id,
        protected string $rid,
        protected string $title
    ) {
    }

    public function getAssId(): int
    {
        return $this->ass_id;
    }

    public function getGiverId(): int
    {
        return $this->giver_id;
    }

    public function getPeerId(): int
    {
        return $this->peer_id;
    }

    public function getCriteriaId(): int
    {
        return $this->criteria_id;
    }

    public function getRid(): string
    {
        return $this->rid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
