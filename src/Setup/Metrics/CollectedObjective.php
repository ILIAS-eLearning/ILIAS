<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

use ILIAS\Setup;

/**
 * Base class to simplify collection of metrics.
 */
abstract class CollectedObjective implements Setup\Objective
{
    /**
     * @var Storage
     */
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Attempt to gather metrics based on the provided environment.
     *
     * Make sure to be very cautious regarding expectations towards the environment.
     * It might only be initialized partially or not be initialized at all, since
     * preconditions might not have been met. This should be implemented with a
     * best effort approach to gather as much metrics as possible even when the
     * installation is damaged.
     */
    abstract protected function collectFrom(Setup\Environment $environment, Storage $storage) : void;

    /**
     * Give preconditions that might or might not be fullfilled.
     *
     * Since collection of metrics should also work in (partially) broken installations
     * the preconditions given here will only be tentatively fullfilled when collectFrom
     * is called.
     *
     * @return Setup\Objective[]
     */
    abstract protected function getTentativePreconditions(Setup\Environment $environment) : array;

    public function getHash() : string
    {
        return hash("sha256", static::class);
    }

    public function getLabel() : string
    {
        return "Collect metrics.";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return array_map(
            function (Setup\Objective $o) {
                return new Setup\Objective\Tentatively($o);
            },
            $this->getTentativePreconditions($environment)
        );
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $this->collectFrom($environment, $this->storage);
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        // We want to always collect fresh metrics.
        return true;
    }
}
