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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * tools/language-identifier-enum/generate_language_identifier_enum.php as a process, run from a copy in a throwaway root
 * (it derives its repository root from its own location); the generated enum is loaded in a separate process.
 */
class LanguageIdentifierEnumToolTest extends TestCase
{
    private const string TOOL = __DIR__ . '/../tools/language-identifier-enum/generate_language_identifier_enum.php';
    private const string NAMESPACE = 'ILIAS\Demo';
    private const string POT = 'components/ILIAS/Demo/lang/demo.pot';
    private const string ENUM = 'components/ILIAS/Demo/src/DemoIdentifier.php';

    private string $directory;
    private string $root;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/ilias_key_enum_tool_' . bin2hex(random_bytes(4));
        $this->root = $this->directory . '/repo';
        $tool_dir = $this->root . '/components/ILIAS/Language/tools/language-identifier-enum';
        mkdir($tool_dir, 0775, true);
        mkdir($this->root . '/components/ILIAS/Demo/lang', 0775, true);
        mkdir($this->root . '/components/ILIAS/Demo/src', 0775, true);
        mkdir($this->directory . '/outside', 0775, true);
        copy(self::TOOL, $tool_dir . '/generate_language_identifier_enum.php');
        mkdir($this->root . '/vendor/composer/vendor', 0775, true);
        $real_autoload = realpath(__DIR__ . '/../../../../vendor/composer/vendor/autoload.php');
        $this->assertIsString($real_autoload);
        symlink($real_autoload, $this->root . '/vendor/composer/vendor/autoload.php');
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->directory);
    }

    /** @return array{int, string, string} exit code, stdout, stderr */
    private function runTool(string ...$arguments): array
    {
        $result = ChildPhp::run(
            [
                '-d', 'error_reporting=-1', '-d', 'display_errors=stderr',
                $this->root . '/components/ILIAS/Language/tools/language-identifier-enum/generate_language_identifier_enum.php',
                ...$arguments,
            ],
            $this->root
        );
        $this->assertNotNull($result, 'could not start the child PHP process');

        return $result;
    }

    /** @param list<string> $messages msgids; "plural:<id>" a plural message, "ctx:<context>:<id>" one with a msgctxt */
    private function writePot(array $messages, string $header = "\"X-Domain: demo\\n\"\n", string $path = self::POT): void
    {
        $pot = "msgid \"\"\nmsgstr \"\"\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n" . $header . "\n";
        foreach ($messages as $message) {
            if (str_starts_with($message, 'plural:')) {
                $id = substr($message, 7);
                $pot .= "msgid \"$id\"\nmsgid_plural \"{$id}s\"\nmsgstr[0] \"\"\nmsgstr[1] \"\"\n\n";
            } elseif (str_starts_with($message, 'ctx:')) {
                [, $context, $id] = explode(':', $message, 3);
                $pot .= "msgctxt \"$context\"\nmsgid \"$id\"\nmsgstr \"\"\n\n";
            } else {
                $pot .= "msgid \"$message\"\nmsgstr \"\"\n\n";
            }
        }
        file_put_contents($this->root . '/' . $path, $pot);
    }

    /** @return array{int, string, string} */
    private function generate(string ...$options): array
    {
        return $this->runTool(...$options, ...['--namespace=' . self::NAMESPACE, self::POT, self::ENUM]);
    }

    /** @return array{module: string, cases: array<string, string>} */
    private function loadEnum(string $file = self::ENUM, string $class = self::NAMESPACE . '\DemoIdentifier'): array
    {
        $code = sprintf(
            'require_once %s; require %s; $m = []; foreach (%s::cases() as $c) { $m[$c->name] = $c->value; } '
            . 'echo json_encode(["module" => %s::cases() === [] ? "" : %s::cases()[0]->module(), "cases" => $m, "is_key" => is_a(%s::class, "ILIAS\\\\Language\\\\LanguageIdentifier", true)]);',
            var_export((string) realpath(__DIR__ . '/../../../../vendor/composer/vendor/autoload.php'), true),
            var_export($this->root . '/' . $file, true),
            $class,
            $class,
            $class,
            $class
        );
        file_put_contents($this->directory . '/load.php', '<?php ' . $code);
        $result = ChildPhp::run(
            ['-d', 'error_reporting=-1', '-d', 'display_errors=stderr', $this->directory . '/load.php']
        );
        $this->assertNotNull($result, 'could not start the child PHP process');
        [$exit, $stdout, $stderr] = $result;
        $this->assertSame(0, $exit, $stderr);
        $this->assertSame('', $stderr, 'no warning or deprecation');
        $decoded = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['is_key']);

        return ['module' => $decoded['module'], 'cases' => $decoded['cases']];
    }

    public function testGeneratesCaseNamesAndResolvesCollisionsDeterministically(): void
    {
        $this->writePot([
            'poll_answers', 'a-b.c', '1st', 'class', 'Class', 'style', 'Style', 'STYLE', 'a b', 'a-b',
            'plural:item', 'ctx:demo:with_context', 'Ünï', 'x_2', 'x', 'X',
        ]);

        [$exit, $stdout, $stderr] = $this->generate();

        $this->assertSame(0, $exit, $stderr);
        $this->assertStringContainsString('Written:', $stdout);
        $this->assertStringContainsString('NOTE: Case name STYLE: "style" -> STYLE, "STYLE" -> STYLE_2, "Style" -> STYLE_3', $stderr);
        $this->assertStringContainsString('NOTE: Case name KEY_CLASS: "class" -> KEY_CLASS, "Class" -> KEY_CLASS_2', $stderr);
        $loaded = $this->loadEnum();
        $this->assertSame('demo', $loaded['module']);
        $this->assertEquals([
            'POLL_ANSWERS' => 'poll_answers',
            'A_B_C' => 'a-b.c',
            'KEY_1ST' => '1st',
            'KEY_CLASS' => 'class',
            'KEY_CLASS_2' => 'Class',
            'STYLE' => 'style',
            'STYLE_2' => 'STYLE',
            'STYLE_3' => 'Style',
            'A_B' => 'a b',
            'A_B_2' => 'a-b',
            'ITEM' => 'item',
            'WITH_CONTEXT' => 'with_context',
            '_N_' => 'Ünï',
            'X_2' => 'x_2',
            'X' => 'x',
            'X_3' => 'X',
        ], $loaded['cases']);
    }

    public function testEveryMsgidIsExactlyOneCaseWithItsValue(): void
    {
        $identifiers = ['one', 'Two', 'three_3', '4four', 'class', 'CLASS', 'a-a', 'a_a', 'A.A', 'plural_message'];
        $this->writePot(array_map(static fn(string $i): string => $i === 'plural_message' ? 'plural:plural_message' : $i, $identifiers));

        [$exit, , $stderr] = $this->generate();

        $this->assertSame(0, $exit, $stderr);
        $cases = $this->loadEnum()['cases'];
        $this->assertCount(count($identifiers), $cases);
        $values = array_values($cases);
        sort($values);
        $expected = $identifiers;
        sort($expected);
        $this->assertSame($expected, $values, 'no identifier lost, none doubled, the msgid_plural is no case');
        foreach (array_keys($cases) as $name) {
            $this->assertMatchesRegularExpression('/^[A-Z_][A-Z0-9_]*$/', $name);
        }
    }

    public function testSecondRunIsUnchangedAndStdoutPrintsWhatWouldBeWritten(): void
    {
        $this->writePot(['one', 'two']);
        [, $stdout] = $this->generate('--stdout');
        $this->assertFileDoesNotExist($this->root . '/' . self::ENUM, '--stdout writes nothing');

        $this->assertSame(0, $this->generate()[0]);
        $this->assertSame($stdout, (string) file_get_contents($this->root . '/' . self::ENUM));
        $before = fileperms($this->root . '/' . self::ENUM);
        [$exit, $again] = $this->generate();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Unchanged:', $again);
        $this->assertSame($before, fileperms($this->root . '/' . self::ENUM));
        $this->assertSame(['.', '..', 'DemoIdentifier.php'], scandir($this->root . '/components/ILIAS/Demo/src'), 'no temporary file left');
    }

    /** @return iterable<string, array{?string, string, string}> */
    public static function modules(): iterable
    {
        yield 'option wins' => ['--module=from_option', "\"X-Domain: from_header\\n\"\n", 'from_option'];
        yield 'X-Domain header' => [null, "\"X-Domain: from_header\\n\"\n", 'from_header'];
        yield 'file name' => [null, '', 'demo'];
    }

    #[DataProvider('modules')]
    public function testModuleComesFromTheOptionTheHeaderOrTheFileName(?string $option, string $header, string $expected): void
    {
        $this->writePot(['one'], $header);

        [$exit, , $stderr] = $this->runTool(...array_filter([$option, '--namespace=' . self::NAMESPACE, self::POT, self::ENUM]));

        $this->assertSame(0, $exit, $stderr);
        $this->assertSame($expected, $this->loadEnum()['module']);
    }

    public function testAnEmptyPotGivesAnEnumWithoutCasesThatStillLoads(): void
    {
        $this->writePot([]);

        [$exit, , $stderr] = $this->generate();

        $this->assertSame(0, $exit, $stderr);
        $this->assertSame([], $this->loadEnum()['cases']);
        $this->assertSame(0, $this->generate('--check')[0]);
    }

    public function testTheGeneratedFileIsInTheNamespaceOfTheOptionWithoutBackslashOnlyInTheGlobalOne(): void
    {
        $this->writePot(['one']);

        [$exit, $stdout] = $this->runTool('--stdout', '--namespace=ILIAS\Language', self::POT, 'components/ILIAS/Demo/src/DemoIdentifier.php');

        $this->assertSame(0, $exit);
        $this->assertStringContainsString("namespace ILIAS\\Language;\n", $stdout);
        $this->assertStringNotContainsString('use ILIAS\\Language\\LanguageIdentifier;', $stdout, 'no import of its own namespace');
        $this->assertStringContainsString('enum DemoIdentifier: string implements LanguageIdentifier', $stdout);
    }

    public function testCheckPassesOnWhatWasGenerated(): void
    {
        $this->writePot(['one', 'plural:two', 'Style', 'style']);
        $this->generate();

        [$exit, $stdout, $stderr] = $this->generate('--check');

        $this->assertSame(0, $exit, $stderr);
        $this->assertStringContainsString('OK:', $stdout);
    }

    /** @return iterable<string, array{callable(string): string, string}> */
    public static function deviations(): iterable
    {
        yield 'surplus case' => [static fn(string $code): string => str_replace("    public function module", "    case EXTRA = 'extra';\n\n    public function module", $code), 'Surplus case EXTRA'];
        yield 'missing case' => [static fn(string $code): string => str_replace("    case TWO = 'two';\n", '', $code), 'Missing case TWO'];
        yield 'wrong value' => [static fn(string $code): string => str_replace("case TWO = 'two'", "case TWO = 'zwei'", $code), 'Case TWO has the value'];
        yield 'wrong module' => [static fn(string $code): string => str_replace("return 'demo';", "return 'other';", $code), 'module() returns'];
        yield 'not a LanguageIdentifier' => [static fn(string $code): string => str_replace(' implements LanguageIdentifier', '', $code), 'does not implement'];
        yield 'not string backed' => [static fn(string $code): string => (string) preg_replace("/case (\w+) = '[^']*';/", 'case $1 = 1;', str_replace('enum DemoIdentifier: string', 'enum DemoIdentifier: int', $code)), 'not backed by string'];
    }

    /** @param callable(string): string $break */
    #[DataProvider('deviations')]
    public function testCheckReportsADeviationWithExitCodeOne(callable $break, string $message): void
    {
        $this->writePot(['one', 'two']);
        $this->generate();
        $file = $this->root . '/' . self::ENUM;
        $broken = $break((string) file_get_contents($file));
        $this->assertNotSame(file_get_contents($file), $broken, 'the fixture really breaks the enum');
        file_put_contents($file, $broken);

        [$exit, , $stderr] = $this->generate('--check');

        $this->assertSame(1, $exit, $stderr);
        $this->assertStringContainsString('DEVIATION: ', $stderr);
        $this->assertStringContainsString($message, $stderr);
    }

    public function testCheckNoticesAChangedPot(): void
    {
        $this->writePot(['one', 'two']);
        $this->generate();
        $this->writePot(['one', 'two', 'three']);

        [$exit, , $stderr] = $this->generate('--check');

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('Missing case THREE', $stderr);
    }

    public function testCheckNeverWritesAndNeedsAnExistingEnum(): void
    {
        $this->writePot(['one']);

        [$exit, , $stderr] = $this->generate('--check');

        $this->assertSame(2, $exit);
        $this->assertStringContainsString('does not exist', $stderr);
        $this->assertFileDoesNotExist($this->root . '/' . self::ENUM);
    }

    public function testCheckOfAFileThatDeclaresAnotherEnumIsAnInputError(): void
    {
        $this->writePot(['one']);
        file_put_contents($this->root . '/' . self::ENUM, "<?php\nnamespace Other;\nenum DemoIdentifier: string { case ONE = 'one'; }\n");

        [$exit, , $stderr] = $this->generate('--check');

        $this->assertSame(2, $exit);
        $this->assertStringContainsString('does not declare the enum', $stderr);
    }

    /** @return iterable<string, array{list<string>, string}> arguments ("POT", "ENUM", "NS" are the defaults), stderr */
    public static function invalidInput(): iterable
    {
        yield 'no namespace' => [['POT', 'ENUM'], 'Usage:'];
        yield 'one path missing' => [['NS', 'POT'], 'Usage:'];
        yield 'three paths' => [['NS', 'POT', 'ENUM', 'ENUM'], 'Usage:'];
        yield 'unknown option' => [['NS', '--force', 'POT', 'ENUM'], 'Unknown option'];
        yield 'check and stdout' => [['NS', '--check', '--stdout', 'POT', 'ENUM'], 'exclude each other'];
        yield 'leading backslash' => [['--namespace=\ILIAS\Demo', 'POT', 'ENUM'], 'Invalid namespace'];
        yield 'empty namespace' => [['--namespace=', 'POT', 'ENUM'], 'Invalid namespace'];
        yield 'digit namespace' => [['--namespace=ILIAS\1Demo', 'POT', 'ENUM'], 'Invalid namespace'];
        yield 'namespace with space' => [['--namespace=ILIAS Demo', 'POT', 'ENUM'], 'Invalid namespace'];
        yield 'namespace with code' => [['--namespace=A;eval($x)', 'POT', 'ENUM'], 'Invalid namespace'];
        yield 'double backslash' => [['--namespace=ILIAS\\\\Demo', 'POT', 'ENUM'], 'Invalid namespace'];
        yield 'module with path' => [['NS', '--module=../x', 'POT', 'ENUM'], 'Invalid module'];
        yield 'module with space' => [['NS', '--module=a b', 'POT', 'ENUM'], 'Invalid module'];
        yield 'empty module' => [['NS', '--module=', 'POT', 'ENUM'], 'Invalid module'];
        yield 'enum name with dash' => [['NS', 'POT', 'components/ILIAS/Demo/src/a-b.php'], 'no valid enum name'];
        yield 'enum name with digit first' => [['NS', 'POT', 'components/ILIAS/Demo/src/1Key.php'], 'no valid enum name'];
        yield 'enum no php' => [['NS', 'POT', 'components/ILIAS/Demo/src/DemoIdentifier.txt'], 'no .php file'];
        yield 'enum directory missing' => [['NS', 'POT', 'components/ILIAS/Demo/nowhere/DemoIdentifier.php'], 'does not exist'];
        yield 'pot missing' => [['NS', 'components/ILIAS/Demo/lang/none.pot', 'ENUM'], 'no .pot file'];
        yield 'pot is no pot' => [['NS', 'components/ILIAS/Demo/lang/demo.txt', 'ENUM'], 'no .pot file'];
        yield 'pot outside by ..' => [['NS', 'components/../../outside/x.pot', 'ENUM'], 'no .pot file'];
        yield 'enum outside by ..' => [['NS', 'POT', 'components/../../outside/DemoIdentifier.php'], 'no .php file path'];
        yield 'enum with .. inside missing part' => [['NS', 'POT', 'components/ILIAS/Demo/new/../src/DemoIdentifier.php'], 'no .php file path'];
        yield 'enum absolute outside' => [['NS', 'POT', '/tmp/DemoIdentifier.php'], 'no .php file path'];
        yield 'enum is the root' => [['NS', 'POT', '.php'], 'is no valid enum name'];
        yield 'pot through a symlink out' => [['NS', 'linked/outside.pot', 'ENUM'], 'no .pot file'];
        yield 'enum through a symlink out' => [['NS', 'POT', 'linked/DemoIdentifier.php'], 'no .php file path'];
        yield 'enum is a dangling symlink out' => [['NS', 'POT', 'components/ILIAS/Demo/src/Dangling.php'], 'Refusing|no .php file path'];
    }

    /** @param list<string> $arguments */
    #[DataProvider('invalidInput')]
    public function testInvalidInputIsRefusedWithExitCodeTwoAndNothingIsWritten(array $arguments, string $stderr_pattern): void
    {
        $this->writePot(['one']);
        file_put_contents($this->directory . '/outside/outside.pot', "msgid \"\"\nmsgstr \"\"\n\nmsgid \"x\"\nmsgstr \"\"\n");
        symlink($this->directory . '/outside', $this->root . '/linked');
        symlink($this->directory . '/outside/target.php', $this->root . '/components/ILIAS/Demo/src/Dangling.php');
        $arguments = array_map(static fn(string $a): string => match ($a) {
            'POT' => self::POT,
            'ENUM' => self::ENUM,
            'NS' => '--namespace=' . self::NAMESPACE,
            default => $a,
        }, $arguments);

        [$exit, $stdout, $stderr] = $this->runTool(...$arguments);

        $this->assertSame(2, $exit, $stdout . $stderr);
        $this->assertMatchesRegularExpression('/ERROR: .*(' . $stderr_pattern . ')/', $stderr);
        $this->assertSame([], glob($this->directory . '/outside/*.php') ?: []);
        $this->assertSame(
            [$this->root . '/components/ILIAS/Demo/src/Dangling.php'],
            glob($this->root . '/components/ILIAS/Demo/src/*') ?: [],
            'no enum written'
        );
    }

    public function testAnUnsupportedMsgctxtIsRefused(): void
    {
        $this->writePot(['one', 'ctx:other:two']);

        [$exit, , $stderr] = $this->generate();

        $this->assertSame(2, $exit);
        $this->assertStringContainsString('msgctxt "other"', $stderr);
        $this->assertFileDoesNotExist($this->root . '/' . self::ENUM);
    }

    public function testAnInvalidModuleInTheHeaderIsRefused(): void
    {
        $this->writePot(['one'], "\"X-Domain: ../../x\\n\"\n");

        [$exit, , $stderr] = $this->generate();

        $this->assertSame(2, $exit);
        $this->assertStringContainsString('Invalid module', $stderr);
    }

    public function testControlCharactersFromTheInputAreNotPrintedRaw(): void
    {
        $this->writePot(['one']);

        [$exit, , $stderr] = $this->runTool("--namespace=A\x1b[31mB", self::POT, self::ENUM);

        $this->assertSame(2, $exit);
        $this->assertStringNotContainsString("\x1b", $stderr);
        $this->assertStringContainsString('\x1B', $stderr);
    }

    /** @return iterable<string, array{string}> a path of the .pot below components/ILIAS/Demo/lang */
    public static function unsafePotPaths(): iterable
    {
        yield 'asterisk' => ['a*b/demo.pot'];
        yield 'space' => ['a b/demo.pot'];
        yield 'comment end' => ['x*/y/demo.pot'];
        yield 'code in directory names' => ["x*/system('echo PWNED');/*/demo.pot"];
        yield 'quote' => ["it's/demo.pot"];
    }

    #[DataProvider('unsafePotPaths')]
    public function testAPotPathWithOtherThanPlainPathCharactersIsRefused(string $relative): void
    {
        $path = 'components/ILIAS/Demo/lang/' . $relative;
        mkdir(dirname($this->root . '/' . $path), 0775, true);
        $this->writePot(['one'], "\"X-Domain: demo\\n\"\n", $path);

        [$exit, $stdout, $stderr] = $this->runTool('--namespace=' . self::NAMESPACE, $path, self::ENUM);

        $this->assertSame(2, $exit, $stdout . $stderr);
        $this->assertStringContainsString('may only contain', $stderr);
        $this->assertStringNotContainsString("PWNED\n", $stdout, 'nothing is executed');
        $this->assertFileDoesNotExist($this->root . '/' . self::ENUM);
        [$exit_stdout, $out] = $this->runTool('--stdout', '--namespace=' . self::NAMESPACE, $path, self::ENUM);
        $this->assertSame(2, $exit_stdout);
        $this->assertSame('', $out, '--stdout prints nothing either');
    }

    public function testAMsgidWithACommentEndStaysCodeAndIsChecked(): void
    {
        $this->writePot(['a*/b', '*/', "/* x", 'it\'s', 'ok']);

        [$exit, , $stderr] = $this->generate();

        $this->assertSame(0, $exit, $stderr);
        $file = $this->root . '/' . self::ENUM;
        $lint = ChildPhp::run(['-l', $file]);
        $this->assertNotNull($lint, 'could not start the child PHP process');
        $this->assertSame(0, $lint[0], $lint[1] . $lint[2]);
        $this->assertEqualsCanonicalizing(['a*/b', '*/', '/* x', "it's", 'ok'], array_values($this->loadEnum()['cases']));
        [$check_exit, , $check_stderr] = $this->generate('--check');
        $this->assertSame(0, $check_exit, $check_stderr);
    }

    public function testReservedCaseNamesGetThePrefix(): void
    {
        $this->writePot(['__halt_compiler', 'class', 'CLASS', 'Class']);

        [$exit, , $stderr] = $this->generate();

        $this->assertSame(0, $exit, $stderr);
        $cases = $this->loadEnum()['cases'];
        $this->assertSame('__halt_compiler', $cases['KEY___HALT_COMPILER'] ?? null);
        $this->assertSame('class', $cases['KEY_CLASS'] ?? null);
        $this->assertEqualsCanonicalizing(['KEY___HALT_COMPILER', 'KEY_CLASS', 'KEY_CLASS_2', 'KEY_CLASS_3'], array_keys($cases));
    }

    /**
     * @return iterable<string, array{string, int, string}> the module() method of an enum without
     *         cases, the exit code --check ends with, a message
     */
    public static function modulesOfAnEnumWithoutCases(): iterable
    {
        yield 'the module' => ["return 'demo';", 0, 'OK:'];
        yield 'a comment in between' => ["/* c */ return 'demo'; // x", 0, 'OK:'];
        yield 'another module' => ["return 'other';", 1, 'module() returns'];
        yield 'concatenation' => ["return 'de' . 'mo';", 1, 'does not just return'];
        yield 'call' => ["return strtolower('DEMO');", 1, 'does not just return'];
        yield 'double quoted' => ['return "demo";', 1, 'does not just return'];
        yield 'constant' => ['return self::class;', 1, 'does not just return'];
        yield 'more than the return' => ["echo 'x'; return 'demo';", 1, 'does not just return'];
        yield 'second statement' => ["return 'demo'; echo 'x';", 1, 'does not just return'];
    }

    #[DataProvider('modulesOfAnEnumWithoutCases')]
    public function testCheckReadsTheModuleOfAnEnumWithoutCasesFromItsBody(string $body, int $expected_exit, string $message): void
    {
        $this->writePot([]);
        $this->generate();
        $file = $this->root . '/' . self::ENUM;
        $code = (string) file_get_contents($file);
        $changed = str_replace("return 'demo';", $body, $code);
        $this->assertNotSame('', $changed);
        file_put_contents($file, $changed);

        [$exit, $stdout, $stderr] = $this->generate('--check');

        $this->assertSame($expected_exit, $exit, $stdout . $stderr);
        $this->assertStringContainsString($message, $stdout . $stderr);
    }

    public function testCheckOfAnEnumWithoutCasesDoesNotExecuteItsModuleMethod(): void
    {
        $this->writePot([]);
        $this->generate();
        $file = $this->root . '/' . self::ENUM;
        file_put_contents($file, str_replace("return 'demo';", "echo 'PWNED'; return 'demo';", (string) file_get_contents($file)));

        [$exit, $stdout] = $this->generate('--check');

        $this->assertSame(1, $exit);
        $this->assertStringNotContainsString('PWNED', $stdout);
    }

    public function testControlCharactersOfAMsgidAreMaskedInTheMessage(): void
    {
        $this->writePot(["a\x1b[31mb"]);

        [$exit, $stdout, $stderr] = $this->generate();

        $this->assertSame(2, $exit);
        $this->assertStringNotContainsString("\x1b", $stdout . $stderr);
        $this->assertFileDoesNotExist($this->root . '/' . self::ENUM);
    }
}
