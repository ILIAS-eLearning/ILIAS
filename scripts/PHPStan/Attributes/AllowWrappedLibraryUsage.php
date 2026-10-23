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

namespace ILIAS\Scripts\PHPStan\Attributes;

use Attribute;
use ILIAS\Scripts\PHPStan\Rules\WrappedLibraries\WrappedLibraryUsageRule;

/**
 * Convenience variant of {@see AllowRuleViolation} that already carries the
 * "Wrapped library usage" rule identifier, so only a reason has to be given:
 *
 * ```php
 * #[AllowWrappedLibraryUsage('needs the raw library until the wrapper offers X', 13)]
 * public function send(): void { ... }
 * ```
 *
 * The second argument is the ILIAS major the allowance is granted for; it expires
 * with the next one. See {@see AllowRuleViolation}.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final readonly class AllowWrappedLibraryUsage extends AllowRuleViolation
{
    public function __construct(string $reason, int $ilias_version)
    {
        parent::__construct($reason, $ilias_version, WrappedLibraryUsageRule::IDENTIFIER);
    }
}
