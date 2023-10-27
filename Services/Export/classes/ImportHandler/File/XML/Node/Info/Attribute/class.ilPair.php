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

namespace ImportHandler\File\XML\Node\Info\Attribute;

use ImportHandler\I\File\XML\Node\Info\Attribute\ilPairInterface as ilXMLFileNodeInfoAttributePairInterface;

class ilPair implements ilXMLFileNodeInfoAttributePairInterface
{
    protected string $key;
    protected string $value;

    public function __construct()
    {
        $this->key = '';
        $this->value = '';
    }

    public function withValue(string $value): ilXMLFileNodeInfoAttributePairInterface
    {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    public function withKey(string $key): ilXMLFileNodeInfoAttributePairInterface
    {
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
