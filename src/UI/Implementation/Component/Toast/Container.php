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

namespace ILIAS\UI\Implementation\Component\Toast;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Toast as ComponentInterface;
use ILIAS\UI\NotImplementedException;

class Container implements ComponentInterface\Container
{
    use ComponentHelper;

    /**
     * @var Toast[] $toasts
     */
    protected array $toasts = [];

    public function getToasts(): array
    {
        return $this->toasts;
    }

    public function withAdditionalToast(ComponentInterface\Toast $toast): Container
    {
        $clone = clone $this;
        $clone->toasts[] = $toast;
        return $clone;
    }

    public function withoutToasts(): Container
    {
        $clone = clone $this;
        $clone->toasts = [];
        return $clone;
    }

    public function withVanishTime(int $vanishTime): Container
    {
        throw new NotImplementedException();
    }

    public function getVanishTime(): int
    {
        throw new NotImplementedException();
    }
    public function withDelayTime(int $delayTime): Container
    {
        throw new NotImplementedException();
    }

    public function getDelayTime(): int
    {
        throw new NotImplementedException();
    }
}
