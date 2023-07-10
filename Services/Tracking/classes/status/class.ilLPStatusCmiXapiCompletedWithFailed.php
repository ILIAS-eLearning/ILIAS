<?php

declare(strict_types=0);

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class ilLPStatusCmiXapiCompletedWithFailed
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusCmiXapiCompletedWithFailed extends ilLPStatusCmiXapiCompleted
{
    protected function resultSatisfyFailed(ilCmiXapiResult $result): bool
    {
        if ($result->getStatus() === 'failed') {
            return true;
        }

        return false;
    }
}
