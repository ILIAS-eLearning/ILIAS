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

namespace ILIAS\scripts\Rector;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PHPStan\Type\ObjectType;

final class RemoveRequiresAndIncludesRector extends AbstractRector
{
    /**
     * @return array<class-string<\PhpParser\Node\Expr>>
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Expr\Include_::class];
    }

    /**
     * @param Node\Expr\Include_ $node
     */
    public function refactor(Node $node): \PhpParser\Node\Expr\Include_
    {
        if (!$this->isObjectType($node, new ObjectType(Node\Expr\Assign::class))) {
            $this->nodeRemover->removeNode($node);
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove requires and includes',
            [
                new CodeSample(
                    // code before
                    'require_once "./..."',
                    // code after
                    ''
                ),
            ]
        );
    }
}
