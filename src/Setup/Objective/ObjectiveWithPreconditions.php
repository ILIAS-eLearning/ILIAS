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
 * A wrapper around an objective that adds some preconditions.
 *
 * ATTENTION: This will use the same hash then the original objective and will
 * therefore be indistinguishable.
 */
class ObjectiveWithPreconditions implements Setup\Objective
{
    protected Setup\Objective $original;

    /**
     * @var Setup\Objective[]
     */
    protected array $preconditions;

    public function __construct(Setup\Objective $original, Setup\Objective ...$preconditions)
    {
        if ($preconditions === []) {
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
    public function getHash(): string
    {
        return $this->original->getHash();
    }

    /**
     * @inheritdocs
     */
    public function getLabel(): string
    {
        return $this->original->getLabel();
    }

    /**
     * @inheritdocs
     */
    public function isNotable(): bool
    {
        return $this->original->isNotable();
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return array_merge($this->preconditions, $this->original->getPreconditions($environment));
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        return $this->original->achieve($environment);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return $this->original->isApplicable($environment);
    }
}
