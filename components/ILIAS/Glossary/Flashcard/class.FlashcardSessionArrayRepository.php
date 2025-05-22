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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class FlashcardSessionArrayRepository implements FlashcardSessionRepositoryInterface
{
    protected array $session = [];

    public function __construct()
    {
    }

    public function setInitialTerms(int $glo_id, int $user_id, int $box_nr, array $initial_terms): void
    {
        $key = self::KEY_BASE . $glo_id . "_" . $user_id . "_" . $box_nr . "_initial_terms";
        $this->session[$key] = $initial_terms;
    }

    /**
     * @return int[]
     */
    public function getInitialTerms(int $glo_id, int $user_id, int $box_nr): array
    {
        $key = self::KEY_BASE . $glo_id . "_" . $user_id . "_" . $box_nr . "_initial_terms";
        if (isset($this->session[$key])) {
            return $this->session[$key];
        }
        return [];
    }

    public function setTerms(int $glo_id, int $user_id, int $box_nr, array $terms): void
    {
        $key = self::KEY_BASE . $glo_id . "_" . $user_id . "_" . $box_nr . "_terms";
        $this->session[$key] = $terms;
    }

    /**
     * @return int[]
     */
    public function getTerms(int $glo_id, int $user_id, int $box_nr): array
    {
        $key = self::KEY_BASE . $glo_id . "_" . $user_id . "_" . $box_nr . "_terms";
        if (isset($this->session[$key])) {
            return $this->session[$key];
        }
        return [];
    }
}
