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

namespace ILIAS\CI\PHPStan\Rules;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

class NoLegacySyntaxHighlighterUsagesRule extends LegacyClassUsageRule implements Rule
{
    protected function getHumanReadableRuleName(): string
    {
        return 'Legacy Syntax Highlighter Usages';
    }

    protected function getRelevantILIASVersion(): int
    {
        return 10;
    }


    protected function getForbiddenClasses(): array
    {
        return ['ilSyntaxHighlighter'];
    }
}
