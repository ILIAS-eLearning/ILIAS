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

namespace ILIAS\Glossary;

use ILIAS\Glossary\Settings\Settings;

class InternalDataService
{
    protected Flashcard\DataFactory $flashcard_factory;

    public function __construct()
    {
        $this->flashcard_factory = new Flashcard\DataFactory();
    }

    public function flashcardTerm(
        int $term_id,
        int $user_id,
        int $glo_id,
        int $box_nr,
        ?string $last_access = null
    ): Flashcard\Term {
        return $this->flashcard_factory->term($term_id, $user_id, $glo_id, $box_nr, $last_access);
    }

    public function flashcardBox(
        int $box_nr,
        int $user_id,
        int $glo_id,
        ?string $last_access = null
    ): Flashcard\Box {
        return $this->flashcard_factory->box($box_nr, $user_id, $glo_id, $last_access);
    }

    public function settings(
        int $id,
        bool $online,
        string $virtual,
        bool $glo_menu_active,
        string $pres_mode,
        int $show_tax,
        int $snippet_length,
        bool $flash_active,
        string $flash_mode
    ): Settings {
        return new Settings(
            $id,
            $online,
            $virtual,
            $glo_menu_active,
            $pres_mode,
            $show_tax,
            $snippet_length,
            $flash_active,
            $flash_mode
        );
    }

}
