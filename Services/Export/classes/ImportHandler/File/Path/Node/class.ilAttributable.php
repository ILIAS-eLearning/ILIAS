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

namespace ImportHandler\File\Path\Node;

use ImportHandler\I\File\Path\Node\ilAttributableInterface as ilAttributableFilePathNodeInterface;
use ImportHandler\File\Path\ilComparisonDummy;
use ImportHandler\I\File\Path\ilComparisonInterface;
use XMLReader;

class ilAttributable implements ilAttributableFilePathNodeInterface
{
    protected ilComparisonInterface $comparison;
    protected string $node_name;
    protected string $attribute;
    protected bool $any_attribute_enabled;

    public function __construct()
    {
        $this->node_name = '';
        $this->attribute = '';
        $this->comparison = new ilComparisonDummy();
        $this->any_attribute_enabled = false;
    }

    public function withName(string $node_name): ilAttributableFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->node_name = $node_name;
        return $clone;
    }

    public function withAttribute(string $attribute): ilAttributableFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->attribute = $attribute;
        return $clone;
    }

    public function withComparison(ilComparisonInterface $comparison): ilAttributableFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->comparison = $comparison;
        return $clone;
    }

    public function withAnyAttributeEnabled(bool $enabled): ilAttributableFilePathNodeInterface
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

        return $attribute === ''
            ? $this->node_name
            : $this->node_name . '[' . $attribute . ']';
    }
}
