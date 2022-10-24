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
 * A non-objective, nothing to do to achieve it...
 */
class NullObjective implements Setup\Objective
{
    public const LABEL = "Nothing to do.";

    public function getHash(): string
    {
        return "null-objective";
    }

    public function getLabel(): string
    {
        return self::LABEL;
    }

    public function isNotable(): bool
    {
        return false;
    }

    /*
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return false;
    }
}
