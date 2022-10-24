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

/**
 * User settings for workspace folders
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWorkspaceFolderUserSettings
{
    protected ilTree $tree;
    protected int $user_id;
    protected ilWorkspaceFolderUserSettingsRepository $repo;

    public function __construct(
        int $user_id,
        ilWorkspaceFolderUserSettingsRepository $repo,
        ilWorkspaceTree $tree = null
    ) {
        $this->repo = $repo;
        $this->user_id = $user_id;
        $this->tree = ($tree != null)
            ? $tree
            : new ilWorkspaceTree($user_id);
    }

    public function getSortation(int $wfld_id): int
    {
        $sort = $this->repo->getSortation($wfld_id);
        if ($sort > 0) {
            return $sort;
        }
        if (ilObject::_lookupType($wfld_id) == "wfld") {
            return ilWorkspaceFolderSorting::SORT_DERIVED;
        }
        return ilWorkspaceFolderSorting::SORT_ALPHABETICAL_ASC;
    }

    public function updateSortation(int $wfld_id, int $sortation)
    {
        $this->repo->updateSortation($wfld_id, $sortation);
    }

    /**
     * Get effective sortation for a workspace folder (next upper
     * context that has sortation > 0)
     */
    public function getEffectiveSortation(int $wfld_wsp_id): int
    {
        $tree = $this->tree;

        // get path
        $path = $tree->getPathId($wfld_wsp_id);
        // get object ids of path
        $obj_ids = array_map(function ($wsp_id) use ($tree) {
            return $tree->lookupObjectId($wsp_id);
        }, $path);

        // get sortations for object ids
        $sortations = $this->repo->getSortationMultiple($obj_ids);

        // search bottom to top first one with sortation > 0
        foreach (array_reverse($obj_ids) as $id) {
            if ($sortations[$id] > 0) {
                return $sortations[$id];
            }
        }
        return ilWorkspaceFolderSorting::SORT_ALPHABETICAL_ASC;
    }
}
