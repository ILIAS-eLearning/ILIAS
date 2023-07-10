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
class SkillProfileUserAssignment implements SkillProfileAssignmentInterface
{
    protected string $type = "user";
    protected string $name = "";
    protected int $id = 0;

    public function __construct(
        string $name,
        int $id
    ) {
        $this->name = $name;
        $this->id = $id;
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
}
