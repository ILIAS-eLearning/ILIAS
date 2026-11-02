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

namespace ILIAS\FileServices\Legacy;

/**
 * The part of the repository tree the download jobs walk when they collect
 * files. Goes away once Tree is wired through the component bootstrap.
 *
 * @internal
 */
class RepositoryTreeProxy
{
    /**
     * @param string[] $types
     * @return array<int, array<string, mixed>>
     */
    public function getChildsByTypeFilter(int $ref_id, array $types): array
    {
        return $this->tree()->getChildsByTypeFilter($ref_id, $types);
    }

    private function tree(): \ilTree
    {
        global $DIC;
        return $DIC->repositoryTree();
    }
}
