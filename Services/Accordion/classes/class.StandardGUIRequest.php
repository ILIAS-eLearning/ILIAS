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

namespace ILIAS\Accordion;

use ILIAS\Repository\BaseGUIRequest;

class StandardGUIRequest
{
    use BaseGUIRequest;

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

    public function getAction(): string
    {
        return $this->str("act");
    }

    public function getId(): string
    {
        return $this->str("accordion_id");
    }

    public function getTabNr(): int
    {
        return $this->int("tab_nr");
    }

    public function getUserId(): int
    {
        return $this->int("user_id");
    }

    public function getNavPar(string $par): string
    {
        return $this->str($par);
    }

    public function getNavPage(string $par): string
    {
        return $this->str($par . "page");
    }

    public function getColSide(): string
    {
        return $this->str("col_side");
    }

    public function getBlockId(): string
    {
        return $this->str("block_id");
    }

    public function getBlock(): string
    {
        return $this->str("block");
    }
}
