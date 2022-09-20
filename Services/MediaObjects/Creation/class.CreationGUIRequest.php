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

namespace ILIAS\MediaObjects\Creation;

use ILIAS\Repository\BaseGUIRequest;

class CreationGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getMediaPoolId(): int
    {
        return $this->int("mep");
    }

    public function getSelectedMediaPoolRefId(): int
    {
        return $this->int("mep_ref_id");
    }

    public function getPoolView(): string
    {
        return $this->str("pool_view");
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->intArray("id");
    }

    public function getUploadHash(): string
    {
        $hash = $this->str("mep_hash");
        if ($hash === "") {
            $hash = $this->str("ilfilehash");
        }
        return $hash;
    }
}
