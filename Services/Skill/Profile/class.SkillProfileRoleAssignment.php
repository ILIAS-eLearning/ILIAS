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
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileRoleAssignment implements SkillProfileAssignmentInterface
{
    protected string $type = "role";
    protected string $name = "";
    protected int $id = 0;
    protected string $obj_title = "";
    protected string $obj_type = "";
    protected int $obj_id = 0;

    public function __construct(
        string $name,
        int $id,
        string $obj_title,
        string $obj_type,
        int $obj_id
    ) {
        $this->name = $name;
        $this->id = $id;
        $this->obj_title = $obj_title;
        $this->obj_type = $obj_type;
        $this->obj_id = $obj_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getObjTitle(): string
    {
        return $this->obj_title;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }
}
