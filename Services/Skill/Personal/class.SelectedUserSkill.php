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

namespace ILIAS\Skill\Personal;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SelectedUserSkill
{
    protected int $skill_node_id = 0;
    protected string $title = "";

    public function __construct(
        int $skill_node_id,
        string $title
    ) {
        $this->skill_node_id = $skill_node_id;
        $this->title = $title;
    }

    public function getSkillNodeId(): int
    {
        return $this->skill_node_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
