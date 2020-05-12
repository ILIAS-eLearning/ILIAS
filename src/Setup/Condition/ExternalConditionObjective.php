<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

/**
 * A condition that can't be met by ILIAS itself needs to be met by some external
 * means.
 *
 * ATTENTION: Two ExternalConditionObjectives are considered to be identical if the
 * label is identical. I.e., getHash does not use the actual condition or the message.
 */
class ExternalConditionObjective implements Setup\Objective
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var callable
     */
    protected $condition;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @param callable $condition needs to be function from Environment to bool.
     */
    public function __construct(string $label, callable $condition, string $message = null)
    {
        $this->condition = $condition;
        $this->label = $label;
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this) . "::" . $this->label
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        if (($this->condition)($environment)) {
            return $environment;
        }

        if ($this->message) {
            $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
            $admin_interaction->inform($this->message);
        }

        throw new Setup\UnachievableException(
            "An external condition was not met: {$this->label}"
        );
    }
}
