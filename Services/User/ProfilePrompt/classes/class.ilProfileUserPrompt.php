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
 * User prompt dates
 * @author Alexander Killing <killing@leifos.de>
 */
class ilProfileUserPrompt
{
    protected ?string $last_prompt = null;	// timestamp
    protected ?string $first_login = null; // timestamp
    protected int $user_id;

    public function __construct(
        int $user_id,
        ?string $last_prompt,
        ?string $first_login
    ) {
        $this->user_id = $user_id;
        $this->last_prompt = $last_prompt;
        $this->first_login = $first_login;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getLastPrompt(): ?string
    {
        return $this->last_prompt;
    }

    public function getFirstLogin(): ?string
    {
        return $this->first_login;
    }
}
