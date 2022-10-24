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
 *********************************************************************/

/**
* Class ilStudyProgrammeAutoMembershipSource
*
* @author: Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class ilStudyProgrammeAutoMembershipSource
{
    public const TYPE_ROLE = 'role';
    public const TYPE_GROUP = 'grp';
    public const TYPE_COURSE = 'crs';
    public const TYPE_ORGU = 'orgu';

    public const SOURCE_MAPPING = [
        self::TYPE_ROLE => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_ROLE,
        self::TYPE_GROUP => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_GROUP,
        self::TYPE_COURSE => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_COURSE,
        self::TYPE_ORGU => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_ORGU
    ];

    protected int $prg_obj_id;
    protected string $source_type;
    protected int $source_id;
    protected bool $enabled;
    protected int $last_edited_usr_id;
    protected DateTimeImmutable $last_edited;

    public function __construct(
        int $prg_obj_id,
        string $source_type,
        int $source_id,
        bool $enabled,
        int $last_edited_usr_id,
        DateTimeImmutable $last_edited
    ) {
        if (!in_array($source_type, [
            self::TYPE_ROLE,
            self::TYPE_GROUP,
            self::TYPE_COURSE,
            self::TYPE_ORGU
        ])) {
            throw new InvalidArgumentException("Invalid source-type: " . $source_type, 1);
        }

        $this->prg_obj_id = $prg_obj_id;
        $this->source_type = $source_type;
        $this->source_id = $source_id;
        $this->enabled = $enabled;
        $this->last_edited_usr_id = $last_edited_usr_id;
        $this->last_edited = $last_edited;
    }

    public function getPrgObjId(): int
    {
        return $this->prg_obj_id;
    }

    public function getSourceType(): string
    {
        return $this->source_type;
    }

    public function getSourceId(): int
    {
        return $this->source_id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLastEditorId(): int
    {
        return $this->last_edited_usr_id;
    }

    public function getLastEdited(): DateTimeImmutable
    {
        return $this->last_edited;
    }
}
