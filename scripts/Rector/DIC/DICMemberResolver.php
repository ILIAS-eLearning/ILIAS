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

namespace ILIAS\scripts\Rector\DIC;

use Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\PostRector\Collector\PropertyToAddCollector;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\Compatibility\NodeFactory\ConstructorClassMethodFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;

final class DICMemberResolver
{
    public const DIC = 'DIC';
    public const THIS = 'this';
    public const GLOBALS = 'GLOBALS';
    protected DICMemberMap $DICMemberMap;

    public function __construct(
        protected TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver,
        protected DICDependencyManipulator $dicDependencyManipulator,
        protected PropertyToAddCollector $propertyToAddCollector,
        protected ClassInsertManipulator $classInsertManipulator,
        protected NodesToAddCollector $nodesToAddCollector,
        protected \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory,
        protected ConstructorClassMethodFactory $constructorClassMethodFactory,
        protected \Rector\Core\NodeDecorator\PropertyTypeDecorator $propertyTypeDecorator,
        protected \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector
    ) {
        $this->DICMemberMap = new DICMemberMap();
    }

    /**
     * @return Expr|MethodCall
     */
    private function getStaticDICCall(
        DICMember $DICMember,
        Class_ $class,
        ClassMethod $classMethod
    ): \PhpParser\Node\Expr\Variable {
        // $DIC;
        $dic_variable = $this->dicDependencyManipulator->ensureGlobalDICinMethod($classMethod, $class);
        // new variable like $main_tpl;
        $variable = new Variable($DICMember->getPropertyName());
        // MethodCall to get DIC Dependency
        $expression = new Expression(
            new Assign(
                $variable,
                $this->appendDICMethods(
                    $DICMember,
                    $dic_variable
                )
            )
        );
        $this->dicDependencyManipulator->addStmtToMethodIfNotThereAfterGlobalDIC(
            $classMethod,
            $class,
            $expression
        );

        return $variable;
    }

    public function ensureDICDependency(
        string $name,
        Class_ $class,
        ClassMethod $classMethod
    ): Expr {
        $DICMember = $this->getDICMemberByName($name);

        // return simple $GLOBALS access in static methods or
        // return simple $GLOBALS access in static methods if we are in
        // constructor itself, since currently we have problems to assign the
        // member then...
        $classMethodName = $classMethod->name->name ?? null;
        if ($classMethod->isStatic()) {
            return $this->getStaticDICCall($DICMember, $class, $classMethod);
        }
        if ($classMethodName === \Rector\Core\ValueObject\MethodName::CONSTRUCT) {
            return $this->getStaticDICCall($DICMember, $class, $classMethod);
        }

        // Test primary class
        $mainClass = $DICMember->getMainClass();
        $dicPropertyFetch = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass(
            $class,
            $classMethod,
            $this->getObjectType($mainClass)
        );
        if ($dicPropertyFetch instanceof PropertyFetch) {
            return $dicPropertyFetch;
        }

        // try alternatives
        $alternatives = $DICMember->getAlternativeClasses();
        foreach ($alternatives as $alternative) {
            $dicPropertyFetch = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass(
                $class,
                $classMethod,
                $this->getObjectType($alternative)
            );
            if ($dicPropertyFetch instanceof PropertyFetch) {
                return $dicPropertyFetch;
            }
        }

        // Add property
        $this->propertyToAddCollector->addPropertyWithoutConstructorToClass(
            $DICMember->getPropertyName(),
            $this->getObjectType($mainClass),
            $class
        );

        $dicPropertyFetch = new PropertyFetch(
            new Variable(self::THIS),
            $DICMember->getPropertyName()
        );
        // Method call to get DIC dependency
        $methodCall = $this->appendDICMethods(
            $DICMember,
            new Variable(self::DIC)
        );
        // global $DIC
        $this->dicDependencyManipulator->ensureGlobalDICinConstructor(
            $class
        );
        // $this->xy = $DIC->xy()
        $expression = new Expression(
            new Assign(
                $dicPropertyFetch,
                $methodCall
            )
        );
        $this->dicDependencyManipulator->addStmtToConstructorIfNotThereAfterGlobalDIC(
            $class,
            $expression
        );

        return $dicPropertyFetch;
    }

    private function appendDICMethods(DICMember $dicMember, Expr $expr): \PhpParser\Node\Expr
    {
        foreach ($dicMember->getDicServiceMethod() as $call) {
            $expr = new MethodCall(
                $expr,
                $call
            );
        }
        return $expr;
    }

    private function getDICMemberByName(string $name): DICMember
    {
        return $this->DICMemberMap->getByName($name);
    }

    private function getObjectType(string $name): ObjectType
    {
        return new ObjectType($name);
    }
}
