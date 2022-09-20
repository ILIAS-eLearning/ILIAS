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

namespace ILIAS\User;

use ILIAS\Repository\BaseGUIRequest;

class ProfileGUIRequest
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

    public function getUserId(): int
    {
        $user_id = $this->int("user_id");
        if ($user_id == 0) {
            $user_id = $this->int("user");
        }
        return $user_id;
    }

    public function getBackUrl(): string
    {
        return $this->str("back_url");
    }

    public function getBaseClass(): string
    {
        return $this->str("baseClass");
    }

    public function getPrompted(): int
    {
        return $this->int("prompted");
    }

    public function getOsdId(): int
    {
        return $this->int("osd_id");
    }

    public function getFieldId(): string
    {
        return $this->str("f");
    }

    public function getTerm(): string
    {
        return $this->str("term");
    }

    public function getUserFileCapture(): string
    {
        return $this->str("userfile_capture");
    }
}
