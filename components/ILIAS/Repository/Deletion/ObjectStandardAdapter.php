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

namespace ILIAS\Repository\Deletion;

class ObjectStandardAdapter implements ObjectInterface
{
    protected ?\ilObject $object = null;

    public function __construct(int $ref_id)
    {
        if ($ref_id > 0) {
            $this->object = \ilObjectFactory::getInstanceByRefId($ref_id, false);
        }
    }

    public function getInstanceByRefId(int $ref_id): ?ObjectInterface
    {
        $inst = new self($ref_id);
        if ($inst->getRefId() === $ref_id) {
            return $inst;
        }
        return null;
    }

    public function getId(): int
    {
        return $this->object->getId();
    }
    public function getType(): string
    {
        return $this->object->getType();
    }

    public function getTitle(): string
    {
        return $this->object->getTitle();
    }

    public function getRefId(): int
    {
        if (is_null($this->object)) {
            return 0;
        }
        return $this->object->getRefId();
    }

    public function delete(): void
    {
        $this->object->delete();
    }
}
