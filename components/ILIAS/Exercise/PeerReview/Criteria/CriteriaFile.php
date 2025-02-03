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
