<?php declare(strict_types=1);

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
 
namespace ILIAS\CI\Rector\DIC;

use Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\PostRector\Collector\PropertyToAddCollector;
use PhpParser\Node\Stmt\ClassLike;
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
    const DIC = 'DIC';
    const THIS = 'this';
    const GLOBALS = 'GLOBALS';
    protected TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver;
    protected DICDependencyManipulator $dicDependencyManipulator;
    protected PropertyToAddCollector $propertyToAddCollector;
    protected ClassInsertManipulator $classInsertManipulator;
    protected DICMemberMap $DICMemberMap;
    protected NodesToAddCollector $nodesToAddCollector;
    protected \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory;
    protected ConstructorClassMethodFactory $constructClassMethodFactory;
    protected \Rector\Core\NodeDecorator\PropertyTypeDecorator $propertyTypeDecorator;
    protected \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector;
    
    public function __construct(
        TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver,
        DICDependencyManipulator $classDependencyManipulator,
        PropertyToAddCollector $propertyToAddCollector,
        ClassInsertManipulator $classInsertManipulator,
        NodesToAddCollector $nodesToAddCollector,
        \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory,
        ConstructorClassMethodFactory $constructClassMethodFactory,
        \Rector\Core\NodeDecorator\PropertyTypeDecorator $propertyTypeDecorator,
        \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector
    ) {
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->dicDependencyManipulator = $classDependencyManipulator;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->nodeFactory = $nodeFactory;
        $this->constructClassMethodFactory = $constructClassMethodFactory;
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->DICMemberMap = new DICMemberMap();
    }
    
    /**
     * @param DICMember $DICMember
     * @return Expr|MethodCall
     */
    private function getStaticDICCall(
        DICMember $DICMember,
        Class_ $class,
        ClassMethod $classMethod
    ) {
        // $DIC;
        $dic_variable = $this->dicDependencyManipulator->ensureGlobalDICinMethod($classMethod, $class);
        // new variable like $main_tpl;
        $dic_dependenc_variable = new Variable($DICMember->getPropertyName());
        // MethodCall to get DIC Dependency
        $property_assign = new Expression(
            new Assign(
                $dic_dependenc_variable,
                $this->appendDICMethods(
                    $DICMember,
                    $dic_variable
                )
            )
        );
        $this->dicDependencyManipulator->addStmtToMethodIfNotThereAfterGlobalDIC(
            $classMethod,
            $class,
            $property_assign
        );
        
        return $dic_dependenc_variable;
    }
    
    public function ensureDICDependency(
        string $name,
        Class_ $class,
        ClassMethod $classMethod
    ) : Expr {
        $DICMember = $this->getDICMemberByName($name);
        
        // return simple $GLOBALS access in static methods or
        // return simple $GLOBALS access in static methods if we are in
        // constructor itself, since currently we have problems to assign the
        // member then...
        $classMethodName = $classMethod->name->name ?? null;
        if ($classMethod->isStatic()
            || $classMethodName === \Rector\Core\ValueObject\MethodName::CONSTRUCT) {
            return $this->getStaticDICCall($DICMember, $class, $classMethod);
        }
        
        // Test primary class
        $primary = $DICMember->getMainClass();
        $dicPropertyFetch = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass(
            $class,
            $classMethod,
            $this->getObjectType($primary)
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
            $this->getObjectType($primary),
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
        $property_assign = new Expression(
            new Assign(
                $dicPropertyFetch,
                $methodCall
            )
        );
        $this->dicDependencyManipulator->addStmtToConstructorIfNotThereAfterGlobalDIC(
            $class,
            $property_assign
        );
        
        return $dicPropertyFetch;
    }
    
    private function appendDICMethods(DICMember $m, Expr $methodCall)
    {
        foreach ($m->getDicServiceMethod() as $call) {
            $methodCall = new MethodCall(
                $methodCall,
                $call
            );
        }
        return $methodCall;
    }
    
    private function getDICMemberByName(string $name) : DICMember
    {
        return $this->DICMemberMap->getByName($name);
    }
    
    private function getObjectType(string $name) : ObjectType
    {
        return new ObjectType($name);
    }
}
