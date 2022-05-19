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

namespace ILIAS\MediaPool\Clipboard;

use ILIAS\Repository\BaseGUIRequest;

class ClipboardGUIRequest
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

    /** @return string[] */
    public function getItemIds() : array
    {
        return $this->strArray("id");
    }

    public function getPCId() : string
    {
        return $this->str("pcid");
    }

    public function getReturnCmd() : string
    {
        return $this->str("returnCommand");
    }

    public function getItemId() : int
    {
        return $this->int("clip_item_id");
    }
}
