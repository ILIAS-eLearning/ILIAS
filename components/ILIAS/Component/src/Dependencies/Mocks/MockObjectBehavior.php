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

namespace ILIAS\Component\Dependencies\Mocks;

trait MockObjectBehavior
{
    /** @var array<string, mixed> */
    private array $__mock_configured_returns = [];

    /** @var array<string, \Closure> */
    private array $__mock_configured_callbacks = [];

    public function __mockSetReturnValue(string $method, mixed $value): static
    {
        $this->__mock_configured_returns[$method] = $value;
        return $this;
    }

    public function __mockSetCallback(string $method, callable $callback): static
    {
        $this->__mock_configured_callbacks[$method] = $callback(...);
        return $this;
    }

    /**
     * @param list<mixed> $args
     */
    protected function __mockInvoke(string $method, array $args): mixed
    {
        if (isset($this->__mock_configured_callbacks[$method])) {
            return ($this->__mock_configured_callbacks[$method])(...$args);
        }

        if (array_key_exists($method, $this->__mock_configured_returns)) {
            return $this->__mock_configured_returns[$method];
        }

        return AbstractLightMockBuilder::defaultValueFor($this, $method);
    }
}
