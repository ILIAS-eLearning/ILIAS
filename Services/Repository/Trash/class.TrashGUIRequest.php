<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Repository\Trash;

use ILIAS\Repository;

class TrashGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getTrashIds() : array
    {
        $trash_ids = $this->intArray("trash_id");
        if (count($trash_ids) > 0) {
            return $trash_ids;
        }
        $trash_ids = $this->str("trash_ids");
        if ($trash_ids === "") {
            return [];
        }

        return explode(",", $trash_ids);
    }
}
