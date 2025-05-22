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

declare(strict_types=0);

namespace ILIAS\Tracking\View\PropertyList;

use ILIAS\Tracking\View\PropertyList\BuilderInterface;
use ILIAS\Tracking\View\PropertyList\PropertyListInterface;
use ILIAS\Tracking\View\PropertyList\PropertyList;

class Builder implements BuilderInterface
{
    protected array $properties;

    public function __construct()
    {
        $this->properties = [];
    }

    public function withProperty(
        string $key,
        string $value
    ): BuilderInterface {
        $clone = clone $this;
        $clone->properties[$key] = $value;
        return $clone;
    }

    public function getList(): PropertyListInterface
    {
        return new PropertyList($this->properties);
    }
}
