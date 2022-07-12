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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWorkspaceFolder extends ilObject2
{
    public ilWorkspaceTree $folder_tree;
    protected ilObjUser $current_user;

    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;

        parent::__construct($a_id, $a_reference);

        $this->current_user = $DIC->user();
    }

    protected function initType() : void
    {
        $this->type = "wfld";
    }

    public function setFolderTree(ilWorkspaceTree $a_tree) : void
    {
        $this->folder_tree = $a_tree;
    }
    

    /**
     * Get container view mode
     */
    public function getViewMode() : int
    {
        return ilContainer::VIEW_BY_TYPE;
    }

    /**
     * Add additional information to sub item, e.g. used in
     * courses for timings information etc.
     */
    public function addAdditionalSubItemInformation(array &$a_item_data) : void
    {
    }

    public function gotItems(int $node_id) : bool
    {
        $tree = new ilWorkspaceTree($this->current_user->getId());
        $nodes = $tree->getChilds($node_id, "title");

        if (sizeof($nodes)) {
            return true;
        }
        return false;
    }
}
