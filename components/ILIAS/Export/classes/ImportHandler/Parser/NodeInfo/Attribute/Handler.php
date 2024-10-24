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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute;

use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\HandlerInterface as ParserNodeInfoAttributeInterface;

class Handler implements ParserNodeInfoAttributeInterface
{
    protected string $key;
    protected string $value;

    public function __construct()
    {
        $this->key = '';
        $this->value = '';
    }

    public function withValue(
        string $value
    ): ParserNodeInfoAttributeInterface {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    public function withKey(
        string $key
    ): ParserNodeInfoAttributeInterface {
        $clone = clone $this;
        $clone->key = $key;
        return $clone;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
