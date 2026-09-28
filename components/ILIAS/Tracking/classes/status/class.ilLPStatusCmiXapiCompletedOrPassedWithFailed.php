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

use ILIAS\DI\Container;

class ilLPStatusCmiXapiCompletedOrPassedWithFailed extends ilLPStatusCmiXapiCompletedOrPassed
{
    protected const string LNG_TEXT = 'trac_mode_cmix_compl_or_passed_with_failed';
    protected const string LNG_TEXT_INFO = 'trac_mode_cmix_compl_or_passed_with_failed_info';
    protected ilLanguage $lng;

    protected function resultSatisfyFailed(ilCmiXapiResult $result): bool
    {
        if ($result->getStatus() === 'failed') {
            return true;
        }
        return false;
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }


    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return $this->lng->txt(self::LNG_TEXT_INFO);
    }
}
