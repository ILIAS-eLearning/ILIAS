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

namespace ILIAS\Glossary\Flashcard;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class DataFactory
{
    public function __construct()
    {
    }

    public function term(
        int $term_id,
        int $user_id,
        int $glo_id,
        int $box_nr,
        ?string $last_access = null
    ): Term {
        return new Term($term_id, $user_id, $glo_id, $box_nr, $last_access);
    }

    public function box(
        int $box_nr,
        int $user_id,
        int $glo_id,
        ?string $last_access = null
    ): Box {
        return new Box($box_nr, $user_id, $glo_id, $last_access);
    }
}
