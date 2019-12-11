<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User profile prompt subservice.
 *
 * Manages info texts on the personal profile and prompting mechanics.
 *
 * Do not use, not an official API yet. Only for user service internals.
 *
 * @author killing@leifos.de
 * @ingroup ServicesUser
 */
class ilUserProfilePromptService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get data gateway
     * @return ilUserProfilePromptDataGateway
     */
    public function data()
    {
        return new ilUserProfilePromptDataGateway();
    }

    /**
     * Get a settings object
     * @param int $mode
     * @param int $days
     * @param array $info_texts
     * @param array $prompt_texts
     * @return ilProfilePromptSettings
     */
    public function settings(int $mode, int $days, array $info_texts, array $prompt_texts)
    {
        return new ilProfilePromptSettings(
            $mode,
            $days,
            $info_texts,
            $prompt_texts
        );
    }
}
