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

declare(strict_types=1);

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 */
class ilTreeTrashItem
{
    private int $deleted_by = 0;

    private ?string $deleted = null;

    private string $type = '';

    private ?string $description = '';

    private string $title = '';

    private int $ref_id = 0;

    private int $obj_id = 0;

    /**
     * ilTreeTrashItem constructor.
     */
    public function __construct()
    {
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setDeleted(?string $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getDeleted(): ?string
    {
        return $this->deleted;
    }

    public function setDeletedBy(int $deleted_by): void
    {
        $this->deleted_by = $deleted_by;
    }

    public function getDeletedBy(): int
    {
        return $this->deleted_by;
    }
}
