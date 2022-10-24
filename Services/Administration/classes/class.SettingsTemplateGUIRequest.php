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

namespace ILIAS\Administration;

use ILIAS\Repository;

class SettingsTemplateGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getTemplateId(): int
    {
        return $this->int("templ_id");
    }

    public function getTab(string $id): int
    {
        return $this->int("tab_" . $id);
    }

    public function getSetting(string $id): int
    {
        return $this->int("set_" . $id);
    }

    public function getValue(string $id): string
    {
        return $this->str("value_" . $id);
    }

    public function getHide(string $id): bool
    {
        return (bool) $this->int("hide_" . $id);
    }

    /** @return int[] */
    public function getTemplateIds(): array
    {
        return $this->intArray("tid");
    }
}
