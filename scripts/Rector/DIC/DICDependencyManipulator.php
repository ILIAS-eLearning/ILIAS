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

namespace ILIAS\CI\Rector\DIC;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\Variable;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\Core\Exception\ShouldNotHappenException;

final class DICDependencyManipulator
{
    public const DIC = 'DIC';
    private array $duplicate_checker = [];
    private array $added_constructors = [];

    public function __construct(
        private \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory,
        private \Rector\Core\NodeManipulator\StmtsManipulator $stmtsManipulator,
        private \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator,
        private NodesToAddCollector $nodesToAddCollector,
        private \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder,
        private \Rector\Core\Contract\Console\OutputStyleInterface $outputStyle
    ) {
    }

    private function getDICVariable(): Variable
    {
        return new Variable(self::DIC);
    }

    private function getGlobalDIC(): Stmt\Global_
    {
        return new Stmt\Global_([$this->getDICVariable()]);
    }

    public function addStmtToMethodIfNotThereYetAtFirstPosition(
        ClassMethod $classMethod,
        Stmt\Class_ $class,
        Stmt $stmt
    ): void {
        $class_method_string = $class->name->name . '::' . $classMethod->name->name;
        $stmt_string = $this->nodeComparator->printWithoutComments($stmt);
        if (isset($this->duplicate_checker[$class_method_string][$stmt_string])
            && $this->duplicate_checker[$class_method_string][$stmt_string] === true) {
            return;
        }
        $stmts = $this->stmtsManipulator->filterOutExistingStmts(
            $classMethod,
            [$stmt]
        );
        // all stmts are already there → skip
        if ($stmts === []) {
            return;
        }
        $first = null;
        foreach ($classMethod->getStmts() as $inner_statement) {
            if ($inner_statement->getAttributes() === []) {
                continue;
            }
            $first = $inner_statement;
            break;
        }
        if ($first !== null) {
            $this->nodesToAddCollector->addNodeBeforeNode($stmt, $first);
        } else {
            $classMethod->stmts[] = $stmt;
        }
        $this->duplicate_checker[$class_method_string][$stmt_string] = true;
    }

    private function createConstructor(
        \PhpParser\Node\Stmt\Class_ $class
    ): ClassMethod {
        if (isset($this->added_constructors[$class->name->name])) {
            return $this->added_constructors[$class->name->name];
        }
        $classMethod = $this->nodeFactory->createPublicMethod(
            \Rector\Core\ValueObject\MethodName::CONSTRUCT
        );
        // implement parent constructor call
        if ($this->hasClassParentClassMethod(
            $class,
            \Rector\Core\ValueObject\MethodName::CONSTRUCT
        )) {
            $classMethod->stmts[] = $this->createParentClassMethodCall(
                \Rector\Core\ValueObject\MethodName::CONSTRUCT
            );
        }
        $first_class_method = array_filter($class->stmts, function (\PhpParser\Node $node): bool {
            return $node instanceof ClassMethod;
        });
        $first_class_method = array_shift($first_class_method);
        if ($first_class_method !== null) {
            $this->nodesToAddCollector->addNodeBeforeNode($classMethod, $first_class_method);
        } else {
            array_unshift($class->stmts, $classMethod);
        }
        $this->outputStyle->newline();
        $this->outputStyle->warning(
            'created constructor for ' . $class->name->name . '. Please check the parent-call for missing parameters!'
        );
        $this->outputStyle->newline();

        $this->added_constructors[$class->name->name] = $classMethod;

        return $classMethod;
    }

    public function addStmtToConstructorIfNotThereYetAtFirstPosition(
        \PhpParser\Node\Stmt\Class_ $class,
        Stmt $stmt
    ): void {
        $classMethod = $class->getMethod(
            \Rector\Core\ValueObject\MethodName::CONSTRUCT
        );
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $classMethod = $this->createConstructor($class);
        }
        $this->addStmtToMethodIfNotThereYetAtFirstPosition(
            $classMethod,
            $class,
            $stmt
        );
    }

    public function ensureGlobalDICinConstructor(Stmt\Class_ $class): void
    {
        $stmt = $this->getGlobalDIC();
        $this->addStmtToConstructorIfNotThereYetAtFirstPosition(
            $class,
            $stmt
        );
        $this->duplicate_checker[$class->name->name][$this->nodeComparator->printWithoutComments($stmt)] = true;
    }

    public function ensureGlobalDICinMethod(ClassMethod $classMethod, Stmt\Class_ $class): Variable
    {
        $this->addStmtToMethodIfNotThereYetAtFirstPosition(
            $classMethod,
            $class,
            $this->getGlobalDIC()
        );
        return $this->getDICVariable();
    }

    public function addStmtToMethodIfNotThereAfterGlobalDIC(
        ClassMethod $classMethod,
        Stmt\Class_ $class,
        Stmt $stmt
    ): void {
        $class_method_string = $class->name->name . '::' . $classMethod->name->name;
        $statement_string = $this->nodeComparator->printWithoutComments($stmt);
        if (isset($this->duplicate_checker[$class_method_string][$statement_string])
            && $this->duplicate_checker[$class_method_string][$statement_string] === true) {
            return;
        }
        $stmts = $this->stmtsManipulator->filterOutExistingStmts(
            $classMethod,
            [$stmt]
        );
        // all stmts are already there → skip
        if ($stmts === []) {
            return;
        }

        $node = $this->betterNodeFinder->findFirst($classMethod->stmts, function (\PhpParser\Node $node): bool {
            if (!$node instanceof Stmt\Global_) {
                return false;
            }
            foreach ($node->vars as $var) {
                if (!(property_exists($var, 'name') && $var->name !== null)) {
                    continue;
                }
                if ($var->name !== self::DIC) {
                    continue;
                }
                return true;
            }
            return false;
        });
        $dic_statement_string = $this->nodeComparator->printWithoutComments($this->getGlobalDIC());
        if (!$node instanceof \PhpParser\Node
            && !isset($this->duplicate_checker[$class_method_string][$dic_statement_string]) // we already added global $DIC in this run
            && !$this->duplicate_checker[$class_method_string][$dic_statement_string]
        ) {
            throw new ShouldNotHappenException(
                'no dic found: ' . $class_method_string . ' (' . $statement_string . ') '
            );
        }

        // get first existing statement
        $first_existing = array_filter($classMethod->stmts, function (\PhpParser\Node $node): bool {
            if ($node->getAttributes() === []) {
                return false;
            }
            return !$node instanceof Stmt\Global_;
        });
        $first_existing = array_shift($first_existing);
        if ($first_existing !== null) {
            $this->nodesToAddCollector->addNodeBeforeNode($stmt, $first_existing);
        } else {
            // we use a fallback to add the element in first place.
            // the nodesToAddCollector does not work here, becaue there are only
            // "new" nodes without position
            $classMethod->stmts[] = $stmt;
        }
        $this->duplicate_checker[$class_method_string][$statement_string] = true;
    }

    public function addStmtToConstructorIfNotThereAfterGlobalDIC(
        \PhpParser\Node\Stmt\Class_ $class,
        Stmt $stmt
    ): void {
        $classMethod = $class->getMethod(
            \Rector\Core\ValueObject\MethodName::CONSTRUCT
        );
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $classMethod = $this->createConstructor($class);
        }
        $this->addStmtToMethodIfNotThereAfterGlobalDIC(
            $classMethod,
            $class,
            $stmt
        );
    }

    private function hasClassParentClassMethod(
        \PhpParser\Node\Stmt\Class_ $class,
        string $methodName
    ): bool {
        $scope = $class->getAttribute(
            \Rector\NodeTypeResolver\Node\AttributeKey::SCOPE
        );
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }

    private function createParentClassMethodCall(
        string $methodName
    ): \PhpParser\Node\Stmt\Expression {
        $staticCall = new \PhpParser\Node\Expr\StaticCall(
            new \PhpParser\Node\Name(
                \Rector\Core\Enum\ObjectReference::PARENT()->getValue()
            ),
            $methodName
        );

        // append arguments

        return new \PhpParser\Node\Stmt\Expression($staticCall);
    }
}
