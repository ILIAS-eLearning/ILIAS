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

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class PHPVersionCondition extends ExternalConditionObjective
{
    public function __construct(
        string $min_version,
        ?string $max_version = null,
        bool $block_setup = false
    ) {
        parent::__construct(
            "PHP version >= $min_version and <= $max_version",
            static function (Setup\Environment $env) use ($min_version, $max_version): bool {
                return version_compare(phpversion(), $min_version, ">=")
                    && ($max_version !== null && version_compare(phpversion(), $max_version, "<="));
            },
            $max_version === null
                ? "ILIAS " . ILIAS_VERSION_NUMERIC . " requires PHP $min_version - $max_version."
                : "ILIAS " . ILIAS_VERSION_NUMERIC . " requires PHP $min_version or later.",
            $block_setup
        );
    }
}
