<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeDumpTask extends ilSCTask
{
    public function isActive(): bool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        if ($tree->getTreeImplementation() instanceof \ilMaterializedPathTree) {
            return false;
        }
        return true;
    }
}
