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

namespace ILIAS\COPage\PC;

use ILIAS\Repository\BaseGUIRequest;

/**
 * Page component editing request. Includes common and generic request
 * parameter handling.
 */
class EditGUIRequest
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

    public function getSubCmd(): string
    {
        return $this->str("subCmd");
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getHierId(): string
    {
        return $this->str("hier_id");
    }

    public function getPCId(): string
    {
        $pc_id = $this->str("pcid");
        if ($pc_id == "") {
            $pc_id = $this->str("pc_id");   // e.g. PCAMDFormGUI
        }
        return $pc_id;
    }

    public function getInt(string $key): int
    {
        return $this->int($key);
    }

    public function getString(string $key): string
    {
        return $this->str($key);
    }

    public function getRaw(string $key): string
    {
        return $this->raw($key);
    }

    /**
     * @return int[]
     */
    public function getIntArray(string $key): array
    {
        return $this->intArray($key);
    }

    /**
     * @return string[]
     */
    public function getStringArray(string $key): array
    {
        return $this->strArray($key);
    }


    /**
     * @return array[]
     */
    public function getArrayArray(string $key): array
    {
        return $this->arrayArray($key);
    }
}
