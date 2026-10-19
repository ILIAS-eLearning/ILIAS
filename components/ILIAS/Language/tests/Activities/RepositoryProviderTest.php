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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Administration\Setting;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Only what runs without the legacy container: the installed languages, the settings behind an injected Setting and
 * the lookup half of updateModuleCache(). Writes through \ilObjLanguage are covered by the Activity tests only
 * as calls on a mocked provider.
 */
class RepositoryProviderTest extends TestCase
{
    public function testGetInstalledLanguagesDelegatesToTheRepository(): void
    {
        $installed = $this->createMock(InstalledLanguageRepository::class);
        $installed->expects($this->once())->method('getInstalledLanguages')->willReturn(['de', 'en']);

        $provider = new RepositoryProvider($installed, $this->createMock(\ilDBInterface::class));

        $this->assertSame(['de', 'en'], $provider->getInstalledLanguages());
    }

    /** @return array<string, array{0: ?array<string, mixed>}> */
    public static function unusableCacheRowProvider(): array
    {
        return [
            'no lng_modules row' => [null],
            'lang_array is NULL' => [['lang_array' => null]],
            'lang_array is not a string' => [['lang_array' => 42]],
            'lang_array decodes to a scalar' => [['lang_array' => serialize('not-an-array')]],
            'lang_array is broken serialize' => [['lang_array' => 'a:1:{broken']],
            'lang_array is an object (allowed_classes false)' => [['lang_array' => serialize(new \ArrayObject(['x' => 'y']))]],
        ];
    }

    /**
     * Each of these ends before the write to \ilObjLanguage::replaceLangModule(), which needs the legacy container:
     * reaching it would fail with an Error instead of returning.
     *
     * @param ?array<string, mixed> $row
     */
    #[DataProvider('unusableCacheRowProvider')]
    public function testUpdateModuleCacheLeavesAMissingOrBrokenCacheAlone(?array $row): void
    {
        $statement = $this->createMock(\ilDBStatement::class);
        $db = $this->createMock(\ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value, string $type): string => "'" . $value . "'");
        $db->method('query')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn($row);

        // unserialize() reports broken input as a notice, which is not what this test is about
        set_error_handler(static fn(): bool => true, E_NOTICE | E_WARNING);
        try {
            (new RepositoryProvider($this->createMock(InstalledLanguageRepository::class), $db))
                ->updateModuleCache('de', 'common', 'topic', 'value');
        } finally {
            restore_error_handler();
        }

        $this->addToAssertionCount(1);
    }

    public function testUpdateModuleCacheQuotesLanguageKeyAndModuleIntoTheQuery(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->exactly(2))->method('quote')->willReturnCallback(
            static fn(mixed $value, string $type): string => "<$type:$value>"
        );
        $db->expects($this->once())->method('query')->with(
            'SELECT lang_array FROM lng_modules WHERE lang_key = <text:de> AND module = <text:co\'mmon>'
        );
        $db->method('fetchAssoc')->willReturn(null);

        (new RepositoryProvider($this->createMock(InstalledLanguageRepository::class), $db))
            ->updateModuleCache('de', "co'mmon", 'topic', 'value');
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function storedFlagProvider(): array
    {
        return ['"1"' => ['1', true], '"0"' => ['0', false], 'empty string' => ['', false]];
    }

    /** Falsy cast of the stored string, like ilObjLanguageAccess::_checkTranslate(), under the per-language key. */
    #[DataProvider('storedFlagProvider')]
    public function testIsPageTranslationEnabledCastsTheStoredValueUnderThePerLanguageKey(string $stored, bool $expected): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())->method('get')->with('lang_translate_fr', '0')->willReturn($stored);

        $this->assertSame($expected, $this->providerWith($settings)->isPageTranslationEnabled('fr'));
    }

    public function testSettingsAreWrittenAsOneAndZeroUnderTheirKeys(): void
    {
        $written = [];
        $settings = $this->createMock(Setting::class);
        $settings->method('set')->willReturnCallback(
            static function (string $key, string $value) use (&$written): void {
                $written[] = [$key, $value];
            }
        );

        $provider = $this->providerWith($settings);
        $provider->setLanguageDetectionEnabled(true);
        $provider->setLanguageDetectionEnabled(false);
        $provider->setPageTranslationEnabled('de', true);
        $provider->setPageTranslationEnabled('de', false);

        $this->assertSame(
            [['lang_detection', '1'], ['lang_detection', '0'], ['lang_translate_de', '1'], ['lang_translate_de', '0']],
            $written
        );
    }

    private function providerWith(Setting $settings): RepositoryProvider
    {
        return new RepositoryProvider(
            $this->createMock(InstalledLanguageRepository::class),
            $this->createMock(\ilDBInterface::class),
            $settings
        );
    }
}
