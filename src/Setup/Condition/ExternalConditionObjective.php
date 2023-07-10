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
    protected string $label;
    protected \Closure $condition;
    protected ?string $message;

    /**
     * @param callable $condition needs to be function from Environment to bool.
     */
    public function __construct(string $label, \Closure $condition, string $message = null)
    {
        $this->condition = $condition;
        $this->label = $label;
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash(
            "sha256",
            get_class($this) . "::" . $this->label
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        if (($this->condition)($environment)) {
            return $environment;
        }

        if ($this->message) {
            $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
            $admin_interaction->inform($this->message);
        }

        throw new Setup\UnachievableException(
            "An external condition was not met: $this->label"
        );
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
