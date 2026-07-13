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

namespace ILIAS\WebDAV\Objects;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class TreeProxy implements Proxy
{
    public function __construct(
        protected int $ref_id,
        protected int $obj_id,
        protected string $title,
        protected int $last_update,
        protected Type $type
    ) {
    }

    public function getRefId(): ?int
    {
        return $this->ref_id;
    }

    public function getObjId(): ?int
    {
        return $this->obj_id;
    }

    public function getLastModified(): int
    {
        return $this->last_update;
    }

    public function getName(): string
    {
        return $this->title;
    }

    public function setName(string $name): void
    {
        $this->title = $name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

}
