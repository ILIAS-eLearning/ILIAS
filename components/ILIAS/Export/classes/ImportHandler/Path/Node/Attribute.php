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

namespace ILIAS\Export\ImportHandler\Path\Node;

use ILIAS\Export\ImportHandler\I\Path\Comparison\HandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AttributeInterface as AttributeFilePathNodeInterface;

class Attribute implements AttributeFilePathNodeInterface
{
    protected HandlerInterface $comparison;
    protected string $attribute;
    protected bool $any_attribute_enabled;

    public function __construct()
    {
        $this->attribute = '';
        $this->any_attribute_enabled = false;
    }

    public function withAttribute(string $attribute): AttributeFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->attribute = $attribute;
        return $clone;
    }

    public function withComparison(HandlerInterface $comparison): AttributeFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->comparison = $comparison;
        return $clone;
    }

    public function withAnyAttributeEnabled(bool $enabled): AttributeFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->any_attribute_enabled = $enabled;
        return $clone;
    }

    public function toString(): string
    {
        $attribute = $this->any_attribute_enabled
            ? '@*'
            : '@' . $this->attribute . (isset($this->comparison) ? $this->comparison->toString() : "");
        return '[' . $attribute . ']';
    }

    public function requiresPathSeparator(): bool
    {
        return true;
    }
}
