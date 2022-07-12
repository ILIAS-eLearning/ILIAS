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

/**
 * User profile prompt subservice.
 * Manages info texts on the personal profile and prompting mechanics.
 * Do not use, not an official API yet. Only for user service internals.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserProfilePromptService
{
    public function __construct()
    {
    }

    public function data() : ilUserProfilePromptDataGateway
    {
        return new ilUserProfilePromptDataGateway();
    }

    public function settings(
        int $mode,
        int $days,
        array $info_texts,
        array $prompt_texts
    ) : ilProfilePromptSettings {
        return new ilProfilePromptSettings(
            $mode,
            $days,
            $info_texts,
            $prompt_texts
        );
    }
}
