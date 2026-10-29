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

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use MigratedPoFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ShippedTranslationsBuild: on success a collision list, the previous build kept, older ones removed, an unchanged run
 * touching nothing, one build at a time, a `.po` that cannot be compiled served from a former build; on failure
 * `current.json` stays as it was and no half-written build remains.
 */
class ShippedTranslationsBuildTest extends TestCase
{
    private string $root;
    private string $artifacts;
    private string|false $messages_locale;
    private string|false $language;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        $this->messages_locale = setlocale(LC_MESSAGES, '0');
        $this->language = getenv('LANGUAGE');
        $this->root = sys_get_temp_dir() . '/ilias_shipped_build_test_' . bin2hex(random_bytes(6));
        mkdir($this->root, 0775, true);
        $this->artifacts = MigratedLanguageFilePaths::shippedArtifactDirectory($this->root);
    }

    protected function tearDown(): void
    {
        NativeGettext::resetForTests();
        if ($this->messages_locale !== false) {
            setlocale(LC_MESSAGES, $this->messages_locale);
        }
        putenv($this->language === false ? 'LANGUAGE' : 'LANGUAGE=' . $this->language);
        MigratedPoFixture::removeDirectory($this->root);
        MigratedPoFixture::resetRuntime();
    }

    /**
     * @param array<string, string> $entries
     */
    private function ship(string $module, string $lang_key, array $entries): void
    {
        MigratedPoFixture::writePo($this->root . '/' . $module . '/' . $module . '_' . $lang_key . '.po', MigratedPoFixture::catalog($module, $entries));
    }

    /**
     * @param list<string> $modules
     * @param list<string> $messages filled with what the build informs about
     * @return array{build: ?string, compiled: int, unchanged: bool, rebuilt: bool, removed: int, warnings: list<string>, collisions: list<string>, collision_summary: list<string>, manifest: ?string}
     */
    private function build(array $modules, ?string $locale_source = null, array &$messages = [], ?\Closure $on_inform = null): array
    {
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            ...array_map(static fn(string $module): LanguageFileDirectory => MigratedPoFixture::directory($module, $module . '/'), $modules)
        );

        return (new ShippedTranslationsBuild($manager, $this->root, new ShippedTranslations(), $locale_source))->run(
            static function (string $message) use (&$messages, $on_inform): void {
                $messages[] = $message;
                if ($on_inform !== null) {
                    $on_inform($message);
                }
            }
        );
    }

    /**
     * @return list<string> the names in the artifact directory
     */
    private function artifactEntries(): array
    {
        return array_values(array_diff(scandir($this->artifacts) ?: [], ['.', '..']));
    }

    private function pointerFile(): string
    {
        return MigratedLanguageFilePaths::buildPointerFile($this->artifacts);
    }

    private function buildDirectory(string $build): string
    {
        return MigratedLanguageFilePaths::buildDirectory($this->artifacts, $build);
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(string $build): array
    {
        return json_decode((string) file_get_contents($this->buildDirectory($build) . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    private function breakPo(string $module, string $lang_key): void
    {
        file_put_contents($this->root . '/' . $module . '/' . $module . '_' . $lang_key . '.po', "msgid \"kaputt\n");
    }

    /**
     * @return array<string, string>
     */
    private function servedValues(string $build, string $module, string $lang_key): array
    {
        return MigratedPoFixture::readMo(MigratedLanguageFilePaths::buildMoFile($this->buildDirectory($build), $module, $lang_key));
    }

    /**
     * A locale directory the C library cannot load (see findLocaleSource(): it only looks for the file).
     */
    private function brokenLocaleSource(): string
    {
        $source = $this->root . '/broken-locale';
        mkdir($source, 0775, true);
        file_put_contents($source . '/LC_CTYPE', 'this is no locale file');

        return $source;
    }

    public function testIdentifiersWithDifferentValuesInSeveralModulesAreListedPerLanguageAndOnEveryRun(): void
    {
        $this->ship('mca', 'de', ['dup' => 'Eins', 'same' => 'Gleich', 'only_a' => 'A']);
        $this->ship('mcb', 'de', ['dup' => 'Zwei', 'same' => 'Gleich']);
        $this->ship('mca', 'en', ['dup' => 'One']);
        $this->ship('mcb', 'en', ['dup' => 'Two']);
        $this->ship('mcc', 'en', ['dup' => 'One']);

        $first = $this->build(['mca', 'mcb', 'mcc']);
        $second = $this->build(['mca', 'mcb', 'mcc']);

        $expected = [
            'Key "dup" has different values in the modules mca, mcb (languages: de) - txt() serves the one of the module loaded last.',
            'Key "dup" has different values in the modules mca, mcb, mcc (languages: en) - txt() serves the one of the module loaded last.',
        ];
        $this->assertSame($expected, $first['collisions'], '"same" has one value everywhere, "only_a" is in one module only');
        $this->assertSame($expected, $second['collisions'], 'listed again by an unchanged build, not only by the one that wrote it');
        $this->assertSame($expected, $this->manifest((string) $first['build'])['collisions'] ?? null, 'kept in the manifest of the build');
    }

    public function testCollisionsAreSummarizedPerSetOfModulesOnlyByTheRunThatWritesTheBuild(): void
    {
        $this->ship('mca', 'de', ['one' => 'A1', 'two' => 'A2', 'three' => 'A3']);
        $this->ship('mcb', 'de', ['one' => 'B1', 'two' => 'B2']);
        $this->ship('mcc', 'de', ['three' => 'C3']);

        $first = $this->build(['mca', 'mcb', 'mcc']);
        $second = $this->build(['mca', 'mcb', 'mcc']);

        $this->assertTrue($first['rebuilt']);
        $this->assertCount(3, $first['collisions']);
        $this->assertSame([
            '2 key(s) with different values in the modules mca, mcb - txt() serves the one of the module loaded last: one, two',
            '1 key(s) with different values in the modules mca, mcc - txt() serves the one of the module loaded last: three',
        ], $first['collision_summary']);

        $this->assertFalse($second['rebuilt']);
        $this->assertSame([], $second['collision_summary']);
        $this->assertSame($first['collisions'], $second['collisions']);
        $this->assertSame($this->buildDirectory((string) $first['build']) . '/manifest.json', $second['manifest']);
        $this->assertFileExists((string) $second['manifest']);
    }

    public function testCountsTheMarkupWarningsOfABuildApartFromTheOthers(): void
    {
        $this->ship('mca', 'de', ['greeting' => '<script>x</script>Hallo']);
        $this->ship('mcb', 'de', ['plain' => 'Hallo']);
        $this->breakPo('mcb', 'de');

        $result = $this->build(['mca', 'mcb']);

        $markup = array_values(array_diff($result['warnings'], $result['other_warnings']));
        $this->assertSame(1, $result['markup_warnings']);
        $this->assertCount(1, $markup);
        $this->assertStringContainsString('(module "mca", key "greeting")', $markup[0]);
        $this->assertGreaterThan(count($markup), count($result['warnings']), 'the broken .po warning is no markup warning');
    }

    public function testOnlyFilesOfNoModuleInTheSameDirectoryAreReportedAsWithoutLanguageKey(): void
    {
        MigratedPoFixture::writePo($this->root . '/shared/chat_de.po', MigratedPoFixture::catalog('chat', ['room' => 'Raum']));
        MigratedPoFixture::writePo($this->root . '/shared/chat_adm_de.po', MigratedPoFixture::catalog('chat_adm', ['server' => 'Server']));
        MigratedPoFixture::writePo($this->root . '/shared/chat_pt_BR.po', MigratedPoFixture::catalog('chat', ['room' => 'Sala']));
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('chat', 'shared/'),
            MigratedPoFixture::directory('chat_adm', 'shared/')
        );
        $messages = [];

        $result = (new ShippedTranslationsBuild($manager, $this->root, new ShippedTranslations()))->run(
            static function (string $message) use (&$messages): void {
                $messages[] = $message;
            }
        );

        $this->assertSame(2, $result['compiled']);
        $without_key = array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'no valid language key')));
        $this->assertSame(
            ['WARNING: "shared/chat_pt_BR.po" is not compiled: its name contains no valid language key.'],
            $without_key,
            '"chat_adm_de.po" matches "chat_*.po" but belongs to the module "chat_adm"'
        );
    }

    public function testABuildNativeGettextCannotServeLeavesCurrentAndThePreviousBuildAsTheyAreAndNoRemnants(): void
    {
        MigratedPoFixture::requireGlibcLocale($this);
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $good = $this->build(['mfa']);
        $pointer_before = (string) file_get_contents($this->pointerFile());
        $entries_before = $this->artifactEntries();
        $this->ship('mfa', 'de', ['greeting' => 'Moin']);
        // as in a fresh Setup process: nothing active that could let the broken locale pass
        NativeGettext::resetForTests();
        NativeGettext::useSystemLocaleForTests(false);

        try {
            $this->build(['mfa'], $this->brokenLocaleSource());
            $this->fail('the build must not be accepted');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Native gettext is not available', $e->getMessage());
        }

        $this->assertSame($pointer_before, (string) file_get_contents($this->pointerFile()));
        $this->assertSame($entries_before, $this->artifactEntries(), 'neither the rejected build nor a temporary directory is left');
        $this->assertDirectoryExists($this->buildDirectory((string) $good['build']));
    }

    public function testABuildWithoutAnyTranslatedValueCannotBeCheckedAndIsRefused(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->build(['mfa']);
        $pointer_before = (string) file_get_contents($this->pointerFile());
        $entries_before = $this->artifactEntries();
        // a value equal to its identifier is no proof of anything, an empty one is no message at all
        $this->ship('mfa', 'de', ['same' => 'same', 'empty' => '']);

        try {
            $this->build(['mfa']);
            $this->fail('the build must not be accepted');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('nothing to check native gettext with', $e->getMessage());
        }

        $this->assertSame($pointer_before, (string) file_get_contents($this->pointerFile()));
        $this->assertSame($entries_before, $this->artifactEntries(), 'the aborted build left nothing');
    }

    public function testMissingLocaleOfTheCLibraryAbortsBeforeAnythingIsWritten(): void
    {
        // the first build is not the subject of the test: it uses what the platform can load
        if (MigratedPoFixture::glibcLocaleAvailable()) {
            NativeGettext::useSystemLocaleForTests(false);
        } else {
            $this->requireSystemLocale();
            NativeGettext::useSystemLocaleForTests(true);
        }
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->build(['mfa']);
        $pointer_file = $this->pointerFile();
        $pointer_before = (string) file_get_contents($pointer_file);
        $entries_before = $this->artifactEntries();
        NativeGettext::useSystemLocaleForTests(false);

        try {
            $this->build(['mfa'], $this->root . '/no-such-locale');
            $this->fail('a build without a locale to ship must not be accepted');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('"C.utf8" of the C library was not found', $e->getMessage());
            $this->assertStringContainsString('no-such-locale', $e->getMessage());
        }

        $this->assertSame($pointer_before, (string) file_get_contents($pointer_file));
        $this->assertSame($entries_before, $this->artifactEntries());
    }

    public function testAnArtifactDirectoryThatCannotBeLockedAbortsTheBuild(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        mkdir($this->artifacts . '/build.lock', 0775, true);

        try {
            $this->build(['mfa']);
            $this->fail('a build must not run without its lock');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Could not lock', $e->getMessage());
        }

        $this->assertFileDoesNotExist($this->pointerFile());
    }

    public function testBuildsAreSerializedByTheLockFileAndTheLockIsReleasedAfterwards(): void
    {
        $this->ship('mla', 'de', ['greeting' => 'Hallo']);
        $lock_file = $this->artifacts . '/build.lock';
        $held_during_the_build = null;

        // the warning for the invalid module name is informed while the build runs
        $this->build(['mla', 'invalid module'], on_inform: function () use ($lock_file, &$held_during_the_build): void {
            $handle = fopen($lock_file, 'c');
            $this->assertNotFalse($handle);
            $held_during_the_build ??= !flock($handle, LOCK_EX | LOCK_NB);
            fclose($handle);
        });

        $this->assertTrue($held_during_the_build, 'nobody else can lock while a build runs');
        $handle = fopen($lock_file, 'c');
        $this->assertNotFalse($handle);
        $this->assertTrue(flock($handle, LOCK_EX | LOCK_NB), 'released after the build');
        fclose($handle);
    }

    public function testTheLockIsReleasedAfterAFailedBuildToo(): void
    {
        $this->ship('mla', 'de', ['greeting' => 'Hallo']);

        try {
            $this->build(['mla'], $this->root . '/no-such-locale');
        } catch (RuntimeException) {
        }

        $handle = fopen($this->artifacts . '/build.lock', 'c');
        $this->assertNotFalse($handle);
        $this->assertTrue(flock($handle, LOCK_EX | LOCK_NB));
        fclose($handle);
    }

    public function testOnlyTheCurrentAndThePreviousBuildStayAndUnrelatedFilesAreLeftAlone(): void
    {
        $builds = [];
        foreach (['Eins', 'Zwei', 'Drei'] as $value) {
            $this->ship('mba', 'de', ['greeting' => $value]);
            $result = $this->build(['mba']);
            $builds[] = (string) $result['build'];
            if ($value === 'Eins') {
                // things in the artifact directory that are none of the build's business
                mkdir($this->artifacts . '/keep-me');
                file_put_contents($this->artifacts . '/keep-me.txt', 'x');
                mkdir($this->artifacts . '/de');
                file_put_contents($this->artifacts . '/de/notes.txt', 'not a catalog');
                // the former layout `<lang>/<module>.mo` is a build's business
                mkdir($this->artifacts . '/fr');
                file_put_contents($this->artifacts . '/fr/old.mo', 'catalog');
            }
        }

        $this->assertCount(3, array_unique($builds));
        $this->assertDirectoryDoesNotExist($this->buildDirectory($builds[0]), 'two builds back');
        $this->assertDirectoryExists($this->buildDirectory($builds[1]), 'the previous build: running requests may still read it');
        $this->assertDirectoryExists($this->buildDirectory($builds[2]));
        $pointer = ShippedTranslationsBuild::readPointer($this->artifacts);
        $this->assertSame($builds[2], $pointer['build'] ?? null);
        $this->assertSame($builds[1], $pointer['previous'] ?? null);
        $expected = ['build.lock', basename($this->pointerFile()), 'de', 'keep-me', 'keep-me.txt', $builds[1], $builds[2]];
        $actual = $this->artifactEntries();
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual);
    }

    public function testASecondRunWithNothingChangedRewritesNothing(): void
    {
        $this->ship('mua', 'de', ['greeting' => 'Hallo']);
        $first = $this->build(['mua']);
        $mo = MigratedLanguageFilePaths::buildMoFile($this->buildDirectory((string) $first['build']), 'mua', 'de');
        $pointer = $this->pointerFile();
        $identity = [fileinode($mo), fileinode($pointer)];
        clearstatcache();

        $second = $this->build(['mua']);

        $this->assertFalse($first['unchanged']);
        $this->assertSame(1, $first['compiled']);
        $this->assertTrue($second['unchanged']);
        $this->assertSame(0, $second['compiled']);
        $this->assertSame(0, $second['removed']);
        $this->assertSame($first['build'], $second['build']);
        $this->assertSame($identity, [fileinode($mo), fileinode($pointer)], 'neither the build nor current.json were written again');
    }

    private function requireSystemLocale(): void
    {
        $before = setlocale(LC_MESSAGES, '0');
        $found = setlocale(LC_MESSAGES, NativeGettext::SYSTEM_LOCALES);
        setlocale(LC_MESSAGES, $before);
        if ($found === false) {
            $this->markTestSkipped('none of ' . implode(', ', NativeGettext::SYSTEM_LOCALES) . ' is installed');
        }
    }

    /** @return string the build id; the process uses the shipped locale (glibc) again afterwards */
    private function buildWithSystemLocale(string $module): string
    {
        $this->requireSystemLocale();
        $this->ship($module, 'de', ['greeting' => 'Hallo']);
        NativeGettext::resetForTests();
        NativeGettext::useSystemLocaleForTests(true);
        $build = (string) $this->build([$module])['build'];
        NativeGettext::resetForTests();
        // the check is about the manifest, whatever this platform uses
        NativeGettext::useSystemLocaleForTests(false);

        return $build;
    }

    public function testSystemLocaleBuildShipsNoLocaleIsReusedAndDiffersFromTheGlibcBuild(): void
    {
        $this->requireSystemLocale();
        MigratedPoFixture::requireGlibcLocale($this);
        $this->ship('msl', 'de', ['greeting' => 'Hallo']);
        $glibc = $this->build(['msl']);
        $glibc_locale = MigratedLanguageFilePaths::buildLocaleDirectory($this->buildDirectory((string) $glibc['build']));
        NativeGettext::resetForTests();
        NativeGettext::useSystemLocaleForTests(true);

        $first = $this->build(['msl']);
        $second = $this->build(['msl']);

        $this->assertDirectoryExists($glibc_locale . '/' . NativeGettext::LOCALE_NAME);
        $this->assertNotSame($glibc['build'], $first['build']);
        $this->assertDirectoryDoesNotExist(MigratedLanguageFilePaths::buildLocaleDirectory($this->buildDirectory((string) $first['build'])));
        $this->assertSame(['Hallo'], array_values($this->servedValues((string) $first['build'], 'msl', 'de')));
        $this->assertFalse($first['unchanged']);
        $this->assertTrue($second['unchanged']);
        $this->assertSame($first['build'], $second['build']);
    }

    public function testPlatformProblemTellsASystemLocaleBuildOnlyWhereTheShippedLocaleIsNeeded(): void
    {
        $directory = $this->buildDirectory($this->buildWithSystemLocale('mpp'));

        $this->assertSame(
            sprintf('the build was made on %s with a system locale, run "php cli/setup.php build" on the platform of the web server', PHP_OS_FAMILY),
            ShippedTranslationsBuild::platformProblem($directory)
        );

        NativeGettext::useSystemLocaleForTests(true);
        $this->assertNull(ShippedTranslationsBuild::platformProblem($directory), 'the platform of a system locale can use the build');
    }

    public function testPlatformProblemIsNullForAShippedLocaleBuildAndForAManifestOfAFormerVersion(): void
    {
        $shipped = $this->buildDirectory($this->buildWithSystemLocale('mpq'));
        $this->editManifest($shipped, static function (array $data): array {
            $data['locale'] = 'shipped';

            return $data;
        });
        $former = $this->buildDirectory($this->buildWithSystemLocale('mpr'));
        $this->editManifest($former, static function (array $data): array {
            unset($data['locale'], $data['platform']);

            return $data;
        });

        $this->assertNull(ShippedTranslationsBuild::platformProblem($shipped));
        $this->assertNull(ShippedTranslationsBuild::platformProblem($former), 'without the fields a manifest counts as shipped');
        $this->assertNull(ShippedTranslationsBuild::platformProblem($this->root . '/no-build'));
    }

    /**
     * @param \Closure(array<string, mixed>): array<string, mixed> $change
     */
    private function editManifest(string $build_directory, \Closure $change): void
    {
        $file = $build_directory . '/manifest.json';
        $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('system', $data['locale'] ?? null, 'precondition');
        file_put_contents($file, json_encode($change($data), JSON_THROW_ON_ERROR));
    }

    public function testBuildsOfTheOtherLocaleKindAndTheirPointerSurviveTheCleanup(): void
    {
        $this->requireSystemLocale();
        MigratedPoFixture::requireGlibcLocale($this);
        $build_as = function (bool $system_locale, string $greeting): string {
            $this->ship('mcl', 'de', ['greeting' => $greeting]);
            NativeGettext::resetForTests();
            NativeGettext::useSystemLocaleForTests($system_locale);

            return (string) $this->build(['mcl'])['build'];
        };
        $system_pointer = MigratedLanguageFilePaths::buildPointerFileOf($this->artifacts, true);
        $glibc_pointer = MigratedLanguageFilePaths::buildPointerFileOf($this->artifacts, false);

        $system_1 = $build_as(true, 'S1');
        $system_pointer_before = (string) file_get_contents($system_pointer);
        $glibc_1 = $build_as(false, 'G1');
        $glibc_2 = $build_as(false, 'G2');
        $glibc_3 = $build_as(false, 'G3');

        $this->assertSame($system_pointer_before, (string) file_get_contents($system_pointer), 'a glibc build never switches the pointer of the system locale');
        $this->assertDirectoryExists($this->buildDirectory($system_1));
        $this->assertDirectoryDoesNotExist($this->buildDirectory($glibc_1), 'per kind only the current and the previous build stay');
        $this->assertSame([$glibc_3, $glibc_2], [ShippedTranslationsBuild::readPointerOf($this->artifacts, false)['build'] ?? null, ShippedTranslationsBuild::readPointerOf($this->artifacts, false)['previous'] ?? null]);

        $system_2 = $build_as(true, 'S2');
        $system_3 = $build_as(true, 'S3');

        $this->assertFileExists($glibc_pointer);
        foreach ([$glibc_3, $glibc_2, $system_3, $system_2] as $kept) {
            $this->assertDirectoryExists($this->buildDirectory($kept));
        }
        $this->assertDirectoryDoesNotExist($this->buildDirectory($system_1));
        $this->assertSame([$system_3, $system_2], [ShippedTranslationsBuild::readPointerOf($this->artifacts, true)['build'] ?? null, ShippedTranslationsBuild::readPointerOf($this->artifacts, true)['previous'] ?? null]);
        $this->assertSame($glibc_3, ShippedTranslationsBuild::readPointerOf($this->artifacts, false)['build'] ?? null);
    }

    /**
     * @return iterable<string, array{bool}> whether the building PHP uses a system locale
     */
    public static function localeKinds(): iterable
    {
        yield 'system locale' => [true];
        yield 'glibc' => [false];
    }

    #[DataProvider('localeKinds')]
    public function testABuildRemovesLeftoversOfItsOwnLocaleKindOnly(bool $system_locale): void
    {
        if ($system_locale) {
            $this->requireSystemLocale();
        } else {
            MigratedPoFixture::requireGlibcLocale($this);
        }
        $run = function (string $greeting) use ($system_locale): void {
            $this->ship('mlk', 'de', ['greeting' => $greeting]);
            NativeGettext::resetForTests();
            NativeGettext::useSystemLocaleForTests($system_locale);
            $this->build(['mlk']);
        };
        $run('Hallo');
        $own_temporary = $this->artifacts . '/.b-' . str_repeat('1', 32) . ($system_locale ? '.system.' : '.') . str_repeat('a', 12);
        $foreign_temporary = $this->artifacts . '/.b-' . str_repeat('2', 32) . ($system_locale ? '.' : '.system.') . str_repeat('b', 12);
        $own_build = $this->artifacts . '/b-' . str_repeat('3', 32);
        $foreign_build = $this->artifacts . '/b-' . str_repeat('4', 32);
        $own_pointer_temporary = $this->artifacts . '/.' . ($system_locale ? 'current-system' : 'current') . '.json.abc123';
        $foreign_pointer_temporary = $this->artifacts . '/.' . ($system_locale ? 'current' : 'current-system') . '.json.abc123';
        foreach ([$own_temporary, $foreign_temporary, $own_build . '/messages', $foreign_build . '/messages'] as $directory) {
            mkdir($directory, 0775, true);
        }
        // a build is of the glibc kind where it ships a locale
        mkdir(($system_locale ? $foreign_build : $own_build) . '/locale', 0775, true);
        file_put_contents($own_pointer_temporary, '{}');
        file_put_contents($foreign_pointer_temporary, '{}');

        $run('Moin');

        foreach ([$foreign_temporary, $foreign_build, $foreign_pointer_temporary] as $kept) {
            $this->assertFileExists($kept, 'left by the other locale kind');
        }
        foreach ([$own_temporary, $own_build, $own_pointer_temporary] as $removed) {
            $this->assertFileDoesNotExist($removed, 'a leftover of this locale kind');
        }
    }

    public function testRebuildingAnEarlierStateSwitchesBackWithoutLosingThePreviousBuild(): void
    {
        $this->ship('mra', 'de', ['greeting' => 'Hallo']);
        $a = (string) $this->build(['mra'])['build'];
        $this->ship('mra', 'de', ['greeting' => 'Moin']);
        $b = (string) $this->build(['mra'])['build'];
        $this->ship('mra', 'de', ['greeting' => 'Hallo']);

        $c = $this->build(['mra']);

        $this->assertSame($a, $c['build'], 'the build id is a fingerprint of the content');
        $pointer = ShippedTranslationsBuild::readPointer($this->artifacts);
        $this->assertSame($a, $pointer['build'] ?? null);
        $this->assertSame($b, $pointer['previous'] ?? null);
        $this->assertDirectoryExists($this->buildDirectory($b));
        $this->assertFalse($c['unchanged'], 'the build served before was another one');
    }

    public function testServesShippedPoIsTrueOnlyForThePoTheBuildWasMadeFrom(): void
    {
        $po = $this->root . '/msp/msp_de.po';
        $this->ship('msp', 'de', ['greeting' => 'Hallo']);
        $this->ship('msp', 'en', ['greeting' => 'Hello']);
        $serves = fn(string $module, string $lang_key, string $file): bool => ShippedTranslationsBuild::servesShippedPo($this->artifacts, $module, $lang_key, $file);

        $this->assertFalse($serves('msp', 'de', $po), 'no build at all');

        $this->build(['msp']);
        $this->assertTrue($serves('msp', 'de', $po));
        $this->assertTrue($serves('msp', 'en', $this->root . '/msp/msp_en.po'));
        $this->assertFalse($serves('msp', 'de', $this->root . '/msp/msp_en.po'), 'the hash of another language');
        $this->assertFalse($serves('msp', 'fr', $po), 'a language that was not built');
        $this->assertFalse($serves('other', 'de', $po), 'a module that was not built');
        $this->assertFalse($serves('msp', 'de', $this->root . '/msp/missing.po'), 'a file that does not exist');

        $this->ship('msp', 'de', ['greeting' => 'Moin']);
        $this->assertFalse($serves('msp', 'de', $po), 'written after the build (e.g. by merge)');
        $this->assertTrue($serves('msp', 'en', $this->root . '/msp/msp_en.po'), 'another language is not affected');

        $this->build(['msp']);
        $this->assertTrue($serves('msp', 'de', $po));
    }

    /** The manifests are cached per build directory: each variant changes the file before the first call. */
    public function testServesShippedPoIsFalseWhenTheManifestOfTheCurrentBuildHasNoHashes(): void
    {
        $this->ship('msp', 'de', ['greeting' => 'Hallo']);
        $build = (string) $this->build(['msp'])['build'];
        $manifest = $this->manifest($build);
        unset($manifest['hashes']);
        file_put_contents($this->buildDirectory($build) . '/manifest.json', json_encode($manifest));

        $this->assertFalse(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'msp', 'de', $this->root . '/msp/msp_de.po'));
    }

    public function testServesShippedPoIsFalseWhenTheManifestOfTheCurrentBuildIsGone(): void
    {
        $this->ship('msp', 'de', ['greeting' => 'Hallo']);
        $build = (string) $this->build(['msp'])['build'];
        unlink($this->buildDirectory($build) . '/manifest.json');

        $this->assertFalse(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'msp', 'de', $this->root . '/msp/msp_de.po'));
    }

    public function testCurrentJsonHoldsExactlyTheBuildThePreviousBuildAndTheModules(): void
    {
        $this->ship('mpa', 'de', ['greeting' => 'Hallo']);
        $this->ship('mpa', 'en', ['greeting' => 'Hello']);
        $first = (string) $this->build(['mpa'])['build'];
        $this->ship('mpa', 'de', ['greeting' => 'Moin']);
        $second = (string) $this->build(['mpa'])['build'];

        $data = json_decode((string) file_get_contents($this->pointerFile()), true, 512, JSON_THROW_ON_ERROR);

        $keys = array_keys($data);
        sort($keys);
        $this->assertSame(['build', 'modules', 'previous', 'shipped', 'sources'], $keys);
        sort($data['modules']['mpa']);
        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $data['sources']);
        unset($data['sources']);
        $this->assertSame(['build' => $second, 'previous' => $first, 'modules' => ['mpa' => ['de', 'en']], 'shipped' => ['mpa']], $data);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: list<string>|string|null}> the "shipped" entry written, what readPointer() gives
     */
    public static function writtenShippedLists(): array
    {
        return [
            'written before the list existed' => [[], null],
            'null' => [['shipped' => null], null],
            'invalid and non-string entries are dropped' => [['shipped' => ['mpa', 'Bad Name', 5, '../x', 'mpb']], ['mpa', 'mpb']],
            // only the list is lost: the runtime falls back to the templates
            'not a list' => [['shipped' => ['a' => 'mpa']], null],
            'a string' => [['shipped' => 'mpa'], null],
        ];
    }

    /**
     * @param array<string, mixed> $changes
     * @param list<string>|string|null $expected "invalid": the whole pointer is rejected
     */
    #[DataProvider('writtenShippedLists')]
    public function testReadPointerTakesOnlyValidModulesOfTheShippedList(array $changes, array|string|null $expected): void
    {
        $this->ship('mpa', 'de', ['greeting' => 'Hallo']);
        $this->build(['mpa']);
        $data = json_decode((string) file_get_contents($this->pointerFile()), true, 512, JSON_THROW_ON_ERROR);
        unset($data['shipped']);
        file_put_contents($this->pointerFile(), json_encode($changes + $data));

        $pointer = ShippedTranslationsBuild::readPointer($this->artifacts);

        if ($expected === 'invalid') {
            $this->assertNull($pointer);
        } else {
            $this->assertNotNull($pointer);
            $this->assertSame($expected, $pointer['shipped']);
            $this->assertSame(['mpa' => ['de']], $pointer['modules'], 'the rest of an old file is still read');
        }
    }

    public function testEveryContributedModuleIsRecordedAlsoWithoutAnyPoAndWithoutABuild(): void
    {
        $this->ship('mpa', 'de', ['greeting' => 'Hallo']);
        mkdir($this->root . '/mpz', 0775, true);

        $this->build(['mpz', 'mpa']);

        $this->assertSame(['mpa', 'mpz'], ShippedTranslationsBuild::readPointer($this->artifacts)['shipped'] ?? null, 'sorted, "mpz" has no .po');

        MigratedPoFixture::removeDirectory($this->root . '/mpa');
        $result = $this->build(['mpz', 'mpa']);

        $this->assertNull($result['build']);
        $this->assertSame(
            ['build' => null, 'previous' => null, 'modules' => [], 'shipped' => ['mpa', 'mpz'], 'sources' => null],
            ShippedTranslationsBuild::readPointer($this->artifacts),
            'without any .po no build, the contributions are still recorded'
        );
    }

    private function directoryWithPattern(string $path, string $pattern): LanguageFileDirectory
    {
        return new class ($path, $pattern) implements LanguageFileDirectory, NamesShippedLanguageFiles {
            public function __construct(private string $path, private string $pattern)
            {
            }

            public function getPrefix(): string
            {
                return 'mtp';
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getSuffix(): string
            {
                return '';
            }

            public function isLocal(): bool
            {
                return false;
            }

            public function getShippedFileNamePattern(): string
            {
                return $this->pattern;
            }
        };
    }

    /**
     * @return array<string, array{0: string, 1: ?string, 2: string, 3: bool}> directory, pattern of the .po files,
     *         template to create, whether a warning is expected
     */
    public static function templateLayouts(): array
    {
        return [
            'lang/<module>.pot of a component' => ['components/Vendor/Comp/lang/', null, 'mtp.pot', false],
            'no template' => ['components/Vendor/Comp/lang/', null, '', true],
            'template outside components/<Vendor>/<Component>/lang/' => ['mtp/', null, 'mtp.pot', true],
            'template named by the pattern, not the module' => ['components/Vendor/Comp/lang/', 'ilias_%s', 'ilias.pot', true],
        ];
    }

    #[DataProvider('templateLayouts')]
    public function testAContributionWithoutItsTemplateInTheComponentsLangDirectoryIsWarnedAbout(
        string $path,
        ?string $pattern,
        string $template,
        bool $expect_warning
    ): void {
        MigratedPoFixture::writePo(
            $this->root . '/' . $path . ($pattern === null ? 'mtp_de' : 'ilias_de') . '.po',
            MigratedPoFixture::catalog('mtp', ['greeting' => 'Hallo'])
        );
        if ($template !== '') {
            file_put_contents($this->root . '/' . $path . $template, '');
        }
        $directory = $pattern === null
            ? MigratedPoFixture::directory('mtp', $path)
            : $this->directoryWithPattern($path, $pattern);
        $messages = [];

        (new ShippedTranslationsBuild(
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory),
            $this->root,
            new ShippedTranslations()
        ))->run(static function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $warnings = array_values(array_filter($messages, static fn(string $m): bool => str_contains($m, 'has no template')));
        $this->assertSame(
            $expect_warning ? ['WARNING: Module "mtp" has no template "components/<Vendor>/<Component>/lang/mtp.pot" - without a build Setup and CLI do not recognise it as shipped.'] : [],
            $warnings
        );
    }

    /** The file name pattern is invalid, so nothing is compiled - the module is still recorded as shipped (never read from the database). */
    public function testAContributionWithAnInvalidFileNamePatternIsStillRecordedAsShipped(): void
    {
        $messages = [];

        (new ShippedTranslationsBuild(
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directoryWithPattern('mtp/', '%s_%s')),
            $this->root,
            new ShippedTranslations()
        ))->run(static function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->assertNotEmpty(array_filter($messages, static fn(string $m): bool => str_contains($m, 'The language files of module "mtp" are not compiled')));
        $this->assertSame(['mtp'], ShippedTranslationsBuild::readPointer($this->artifacts)['shipped'] ?? null);
    }

    public function testReadPointerIgnoresFieldsAFormerVersionWroteIntoCurrentJson(): void
    {
        $this->ship('mpa', 'de', ['greeting' => 'Hallo']);
        $build = (string) $this->build(['mpa'])['build'];
        $pointer = ShippedTranslationsBuild::readPointer($this->artifacts);
        $data = json_decode((string) file_get_contents($this->pointerFile()), true, 512, JSON_THROW_ON_ERROR);
        $data['hashes'] = ['mpa' => ['de' => str_repeat('a', 64)]];
        $data['warnings'] = ['old warning'];
        $data['collisions'] = ['old collision'];
        file_put_contents($this->pointerFile(), json_encode($data));

        $this->assertSame(['build' => $build, 'previous' => null, 'modules' => ['mpa' => ['de']], 'shipped' => ['mpa'], 'sources' => $pointer['sources'] ?? null], ShippedTranslationsBuild::readPointer($this->artifacts));
        $this->assertSame($pointer, ShippedTranslationsBuild::readPointer($this->artifacts));
        $this->assertTrue($this->build(['mpa'])['unchanged'], 'a current.json of the former layout is no reason to rebuild');
    }

    public function testABrokenPoIsServedFromTheServedBuildAndIsNotCountedAsCompiled(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);
        $good = (string) $this->build(['mfa', 'mfb'])['build'];
        $this->breakPo('mfa', 'de');

        $result = $this->build(['mfa', 'mfb']);

        $this->assertNotSame($good, $result['build']);
        $this->assertSame(['greeting' => 'Hallo'], $this->servedValues((string) $result['build'], 'mfa', 'de'));
        $this->assertSame(['greeting' => 'Servus'], $this->servedValues((string) $result['build'], 'mfb', 'de'));
        $this->assertSame(1, $result['compiled'], 'only "mfb"; the catalog of "mfa" is taken over, not compiled');
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('mfa_de.po', $result['warnings'][0]);
        $this->assertStringContainsString($good, $result['warnings'][0], 'names the build it is served from');
        $this->assertSame($result['warnings'], $this->manifest((string) $result['build'])['warnings']);
        $this->assertSame(['mfa' => ['de'], 'mfb' => ['de']], ShippedTranslationsBuild::readPointer($this->artifacts)['modules'] ?? null);
        $this->assertSame($good, ShippedTranslationsBuild::readPointer($this->artifacts)['previous'] ?? null);
        $this->assertFalse(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'mfa', 'de', $this->root . '/mfa/mfa_de.po'), 'the broken .po is not what is served');
        $this->assertTrue(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'mfb', 'de', $this->root . '/mfb/mfb_de.po'));
    }

    public function testTheFallbackChainsOverSeveralBuildsAndWarnsOnEveryRun(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);
        $this->build(['mfa', 'mfb']);
        $this->breakPo('mfa', 'de');
        $second = $this->build(['mfa', 'mfb']);
        // another module changes, the broken file stays broken: the catalog of "mfa" comes from the second build
        $this->ship('mfb', 'de', ['greeting' => 'Moin']);

        $third = $this->build(['mfa', 'mfb']);

        $this->assertNotSame($second['build'], $third['build']);
        $this->assertSame(['greeting' => 'Hallo'], $this->servedValues((string) $third['build'], 'mfa', 'de'));
        $this->assertSame(['greeting' => 'Moin'], $this->servedValues((string) $third['build'], 'mfb', 'de'));
        $this->assertCount(1, $third['warnings']);
        $this->assertFalse(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'mfa', 'de', $this->root . '/mfa/mfa_de.po'));
        // an unchanged run repeats the warning
        $this->assertSame($third['warnings'], $this->build(['mfa', 'mfb'])['warnings']);
    }

    public function testABrokenPoIsServedFromThePreviousBuildWhenTheServedOneIsDamaged(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);
        $first = (string) $this->build(['mfa', 'mfb'])['build'];
        $this->ship('mfb', 'de', ['greeting' => 'Moin']);
        $second = (string) $this->build(['mfa', 'mfb'])['build'];
        // the C library keeps what it read from a path: damage a file of a path it has not seen
        MigratedPoFixture::moveArtifactsToFreshDirectory();
        $this->artifacts = MigratedLanguageFilePaths::shippedArtifactDirectory($this->root);
        $damaged = MigratedLanguageFilePaths::buildMoFile($this->buildDirectory($second), 'mfa', 'de');
        $content = (string) file_get_contents($damaged);
        file_put_contents($damaged, substr($content, 0, -1) . ($content[-1] === "\x00" ? "\x01" : "\x00"));
        $this->breakPo('mfa', 'de');

        $result = $this->build(['mfa', 'mfb']);

        $this->assertSame(['greeting' => 'Hallo'], $this->servedValues((string) $result['build'], 'mfa', 'de'));
        $this->assertStringContainsString($first, $result['warnings'][0], 'the intact previous build, not the damaged served one');
    }

    public function testADamagedCatalogOfTheFormerBuildIsNotTakenOver(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $good = (string) $this->build(['mfa'])['build'];
        MigratedPoFixture::moveArtifactsToFreshDirectory();
        $this->artifacts = MigratedLanguageFilePaths::shippedArtifactDirectory($this->root);
        $damaged = MigratedLanguageFilePaths::buildMoFile($this->buildDirectory($good), 'mfa', 'de');
        $content = (string) file_get_contents($damaged);
        file_put_contents($damaged, substr($content, 0, -1) . ($content[-1] === "\x00" ? "\x01" : "\x00"));
        $this->assertSame(strlen($content), strlen((string) file_get_contents($damaged)), 'precondition: same size, other hash');
        $this->breakPo('mfa', 'de');
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);

        $result = $this->build(['mfa', 'mfb']);

        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('"mfa" is not served for "de"', $result['warnings'][0]);
        $this->assertSame(['mfb' => ['de']], ShippedTranslationsBuild::readPointer($this->artifacts)['modules'] ?? null, 'the damaged catalog is not taken over');
    }

    public function testABrokenPoWithoutAFormerBuildIsNotServedAndTheBuildGoesOn(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);
        $this->breakPo('mfb', 'de');

        $result = $this->build(['mfa', 'mfb']);

        $this->assertNotNull($result['build']);
        $this->assertSame(1, $result['compiled']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('mfb_de.po', $result['warnings'][0]);
        $this->assertStringContainsString('"mfb" is not served for "de"', $result['warnings'][0]);
        $this->assertSame(['mfa' => ['de']], ShippedTranslationsBuild::readPointer($this->artifacts)['modules'] ?? null);
        $this->assertSame(['greeting' => 'Hallo'], $this->servedValues((string) $result['build'], 'mfa', 'de'));
        $this->assertFalse(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'mfb', 'de', $this->root . '/mfb/mfb_de.po'));
    }

    public function testABrokenPoOfANewModuleIsNotServedAndTheBuildIsSwitched(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $good = (string) $this->build(['mfa'])['build'];
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);
        $this->breakPo('mfb', 'de');
        $this->ship('mfa', 'de', ['greeting' => 'Moin']);

        $result = $this->build(['mfa', 'mfb']);

        $this->assertNotSame($good, $result['build']);
        $this->assertStringContainsString('"mfb" is not served for "de"', $result['warnings'][0] ?? '');
        $pointer = ShippedTranslationsBuild::readPointer($this->artifacts);
        $this->assertSame($result['build'], $pointer['build'] ?? null);
        $this->assertSame($good, $pointer['previous'] ?? null);
        $this->assertSame(['mfa' => ['de']], $pointer['modules'] ?? null);
        $this->assertSame(['greeting' => 'Moin'], $this->servedValues((string) $result['build'], 'mfa', 'de'));
    }

    /**
     * Builds "mfa"/"mfb" and moves the artifacts to a path the C library has not seen (it caches per path).
     *
     * @return array{string, string} build id, path of the `.mo`
     */
    private function formerBuildWithMovedArtifacts(): array
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->ship('mfb', 'de', ['greeting' => 'Servus']);
        $good = (string) $this->build(['mfa', 'mfb'])['build'];
        MigratedPoFixture::moveArtifactsToFreshDirectory();
        $this->artifacts = MigratedLanguageFilePaths::shippedArtifactDirectory($this->root);

        return [$good, MigratedLanguageFilePaths::buildMoFile($this->buildDirectory($good), 'mfa', 'de')];
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function changeManifest(string $build, array $changes): void
    {
        file_put_contents(
            $this->buildDirectory($build) . '/manifest.json',
            json_encode($changes + $this->manifest($build), JSON_THROW_ON_ERROR)
        );
    }

    private function assertMfaIsNotServed(): void
    {
        $result = $this->build(['mfa', 'mfb']);

        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('"mfa" is not served for "de"', $result['warnings'][0]);
        $this->assertSame(['mfb' => ['de']], ShippedTranslationsBuild::readPointer($this->artifacts)['modules'] ?? null);
    }

    public function testACatalogOfABuildCompiledByOtherCodeIsNotTakenOver(): void
    {
        [$good] = $this->formerBuildWithMovedArtifacts();
        $this->changeManifest($good, ['compatibility' => 'compiled by other code']);
        $this->breakPo('mfa', 'de');

        $this->assertMfaIsNotServed();
    }

    /** Control of the tests above: with nothing manipulated the catalog of the former build is taken over. */
    public function testTheCatalogOfAFormerBuildOfTheSameCodeIsTakenOver(): void
    {
        [$good] = $this->formerBuildWithMovedArtifacts();
        $this->breakPo('mfa', 'de');

        $result = $this->build(['mfa', 'mfb']);

        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString($good, $result['warnings'][0], 'names the build it is served from');
        $this->assertSame(['mfa' => ['de'], 'mfb' => ['de']], ShippedTranslationsBuild::readPointer($this->artifacts)['modules'] ?? null);
        $this->assertSame(['greeting' => 'Hallo'], $this->servedValues((string) $result['build'], 'mfa', 'de'));
    }

    /** The value a build writes into its manifest covers the format version and the files of the classes that make up a catalog. */
    public function testTheCompatibilityOfABuildHashesTheFormatVersionAndTheCatalogClasses(): void
    {
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $build = (string) $this->build(['mfa'])['build'];

        $reflection = new \ReflectionClass(ShippedTranslationsBuild::class);
        $classes = $reflection->getReflectionConstant('CATALOG_CLASSES')->getValue();
        $hashes = array_map(
            static fn(string $class): string => hash_file('sha256', (string) (new \ReflectionClass($class))->getFileName()),
            $classes
        );
        $expected = hash('sha256', $reflection->getReflectionConstant('FORMAT_VERSION')->getValue() . '|' . hash('sha256', implode('|', $hashes)));

        $this->assertSame($expected, $this->manifest($build)['compatibility']);
    }

    public function testTheCatalogClassesIncludeThoseThatDetermineTheBytesOfACatalog(): void
    {
        $classes = (new \ReflectionClass(ShippedTranslationsBuild::class))->getReflectionConstant('CATALOG_CLASSES')->getValue();

        foreach ([TranslationCatalog::class, TranslationEntry::class, PluralForms::class, ShippedTranslations::class, TranslationMarkupPolicy::class] as $class) {
            $this->assertContains($class, $classes);
        }
    }

    public function testACatalogOfABuildWithoutACompatibilityValueIsNotTakenOver(): void
    {
        [$good] = $this->formerBuildWithMovedArtifacts();
        $manifest = $this->manifest($good);
        unset($manifest['compatibility']);
        file_put_contents($this->buildDirectory($good) . '/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $this->breakPo('mfa', 'de');

        $this->assertMfaIsNotServed();
    }

    public function testACatalogWithMarkupThePolicyDisallowsIsNotTakenOver(): void
    {
        [$good, $mo_file] = $this->formerBuildWithMovedArtifacts();
        // an intact file as far as the manifest tells (size and hash fit), but with a value no build would write
        $mo = MigratedPoFixture::catalog('mfa', ['greeting' => '<script>alert(1)</script>Hallo'])->toMoString();
        file_put_contents($mo_file, $mo);
        $manifest = $this->manifest($good);
        $manifest['files'][substr($mo_file, strlen($this->buildDirectory($good)) + 1)] = [strlen($mo), hash('sha256', $mo)];
        file_put_contents($this->buildDirectory($good) . '/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $this->breakPo('mfa', 'de');

        $this->assertMfaIsNotServed();
    }

    public function testACatalogReachedOnlyThroughASymlinkOutOfTheBuildIsNotTakenOver(): void
    {
        [, $mo_file] = $this->formerBuildWithMovedArtifacts();
        // size and hash fit: only the location tells
        $outside = $this->root . '/outside.mo';
        rename($mo_file, $outside);
        symlink($outside, $mo_file);
        $this->breakPo('mfa', 'de');

        $this->assertMfaIsNotServed();
    }
}
