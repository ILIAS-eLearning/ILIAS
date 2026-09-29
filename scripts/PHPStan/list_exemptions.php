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

/**
 * Lists every rule-violation exemption granted in the code base.
 *
 * Exemptions are granted per ILIAS major version and expire with the next one, so
 * this is the list somebody has to walk through when the version is bumped. Run it
 * through scripts/PHPStan/list_exemptions.sh.
 */

namespace ILIAS\Scripts\PHPStan;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

require_once __DIR__ . '/../../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/IliasVersion.php';

/** Attribute short names that grant an exemption. */
const EXEMPTION_ATTRIBUTES = ['AllowRuleViolation', 'AllowSuperglobalWrite'];

$target = 'components/ILIAS';
$against = IliasVersion::major();
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--version=')) {
        $against = (int) substr($argument, strlen('--version='));
        continue;
    }
    $target = rtrim($argument, '/');
}

if (!is_dir($target)) {
    fwrite(STDERR, "Not a directory: {$target}\n");
    exit(2);
}

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$printer = new PrettyPrinter();

/**
 * Collects the exemption attributes of one file together with the declaration they
 * sit on.
 */
final class ExemptionVisitor extends NodeVisitorAbstract
{
    /** @var list<array{line:int, on:string, rules:string, version:?int, reason:string}> */
    public array $found = [];

    /** @var list<string> */
    private array $scope = [];

    public function __construct(private readonly PrettyPrinter $printer)
    {
    }

    public function enterNode(Node $node): null
    {
        if ($node instanceof Node\Stmt\ClassLike && $node->name !== null) {
            $this->scope[] = $node->name->toString();
        }
        if ($node instanceof Node\Stmt\ClassMethod || $node instanceof Node\Stmt\Function_) {
            $this->scope[] = $node->name->toString() . '()';
        }

        if ($node instanceof Node\Stmt\ClassLike
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Stmt\Function_) {
            $this->collect($node);
        }

        return null;
    }

    public function leaveNode(Node $node): null
    {
        if ($node instanceof Node\Stmt\ClassLike && $node->name !== null) {
            array_pop($this->scope);
        }
        if ($node instanceof Node\Stmt\ClassMethod || $node instanceof Node\Stmt\Function_) {
            array_pop($this->scope);
        }

        return null;
    }

    private function collect(Node\Stmt\ClassLike|Node\Stmt\ClassMethod|Node\Stmt\Function_ $node): void
    {
        foreach ($node->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                $short = $attribute->name->getLast();
                if (!in_array($short, EXEMPTION_ATTRIBUTES, true)) {
                    continue;
                }

                $reason = null;
                $version = null;
                $rules = [];
                foreach ($attribute->args as $argument) {
                    $value = $argument->value;
                    if ($value instanceof Node\Scalar\Int_) {
                        $version ??= $value->value;
                        continue;
                    }
                    if ($value instanceof Node\Scalar\String_) {
                        if ($reason === null) {
                            $reason = $value->value;
                        } else {
                            $rules[] = $value->value;
                        }
                        continue;
                    }
                    // concatenations of strings and ::class constants
                    $reason ??= $this->text($value);
                }

                if ($rules === []) {
                    $rules = [$short === 'AllowSuperglobalWrite' ? 'ilias.superglobalWrite' : '(unspecified)'];
                }

                $this->found[] = [
                    'line' => $attribute->getStartLine(),
                    'on' => implode('::', $this->scope),
                    'rules' => implode(', ', $rules),
                    'version' => $version,
                    'reason' => self::flatten($reason ?? ''),
                ];
            }
        }
    }

    /**
     * Best-effort rendering of an argument that is not a plain string literal,
     * so a reason built by concatenation still reads like a sentence.
     */
    private function text(Node\Expr $expr): string
    {
        if ($expr instanceof Node\Scalar\String_) {
            return $expr->value;
        }
        if ($expr instanceof Node\Expr\BinaryOp\Concat) {
            return $this->text($expr->left) . $this->text($expr->right);
        }
        if ($expr instanceof Node\Expr\ClassConstFetch
            && $expr->name instanceof Node\Identifier
            && $expr->name->toString() === 'class'
            && $expr->class instanceof Node\Name) {
            return $expr->class->getLast();
        }

        return $this->printer->prettyPrintExpr($expr);
    }

    private static function flatten(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $text));
    }
}

$rows = [];

$iterator = new \RecursiveIteratorIterator(
    new \RecursiveCallbackFilterIterator(
        new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS),
        static fn(\SplFileInfo $file): bool =>
            !in_array($file->getFilename(), ['node_modules', 'vendor', 'libs', 'lib'], true)
    )
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $code = (string) file_get_contents($path);

    // attribute-based exemptions
    if (str_contains($code, 'AllowRuleViolation') || str_contains($code, 'AllowSuperglobalWrite')) {
        try {
            $ast = $parser->parse($code);
        } catch (\Throwable $e) {
            fwrite(STDERR, "Could not parse {$path}: {$e->getMessage()}\n");
            $ast = null;
        }
        if ($ast !== null) {
            $visitor = new ExemptionVisitor($printer);
            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);
            foreach ($visitor->found as $entry) {
                $rows[] = $entry + ['file' => $path, 'kind' => 'attribute'];
            }
        }
    }

    // inline ignores
    if (str_contains($code, '@phpstan-ignore')) {
        foreach (explode("\n", $code) as $index => $line) {
            if (!preg_match('/@phpstan-ignore\s+(ilias\.[A-Za-z0-9_.]+)\s*(?:\((.*)\))?/', $line, $match)) {
                continue;
            }
            $rule = $match[1];
            $version = null;
            if (preg_match('/^(.*)\.v(\d+)$/', $rule, $version_match)) {
                $rule = $version_match[1];
                $version = (int) $version_match[2];
            }
            $rows[] = [
                'file' => $path,
                'line' => $index + 1,
                'on' => '(statement)',
                'rules' => $rule,
                'version' => $version,
                'reason' => trim($match[2] ?? ''),
                'kind' => 'inline',
            ];
        }
    }
}

usort($rows, static fn(array $a, array $b): int => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);

$expired = 0;
$unversioned = 0;
$grouped = [];
foreach ($rows as $row) {
    $parts = explode('/', $row['file']);
    $component = ($parts[0] === 'components' && isset($parts[2])) ? $parts[2] : $parts[0];
    $grouped[$component][] = $row;
}

echo "Rule-violation exemptions, checked against ILIAS {$against}\n";
echo str_repeat('=', 72), "\n";

foreach ($grouped as $component => $entries) {
    echo "\n", $component, "\n";
    foreach ($entries as $row) {
        if ($row['version'] === null) {
            $status = 'NO VERSION';
            $unversioned++;
        } elseif ($row['version'] < $against) {
            $status = 'EXPIRED';
            $expired++;
        } else {
            $status = 'valid for ' . $row['version'];
        }

        printf(
            "  [%-12s] %s:%d\n      %s  on %s\n      %s\n",
            $status,
            $row['file'],
            $row['line'],
            $row['rules'],
            $row['on'],
            $row['reason'] === '' ? '(no reason given)' : $row['reason']
        );
    }
}

$total = count($rows);
echo "\n", str_repeat('-', 72), "\n";
printf("%d exemption(s); %d expired, %d without a version\n", $total, $expired, $unversioned);

exit(($expired + $unversioned) > 0 ? 1 : 0);
