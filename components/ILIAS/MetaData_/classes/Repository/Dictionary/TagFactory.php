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

namespace ILIAS\MetaData\Repository\Dictionary;

class TagFactory
{
    public function tag(
        string $create,
        string $read,
        string $update,
        string $delete,
        bool $is_parent,
        string $table,
        ExpectedParameter ...$expected_parameters
    ): TagInterface {
        return new Tag(
            $create,
            $read,
            $update,
            $delete,
            $is_parent,
            $table,
            ...$expected_parameters
        );
    }
}
