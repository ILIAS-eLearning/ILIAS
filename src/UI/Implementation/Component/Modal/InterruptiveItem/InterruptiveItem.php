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

namespace ILIAS\UI\Implementation\Component\Modal\InterruptiveItem;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem as ItemInterface;

abstract class InterruptiveItem implements ItemInterface
{
    use ComponentHelper;

    protected string $id;
    protected string $parameter_name = 'interruptive_items';

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function withParameterName(string $parameter_name): static
    {
        $clone = clone $this;
        $clone->parameter_name = $parameter_name;
        return $clone;
    }

    public function getParameterName(): string
    {
        return $this->parameter_name;
    }

}
