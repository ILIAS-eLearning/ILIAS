<?php

namespace ILIAS\CI\Rector;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveRequiresAndIncludesRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Include_::class];
    }
    
    /**
     * @param Node\Expr\Include_ $node - we can add "MethodCall" type here, because
     *                                 only this node is in "getNodeTypes()"
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, Node\Expr\Assign::class)) {
            $this->nodeRemover->removeNode($node);
        }
        
        return $node;
    }
    
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition(
            'Remove requires and includes', [
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
