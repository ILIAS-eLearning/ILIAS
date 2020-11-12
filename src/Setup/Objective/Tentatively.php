<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * A wrapper around an objective that attempts to achieve the wrapped
 * objective but won't stop the process on failure.
 */
class Tentatively implements Setup\Objective
{
    /**
     * @var Setup\Objective
     */
    protected $other;

    public function __construct(Setup\Objective $other)
    {
        $this->other = $other;
    }

    public function getHash() : string
    {
        if ($this->other instanceof Tentatively) {
            return $this->other->getHash();
        }
        return "tentatively " . $this->other->getHash();
    }

    public function getLabel() : string
    {
        if ($this->other instanceof Tentatively) {
            return $this->other->getLabel();
        }
        return "Tentatively: " . $this->other->getLabel();
    }

    public function isNotable() : bool
    {
        return $this->other->isNotable();
    }

    /*
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        if ($this->other instanceof Tentatively) {
            return $this->other->getPreconditions($environment);
        }
        return array_map(
            function ($p) {
                if ($p instanceof Tentatively) {
                    return $p;
                }
                return new Tentatively($p);
            },
            $this->other->getPreconditions($environment)
        );
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        try {
            return $this->other->achieve($environment);
        } catch (Setup\UnachievableException $e) {
        }
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return $this->other->isApplicable($environment);
    }
}
