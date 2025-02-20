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

namespace ILIAS\File\Capabilities;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Context implements \Stringable
{
    public const CONTEXT_REPO = 1;
    public const CONTEXT_WORKSPACE = 2;

    public function __construct(
        private int $object_id,
        private int $calling_id,
        private int $context
    ) {
        // $context mut be one of the constants
        if ($context !== self::CONTEXT_REPO && $context !== self::CONTEXT_WORKSPACE) {
            throw new \InvalidArgumentException('Invalid context');
        }
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getCallingId(): int
    {
        return $this->calling_id;
    }

    public function getContext(): int
    {
        return $this->context;
    }

    public function getNode(): string
    {
        return $this->getContext() . '_' . $this->getCallingId();
    }

    public function withCallingId(int $calling_id): self
    {
        $clone = clone $this;
        $clone->calling_id = $calling_id;
        return $clone;
    }

    public function withObjectId(int $object_id): self
    {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function __toString(): string
    {
        return (string) $this->getCallingId();
    }

}
