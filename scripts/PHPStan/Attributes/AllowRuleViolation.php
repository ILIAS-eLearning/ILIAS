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

/**
 * Marks a class, method or function that is deliberately allowed to violate one or
 * more ILIAS custom code rules, exempting it from those rules.
 *
 * This is a zero-cost marker: it is never instantiated at runtime, only read via
 * reflection by the rules ({@see RuleViolationAllowance}). Use it only for genuinely
 * unavoidable cases and always give a reason.
 *
 * An allowance is granted for one ILIAS major version and expires with the next one:
 * `$ilias_version` names the major it was granted for, and the code position starts
 * being reported again as soon as the analysis runs against a later major. Keeping an
 * allowance means renewing it deliberately, so nothing is exempted forever by
 * accident.
 *
 * The rules are identified by their PHPStan error identifier (the value shown as
 * `🪪 <identifier>` in the analysis output, e.g. `ilias.superglobalWrite`). Pass one
 * or more; an allowance with no identifiers exempts nothing.
 *
 * PHP attributes can only sit on declarations, so this exempts the whole
 * class/method/function. For a single free-standing statement (e.g. in a resource
 * script without an enclosing function) use an inline
 * `// @phpstan-ignore <identifier> (reason)` comment instead.
 *
 * For frequently exempted rules there are convenience subclasses that already carry
 * the identifier, e.g. {@see AllowSuperglobalWrite}. The checker matches those via
 * `ReflectionAttribute::IS_INSTANCEOF`, so subclasses work exactly like the base
 * attribute.
 *
 * Example:
 * ```php
 * #[AllowRuleViolation('sanitizes $_GET before the HTTP service exists', 12, 'ilias.superglobalWrite')]
 * public static function recursivelyRemoveUnsafeCharacters(): void { ... }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
readonly class AllowRuleViolation
{
    /** @var list<string> */
    public array $rules;

    public function __construct(
        public string $reason,
        public int $ilias_version,
        string ...$rules
    ) {
        $this->rules = array_values($rules);
    }
}
