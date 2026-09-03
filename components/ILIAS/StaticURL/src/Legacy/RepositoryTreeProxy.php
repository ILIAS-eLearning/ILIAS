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

namespace ILIAS\StaticURL\Legacy;

/**
 * The part of the repository tree {@see \ILIAS\StaticURL\Context} walks when it
 * looks for an accessible parent. Goes away once Tree is wired through the
 * component bootstrap.
 *
 * @internal
 */
class RepositoryTreeProxy
{
    public function isInTree(?int $ref_id): bool
    {
        return $this->tree()->isInTree($ref_id);
    }

    public function getParentId(int $ref_id): ?int
    {
        return $this->tree()->getParentId($ref_id);
    }

    public function getRootId(): int
    {
        return $this->tree()->getRootId();
    }

    private function tree(): \ilTree
    {
        global $DIC;
        return $DIC->repositoryTree();
    }
}
