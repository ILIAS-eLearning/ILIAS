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
 
namespace ILIAS\CI\Rector\ilUtils;

use PhpParser\Node;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use ILIAS\CI\Rector\DIC\DICMemberMap;
use Rector\Core\Exception\ShouldNotHappenException;

final class ReplaceUtilSendMessageRector extends \Rector\Core\Rector\AbstractRector
{
    protected \ILIAS\CI\Rector\DIC\DICMemberResolver $dic_member_resolver;
    protected \Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver $type_resolver;
    protected \Rector\Core\NodeManipulator\ClassDependencyManipulator $class_dependecied;
    protected \Rector\PostRector\Collector\PropertyToAddCollector $poperty_adder;
    protected \Rector\Core\NodeManipulator\ClassInsertManipulator $class_insert;
    protected array $old_method_names = [
        'sendInfo',
        'sendSuccess',
        'sendFailure',
        'sendQuestion',
    ];
    protected string $new_method_name = 'setOnScreenMessage';
    
    public function __construct(
        \ILIAS\CI\Rector\DIC\DICMemberResolver $dic_member_resolver,
        \Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver $typre_resolver,
        \Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator,
        \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector,
        \Rector\Core\NodeManipulator\ClassInsertManipulator $class_insert
    ) {
        $this->dic_member_resolver = $dic_member_resolver;
        $this->type_resolver = $typre_resolver;
        $this->class_dependecied = $classDependencyManipulator;
        $this->poperty_adder = $propertyToAddCollector;
        $this->class_insert = $class_insert;
    }
    
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    
    private function isApplicable(Node $node) : bool
    {
        /** @var $node \PhpParser\Node\Expr\StaticCall::class */
        if (!$node->class instanceof \PhpParser\Node\Name) {
            return false;
        }
        $staticCallClassName = $node->class->toString();
        if ($staticCallClassName !== \ilUtil::class) {
            // not calling ilUtil
            return false;
        }
        if (!$node->name instanceof \PhpParser\Node\Identifier) {
            // node has no name
            return false;
        }
        if (!in_array($node->name->name, $this->old_method_names)) {
            // not interested in method since not in list
            return false;
        }
        
        return true;
    }
    
    /**
     * @param Node $node the Static Call to ilUtil:sendXY
     */
    public function refactor(Node $node)
    {
        if (!$this->isApplicable($node)) {
            return null; // leave the node as it is
        }
        $class_where_call_happens = $this->betterNodeFinder->findParentType(
            $node,
            \PhpParser\Node\Stmt\Class_::class
        );
        if (!$class_where_call_happens instanceof \PhpParser\Node\Stmt\Class_) {
            // not on class, abort
            return null; // leave the node as it is
        }
        $method_where_call_happend = $this->betterNodeFinder->findParentType(
            $node,
            \PhpParser\Node\Stmt\ClassMethod::class
        );
        if (!$method_where_call_happend instanceof \PhpParser\Node\Stmt\ClassMethod) {
            // not in a method, abort
            return null; // leave the node as it is
        }
        
        if ($method_where_call_happend->isStatic()) {
//            return null;
        }
        
        // prepend a new argument with the type of the message, aka sendInfo goes to setOnScreenMessage('info', ...
        $message_type = strtolower(str_replace('send', '', $node->name->name));
        $first_argument = $this->nodeFactory->createArg($message_type);
        $arguments = $node->args;
        array_unshift($arguments, $first_argument);
        
        // ensure a dic property for ilGlobalTemplate is in the class. or we get another Expr to fetch ilGlobalTemplate
        try {
            $dicPropertyFetch = $this->dic_member_resolver->ensureDICDependency(
                DICMemberMap::TPL,
                $class_where_call_happens,
                $method_where_call_happend
            );
        } catch (ShouldNotHappenException $e) {
            throw  new ShouldNotHappenException(
                "Could not process " . $this->file->getFilePath() . ': ' . $e->getMessage()
            );
            echo "Could not process " . $this->file->getFilePath() . ': ' . $e->getMessage();
            // there are places where the DIC dependency could not be added, we must skip thoses places
            return null;
        }
        
        // return new method call
        $methodCall = new \PhpParser\Node\Expr\MethodCall(
            $dicPropertyFetch,
            $this->new_method_name,
            $arguments
        );
        return $methodCall;
    }
    
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDefinition(
            'lorem',
            [
                new CodeSample(
                    "\ilUtil::sendQuestion('my_text', true);",
                    "\$this->main_tpl->setOnScreenMessage('question', 'my_text', true)"
                )
            ],
        );
    }
}
