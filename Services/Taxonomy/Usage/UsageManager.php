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

namespace ILIAS\Taxonomy\Usage;

class UsageManager
{
    protected UsageDBRepository $db_repo;

    public function __construct(
        UsageDBRepository $db_repo
    ) {
        $this->db_repo = $db_repo;
    }

    public function getUsageOfObject(int $obj_id, bool $include_titles = false): array
    {
        return $this->db_repo->getUsageOfObject($obj_id, $include_titles);
    }
}
