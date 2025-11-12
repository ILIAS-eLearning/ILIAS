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

namespace ILIAS\StaticURL\Shortlinks\Shortlink\Target;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ILIASTypeDataResolver implements TypeDataResolver
{
    public function __construct(
        private \ilTree $tree
    ) {
    }

    public function resolveForRefId(int $ref_id): RepoTypeData
    {
        $tree_info = $this->tree->getNodeData($ref_id);

        return new RepoTypeData(
            $tree_info['type'],
            (int) $tree_info['ref_id'],
        );
    }

}
