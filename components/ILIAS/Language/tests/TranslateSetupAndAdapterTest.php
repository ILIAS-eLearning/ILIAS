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

use ILIAS\Language\Language;
use ILIAS\Language\LanguageIdentifier;
use ILIAS\Language\LanguageLegacyInitialisationAdapter;
use PHPUnit\Framework\Attributes\DataProvider;

enum TranslateSetupTestIdentifier: string implements LanguageIdentifier
{
    case KNOWN = 'known';
    case UNKNOWN = 'unknown';
    case EMPTY = '';

    public function module(): string
    {
        return 'setup_module';
    }
}

class TranslateSetupAndAdapterTest extends ilLanguageBaseTestCase
{
    /** @return iterable<string, array{string|LanguageIdentifier, ?int, ?string, string}> */
    public static function setupLookups(): iterable
    {
        yield 'string' => ['known', null, null, 'Bekannt'];
        yield 'string, n and language have no effect' => ['known', 3, 'fr', 'Bekannt'];
        yield 'enum' => [TranslateSetupTestIdentifier::KNOWN, null, null, 'Bekannt'];
        yield 'enum, n and language have no effect' => [TranslateSetupTestIdentifier::KNOWN, 0, 'fr', 'Bekannt'];
        yield 'unknown string' => ['unknown', null, null, '-unknown-'];
        yield 'unknown enum' => [TranslateSetupTestIdentifier::UNKNOWN, 1, null, '-unknown-'];
        yield 'empty string' => ['', null, null, ''];
        yield 'empty enum' => [TranslateSetupTestIdentifier::EMPTY, null, null, ''];
    }

    #[DataProvider('setupLookups')]
    public function testSetupLanguageTranslateIsTxt(string|LanguageIdentifier $key, ?int $n, ?string $lang, string $expected): void
    {
        $GLOBALS['log'] = $this->createStub(ilLogger::class);
        $language = new ilSetupLanguage('de');
        $language->text = ['known' => 'Bekannt'];

        $this->assertSame($expected, $language->translate($key, $n, $lang));
        if (is_string($key)) {
            $this->assertSame($language->txt($key), $language->translate($key));
        }
    }

    /** $DIC->language() only returns an ilLanguage, so another Language implementation is handed in by overriding the lookup. */
    private static function adapterOf(Language $language): LanguageLegacyInitialisationAdapter
    {
        return new class ($language) extends LanguageLegacyInitialisationAdapter {
            public function __construct(private readonly Language $language)
            {
            }

            protected function getLegacyLanguageInstance(): Language
            {
                return $this->language;
            }
        };
    }

    public function testAdapterPassesEverythingOnToAnIlLanguage(): void
    {
        $key = TranslateSetupTestIdentifier::KNOWN;
        $inner = $this->createMock(ilLanguage::class);
        $inner->expects($this->exactly(2))->method('translate')
            ->willReturnCallback(static fn(string|LanguageIdentifier $k, ?int $n, ?string $l): string => 'got ' . (is_string($k) ? $k : $k->value) . ' ' . var_export($n, true) . ' ' . var_export($l, true));
        $inner->expects($this->never())->method('loadLanguageModule');
        $this->setGlobalVariable('lng', $inner);

        $adapter = new LanguageLegacyInitialisationAdapter();

        $this->assertSame("got known 2 'fr'", $adapter->translate($key, 2, 'fr'));
        $this->assertSame('got plain NULL NULL', $adapter->translate('plain'));
    }

    public function testAdapterWithoutIlLanguageLoadsTheModuleOfAnEnumAndUsesTxt(): void
    {
        $inner = $this->createMock(Language::class);
        $calls = [];
        $inner->expects($this->once())->method('loadLanguageModule')
            ->willReturnCallback(function (string $module) use (&$calls): void {
                $calls[] = 'load ' . $module;
            });
        $inner->expects($this->once())->method('txt')->with('known')
            ->willReturnCallback(function () use (&$calls): string {
                $calls[] = 'txt';

                return 'Bekannt';
            });

        $this->assertSame('Bekannt', self::adapterOf($inner)->translate(TranslateSetupTestIdentifier::KNOWN, 4, 'fr'));
        $this->assertSame(['load setup_module', 'txt'], $calls, 'the module is loaded before the lookup');
    }

    public function testAdapterWithoutIlLanguageLooksUpAStringWithoutLoadingAModule(): void
    {
        $inner = $this->createMock(Language::class);
        $inner->expects($this->never())->method('loadLanguageModule');
        $inner->expects($this->once())->method('txt')->with('known')->willReturn('Bekannt');

        $this->assertSame('Bekannt', self::adapterOf($inner)->translate('known'));
    }
}
