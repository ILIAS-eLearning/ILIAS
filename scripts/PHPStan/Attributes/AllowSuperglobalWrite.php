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
use ILIAS\Scripts\PHPStan\Rules\SuperGlobals\AbstractSuperglobalWriteRule;

/**
 * Convenience variant of {@see AllowRuleViolation} that already carries the
 * "No Superglobal Write" rule identifier, so only a reason has to be given:
 *
 * ```php
 * #[AllowSuperglobalWrite('sanitizes $_GET before the HTTP service exists')]
 * public static function recursivelyRemoveUnsafeCharacters(): void { ... }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final readonly class AllowSuperglobalWrite extends AllowRuleViolation
{
    public function __construct(string $reason)
    {
        parent::__construct($reason, AbstractSuperglobalWriteRule::IDENTIFIER);
    }
}
