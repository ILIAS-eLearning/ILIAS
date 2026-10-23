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

use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every module a component contributes via ComponentLanguageFileDirectory ships its .pot/_en.po and is gone from lang/ilias_*.lang.
 * Contributions are read from the source (string literals, also over several lines): module, path_inside_component and file name pattern.
 */
class ShippedPoModulesConsistencyTest extends TestCase
{
    private const string CALL = '/new\s+\\\\?(?:\w+\\\\)*ComponentLanguageFileDirectory\s*\(/';
    private const string CONTRIBUTION = '/new\s+\\\\?(?:\w+\\\\)*ComponentLanguageFileDirectory\s*\(\s*\$this\s*,\s*([\'"])([A-Za-z0-9_]+)\1'
        . '(?:\s*,\s*([\'"])([^\'"]*)\3(?:\s*,\s*(?:null|([\'"])([^\'"]*)\5))?)?\s*,?\s*\)/i';

    private static function root(): string
    {
        return (string) realpath(__DIR__ . '/../../../../../');
    }

    /** @return string the component class without comments */
    private static function code(string $file): string
    {
        $code = '';
        foreach (token_get_all((string) file_get_contents($file)) as $token) {
            if (!is_array($token) || !in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                $code .= is_array($token) ? $token[1] : $token;
            }
        }

        return $code;
    }

    /**
     * @return array{modules: array<string, list<array{file: string, lang_dir: string, pattern: string}>>, unparsed: list<string>}
     *         module => contributions; component classes with a contribution in another notation
     */
    private static function contributions(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $modules = [];
        $unparsed = [];
        foreach (glob(self::root() . '/components/*/*/*.php') ?: [] as $file) {
            if (basename($file, '.php') !== basename(dirname($file))) {
                continue;
            }
            $code = self::code($file);
            $calls = preg_match_all(self::CALL, $code);
            $parsed = preg_match_all(self::CONTRIBUTION, $code, $matches, PREG_SET_ORDER);
            if ($calls !== $parsed) {
                $unparsed[] = substr($file, strlen(self::root()) + 1);
            }
            foreach ($matches as $match) {
                $module = $match[2];
                $path = ($match[4] ?? '') === '' ? 'lang/' : $match[4];
                $modules[$module][] = [
                    'file' => $file,
                    'lang_dir' => dirname($file) . '/' . trim($path, '/'),
                    'pattern' => ($match[6] ?? '') === '' ? $module . '_%s' : $match[6],
                ];
            }
        }
        ksort($modules);

        return $cache = ['modules' => $modules, 'unparsed' => $unparsed];
    }

    /** @return array<string, array{0: string}> */
    public static function moduleProvider(): array
    {
        $cases = [];
        foreach (array_keys(self::contributions()['modules']) as $module) {
            $cases[$module] = [$module];
        }

        return $cases;
    }

    public function testContributionsAreFound(): void
    {
        self::assertGreaterThan(60, count(self::contributions()['modules']));
        self::assertSame([], self::contributions()['unparsed'], 'ComponentLanguageFileDirectory with arguments other than string literals');
    }

    public function testEveryShippedPotIsContributed(): void
    {
        $contributed = [];
        foreach (self::contributions()['modules'] as $module => $contributions) {
            foreach ($contributions as $contribution) {
                $contributed[$contribution['lang_dir'] . '/' . MigratedLanguageFilePaths::templateFileName($contribution['pattern'], $module)] = true;
            }
        }
        $uncontributed = [];
        foreach (glob(self::root() . '/components/*/*/lang/*.pot') ?: [] as $pot) {
            if (!isset($contributed[$pot])) {
                $uncontributed[] = substr($pot, strlen(self::root()) + 1);
            }
        }

        self::assertSame([], $uncontributed, '.pot without ComponentLanguageFileDirectory contribution');
    }

    #[DataProvider('moduleProvider')]
    public function testModuleIsContributedExactlyOnce(string $module): void
    {
        $contributions = self::contributions()['modules'][$module];
        self::assertCount(1, $contributions, "module '$module' contributed more than once: " . json_encode(array_column($contributions, 'file')));
    }

    #[DataProvider('moduleProvider')]
    public function testModuleShipsPotAndEnglishPo(string $module): void
    {
        foreach (self::contributions()['modules'][$module] as ['lang_dir' => $dir, 'pattern' => $pattern]) {
            self::assertFileExists("$dir/" . MigratedLanguageFilePaths::templateFileName($pattern, $module), "module '$module': .pot missing");
            self::assertFileExists("$dir/" . sprintf($pattern, 'en') . '.po', "module '$module': English .po missing");
        }
    }

    #[DataProvider('moduleProvider')]
    public function testModuleIsRemovedFromLegacyLangFiles(string $module): void
    {
        $offending = [];
        foreach (glob(self::root() . '/lang/ilias_*.lang') ?: [] as $file) {
            foreach (new SplFileObject($file) as $number => $line) {
                if (str_starts_with((string) $line, $module . '#:#')) {
                    $offending[] = basename($file) . ':' . ($number + 1);
                }
            }
        }

        self::assertSame([], $offending, "module '$module' still has lines in lang/ilias_*.lang");
    }
}
