<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class LegacyPathHelperHelper
{
    public function createRelativePath($absTargetDir)
    {
        return ILIAS\Filesystem\Util\LegacyPathHelper::createRelativePath($absTargetDir);
    }
}
