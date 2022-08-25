<?php

declare(strict_types=1);

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

namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * A callable objective wraps a callable into an objective.
 *
 * The callable receives the environment as parameter. It may return an updated
 * version of the environment, other results will be discarded.
 */
class CallableObjective implements Setup\Objective
{
    /**
     * @var callable
     */
    protected $callable;

    protected string $label;

    protected bool $is_notable;

    /**
     * @var	Setup\Objective[]
     */
    protected array $preconditions;

    public function __construct(callable $callable, string $label, bool $is_notable, Setup\Objective ...$preconditions)
    {
        $this->callable = $callable;
        $this->label = $label;
        $this->is_notable = $is_notable;
        $this->preconditions = $preconditions;
    }

    public function getHash(): string
    {
        return hash(
            "sha256",
            spl_object_hash($this)
        );
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isNotable(): bool
    {
        return $this->is_notable;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return $this->preconditions;
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $res = call_user_func($this->callable, $environment);
        if ($res instanceof Setup\Environment) {
            return $res;
        }
        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
