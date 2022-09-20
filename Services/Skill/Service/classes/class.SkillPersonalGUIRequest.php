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

namespace ILIAS\Skill\Service;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Request wrapper for personal skills guis. This class processes
 * all request parameters which are not handled by form classes already.
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillPersonalGUIRequest extends SkillGUIRequest
{
    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        parent::__construct($http, $refinery, $passed_query_params, $passed_post_data);
    }

    public function getNodeId(): int
    {
        return $this->int("node_id");
    }

    public function getProfileId(): int
    {
        return $this->int("profile_id");
    }

    public function getSkillId(): int
    {
        return $this->int("skill_id");
    }

    /**
     * @return int[]
     */
    public function getSkillIds(): array
    {
        return $this->getIds();
    }

    public function getBasicSkillId(): int
    {
        return $this->int("basic_skill_id");
    }

    public function getTrefId(): int
    {
        return $this->int("tref_id");
    }

    public function getLevelId(): int
    {
        return $this->int("level_id");
    }

    public function getSelfEvaluationLevelId(): int
    {
        return $this->int("se");
    }

    public function getWorkspaceId(): int
    {
        return $this->int("wsp_id");
    }

    /**
     * @return int[]
     */
    public function getWorkspaceIds(): array
    {
        return $this->intArray("wsp_id");
    }

    public function getListMode(): string
    {
        return $this->str("list_mode");
    }

    public function getTypeOfFormation(): int
    {
        return $this->int("type_of_formation");
    }

    public function getShowTargetLevel(): bool
    {
        return $this->bool("target_level");
    }

    public function getShowMaterialsResources(): bool
    {
        return $this->bool("mat_res");
    }
}
