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

namespace ILIAS\Exercise;

use ILIAS\Exercise\Assignment\Assignment;

/**
 * Internal factory for data objects
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDataService
{
    //protected SubService\DataFactory $sub_factory;

    public function __construct()
    {
    }

    public function assignment(
        int $id,
        int $exc_id,
        string $title,
        int $order_nr,
        int $type,
        string $instructions,
        bool $mandatory,
        int $deadline_mode,
        int $deadline,
        int $deadline2,
        int $relative_deadline,
        int $rel_deadline_last_submission
    ): Assignment {
        return new Assignment(
            $id,
            $exc_id,
            $title,
            $order_nr,
            $type,
            $instructions,
            $mandatory,
            $deadline_mode,
            $deadline,
            $deadline2,
            $relative_deadline,
            $rel_deadline_last_submission
        );
    }
}
