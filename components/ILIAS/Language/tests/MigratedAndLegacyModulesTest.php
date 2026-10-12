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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * ilLanguage with migrated modules (native gettext) mixed with legacy modules (lng_modules): the module loaded last wins
 * for an identifier. Every test in its own process (CLIENT_DATA_DIR and ILIAS_ABSOLUTE_PATH cannot be redefined, see NtxtTest).
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class MigratedAndLegacyModulesTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    private string $relative_path = '';

    /** @var list<string> the warnings the component logger received */
    private array $warnings = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }
        MigratedPoFixture::resetRuntime();
        MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        $this->fixture_directory = __DIR__ . '/tmp-mixed-modules-' . bin2hex(random_bytes(4));
        $relative_path = $this->relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        // mig_a: shared/only_a in de, shared/other in en (so "other" is listed but not translated in de); mig_b: shared and other in de
        MigratedPoFixture::writeShippedPo($relative_path, 'mig_a', 'de', MigratedPoFixture::catalog('mig_a', ['shared' => 'Mig A', 'only_a' => 'A']));
        MigratedPoFixture::writeShippedPo($relative_path, 'mig_a', 'en', MigratedPoFixture::catalog('mig_a', ['shared' => 'Mig A en', 'other' => 'Other en']));
        MigratedPoFixture::writeShippedPo($relative_path, 'mig_b', 'de', MigratedPoFixture::catalog('mig_b', ['shared' => 'Mig B', 'other' => 'Mig B other']));

        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                MigratedPoFixture::directory('mig_a', $relative_path),
                MigratedPoFixture::directory('mig_b', $relative_path)
            )
        );
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
        $logger = $this->createStub(ilLogger::class);
        $logger->method('warning')->willReturnCallback(function (string $message): void {
            $this->warnings[] = $message;
        });
        $factory = $this->createStub(ilLoggerFactory::class);
        $factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $factory);
    }

    protected function tearDown(): void
    {
        if ($this->fixture_directory !== null) {
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . basename($this->fixture_directory));
        }
        if (defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /** @param array<string, string> $legacy_values what lng_modules holds for the module "legacy" in the language */
    private function language(array $legacy_values = [], string $lang_key = 'de'): ilLanguage
    {
        global $DIC;
        MigratedPoFixture::build($DIC[LanguageFileDirectoryManager::class], (string) ILIAS_ABSOLUTE_PATH);
        $language = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $reflected = new ReflectionObject($language);
        foreach (['lang_key', 'lang_user', 'lang_default'] as $property) {
            $reflected->getProperty($property)->setValue($language, $lang_key);
        }
        $reflected->getProperty('log')->setValue($language, $this->createStub(ilLogger::class));
        $reflected->getProperty('cached_modules')->setValue($language, ['legacy' => $legacy_values]);

        return $language;
    }

    /** @return iterable<string, array{list<string>, string}> */
    public static function loadOrders(): iterable
    {
        yield 'migrated, then legacy' => [['mig_a', 'legacy'], 'Legacy'];
        yield 'legacy, then migrated' => [['legacy', 'mig_a'], 'Mig A'];
        yield 'migrated, legacy, another migrated' => [['mig_a', 'legacy', 'mig_b'], 'Mig B'];
        yield 'legacy, migrated, migrated' => [['legacy', 'mig_a', 'mig_b'], 'Mig B'];
        yield 'migrated, migrated, legacy' => [['mig_b', 'mig_a', 'legacy'], 'Legacy'];
    }

    /** @param list<string> $order */
    #[DataProvider('loadOrders')]
    public function testTheModuleLoadedLastWinsAlsoAcrossMigratedAndLegacyModules(array $order, string $expected): void
    {
        $language = $this->language(['shared' => 'Legacy', 'only_legacy' => 'L']);

        foreach ($order as $module) {
            $language->loadLanguageModule($module);
        }

        $this->assertSame($expected, $language->txt('shared'));
        $this->assertTrue($language->exists('shared'));
        $this->assertSame('L', $language->txt('only_legacy'), 'what only the legacy module has stays');
        $this->assertSame(in_array('mig_a', $order, true) ? 'A' : '-only_a-', $language->txt('only_a'), 'what only a migrated module has stays');
        $this->assertSame([], $this->warnings);
    }

    /** Unlike the .lang/database path (array_merge() kept the earlier value) an identifier listed but untranslated in the last module gives "-key-", without a warning. */
    public function testAnIdentifierTheMigratedModuleLoadedLastDoesNotTranslateIsMissingEvenIfALegacyModuleBeforeHasIt(): void
    {
        $language = $this->language(['other' => 'Legacy other']);

        $language->loadLanguageModule('legacy');
        $language->loadLanguageModule('mig_a');

        $this->assertSame('-other-', $language->txt('other'), 'listed by mig_a (it is translated in en), not translated in de');
        $this->assertFalse($language->exists('other'));
        $this->assertSame([], $this->warnings, 'an untranslated key is no problem');
    }

    public function testAnIdentifierTheMigratedModuleLoadedLastDoesNotTranslateIsMissingEvenIfAMigratedModuleBeforeHasIt(): void
    {
        $language = $this->language();

        $language->loadLanguageModule('mig_b');
        $this->assertSame('Mig B other', $language->txt('other'), 'precondition');
        $language->loadLanguageModule('mig_a');

        $this->assertSame('-other-', $language->txt('other'));
        $this->assertFalse($language->exists('other'));
        $this->assertSame([], $this->warnings);
    }

    /** A module shipping a language the build lacks (no "setup build" since) has no identifiers and is reported once per module, language and request. */
    public function testWhetherALanguageIsShippedWithoutBuildIsDecidedOncePerRequest(): void
    {
        $this->language();
        $migrated_keys_of = new ReflectionMethod(ilLanguage::class, 'migratedKeysOf');
        MigratedPoFixture::writeShippedPo($this->relative_path, 'mig_a', 'it', MigratedPoFixture::catalog('mig_a', ['shared' => 'Mig A it']));

        $this->assertSame([], $migrated_keys_of->invoke(null, 'mig_a', 'it'), 'shipped, not built: migrated without any identifier');
        $this->assertNull($migrated_keys_of->invoke(null, 'mig_a', 'fr'), 'not shipped: not migrated');
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('ships a language file for "it" that is not built', $this->warnings[0]);

        MigratedPoFixture::writeShippedPo($this->relative_path, 'mig_a', 'fr', MigratedPoFixture::catalog('mig_a', ['shared' => 'Mig A fr']));
        unlink(ILIAS_ABSOLUTE_PATH . '/' . $this->relative_path . 'mig_a_it.po');

        $this->assertNull($migrated_keys_of->invoke(null, 'mig_a', 'fr'), 'decided before the file appeared');
        $this->assertSame([], $migrated_keys_of->invoke(null, 'mig_a', 'it'), 'decided before the file vanished');
        $this->assertCount(1, $this->warnings, 'reported once');
    }

    public function testAnIdentifierNobodyTranslatesIsMissingWithoutAWarning(): void
    {
        $language = $this->language();

        $language->loadLanguageModule('mig_a');

        $this->assertSame('-other-', $language->txt('other'));
        $this->assertFalse($language->exists('other'));
        $this->assertSame('-unknown-', $language->txt('unknown'));
        $this->assertSame([], $this->warnings);
    }

    public function testTxtlngAndLookupEntryReadAnyLanguageIndependentOfTheInstance(): void
    {
        $language = $this->language();
        $language->loadLanguageModule('mig_a');

        $this->assertSame('Mig A en', $language->txtlng('mig_a', 'shared', 'en'));
        $this->assertSame('Other en', ilLanguage::_lookupEntry('en', 'mig_a', 'other'));
        $this->assertSame('Mig A', $language->txtlng('mig_a', 'shared', 'de'));
        $this->assertSame('Mig A', ilLanguage::_lookupEntry('de', 'mig_a', 'shared'));
        $this->assertSame('-other-', ilLanguage::_lookupEntry('de', 'mig_a', 'other'), 'listed, not translated in de');
        $this->assertSame('-nokey-', ilLanguage::_lookupEntry('de', 'mig_a', 'nokey'));
        $this->assertSame('Mig B', ilLanguage::_lookupEntry('de', 'mig_b', 'shared'), 'a module that was never loaded in this instance');
        $this->assertSame([], $this->warnings);
    }
}
