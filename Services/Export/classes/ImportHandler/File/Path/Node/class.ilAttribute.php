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

namespace ILIAS\Export\ImportHandler\File\Path\Node;

use ILIAS\Export\ImportHandler\File\Path\Comparison\ilHandlerDummy;
use ILIAS\Export\ImportHandler\I\File\Path\Comparison\ilHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilAttributeInterface as ilAttributeFilePathNodeInterface;

class ilAttribute implements ilAttributeFilePathNodeInterface
{
    protected ilHandlerInterface $comparison;
    protected string $attribute;
    protected bool $any_attribute_enabled;

    public function __construct()
    {
        $this->attribute = '';
        $this->comparison = new ilHandlerDummy();
        $this->any_attribute_enabled = false;
    }

    public function withAttribute(string $attribute): ilAttributeFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->attribute = $attribute;
        return $clone;
    }

    public function withComparison(ilHandlerInterface $comparison): ilAttributeFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->comparison = $comparison;
        return $clone;
    }

    public function withAnyAttributeEnabled(bool $enabled): ilAttributeFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->any_attribute_enabled = $enabled;
        return $clone;
    }

    public function toString(): string
    {
        $attribute = $this->any_attribute_enabled
            ? '@*'
            : '@' . $this->attribute . $this->comparison->toString();
        return '[' . $attribute . ']';
    }

    public function requiresPathSeparator(): bool
    {
        return true;
    }
}
