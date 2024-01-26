<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * A wrapper around an objective that adds some preconditions.
 *
 * ATTENTION: This will use the same hash then the original objective and will
 * therefore be indistinguishable.
 */
class ObjectiveWithPreconditions implements Setup\Objective
{
    /**
     * @var Setup\Objective
     */
    protected $original;

    /**
     * @var Setup\Objective[]
     */
    protected $preconditions;

    public function __construct(Setup\Objective $original, Setup\Objective ...$preconditions)
    {
        if (count($preconditions) === 0) {
            throw new \InvalidArgumentException(
                "Expected at least one precondition."
            );
        }
        $this->original = $original;
        $this->preconditions = $preconditions;
    }

    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            self::class
            . $this->original->getHash()
            . implode(
                "",
                array_map(
                    function ($o) { return $o->getHash(); },
                    $this->preconditions
                )
            )
        );
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return $this->original->getLabel();
    }

    /**
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return $this->original->isNotable();
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return array_merge($this->preconditions, $this->original->getPreconditions($environment));
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        return $this->original->achieve($environment);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return $this->original->isApplicable($environment);
    }
}
