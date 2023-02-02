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

namespace ILIAS\Skill\Profile;

/**
 * Skill profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class SkillProfile
{
    protected int $id = 0;
    protected string $title = "";
    protected string $description = "";
    protected int $skill_tree_id = 0;
    protected string $image_id = "";
    protected int $ref_id = 0;

    public function __construct(
        int $id,
        string $title,
        string $description,
        int $skill_tree_id,
        string $image_id = "",
        int $ref_id = 0
    ) {
        global $DIC;

        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->skill_tree_id = $skill_tree_id;
        $this->image_id = $image_id;
        $this->ref_id = $ref_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSkillTreeId(): int
    {
        return $this->skill_tree_id;
    }

    public function getImageId(): string
    {
        return $this->image_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }
}
