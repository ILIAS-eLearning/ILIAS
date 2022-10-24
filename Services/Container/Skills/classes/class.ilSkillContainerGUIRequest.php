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
 ********************************************************************
 */

use ILIAS\Skill\Service\SkillGUIRequest;

/**
 * Request wrapper for skill guis in container classes. This class processes
 * all request parameters which are not handled by form classes already.
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillContainerGUIRequest extends SkillGUIRequest
{
    public function __construct(
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        global $DIC;

        $http = $DIC->http();
        $refinery = $DIC->refinery();

        parent::__construct($http, $refinery, $passed_query_params, $passed_post_data);
    }

    public function getUserId(): int
    {
        return $this->int("usr_id");
    }

    public function getUserIds(): array
    {
        return $this->intArray("usr_id");
    }

    public function getSelectedSkill(): string
    {
        return $this->str("selected_skill");
    }

    public function getCombinedSkillIds(): array
    {
        return $this->strArray("id");
    }

    public function getSelectedProfileId(): int
    {
        return $this->int("p_id");
    }

    public function getProfileIds(): array
    {
        return $this->getIds();
    }
}
