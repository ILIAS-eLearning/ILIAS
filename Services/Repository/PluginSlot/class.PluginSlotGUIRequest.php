<?php

declare(strict_types=1);

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

namespace ILIAS\Repository\PluginSlot;

use ILIAS\Repository;

class PluginSlotGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getBaseClass(): string
    {
        return $this->str("baseClass");
    }

    public function getUserId(): int
    {
        return $this->int("user_id");
    }

    public function getForwardCmd(): string
    {
        return $this->str("forwardCmd");
    }
}
