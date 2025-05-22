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

declare(strict_types=0);

/**
 * Class ilLPStatusCmiXapiCompletedOrPassed
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusCmiXapiCompletedOrPassed extends ilLPStatusCmiXapiAbstract
{
    protected function resultSatisfyCompleted(ilCmiXapiResult $result): bool
    {
        if ($result->getStatus() === 'completed') {
            return true;
        }

        if ($result->getStatus() === 'passed') {
            return true;
        }

        return false;
    }

    protected function resultSatisfyFailed(ilCmiXapiResult $result): bool
    {
        return false;
    }
}
