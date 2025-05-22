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

namespace ILIAS\Glossary\Flashcard;

class Term
{
    public function __construct(
        protected int $term_id,
        protected int $user_id,
        protected int $glo_id,
        protected int $box_nr,
        protected ?string $last_access = null
    ) {

    }

    public function getTermId(): int
    {
        return $this->term_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getGloId(): int
    {
        return $this->glo_id;
    }

    public function getBoxNr(): int
    {
        return $this->box_nr;
    }

    public function getLastAccess(): ?string
    {
        return $this->last_access;
    }
}
