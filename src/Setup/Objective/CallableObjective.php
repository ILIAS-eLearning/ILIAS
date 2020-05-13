<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $is_notable;

    /**
     * @var	Setup\Objective[]
     */
    protected $preconditions;

    public function __construct(callable $callable, string $label, bool $is_notable, Setup\Objective ...$preconditions)
    {
        $this->callable = $callable;
        $this->label = $label;
        $this->is_notable = $is_notable;
        $this->preconditions = $preconditions;
    }

    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            spl_object_hash($this)
        );
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return $this->is_notable;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return $this->preconditions;
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $res = call_user_func($this->callable, $environment);
        if ($res instanceof Setup\Environment) {
            return $res;
        }
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
