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

interface FlashcardSessionRepositoryInterface
{
    public const KEY_BASE = "glo_flashcard_";

    public function setInitialTerms(int $glo_id, int $user_id, int $box_nr, array $initial_terms): void;

    public function getInitialTerms(int $glo_id, int $user_id, int $box_nr): array;

    public function setTerms(int $glo_id, int $user_id, int $box_nr, array $terms): void;

    public function getTerms(int $glo_id, int $user_id, int $box_nr): array;
}
