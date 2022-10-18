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
 ********************************************************************
 */

namespace ILIAS\Skill\Service;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Request wrapper for guis in skill administration. This class processes
 * all request parameters which are not handled by form classes already.
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillAdminGUIRequest extends SkillGUIRequest
{
    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        parent::__construct($http, $refinery, $passed_query_params, $passed_post_data);
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getNodeId(): int
    {
        return $this->int("node_id");
    }

    public function getRootId(): int
    {
        return $this->int("root_id");
    }

    public function getTrefId(): int
    {
        return $this->int("tref_id");
    }

    public function getCombinedSkillId(): string
    {
        return $this->str("cskill_id");
    }

    public function getTemplateMode(): bool
    {
        return $this->bool("tmpmode");
    }

    public function getTemplatesTree(): int
    {
        return $this->int("templates_tree");
    }

    public function getBackCommand(): string
    {
        return $this->str("backcmd");
    }

    public function getSkillExpand(): string
    {
        return $this->str("skexpand");
    }

    public function getSkillProfileId(): int
    {
        return $this->int("sprof_id");
    }

    public function getLocalContext(): bool
    {
        return $this->bool("local_context");
    }

    /**
     * @return int[]
     */
    public function getOrder(): array
    {
        return $this->intArray("order");
    }

    public function getLevelId(): int
    {
        return $this->int("level_id");
    }

    /**
     * @return int[]
     */
    public function getLevelIds(): array
    {
        return $this->getIds();
    }

    /**
     * @return string[]
     */
    public function getAssignedLevelIds(): array
    {
        return $this->strArray("ass_id");
    }

    /**
     * @return int[]
     */
    public function getResourceIds(): array
    {
        return $this->getIds();
    }

    /**
     * @return bool[]
     */
    public function getSuggested(): array
    {
        return $this->boolArray("suggested");
    }

    /**
     * @return bool[]
     */
    public function getTrigger(): array
    {
        return $this->boolArray("trigger");
    }

    /**
     * @return string[]
     */
    public function getTitles(): array
    {
        return $this->strArray("title");
    }

    /**
     * @return int[]
     */
    public function getNodeIds(): array
    {
        return $this->getIds();
    }

    /**
     * @return int[]
     */
    public function getProfileIds(): array
    {
        return $this->getIds();
    }

    public function getUserLogin(): string
    {
        return $this->str("user_login");
    }

    /**
     * @return int[]
     */
    public function getUsers(): array
    {
        return $this->intArray("user");
    }

    /**
     * @return int[]
     */
    public function getUserIds(): array
    {
        return $this->getIds();
    }

    /**
     * @return string[]
     */
    public function getSelectedIds(string $post_var): array
    {
        return $this->strArray($post_var);
    }
}
