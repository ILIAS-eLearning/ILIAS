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
 * Class ilLPStatusCmiXapiCompleted
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusCmiXapiCompleted extends ilLPStatusCmiXapiAbstract
{
    protected function resultSatisfyCompleted(ilCmiXapiResult $result): bool
    {
        if ($result->getStatus() === 'completed') {
            return true;
        }

        return false;
    }

    protected function resultSatisfyFailed(ilCmiXapiResult $result): bool
    {
        return false;
    }
}
