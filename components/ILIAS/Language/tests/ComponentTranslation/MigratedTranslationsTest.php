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

use ILIAS\DI\Container;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use MigratedPoFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * MigratedTranslations, the runtime read path: what build and overlay serve through native gettext, what an unreadable
 * build or overlay does (shipped values stay served, one warning per request), and "not translated" (no value, no
 * warning) versus "translated as its identifier".
 */
class MigratedTranslationsTest extends TestCase
{
    private string $root;
    private string $client_data_dir;
    private string $build_directory;

    /**
     * @var list<string> the warnings the component logger received
     */
    private array $warnings = [];

    protected function setUp(): void
    {
        MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_migrated_translations_' . bin2hex(random_bytes(6));
        $this->client_data_dir = $this->root . '/client';
        mkdir($this->client_data_dir, 0775, true);

        $this->warnings = [];
        $logger = $this->createStub(\ilLogger::class);
        $logger->method('warning')->willReturnCallback(function (string $message): void {
            $this->warnings[] = $message;
        });
        $factory = $this->createStub(\ilLoggerFactory::class);
        $factory->method('getComponentLogger')->willReturn($logger);
        $GLOBALS['DIC'] = new Container();
        $GLOBALS['DIC']['ilLoggerFactory'] = static fn() => $factory;
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->root);
        MigratedPoFixture::resetRuntime();
    }

    /**
     * Module "mtr" (de: greeting, same = its own identifier, a plural item; en: greeting, only_en) and
     * module "mtz" (de: other), built.
     */
    private function build(): void
    {
        $item = new TranslationEntry('mtr', 'item');
        $item->setPlural('items', ['Eintrag', 'Einträge']);
        $de = MigratedPoFixture::catalog('mtr', ['greeting' => 'Hallo', 'same' => 'same']);
        $de->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $de->add($item);
        MigratedPoFixture::writePo($this->root . '/mtr/mtr_de.po', $de);
        MigratedPoFixture::writePo($this->root . '/mtr/mtr_en.po', MigratedPoFixture::catalog('mtr', ['greeting' => 'Hello', 'only_en' => 'Only']));
        MigratedPoFixture::writePo($this->root . '/mtz/mtz_de.po', MigratedPoFixture::catalog('mtz', ['other' => 'Anders']));

        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('mtr', 'mtr/'),
            MigratedPoFixture::directory('mtz', 'mtz/')
        );
        $build = MigratedPoFixture::build($manager, $this->root)['build'];
        $this->assertNotNull($build);
        $this->build_directory = MigratedLanguageFilePaths::buildDirectory(MigratedPoFixture::artifactDirectory(), $build);
    }

    /**
     * @param array<string, string> $entries
     * @return string the revision directory
     */
    private function writeOverlay(array $entries, string $module = 'mtr', string $lang_key = 'de'): string
    {
        $overlay_directory = MigratedLanguageFilePaths::overlayDirectory($this->client_data_dir, $module, $lang_key);
        MigratedPoFixture::writeOverlayRevision($overlay_directory . '/' . $module . '_' . $lang_key, MigratedPoFixture::catalog($module, $entries));

        return MigratedLanguageFilePaths::overlayRevisionDirectory(
            $overlay_directory,
            trim((string) file_get_contents(MigratedLanguageFilePaths::overlayCurrentFile($overlay_directory)))
        );
    }

    private function text(string $key, string $module = 'mtr', string $lang_key = 'de'): ?string
    {
        return MigratedTranslations::text($module, $lang_key, $key, $this->client_data_dir);
    }

    /**
     * @return array<string, string>|null
     */
    private function keys(string $module = 'mtr', string $lang_key = 'de'): ?array
    {
        return MigratedTranslations::keysOf($module, $lang_key, $this->client_data_dir);
    }

    public function testServesTheBuildAndTellsTranslatedAsItselfFromNotTranslated(): void
    {
        $this->build();

        $this->assertSame(['greeting', 'item', 'only_en', 'same'], array_keys($this->keys() ?? []), 'the identifiers of every language of the module');
        $this->assertSame('Hallo', $this->text('greeting'));
        $this->assertSame('Hello', $this->text('greeting', 'mtr', 'en'));
        $this->assertSame('same', $this->text('same'), 'a value equal to its identifier is a value (marker ilias-identity)');
        $this->assertNull($this->text('only_en'), 'listed, but not translated in this language');
        $this->assertNull($this->text('unknown'));
        $this->assertSame([], $this->warnings, 'an untranslated key is no problem');
    }

    public function testAModuleOrLanguageThatWasNotBuiltIsNotMigrated(): void
    {
        $this->build();

        $this->assertNull($this->keys('mtz', 'en'));
        $this->assertNull($this->keys('mtr', 'fr'));
        $this->assertNull($this->keys('nomodule', 'de'));
        $this->assertNull($this->keys('../mtr', 'de'), 'a module name is no path');
        $this->assertNull($this->text('greeting', 'mtr', 'fr'));
        $this->assertSame([], $this->warnings);
    }

    public function testTheValueOfAPluralMessageIsItsLastFormAndPluralTextSelectsByQuantity(): void
    {
        $this->build();

        $this->assertSame('Einträge', $this->text('item'), 'the default form of a plural message is the last one (see PluralForms)');
        $this->assertSame('Eintrag', MigratedTranslations::pluralText('mtr', 'de', 'item', 1, $this->client_data_dir));
        $this->assertSame('Einträge', MigratedTranslations::pluralText('mtr', 'de', 'item', 0, $this->client_data_dir));
        $this->assertNull(MigratedTranslations::pluralText('mtr', 'de', 'greeting', 2, $this->client_data_dir), 'no plural message');
        $this->assertNull(MigratedTranslations::pluralText('mtr', 'de', 'unknown', 2, $this->client_data_dir));
    }

    public function testTheOverlayWinsAndBringsItsOwnIdentifiers(): void
    {
        $this->build();
        $this->writeOverlay(['greeting' => 'Servus', 'extra' => 'Zusatz', 'item' => 'Datensatz']);

        $this->assertSame('Servus', $this->text('greeting'));
        $this->assertSame('Zusatz', $this->text('extra'));
        $this->assertSame('Hello', $this->text('greeting', 'mtr', 'en'), 'the overlay of another language is not involved');
        $this->assertSame('same', $this->text('same'), 'not in the overlay: the shipped value');
        $this->assertContains('extra', array_keys($this->keys() ?? []));
        $this->assertSame('Datensatz', $this->text('item'));
        $this->assertNull(
            MigratedTranslations::pluralText('mtr', 'de', 'item', 5, $this->client_data_dir),
            'a singular value in the overlay replaces the plural message'
        );
        $this->assertSame([], $this->warnings);
    }

    public function testAnOverlayValueEqualToItsIdentifierIsAValueNotAMissingTranslation(): void
    {
        $this->build();
        $this->writeOverlay(['greeting' => 'greeting']);

        $this->assertSame('greeting', $this->text('greeting'), 'not the shipped "Hallo", not null');
    }

    /**
     * @return iterable<string, array{\Closure(string): void}>
     */
    public static function unreadableOverlays(): iterable
    {
        yield 'current names no valid revision' => [static function (string $revision_directory): void {
            file_put_contents(dirname($revision_directory) . '/current', "not-a-revision\n");
        }];
        yield 'current names a revision that is gone' => [static function (string $revision_directory): void {
            MigratedPoFixture::removeDirectory($revision_directory);
        }];
        yield 'keys.json is not JSON' => [static function (string $revision_directory): void {
            file_put_contents($revision_directory . '/keys.json', '{nope');
        }];
        yield 'keys.json is an object' => [static function (string $revision_directory): void {
            file_put_contents($revision_directory . '/keys.json', '{"greeting":"extra"}');
        }];
        yield 'keys.json holds something that is no string' => [static function (string $revision_directory): void {
            file_put_contents($revision_directory . '/keys.json', '["extra", 5]');
        }];
        yield 'the catalog is not readable' => [static function (string $revision_directory): void {
            file_put_contents($revision_directory . '/messages/LC_MESSAGES/mtr.de.overlay.mo', 'garbage');
        }];
    }

    /**
     * @param \Closure(string): void $damage
     */
    #[DataProvider('unreadableOverlays')]
    public function testAnOverlayThatCannotBeReadIsLoggedOnceAndTheShippedValuesAreServed(\Closure $damage): void
    {
        $this->build();
        $damage($this->writeOverlay(['greeting' => 'Servus', 'extra' => 'Zusatz']));

        for ($i = 0; $i < 3; $i++) {
            $this->assertSame('Hallo', $this->text('greeting'));
            $this->assertNull($this->text('extra'));
        }

        $this->assertArrayNotHasKey('extra', $this->keys() ?? []);
        $this->assertCount(1, $this->warnings, 'once per request, not once per lookup');
        $this->assertStringContainsString('local changes are not served', $this->warnings[0]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function symbolicLinkLevels(): iterable
    {
        yield 'lang/<module>' => ['module'];
        yield '<lang>' => ['lang'];
        yield 'current' => ['current'];
        yield 'current, dangling' => ['dangling'];
        yield 'the revision directory' => ['revision'];
        yield 'messages' => ['messages'];
        yield 'LC_MESSAGES' => ['catalogs'];
        yield 'the catalog' => ['mo'];
        yield 'keys.json' => ['keys'];
    }

    /**
     * Like the write path, the read path follows no symlink below the overlay root: the files are readable through the
     * link and still not served.
     */
    #[DataProvider('symbolicLinkLevels')]
    public function testAnOverlayWithASymbolicLinkAnywhereBelowTheOverlayRootIsNotServed(string $level): void
    {
        $this->build();
        $revision_directory = $this->writeOverlay(['greeting' => 'Servus', 'extra' => 'Zusatz']);
        $overlay_directory = dirname($revision_directory);
        $path = match ($level) {
            'module' => dirname($overlay_directory),
            'lang' => $overlay_directory,
            'current', 'dangling' => $overlay_directory . '/current',
            'revision' => $revision_directory,
            'messages' => $revision_directory . '/messages',
            'catalogs' => $revision_directory . '/' . NativeGettext::CATALOG_DIRECTORY,
            'mo' => MigratedLanguageFilePaths::overlayMoFile($revision_directory, 'mtr', 'de'),
            'keys' => $revision_directory . '/keys.json',
            default => throw new \LogicException($level),
        };
        if ($level === 'dangling') {
            unlink($path);
            symlink($this->root . '/does-not-exist', $path);
        } else {
            mkdir($this->root . '/away', 0775, true);
            $target = $this->root . '/away/' . bin2hex(random_bytes(4));
            rename($path, $target);
            symlink($target, $path);
        }

        $this->assertSame('Hallo', $this->text('greeting'));
        $this->assertNull($this->text('extra'));
        $this->assertArrayNotHasKey('extra', $this->keys() ?? []);
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('symbolic link', $this->warnings[0]);
    }

    public function testACurrentFileLargerThanARevisionNameIsNotServedAndOnlyItsStartIsRead(): void
    {
        $this->build();
        $revision_directory = $this->writeOverlay(['greeting' => 'Servus']);
        $revision = basename($revision_directory);
        $current = dirname($revision_directory) . '/current';
        // the revision name is no longer at the start, and the file is far larger than any name
        file_put_contents($current, str_repeat('x', 200) . $revision . "\n" . str_repeat('y', 1_000_000));

        $this->assertSame('Hallo', $this->text('greeting'));
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('names no valid revision', $this->warnings[0]);
    }

    public function testOnlyTheStartOfACurrentFileIsRead(): void
    {
        $this->build();
        $revision_directory = $this->writeOverlay(['greeting' => 'Servus']);
        $current = dirname($revision_directory) . '/current';
        // the revision name, then far more than a revision file ever holds
        file_put_contents($current, basename($revision_directory) . str_repeat(' ', 200) . 'trailing');

        $this->assertSame('Servus', $this->text('greeting'));
        $this->assertSame([], $this->warnings);
    }

    public function testAPluralMessageIsServedAgainAfterAForeignReset(): void
    {
        $this->build();
        $this->assertSame('Eintrag', MigratedTranslations::pluralText('mtr', 'de', 'item', 1, $this->client_data_dir), 'precondition');
        setlocale(LC_MESSAGES, 'C');

        $this->assertSame('Einträge', MigratedTranslations::pluralText('mtr', 'de', 'item', 5, $this->client_data_dir));
        $this->assertSame([], $this->warnings);
    }

    /**
     * Native gettext losing its settings without being able to restore them: nothing is served, not even the identifier
     * for a value equal to it (as the overlay does for a listed identifier; txt() shows "-key-").
     */
    public function testLostNativeGettextServesNothingNotEvenTheIdentifierForAnOverlayValue(): void
    {
        $this->build();
        $plural = MigratedPoFixture::catalog('mtr', ['greeting' => 'Servus', 'same' => 'same']);
        $plural->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $item = new TranslationEntry('mtr', 'item');
        $item->setPlural('items', ['Datensatz', 'Datensätze']);
        $plural->add($item);
        $overlay_directory = MigratedLanguageFilePaths::overlayDirectory($this->client_data_dir, 'mtr', 'de');
        MigratedPoFixture::writeOverlayRevision($overlay_directory . '/mtr_de', $plural);
        $this->assertSame('Servus', $this->text('greeting'), 'precondition');
        $this->assertSame('same', $this->text('same'), 'precondition');
        $this->assertSame('Datensätze', MigratedTranslations::pluralText('mtr', 'de', 'item', 5, $this->client_data_dir), 'precondition');
        // another part of the request resets LC_MESSAGES, and the locale cannot be activated again
        setlocale(LC_MESSAGES, 'C');
        MigratedPoFixture::removeDirectory(MigratedLanguageFilePaths::buildLocaleDirectory($this->build_directory) . '/' . NativeGettext::LOCALE_NAME);

        $this->assertNull($this->text('greeting'), 'overlay value');
        $this->assertNull($this->text('same'), 'overlay value equal to its identifier');
        $this->assertNull($this->text('other', 'mtz'), 'shipped value of a module read for the first time only now');
        $this->assertNull(MigratedTranslations::pluralText('mtr', 'de', 'item', 5, $this->client_data_dir));

        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);
        $this->assertTrue($problem['fatal']);
        $this->assertStringContainsString('Native gettext is not available', $problem['message']);
        $this->assertCount(1, $this->warnings);
    }

    public function testNativeGettextThatIsActivatedAgainServesTheOverlayValueAfterAForeignReset(): void
    {
        $this->build();
        $this->writeOverlay(['greeting' => 'Servus', 'same' => 'same']);
        $this->assertSame('Servus', $this->text('greeting'), 'precondition');
        setlocale(LC_MESSAGES, 'C');

        $this->assertSame('Servus', $this->text('greeting'));
        $this->assertSame('same', $this->text('same'));
        $this->assertSame([], $this->warnings);
    }

    public function testWithoutAClientDataDirOrAnOverlayTheShippedValuesAreServedWithoutAWarning(): void
    {
        $this->build();

        $this->assertSame('Hallo', MigratedTranslations::text('mtr', 'de', 'greeting', null));
        $this->assertSame('Hallo', $this->text('greeting'), 'no overlay of this module and language: no current file');
        $this->assertSame([], $this->warnings);
    }

    public function testANewRevisionIsServedAfterInvalidateAlsoWhenTheProcessReadTheEarlierOnes(): void
    {
        $this->build();

        foreach (['Servus', 'Moin', 'Grüß Gott', 'Servus'] as $value) {
            $this->writeOverlay(['greeting' => $value]);
            MigratedTranslations::invalidate('mtr', 'de');

            $this->assertSame($value, $this->text('greeting'));
        }
        $this->assertSame([], $this->warnings);
    }

    public function testForgetLanguageDropsOnlyThatLanguage(): void
    {
        $this->build();
        $this->writeOverlay(['greeting' => 'Servus']);
        $this->assertSame('Servus', $this->text('greeting'));
        $this->assertSame('Hello', $this->text('greeting', 'mtr', 'en'));
        $this->writeOverlay(['greeting' => 'Moin']);
        $this->writeOverlay(['greeting' => 'Cheers'], 'mtr', 'en');

        MigratedTranslations::forgetLanguage('de');

        $this->assertSame('Moin', $this->text('greeting'));
        $this->assertSame('Hello', $this->text('greeting', 'mtr', 'en'), 'en was not forgotten (its overlay is not read again)');
    }

    public function testWithoutABuildNothingIsMigratedAndTheProblemSaysSo(): void
    {
        $this->assertNull($this->keys());
        $this->assertNull($this->text('greeting'));

        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);

        $this->assertStringContainsString('There is no build', $problem['message']);
        $this->assertTrue($problem['fatal']);
        $this->assertCount(1, $this->warnings);
    }

    public function testAHealthyBuildHasNoProblem(): void
    {
        $this->build();

        $this->assertNull(MigratedTranslations::getProblem($this->client_data_dir));
        $this->assertSame([], $this->warnings);
    }

    public function testNoBuildIsAFatalProblemWithoutAbsolutePaths(): void
    {
        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);

        $this->assertTrue($problem['fatal']);
        $this->assertStringContainsString('"artifacts/language/current.json"', $problem['message']);
        $this->assertStringNotContainsString(sys_get_temp_dir(), $problem['message']);
    }

    public function testAProblemOfOneModuleIsNotFatalAndHasNoAbsolutePaths(): void
    {
        $this->build();
        unlink(MigratedLanguageFilePaths::buildKeysFile($this->build_directory, 'mtr'));
        MigratedTranslations::reset();

        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);

        $this->assertFalse($problem['fatal']);
        $this->assertStringContainsString('"artifacts/language/' . basename($this->build_directory) . '/keys/mtr.php"', $problem['message']);
        $this->assertStringNotContainsString(sys_get_temp_dir(), $problem['message']);
    }

    public function testAFatalProblemIsReportedBeforeAnEarlierNonFatalOne(): void
    {
        $this->build();
        unlink(MigratedLanguageFilePaths::buildKeysFile($this->build_directory, 'mtr'));
        file_put_contents(MigratedLanguageFilePaths::buildMoFile($this->build_directory, 'mtz', 'de'), 'garbage');
        MigratedTranslations::reset();
        $this->keys('mtr');
        $this->keys('mtz');

        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);

        $this->assertTrue($problem['fatal']);
        $this->assertStringContainsString('mtz.de.mo', $problem['message']);
    }

    public function testAnOverlayProblemNamesItsPathRelativeToTheClientDataDirectory(): void
    {
        $this->build();
        $revision_directory = $this->writeOverlay(['greeting' => 'Servus']);
        file_put_contents($revision_directory . '/keys.json', '{nope');
        $this->keys();

        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);

        $this->assertFalse($problem['fatal']);
        $this->assertStringContainsString('"<data>/lang/mtr/de/' . basename($revision_directory) . '"', $problem['message']);
        $this->assertStringNotContainsString($this->client_data_dir, $problem['message']);
    }

    public function testGetProblemChecksTheFirstModuleOfTheBuildEvenIfNothingWasUsedYet(): void
    {
        $this->build();
        unlink(MigratedLanguageFilePaths::buildKeysFile($this->build_directory, 'mtr'));
        MigratedTranslations::reset();

        $problem = MigratedTranslations::getProblem($this->client_data_dir);
        $this->assertNotNull($problem);

        $this->assertStringContainsString('identifiers of module "mtr" cannot be read', $problem['message']);
        $this->assertFalse($problem['fatal'], 'concerns one module only');
        $this->assertStringNotContainsString(MigratedPoFixture::artifactDirectory(), $problem['message'], 'no full path in the message for the GUI');
    }

    public function testABuildWithoutTheIdentifiersOfAModuleServesNothingOfIt(): void
    {
        $this->build();
        unlink(MigratedLanguageFilePaths::buildKeysFile($this->build_directory, 'mtr'));
        MigratedTranslations::reset();

        $this->assertSame([], $this->keys(), 'migrated, but without any identifier');
        $this->assertNull($this->text('greeting'));
        $this->assertCount(1, $this->warnings);
        $this->assertSame('Anders', $this->text('other', 'mtz'), 'another module is not affected');
    }

    public function testTheSameProblemOfAnotherLanguageOfTheModuleIsNotLoggedAgain(): void
    {
        $this->build();
        unlink(MigratedLanguageFilePaths::buildKeysFile($this->build_directory, 'mtr'));
        MigratedTranslations::reset();

        $this->assertSame([], $this->keys('mtr', 'de'));
        $this->assertSame([], $this->keys('mtr', 'en'));

        $this->assertCount(1, $this->warnings, 'one message per request');
    }

    public function testACatalogOfTheBuildThatCannotBeReadIsLoggedAndNothingOfItIsServed(): void
    {
        $this->build();
        // never read by this process (the build checks one catalog only), so it is read as it is now
        file_put_contents(MigratedLanguageFilePaths::buildMoFile($this->build_directory, 'mtz', 'de'), 'garbage');

        $this->assertSame(['other'], array_keys($this->keys('mtz') ?? []), 'its identifiers are known');
        $this->assertNull($this->text('other', 'mtz'));
        $this->assertNull($this->text('other', 'mtz'));

        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('cannot read', $this->warnings[0]);
        $this->assertSame('Hallo', $this->text('greeting'), 'another module is not affected');
    }

    public function testResetForgetsTheBuildSoANewOneIsServed(): void
    {
        $this->build();
        $this->assertSame('Hallo', $this->text('greeting'));

        MigratedPoFixture::writePo($this->root . '/mtr/mtr_de.po', MigratedPoFixture::catalog('mtr', ['greeting' => 'Guten Tag']));
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('mtr', 'mtr/'),
            MigratedPoFixture::directory('mtz', 'mtz/')
        );
        MigratedPoFixture::build($manager, $this->root);
        MigratedTranslations::reset();

        $this->assertSame('Guten Tag', $this->text('greeting'));
    }

    public function testAnOverlayIsServedOnceTheClientDataDirectoryBecomesKnown(): void
    {
        $this->build();
        $this->writeOverlay(['greeting' => 'Overlay']);

        $this->assertSame('Hallo', MigratedTranslations::text('mtr', 'de', 'greeting', null), 'Setup before the client data directory is known');
        $this->assertSame('Hallo', MigratedTranslations::text('mtr', 'de', 'greeting', null), 'still unknown on the next call');
        $this->assertSame('Overlay', $this->text('greeting'));
        $this->assertSame([], $this->warnings);
    }

    public function testTheOverlayIsCheckedOnlyOnceAfterTheClientDataDirectoryIsKnown(): void
    {
        $this->build();

        $this->assertSame('Hallo', $this->text('greeting'), 'no overlay yet');
        $this->writeOverlay(['greeting' => 'Late']);

        $this->assertSame('Hallo', $this->text('greeting'), 'not looked at again without invalidate()');
        MigratedTranslations::invalidate('mtr', 'de');
        $this->assertSame('Late', $this->text('greeting'));
    }

    public function testAModuleIsShippedIfItIsBuiltOrHasATemplateInTheComponents(): void
    {
        $this->assertFalse(MigratedTranslations::isShippedModule('mtr'), 'no build, no template');
        $this->assertTrue(MigratedTranslations::isShippedModule('tos'), 'without a build: template of the TermsOfService component');
        $this->build();
        MigratedTranslations::reset();

        $this->assertTrue(MigratedTranslations::isShippedModule('mtr'), 'built');
        $this->assertFalse(MigratedTranslations::isShippedModule('tos'), 'with a build only its recorded contributions count');
        $this->assertFalse(MigratedTranslations::isShippedModule('some_plugin_module'));
    }

    public function testWithABuildTheRecordedContributionsDecideEvenForAModuleWithoutAnyPo(): void
    {
        mkdir($this->root . '/mtq', 0775, true);
        MigratedPoFixture::writePo($this->root . '/mtr/mtr_de.po', MigratedPoFixture::catalog('mtr', ['greeting' => 'Hallo']));
        MigratedPoFixture::build(
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                MigratedPoFixture::directory('mtr', 'mtr/'),
                MigratedPoFixture::directory('mtq', 'mtq/')
            ),
            $this->root
        );

        $this->assertTrue(MigratedTranslations::isShippedModule('mtq'), 'contributed, no .po, no template');
        $this->assertFalse(MigratedTranslations::isShippedModule('tos'), 'a template the build did not record');
    }

    /**
     * @return array<string, array{0: bool}> whether the build has any .po
     */
    public static function listsNobodyCanRelyOn(): array
    {
        return ['a pointer without build' => [false], 'a pointer written before the list existed' => [true]];
    }

    #[DataProvider('listsNobodyCanRelyOn')]
    public function testWithoutABuildOrAListOfContributionsTheShippedTemplatesDecide(bool $with_build): void
    {
        mkdir($this->root . '/mtr', 0775, true);
        if ($with_build) {
            $this->build();
            $pointer_file = MigratedLanguageFilePaths::buildPointerFile(MigratedPoFixture::artifactDirectory());
            $data = json_decode((string) file_get_contents($pointer_file), true, 512, JSON_THROW_ON_ERROR);
            unset($data['shipped']);
            file_put_contents($pointer_file, json_encode($data));
        } else {
            MigratedPoFixture::build(
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), MigratedPoFixture::directory('mtr', 'mtr/')),
                $this->root
            );
        }
        MigratedTranslations::reset();

        $this->assertTrue(MigratedTranslations::isShippedModule('tos'), 'template of the TermsOfService component');
        $this->assertFalse(MigratedTranslations::isShippedModule('some_plugin_module'));
        $this->assertSame($with_build, MigratedTranslations::isShippedModule('mtr'), 'built, but in no template');
    }

    public function testShipsPoForNeedsATemplateADefaultNamedPoAndAValidLanguageKey(): void
    {
        $this->assertTrue(MigratedTranslations::shipsPoFor('tos', 'de'));
        $this->assertFalse(MigratedTranslations::shipsPoFor('tos', 'xx'), 'no such .po');
        $this->assertFalse(MigratedTranslations::shipsPoFor('tos', '../de'), 'no path');
        $this->assertFalse(MigratedTranslations::shipsPoFor('some_plugin_module', 'de'), 'no template');
    }

    public function testReportMissingBuildIsFatalWithoutABuildAndSilentWithOne(): void
    {
        $this->build();
        $this->assertFalse(MigratedTranslations::reportMissingBuild());
        $this->assertSame([], $this->warnings);

        MigratedPoFixture::resetRuntime();
        $this->assertTrue(MigratedTranslations::reportMissingBuild());
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('There is no build', implode("\n", $this->warnings));
    }
}
