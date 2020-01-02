<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeDumpTask extends ilSCTask
{

    /**
     * Check if task is active
     * @return bool
     */
    public function isActive()
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        if ($tree->getTreeImplementation() instanceof \ilMaterializedPathTree) {
            return false;
        }
        return true;
    }
}
