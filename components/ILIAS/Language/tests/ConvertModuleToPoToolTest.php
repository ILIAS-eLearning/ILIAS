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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Re-running tools/po-migration/convert_module_to_po.php for "tos" over the shipped files reproduces them byte for byte.
 */
class ConvertModuleToPoToolTest extends TestCase
{
    private const string TOOL = __DIR__ . '/../tools/po-migration/convert_module_to_po.php';
    private const string SHIPPED_DIRECTORY = __DIR__ . '/../../TermsOfService/lang';
    private const string POLL_SHIPPED_DIRECTORY = __DIR__ . '/../../Poll/lang';

    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/ilias_convert_tool_' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/out', 0775, true);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->directory);
    }

    /** @return array{int, string, string} exit code, stdout, stderr */
    private function runTool(string ...$arguments): array
    {
        return $this->runToolAt(self::TOOL, ...$arguments);
    }

    /**
     * The tool derives `$repo_root` from its own `__DIR__` (five levels up) with no override, so fixture
     * repos need a copy of the tool at the same depth in a throwaway root.
     *
     * @return array{int, string, string} exit code, stdout, stderr
     */
    private function runToolAt(string $tool_path, string ...$arguments): array
    {
        $result = ChildPhp::run(
            ['-d', 'error_reporting=-1', '-d', 'display_errors=stderr', $tool_path, ...$arguments]
        );
        $this->assertNotNull($result, 'could not start the child PHP process');

        return $result;
    }

    /** Throwaway root with a copy of the tool at the same relative depth and a symlinked Composer autoloader. */
    private function buildFixtureRepo(): string
    {
        $root = $this->directory . '/repo';
        $tool_dir = $root . '/components/ILIAS/Language/tools/po-migration';
        mkdir($tool_dir, 0775, true);
        mkdir($root . '/lang', 0775, true);
        mkdir($root . '/out', 0775, true);
        copy(self::TOOL, $tool_dir . '/convert_module_to_po.php');

        mkdir($root . '/vendor/composer/vendor', 0775, true);
        $real_autoload = realpath(__DIR__ . '/../../../../vendor/composer/vendor/autoload.php');
        $this->assertIsString($real_autoload, 'the real Composer autoloader was not found');
        symlink($real_autoload, $root . '/vendor/composer/vendor/autoload.php');

        return $root;
    }

    private function toolPathOf(string $fixture_root): string
    {
        return $fixture_root . '/components/ILIAS/Language/tools/po-migration/convert_module_to_po.php';
    }

    /**
     * Without plurals.json the tool writes no "Plural-Forms" header and no plural message.
     *
     * @param array<string, string> $plural_forms lang_key => "Plural-Forms" header
     * @param array<string, array<string, array{singular?: string, plural_id?: string}>> $modules
     *        module => msgid => definition
     */
    private function writePluralsConfig(string $fixture_root, array $plural_forms, array $modules): void
    {
        file_put_contents(
            $fixture_root . '/components/ILIAS/Language/tools/po-migration/plurals.json',
            (string) json_encode(['plural_forms' => $plural_forms, 'modules' => $modules], JSON_THROW_ON_ERROR)
        );
    }

    private function writeLangFile(string $fixture_root, string $lang_key, string $content): void
    {
        file_put_contents($fixture_root . '/lang/ilias_' . $lang_key . '.lang', $content);
    }

    /** @return array<string, string> file name => content */
    private static function files(string $directory): array
    {
        $files = [];
        foreach (glob($directory . '/*.{po,pot}', GLOB_BRACE) ?: [] as $file) {
            $files[basename($file)] = (string) file_get_contents($file);
        }
        ksort($files);

        return $files;
    }

    /** The tool keeps the headers of an existing target file, so it runs over a copy of the shipped files. */
    public function testReconvertingTosReproducesTheShippedFilesByteForByte(): void
    {
        $shipped = self::files(self::SHIPPED_DIRECTORY);
        $this->assertNotSame([], $shipped, 'the shipped tos files were not found');
        foreach ($shipped as $name => $content) {
            file_put_contents($this->directory . '/out/' . $name, $content);
        }

        [$exit_code, $stdout, $stderr] = $this->runTool('tos', 'en', $this->directory . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertSame('', $stderr, 'no warnings, notices or deprecations');
        $this->assertStringContainsString('round-trip identically through PO', $stdout);
        $this->assertSame(array_keys($shipped), array_keys(self::files($this->directory . '/out')), 'no file added or lost');
        foreach (self::files($this->directory . '/out') as $name => $content) {
            $this->assertSame($shipped[$name], $content, $name);
        }
    }

    /**
     * Plural pilot "poll": re-running reproduces the shipped files byte for byte, including single-form "ja".
     * A "n==1 -> 0, else 1" language gets msgstr[0] from the configured singular key and msgstr[1] from the
     * message's own value; otherwise every form gets the message's own value.
     */
    public function testReconvertingPollReproducesTheShippedFilesByteForByteIncludingPluralForms(): void
    {
        $shipped = self::files(self::POLL_SHIPPED_DIRECTORY);
        $this->assertNotSame([], $shipped, 'the shipped poll files were not found');
        $this->assertStringContainsString('msgid_plural "poll_population_plural"', $shipped['poll_de.po'] ?? '', 'precondition: poll_de.po has the expected plural message');
        foreach ($shipped as $name => $content) {
            file_put_contents($this->directory . '/out/' . $name, $content);
        }

        [$exit_code, $stdout, $stderr] = $this->runTool('--skip-unmigratable-keys', 'poll', 'en', $this->directory . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        // poll_import is missing in en, so the shipped files leave it out
        $this->assertMatchesRegularExpression('/\AWARNING: [^\n]+\n(  [a-z]+: poll_import\n)+\z/', $stderr, 'only poll_import is left out, no notices or deprecations');
        $this->assertStringContainsString('(0 entries filled from \'en\', flagged fuzzy)', $stdout);
        $this->assertSame(array_keys($shipped), array_keys(self::files($this->directory . '/out')), 'no file added or lost');
        foreach (self::files($this->directory . '/out') as $name => $content) {
            $this->assertSame($shipped[$name], $content, $name);
        }
    }

    /** Every language's keys must be a subset of the reference language's, else the run fails before writing. */
    public function testFailsWithExitOneAndWritesNothingWhenALanguageHasAKeyMissingFromTheReferenceLanguage(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");
        $this->writeLangFile($root, 'fr', "tst#:#greeting#:#Bonjour\ntst#:#extra#:#En trop\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString("reference language 'de' does not have", $stderr);
        $this->assertStringContainsString('fr: extra', $stderr);
        $this->assertSame([], glob($root . '/out/*') ?: [], 'nothing must be written once an inconsistency was found');
    }

    /** A value with several "#:#" is parsed like LanguageInstallationManager::insertLanguage(): first part only, plus a warning. */
    public function testWarnsOnAnExtraHashColonHashInAValueAndUsesOnlyThePartBeforeItLikeTheInstaller(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo#:#Extra\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString(
            'contains "#:#" in its value - only the part before it is used, like the installer does.',
            $stderr
        );
        $catalog = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_de.po');
        $entry = $catalog->find(null, 'greeting');
        $this->assertNotNull($entry);
        $this->assertSame('Hallo', $entry->getTranslation());
    }

    /** A note merely mentioning "new variable" (no leading date) stays an extracted comment, not the fuzzy flag. */
    public function testANoteThatMerelyMentionsNewVariableStaysANoteAndIsNotMarkedFuzzy(): void
    {
        $root = $this->buildFixtureRepo();
        $note = 'Bitte pruefen, ob hier evtl. eine neue Variable (englisch: new variable) gemeint war';
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo###$note\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $catalog = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_de.po');
        $entry = $catalog->find(null, 'greeting');
        $this->assertNotNull($entry);
        $this->assertFalse($entry->hasFlag('fuzzy'));
        $this->assertSame([$note], $entry->getExtractedComments());
    }

    /** Counterpart of the test above: the dated placeholder format does become the fuzzy flag. */
    public function testTheDatedNewVariablePlaceholderFormatIsMarkedFuzzyNotKeptAsANote(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");
        $this->writeLangFile($root, 'fr', "tst#:#greeting#:####13 3 2019 new variable\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $catalog = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_fr.po');
        $entry = $catalog->find(null, 'greeting');
        $this->assertNotNull($entry);
        $this->assertTrue($entry->hasFlag('fuzzy'));
        $this->assertSame([], $entry->getExtractedComments());
    }

    /**
     * A reference key a language lacks or has empty gets the reference value, flagged fuzzy; an own note of
     * the empty entry stays its extracted comment.
     */
    public function testAMissingOrEmptyValueGetsTheReferenceValueFlaggedFuzzy(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'en', "tst#:#greeting#:#Hello\ntst#:#bye#:#Bye\ntst#:#none#:#\n");
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");
        $this->writeLangFile($root, 'fr', "tst#:#greeting#:####Bitte uebersetzen\ntst#:#bye#:#Salut\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $out = dirname($this->toolPathOf($root)) . '/output/tst';
        $pot = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($out . '/tst.pot');
        $this->assertSame(['greeting', 'bye', 'none'], array_map(static fn($e): string => $e->getId(), $pot->getEntries()), 'msgids from en');

        $de = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($out . '/tst_de.po');
        $this->assertSame('Hallo', $de->find(null, 'greeting')?->getTranslation());
        $this->assertFalse($de->find(null, 'greeting')->hasFlag('fuzzy'));
        $this->assertSame('Bye', $de->find(null, 'bye')?->getTranslation(), 'no line: the en value');
        $this->assertTrue($de->find(null, 'bye')->hasFlag('fuzzy'));
        $this->assertSame('', $de->find(null, 'none')?->getTranslation(), 'an empty en value fills nothing');
        $this->assertFalse($de->find(null, 'none')->hasFlag('fuzzy'));

        $fr = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($out . '/tst_fr.po');
        $this->assertSame('Hello', $fr->find(null, 'greeting')?->getTranslation(), 'empty value: the en value');
        $this->assertTrue($fr->find(null, 'greeting')->hasFlag('fuzzy'));
        $this->assertSame(['Bitte uebersetzen'], $fr->find(null, 'greeting')->getExtractedComments());
        $this->assertFalse($fr->find(null, 'bye')?->hasFlag('fuzzy'));

        $this->assertMatchesRegularExpression('/^de\s+1\s+0\s+1\s+yes$/m', $stdout);
        $this->assertMatchesRegularExpression('/^en\s+3\s+0\s+0\s+yes$/m', $stdout);
        $this->assertMatchesRegularExpression('/^fr\s+2\s+0\s+1\s+yes$/m', $stdout);
        $this->assertStringContainsString("(2 entries filled from 'en', flagged fuzzy)", $stdout);
    }

    /** A missing plural message (and its singular key) gets the reference values, flagged fuzzy. */
    public function testAMissingPluralMessageGetsTheReferenceValuesFlaggedFuzzy(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'en', "tst#:#item#:#Entries\ntst#:#item_singular#:#One entry\n");
        $this->writeLangFile($root, 'de', "tst#:#item#:#Eintraege\n");
        $this->writeLangFile($root, 'fr', "tst#:#item_singular#:#Une entree\n");
        $this->writePluralsConfig(
            $root,
            ['en' => 'nplurals=2; plural=(n != 1);', 'de' => 'nplurals=2; plural=(n != 1);', 'fr' => 'nplurals=2; plural=(n > 1);'],
            ['tst' => ['item' => ['singular' => 'item_singular']]]
        );

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'en', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $de_item = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_de.po')->find(null, 'item');
        $this->assertSame(['Eintraege', 'Eintraege'], $de_item?->getPluralTranslations(), 'the singular key filled from the reference language is not taken over');
        $this->assertFalse($de_item->hasFlag('fuzzy'), 'de: a source language, its own value is present');
        $fr_item = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_fr.po')->find(null, 'item');
        $this->assertSame(['Entries', 'Entries'], $fr_item?->getPluralTranslations(), 'the en value in every form');
        $this->assertTrue($fr_item->hasFlag('fuzzy'));
        $this->assertStringContainsString('round-trip identically through PO', $stdout);
    }

    /** A singular key carrying the dated placeholder is not used: every form is the message's own value. */
    public function testAPluralMessageIsMarkedFuzzyWhenItsSingularKeyIsButOnlyWhereTheSingularValueIsActuallyUsed(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#item#:#Eintraege\ntst#:#item_singular#:#Ein Eintrag\n");
        $this->writeLangFile(
            $root,
            'fr',
            "tst#:#item#:#Des entrees\ntst#:#item_singular#:#Une entree###13 3 2019 new variable\n"
        );
        $this->writeLangFile(
            $root,
            'zh',
            "tst#:#item#:#Eintraege ZH\ntst#:#item_singular#:#Ein Eintrag ZH###13 3 2019 new variable\n"
        );
        $this->writePluralsConfig(
            $root,
            [
                'de' => 'nplurals=2; plural=(n != 1);',
                'fr' => 'nplurals=2; plural=(n != 1);',
                'zh' => 'nplurals=1; plural=0;',
            ],
            ['tst' => ['item' => ['singular' => 'item_singular']]]
        );

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $fr_item = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_fr.po')
            ->find(null, 'item');
        $this->assertNotNull($fr_item);
        $this->assertSame(['Des entrees', 'Des entrees'], $fr_item->getPluralTranslations(), 'a fuzzy singular key is not taken over');
        $this->assertTrue($fr_item->hasFlag('fuzzy'), 'fr: the own value is copied into both forms');

        $zh_item = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_zh.po')
            ->find(null, 'item');
        $this->assertNotNull($zh_item);
        $this->assertSame(['Eintraege ZH'], $zh_item->getPluralTranslations(), 'precondition: single form, own value - not the singular key');
        $this->assertFalse($zh_item->hasFlag('fuzzy'), 'zh: the singular key is never used here, so its fuzziness must not leak in');
    }

    /** Without a singular key the own value is copied into every form: fuzzy only for multi-form non-source languages. */
    public function testAPluralMessageWithoutASingularKeyIsFuzzyOnlyInATranslationWithSeveralForms(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#multi#:#Mehrere Werte\n");
        $this->writeLangFile($root, 'fr', "tst#:#multi#:#Plusieurs valeurs\n");
        $this->writeLangFile($root, 'zh', "tst#:#multi#:#Mehrere Werte ZH\n");
        $this->writePluralsConfig(
            $root,
            ['de' => 'nplurals=2; plural=(n != 1);', 'fr' => 'nplurals=2; plural=(n > 1);', 'zh' => 'nplurals=1; plural=0;'],
            ['tst' => ['multi' => []]]
        );

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $de_multi = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_de.po')
            ->find(null, 'multi');
        $this->assertNotNull($de_multi);
        $this->assertSame(['Mehrere Werte', 'Mehrere Werte'], $de_multi->getPluralTranslations());
        $this->assertFalse($de_multi->hasFlag('fuzzy'), 'de: a source language');
        $fr_multi = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_fr.po')
            ->find(null, 'multi');
        $this->assertSame(['Plusieurs valeurs', 'Plusieurs valeurs'], $fr_multi?->getPluralTranslations());
        $this->assertTrue($fr_multi->hasFlag('fuzzy'), 'fr: copied into 2 forms');

        $zh_multi = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_zh.po')
            ->find(null, 'multi');
        $this->assertNotNull($zh_multi);
        $this->assertSame(['Mehrere Werte ZH'], $zh_multi->getPluralTranslations());
        $this->assertFalse($zh_multi->hasFlag('fuzzy'), 'zh: a single form is not "copied" anywhere');
    }

    /** A `lang/ilias_*.lang` whose name yields no lowercase language key is skipped with a warning. */
    public function testAFileWithoutALanguageKeyInItsNameIsSkippedWithAWarning(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");
        file_put_contents($root . '/lang/ilias_ABC.lang', "tst#:#greeting#:#Sollte nicht gelesen werden\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('WARNING: skipping', $stderr);
        $this->assertStringContainsString('ilias_ABC.lang', $stderr);
        $this->assertStringContainsString('no language key in its name', $stderr);
        $this->assertFileDoesNotExist($root . '/out/tst_ABC.po');
    }

    /** "Language-Team" is the English name from `meta_l_<lang>` of ilias_en.lang; without it the header is left out. */
    public function testLanguageTeamHeaderNamesTheLanguageInEnglishFromTheMetaModule(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");
        $this->writeLangFile($root, 'en', "tst#:#greeting#:#Hello\nmeta#:#meta_l_de#:#German\nmeta#:#meta_l_en#:#English\n");
        $this->writeLangFile($root, 'fr', "tst#:#greeting#:#Bonjour\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $de = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_de.po');
        $this->assertSame('de', $de->getHeader('Language'));
        $this->assertSame('German', $de->getHeader('Language-Team'));
        $en = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_en.po');
        $this->assertSame('English', $en->getHeader('Language-Team'));
        $fr = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst_fr.po');
        $this->assertNull($fr->getHeader('Language-Team'));
        $this->assertStringContainsString("NOTE: no meta_l_fr in lang/ilias_en.lang", $stdout);
        $pot = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/tst.pot');
        $this->assertNull($pot->getHeader('Language-Team'));
    }

    /** A custom --pattern names the .po files and the .pot template (pattern without "%s", trimmed of "_-."). */
    public function testCustomPatternNamesThePoFilesAndThePotTemplate(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt(
            $this->toolPathOf($root),
            '--pattern=ilias_%s',
            'tst',
            'de',
            $root . '/out'
        );

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertFileExists($root . '/out/ilias_de.po');
        $this->assertFileExists($root . '/out/ilias.pot');
        $this->assertFileDoesNotExist($root . '/out/tst_de.po');
        $this->assertFileDoesNotExist($root . '/out/tst.pot');
        $entry = \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog::fromPoFile($root . '/out/ilias_de.po')->find(null, 'greeting');
        $this->assertNotNull($entry);
        $this->assertSame('Hallo', $entry->getTranslation());
    }

    /** An invalid --pattern fails with exit code 1 before the .lang files are read and writes nothing. */
    public function testAnInvalidPatternFailsWithExitOneAndWritesNothing(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#greeting#:#Hallo\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt(
            $this->toolPathOf($root),
            '--pattern=no-placeholder-at-all',
            'tst',
            'de',
            $root . '/out'
        );

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('not a valid file name pattern', $stderr);
        $this->assertSame([], glob($root . '/out/*') ?: [], 'nothing must be written for an invalid pattern');
    }

    private const string START = "<!-- language file start -->";

    /** The contribution --remove-from-lang looks for in a component class. */
    private static function contribution(string $module, string $further_arguments = ''): string
    {
        return '$contribute[LanguageFileDirectory::class] = fn() => '
            . "new ComponentLanguageFileDirectory(\$this, '$module'$further_arguments);";
    }

    private static function componentSource(string $code): string
    {
        return "<?php\n\nclass Comp\n{\n    public function init(array &\$contribute): void\n    {\n        $code\n    }\n}\n";
    }

    /** Creates components/<vendor>/Comp/lang (and Comp.php unless $source is null), the outputDir --remove-from-lang needs. */
    private function addComponent(string $root, ?string $source, string $vendor = 'ILIAS'): string
    {
        $directory = $root . '/components/' . $vendor . '/Comp';
        mkdir($directory . '/lang', 0775, true);
        if ($source !== null) {
            file_put_contents($directory . '/Comp.php', $source);
        }

        return $directory . '/lang';
    }

    private function addRegisteredComponent(string $root, string $module): string
    {
        return $this->addComponent($root, self::componentSource(self::contribution($module)));
    }

    /** @return array<string, array{string, int|false}> file name => content and file mode of everything in lang/ */
    private static function langSnapshot(string $root): array
    {
        clearstatcache();
        $snapshot = [];
        foreach (glob($root . '/lang/*') ?: [] as $file) {
            $snapshot[basename($file)] = [(string) file_get_contents($file), fileperms($file)];
        }
        ksort($snapshot);

        return $snapshot;
    }

    /** @return list<string> the keys (msgids) of a written .po/.pot */
    private static function idsOf(string $file): array
    {
        return array_map(
            static fn($entry): string => $entry->getId(),
            TranslationCatalog::fromPoFile($file)->getEntries()
        );
    }

    /** @return array<string, array{array<string, string>, list<string>, string}> lang files, options, expected stderr part */
    public static function failureProvider(): array
    {
        return [
            'key missing from the reference language' => [
                ['de' => "tst#:#a#:#A\n", 'fr' => "tst#:#a#:#B\ntst#:#x#:#X\n"], [], 'fr: x',
            ],
            'empty key in the reference language' => [
                ['de' => "tst#:#a#:#A\ntst#:##:#E\n"], [], 'de: "" (empty key)',
            ],
            'identical duplicate without the option' => [
                ['de' => "tst#:#a#:#A\ntst#:#a#:#A\n"], [], 'de: a',
            ],
            'duplicate with different values, also with the option' => [
                ['de' => "tst#:#a#:#A\ntst#:#a#:#B\n"], ['--skip-unmigratable-keys'], 'duplicate identifiers',
            ],
            'duplicate differing only in its ### comment, also with the option' => [
                ['de' => "tst#:#a#:#A###x\ntst#:#a#:#A###y\n"], ['--skip-unmigratable-keys'], 'duplicate identifiers',
            ],
            'module without any line (migrated already)' => [
                ['de' => "other#:#a#:#A\n", 'fr' => "other#:#a#:#B\n"], [], 'has no lines in any of the 2 lang/ilias_*.lang files',
            ],
            'control character in a key is shown escaped' => [
                ['de' => "tst#:#a#:#A\n", 'fr' => "tst#:#a#:#B\ntst#:#x\033y#:#X\n"], [], 'fr: x\033y',
            ],
            'unknown option' => [
                ['de' => "tst#:#a#:#A\n"], ['--remove-from-lang-typo'], "unknown option '--remove-from-lang-typo'",
            ],
        ];
    }

    /**
     * A run failing before the .po files are written touches no .lang file, and creates no output directory without --remove-from-lang.
     *
     * @param array<string, string> $files
     * @param list<string> $options
     */
    #[DataProvider('failureProvider')]
    public function testAFailedRunNeverTouchesALangFileNorWritesAnything(array $files, array $options, string $expected_stderr): void
    {
        $root = $this->buildFixtureRepo();
        $component_lang = $this->addRegisteredComponent($root, 'tst');
        foreach ($files as $lang_key => $content) {
            $this->writeLangFile($root, $lang_key, $content);
        }
        $before = self::langSnapshot($root);

        [$exit_code, $stdout, $stderr] = $this->runToolAt(
            $this->toolPathOf($root),
            '--remove-from-lang',
            ...$options,
            ...['tst', 'de', $component_lang]
        );
        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString($expected_stderr, $stderr);
        $this->assertStringNotContainsString("\033", $stderr);
        $this->assertSame($before, self::langSnapshot($root));
        $this->assertSame([], glob($component_lang . '/*') ?: [], 'nothing written');

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), ...$options, ...['tst', 'de', $root . '/newout']);
        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertDirectoryDoesNotExist($root . '/newout');
    }

    /** @return array<string, array{list<string>}> */
    public static function invalidArgumentsProvider(): array
    {
        return [
            'short option' => [['-x', 'tst', 'de']],
            'misspelled long option' => [['--foo', 'tst', 'de']],
            'module with a path' => [['../x', 'de']],
            'reference language with a path' => [['tst', '../de']],
        ];
    }

    /** @param list<string> $arguments */
    #[DataProvider('invalidArgumentsProvider')]
    public function testInvalidArgumentsFailWithExitOneAndWriteNothing(array $arguments): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#a#:#A\n");
        $before = self::langSnapshot($root);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), ...$arguments, ...[$root . '/newout']);

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertNotSame('', $stderr);
        $this->assertSame($before, self::langSnapshot($root));
        $this->assertDirectoryDoesNotExist($root . '/newout');
        $this->assertDirectoryDoesNotExist($root . '/x');
    }

    public function testWithoutTheOptionTheHintNamesItAndWithItEverythingUnmigratableIsReportedAndLeftOut(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#a#:#A\ntst#:#a#:#A\ntst#:##:#leer\ntst#:#k#1#:#V###note\n");
        $this->writeLangFile($root, 'fr', "tst#:#a#:#FA\ntst#:#c#:#C\ntst#:#c#:#C\ntst#:#x#y#:#X\ntst#:#k#1#:#FV\n");
        $before = self::langSnapshot($root);

        [$exit_code, , $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');
        $this->assertSame(1, $exit_code);
        $this->assertStringContainsString('--skip-unmigratable-keys', $stderr);
        $this->assertSame([], glob($root . '/out/*') ?: []);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--skip-unmigratable-keys', 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('WARNING', $stderr);
        $this->assertStringContainsString("  de: \"\" (empty key)\n", $stderr);
        $this->assertStringContainsString("  fr: c, x#y\n", $stderr, 'the key with # whole');
        $this->assertStringContainsString("  fr: c\n", $stderr, 'identical duplicate');
        $this->assertStringContainsString("  de: a\n", $stderr);
        $this->assertStringContainsString('round-trip identically', $stdout);
        $this->assertSame(['a', 'k#1'], self::idsOf($root . '/out/tst_de.po'), 'only the migratable keys');
        $this->assertSame(['a', 'k#1'], self::idsOf($root . '/out/tst_fr.po'));
        $this->assertSame('note', TranslationCatalog::fromPoFile($root . '/out/tst_de.po')->find(null, 'k#1')?->getExtractedComments()[0] ?? null);
        $this->assertSame($before, self::langSnapshot($root), 'without --remove-from-lang the .lang files stay');
    }

    /** A key left out anyway does not fail the run for a conflicting duplicate: warning with the option, failure without. */
    public function testADuplicateWithDifferentValuesOfAKeyThatIsLeftOutAnywayOnlyWarns(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#a#:#A\n");
        $this->writeLangFile($root, 'fr', "tst#:#a#:#B\ntst#:#x#:#1\ntst#:#x#:#2\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');
        $this->assertSame(1, $exit_code, $stdout . $stderr);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--skip-unmigratable-keys', 'tst', 'de', $root . '/out');
        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('WARNING', $stderr);
        $this->assertStringContainsString("  fr: x\n", $stderr);
        $this->assertSame(['a'], self::idsOf($root . '/out/tst_fr.po'));
    }

    public function testTheSingularKeyGoesToTheFormThatServesOnlyOneWhateverItsNumber(): void
    {
        $root = $this->buildFixtureRepo();
        foreach (['de' => ['Eintraege', 'Ein Eintrag'], 'es' => ['Entradas', 'Una entrada'], 'ar' => ['AR viele', 'AR eins']] as $lang_key => [$plural, $singular]) {
            $this->writeLangFile($root, $lang_key, "tst#:#item#:#$plural\ntst#:#item_singular#:#$singular\n");
        }
        $this->writePluralsConfig(
            $root,
            [
                'de' => 'nplurals=2; plural=(n != 1);',
                'es' => 'nplurals=3; plural=(n == 1) ? 0 : ((n != 0 && n % 1000000 == 0) ? 1 : 2);',
                'ar' => 'nplurals=6; plural=(n == 0) ? 0 : ((n == 1) ? 1 : ((n == 2) ? 2 : ((n % 100 >= 3 && n % 100 <= 10) ? 3 : ((n % 100 >= 11 && n % 100 <= 99) ? 4 : 5))));',
            ],
            ['tst' => ['item' => ['singular' => 'item_singular']]]
        );

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $forms = static fn(string $lang_key): ?array => TranslationCatalog::fromPoFile($root . "/out/tst_$lang_key.po")->find(null, 'item')?->getPluralTranslations();
        $this->assertSame(['Ein Eintrag', 'Eintraege'], $forms('de'));
        $this->assertSame(['Una entrada', 'Entradas', 'Entradas'], $forms('es'));
        $this->assertSame(['AR viele', 'AR eins', 'AR viele', 'AR viele', 'AR viele', 'AR viele'], $forms('ar'));
    }

    public function testATranslatedSingularEqualToTheOwnValueIsNotFuzzy(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#item#:#Eintraege\ntst#:#item_singular#:#Ein Eintrag\n");
        $this->writeLangFile($root, 'fr', "tst#:#item#:#Photo\ntst#:#item_singular#:#Photo\n");
        $this->writePluralsConfig(
            $root,
            ['de' => 'nplurals=2; plural=(n != 1);', 'fr' => 'nplurals=2; plural=(n != 1);'],
            ['tst' => ['item' => ['singular' => 'item_singular']]]
        );

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $item = TranslationCatalog::fromPoFile($root . '/out/tst_fr.po')->find(null, 'item');
        $this->assertSame(['Photo', 'Photo'], $item?->getPluralTranslations());
        $this->assertFalse($item->hasFlag('fuzzy'), 'both forms are translated, they just are equal');
    }

    public function testAKeyWithTheSyntaxOfAPluralFormIsRejectedOrLeftOut(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#a#:#A\ntst#:#item [0]#:#Form\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');
        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('FAILURE', $stderr);
        $this->assertStringContainsString('item [0]', $stderr);
        $this->assertSame([], glob($root . '/out/*') ?: [], 'nothing was written');

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--skip-unmigratable-keys', 'tst', 'de', $root . '/out');
        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('item [0]', $stderr);
        $this->assertSame(['a'], self::idsOf($root . '/out/tst_de.po'));
    }

    /** Byte-exact removal of the module's own lines only; line endings, mode and the header above the start marker stay. */
    public function testRemoveFromLangRemovesExactlyTheModulesLinesAndKeepsEveryOtherByte(): void
    {
        $root = $this->buildFixtureRepo();
        $out = $this->addRegisteredComponent($root, 'crs');
        $start = self::START;
        $this->writeLangFile($root, 'de', "# header\n$start\ncrs#:#a#:#A\ncrsr#:#a#:#Other\ncrs#:#b#:#B###c\nxcrs#:#a#:#X\nmeta#:#crs#:#crs#:#M\n");
        $this->writeLangFile($root, 'fr', "# header\r\n$start\r\ncrs#:#a#:#FA\r\ncrs#:#b#:#FB\r\ncrsr#:#a#:#R\r\n");
        $this->writeLangFile($root, 'en', "$start\ncrsr#:#a#:#R\ncrs#:#a#:#EA\ncrs#:#b#:#EB");
        $this->writeLangFile($root, 'es', "crs#:#a#:#SA\ncrs#:#b#:#SB\nkeep#:#k#:#K\n");
        $this->writeLangFile($root, 'it', "$start\nmeta#:#x#:#y\n");
        $this->writeLangFile($root, 'ABC', "$start\ncrs#:#a#:#ignored\n");
        chmod($root . '/lang/ilias_de.lang', 0640);
        $before = self::langSnapshot($root);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', 'crs', 'de', $out);

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $after = self::langSnapshot($root);
        $this->assertSame("# header\n$start\ncrsr#:#a#:#Other\nxcrs#:#a#:#X\nmeta#:#crs#:#crs#:#M\n", $after['ilias_de.lang'][0]);
        $this->assertSame("# header\r\n$start\r\ncrsr#:#a#:#R\r\n", $after['ilias_fr.lang'][0]);
        $this->assertSame("$start\ncrsr#:#a#:#R", $after['ilias_en.lang'][0], 'no final line break stays none');
        $this->assertSame("keep#:#k#:#K\n", $after['ilias_es.lang'][0]);
        $this->assertSame($before['ilias_it.lang'], $after['ilias_it.lang']);
        $this->assertSame($before['ilias_ABC.lang'], $after['ilias_ABC.lang']);
        $this->assertSame(0640, $after['ilias_de.lang'][1] & 07777);
        $this->assertSame(array_keys($before), array_keys($after), 'no temporary file left behind');
        $this->assertStringContainsString(sprintf("removed %5d line(s) of 'crs' from lang/ilias_de.lang", 2), $stdout);
        $this->assertStringContainsString(sprintf("removed %5d line(s) of 'crs' from lang/ilias_en.lang", 2), $stdout);
        $this->assertStringNotContainsString('ilias_it.lang', $stdout);
        $this->assertFileExists($out . '/crs_de.po');
        $this->assertFileExists($out . '/crs.pot');
    }

    /** Left out lines (empty key, other language, identical duplicates) leave the .lang files too. */
    public function testRemoveFromLangAlsoRemovesTheLeftOutLines(): void
    {
        $root = $this->buildFixtureRepo();
        $out = $this->addRegisteredComponent($root, 'crs');
        $start = self::START;
        $this->writeLangFile($root, 'de', "$start\ncrs#:#a#:#A\ncrs#:#a#:#A\ncrs#:##:#leer\ncrs#:#b#:#B\nkeep#:#k#:#K\n");
        $this->writeLangFile($root, 'fr', "$start\ncrs#:#a#:#FA\ncrs#:#extra#:#E\nkeep#:#k#:#K2\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt(
            $this->toolPathOf($root),
            '--skip-unmigratable-keys',
            '--remove-from-lang',
            'crs',
            'de',
            $out
        );

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $after = self::langSnapshot($root);
        $this->assertSame("$start\nkeep#:#k#:#K\n", $after['ilias_de.lang'][0]);
        $this->assertSame("$start\nkeep#:#k#:#K2\n", $after['ilias_fr.lang'][0]);
        $this->assertSame(['a', 'b'], self::idsOf($out . '/crs_fr.po'));
    }

    /** A module line above the start marker is never removed: the run then changes no .lang file at all. */
    public function testRemoveFromLangChangesNoFileWhenOneFileHoldsALineThatWouldNotBeRemoved(): void
    {
        $root = $this->buildFixtureRepo();
        $out = $this->addRegisteredComponent($root, 'crs');
        $start = self::START;
        $this->writeLangFile($root, 'de', "$start\ncrs#:#a#:#A\ncrs#:#b#:#B\n");
        $this->writeLangFile($root, 'fr', "crs#:#a#:#FA\n$start\ncrs#:#b#:#FB\n");
        $this->writeLangFile($root, 'en', "$start\ncrs#:#a#:#EA\n");
        $before = self::langSnapshot($root);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', 'crs', 'de', $out);

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('ilias_fr.lang: 2 line(s) of the module were read, but 1 would be removed', $stderr);
        $this->assertSame($before, self::langSnapshot($root));
    }

    /** A second run must not overwrite the now only-source .po/.pot with empty ones. */
    public function testASecondRunForAMigratedModuleFailsAndKeepsThePoFilesByteIdentical(): void
    {
        $root = $this->buildFixtureRepo();
        $out = $this->addRegisteredComponent($root, 'crs');
        $this->writeLangFile($root, 'de', self::START . "\ncrs#:#a#:#A\n");
        $this->writeLangFile($root, 'fr', self::START . "\ncrs#:#a#:#FA\n");
        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', 'crs', 'de', $out);
        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $po_files = self::files($out);
        $this->assertCount(3, $po_files);
        $lang_files = self::langSnapshot($root);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', 'crs', 'de', $out);

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('has no lines in any of the 2 lang/ilias_*.lang files', $stderr);
        $this->assertStringContainsString('most likely it is migrated already', $stderr);
        $this->assertSame($po_files, self::files($out));
        $this->assertSame($lang_files, self::langSnapshot($root));
    }

    /**
     * @return array<string, array{?string, ?string, list<string>, string}> outputDir ({root} replaced; null: none),
     *         content of Comp.php (null: no file), further options, expected stderr part
     */
    public static function refusedTargetProvider(): array
    {
        $registered = self::componentSource(self::contribution('tst'));

        return [
            'no outputDir' => [null, $registered, [], 'the outputDir argument is required'],
            'outputDir does not exist' => ['{root}/components/ILIAS/Comp/missing', $registered, [], 'does not exist'],
            'outputDir outside of components' => ['{root}/out', $registered, [], 'is not components/<Vendor>/<Component>/lang'],
            'outputDir is the component, not its lang/' => ['{root}/components/ILIAS/Comp', $registered, [], 'is not components/<Vendor>/<Component>/lang'],
            'component class missing' => ['{root}/components/ILIAS/Comp/lang', null, [], 'Comp.php was not found'],
            'contribution only in a comment' => [
                '{root}/components/ILIAS/Comp/lang',
                self::componentSource("// " . self::contribution('tst') . "\n        /* " . self::contribution('tst') . ' */'),
                [],
                "contributes no ComponentLanguageFileDirectory for module 'tst'",
            ],
            'contribution for another module' => [
                '{root}/components/ILIAS/Comp/lang',
                self::componentSource(self::contribution('tstx')),
                [],
                "contributes no ComponentLanguageFileDirectory for module 'tst'",
            ],
            'contributed path is not lang/' => [
                '{root}/components/ILIAS/Comp/lang',
                self::componentSource(self::contribution('tst', ", 'translations/'")),
                [],
                "from 'translations/', not from lang/",
            ],
            'contributed pattern differs from the contribution default' => [
                '{root}/components/ILIAS/Comp/lang',
                self::componentSource(self::contribution('tst', ", 'lang/', 'x_%s'")),
                [],
                "pattern 'x_%s', but the run uses 'tst_%s'",
            ],
            '--pattern differs from the contribution' => [
                '{root}/components/ILIAS/Comp/lang',
                $registered,
                ['--pattern=ilias_%s'],
                "but the run uses 'ilias_%s'",
            ],
        ];
    }

    /** @param list<string> $options */
    #[DataProvider('refusedTargetProvider')]
    public function testRemoveFromLangRefusesATargetThatTheInstallationWouldNotReadAndWritesNothing(
        ?string $output,
        ?string $component_source,
        array $options,
        string $expected_stderr
    ): void {
        $root = $this->buildFixtureRepo();
        $component_lang = $this->addComponent($root, $component_source);
        $this->writeLangFile($root, 'de', self::START . "\ntst#:#a#:#A\n");
        $this->writeLangFile($root, 'fr', self::START . "\ntst#:#a#:#FA\n");
        $before = self::langSnapshot($root);
        $arguments = ['tst', 'de', ...($output === null ? [] : [str_replace('{root}', $root, $output)])];

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', ...$options, ...$arguments);

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString($expected_stderr, $stderr);
        $this->assertStringContainsString('Nothing was written.', $stderr);
        $this->assertSame($before, self::langSnapshot($root));
        $this->assertSame([], glob($component_lang . '/*') ?: []);
        $this->assertSame([], glob($root . '/out/*') ?: []);
    }

    /** @return array<string, array{string, list<string>, string}> content of Comp.php, further options, written file */
    public static function acceptedTargetProvider(): array
    {
        return [
            'one line, defaults' => [self::componentSource(self::contribution('tst')), [], 'tst_de.po'],
            'multi-line with fully qualified names and a trailing comma' => [
                self::componentSource(
                    "\$contribute[\\ILIAS\\Language\\ComponentTranslation\\LanguageFileDirectory::class] = fn(): \\X\\LanguageFileDirectory => \n"
                    . "            new \\ILIAS\\Language\\ComponentTranslation\\ComponentLanguageFileDirectory(\n"
                    . "                \$this,\n                'tst',\n                'lang/',\n                'x_%s',\n            );"
                ),
                ['--pattern=x_%s'],
                'x_de.po',
            ],
            'a comment with another module does not matter' => [
                self::componentSource('// ' . self::contribution('tstx') . "\n        " . self::contribution('tst', ", 'lang'")),
                [],
                'tst_de.po',
            ],
        ];
    }

    /** @param list<string> $options */
    #[DataProvider('acceptedTargetProvider')]
    public function testRemoveFromLangAcceptsAContributionInEveryNotationOfTheComponentClass(string $component_source, array $options, string $written): void
    {
        $root = $this->buildFixtureRepo();
        $component_lang = $this->addComponent($root, $component_source);
        $this->writeLangFile($root, 'de', self::START . "\ntst#:#a#:#A\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', ...$options, ...['tst', 'de', $component_lang]);

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertFileExists($component_lang . '/' . $written);
        $this->assertSame(self::START . "\n", self::langSnapshot($root)['ilias_de.lang'][0]);
    }

    public function testRemoveFromLangAcceptsASymbolicLinkThatResolvesToTheComponentLangDirectory(): void
    {
        $root = $this->buildFixtureRepo();
        $component_lang = $this->addRegisteredComponent($root, 'tst');
        symlink($component_lang, $root . '/link');
        $this->writeLangFile($root, 'de', self::START . "\ntst#:#a#:#A\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', 'tst', 'de', $root . '/link');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertFileExists($component_lang . '/tst_de.po');
    }

    /** A symlinked .lang file would fail only when written, after other files changed: --remove-from-lang refuses it up front. */
    public function testRemoveFromLangRefusesASymbolicLinkInLangButTheOptionlessRunStillReadsIt(): void
    {
        $root = $this->buildFixtureRepo();
        $component_lang = $this->addRegisteredComponent($root, 'tst');
        $this->writeLangFile($root, 'de', self::START . "\ntst#:#a#:#A\n");
        symlink($root . '/lang/ilias_de.lang', $root . '/lang/ilias_zz.lang');
        $before = self::langSnapshot($root);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), '--remove-from-lang', 'tst', 'de', $component_lang);

        $this->assertSame(1, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('ilias_zz.lang', $stderr);
        $this->assertSame($before, self::langSnapshot($root));
        $this->assertTrue(is_link($root . '/lang/ilias_zz.lang'));
        $this->assertSame([], glob($component_lang . '/*') ?: []);

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertFileExists($root . '/out/tst_zz.po');
        $this->assertSame($before, self::langSnapshot($root));
    }

    private function writeMetaPo(string $root, string $content): void
    {
        mkdir($root . '/components/ILIAS/Meta/lang', 0775, true);
        file_put_contents($root . '/components/ILIAS/Meta/lang/meta_en.po', $content);
    }

    public function testLanguageTeamFallsBackToAShippedMetaPoAndAnUnreadableOneOnlyWarns(): void
    {
        $root = $this->buildFixtureRepo();
        $this->writeLangFile($root, 'de', "tst#:#a#:#A\n");
        $this->writeLangFile($root, 'en', "tst#:#a#:#A\n");
        $this->writeMetaPo($root, "msgid \"\"\nmsgstr \"\"\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n\nmsgid \"meta_l_de\"\nmsgstr \"German\"\n");

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertSame('German', TranslationCatalog::fromPoFile($root . '/out/tst_de.po')->getHeader('Language-Team'));
        $this->assertNull(TranslationCatalog::fromPoFile($root . '/out/tst_en.po')->getHeader('Language-Team'));

        file_put_contents($root . '/components/ILIAS/Meta/lang/meta_en.po', "msgid \"meta_l_de\"\nmsgstr \"unterminated\n\"x\n garbage");
        MigratedPoFixture::removeDirectory($root . '/out');
        mkdir($root . '/out');

        [$exit_code, $stdout, $stderr] = $this->runToolAt($this->toolPathOf($root), 'tst', 'de', $root . '/out');

        $this->assertSame(0, $exit_code, $stdout . $stderr);
        $this->assertStringContainsString('meta_en.po cannot be read for the language names', $stderr);
        $this->assertFileExists($root . '/out/tst_de.po');
    }
}
