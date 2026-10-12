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

use MigratedPoFixture;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ShippedTranslationsBuild: on success a collision list, the previous build kept, older ones removed, an unchanged run
 * touching nothing, one build at a time; on failure `current.json` (what running requests are served from) stays as it
 * was and no half-written build remains.
 */
class ShippedTranslationsBuildTest extends TestCase
{
    private string $root;
    private string $artifacts;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_shipped_build_test_' . bin2hex(random_bytes(6));
        mkdir($this->root, 0775, true);
        $this->artifacts = MigratedLanguageFilePaths::shippedArtifactDirectory($this->root);
    }

    protected function tearDown(): void
    {
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
     * @return array{build: ?string, compiled: int, unchanged: bool, removed: int, warnings: list<string>, collisions: list<string>}
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
        $this->assertSame($expected, ShippedTranslationsBuild::readPointer($this->artifacts)['collisions'] ?? null);
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
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $good = $this->build(['mfa']);
        $pointer_before = (string) file_get_contents($this->pointerFile());
        $entries_before = $this->artifactEntries();
        $this->ship('mfa', 'de', ['greeting' => 'Moin']);
        // as in a fresh Setup process: nothing active that could let the broken locale pass
        NativeGettext::resetForTests();

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
        $this->ship('mfa', 'de', ['greeting' => 'Hallo']);
        $this->build(['mfa']);
        $pointer_before = (string) file_get_contents($this->pointerFile());
        $entries_before = $this->artifactEntries();

        try {
            $this->build(['mfa'], $this->root . '/no-such-locale');
            $this->fail('a build without a locale to ship must not be accepted');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('"C.utf8" of the C library was not found', $e->getMessage());
            $this->assertStringContainsString('no-such-locale', $e->getMessage());
        }

        $this->assertSame($pointer_before, (string) file_get_contents($this->pointerFile()));
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
        $expected = ['build.lock', 'current.json', 'de', 'keep-me', 'keep-me.txt', $builds[1], $builds[2]];
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

    public function testServesShippedPoIsFalseWhenCurrentJsonHasNoHashes(): void
    {
        $this->ship('msp', 'de', ['greeting' => 'Hallo']);
        $this->build(['msp']);
        $pointer = json_decode((string) file_get_contents($this->pointerFile()), true);
        unset($pointer['hashes']);
        file_put_contents($this->pointerFile(), json_encode($pointer));

        $this->assertFalse(ShippedTranslationsBuild::servesShippedPo($this->artifacts, 'msp', 'de', $this->root . '/msp/msp_de.po'));
    }
}
