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

enum IdentifierTxtSetupTestIdentifier: string implements LanguageIdentifier
{
    case KNOWN = 'known';
    case UNKNOWN = 'unknown';
    case EMPTY = '';

    public function module(): string
    {
        return 'setup_module';
    }
}

class IdentifierTxtSetupAndAdapterTest extends ilLanguageBaseTestCase
{
    /** @return iterable<string, array{string|LanguageIdentifier, string}> */
    public static function setupLookups(): iterable
    {
        yield 'string' => ['known', 'Bekannt'];
        yield 'enum' => [IdentifierTxtSetupTestIdentifier::KNOWN, 'Bekannt'];
        yield 'unknown string' => ['unknown', '-unknown-'];
        yield 'unknown enum' => [IdentifierTxtSetupTestIdentifier::UNKNOWN, '-unknown-'];
        yield 'empty string' => ['', ''];
        yield 'empty enum' => [IdentifierTxtSetupTestIdentifier::EMPTY, ''];
    }

    #[DataProvider('setupLookups')]
    public function testSetupLanguageTxtServesTheValueOfAnEnum(string|LanguageIdentifier $key, string $expected): void
    {
        $GLOBALS['log'] = $this->createStub(ilLogger::class);
        $language = new ilSetupLanguage('de');
        $language->text = ['known' => 'Bekannt'];

        $this->assertSame($expected, $language->txt($key));
    }

    public function testSetupLanguageIgnoresNForAnEnum(): void
    {
        $GLOBALS['log'] = $this->createStub(ilLogger::class);
        $language = new ilSetupLanguage('de');
        $language->text = ['known' => 'Bekannt'];

        foreach ([0, 1, 5] as $n) {
            $this->assertSame('Bekannt', $language->txt(IdentifierTxtSetupTestIdentifier::KNOWN, $n));
            $this->assertSame('-unknown-', $language->txt(IdentifierTxtSetupTestIdentifier::UNKNOWN, $n));
        }
        $this->assertSame('', $language->txt(IdentifierTxtSetupTestIdentifier::EMPTY, 2));
    }

    public function testSetupLanguageServesAStringWithNFromThePluralPathOfIlLanguage(): void
    {
        $GLOBALS['log'] = $this->createStub(ilLogger::class);
        $language = new ilSetupLanguage('de');
        $language->text = ['known' => 'Bekannt'];

        $this->assertSame('Bekannt', $language->txt('known', 3));
        $this->assertSame('-unknown-', $language->txt('unknown', 3));
    }

    public function testAdapterPassesEnumAndNOnUnchanged(): void
    {
        $key = IdentifierTxtSetupTestIdentifier::KNOWN;
        $inner = $this->createMock(Language::class);
        $inner->expects($this->once())->method('txt')->with($this->identicalTo($key), 3)->willReturn('Bekannt');
        $inner->expects($this->never())->method('loadLanguageModule');
        $adapter = new class ($inner) extends LanguageLegacyInitialisationAdapter {
            public function __construct(private readonly Language $language)
            {
            }

            protected function getLegacyLanguageInstance(): Language
            {
                return $this->language;
            }
        };

        $this->assertSame('Bekannt', $adapter->txt($key, 3));
    }
}
