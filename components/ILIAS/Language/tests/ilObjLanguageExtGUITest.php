<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
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

class ilObjLanguageExtGUITest extends TestCase
{
    use ilObjLanguageExtGUITestFixture;

    /** A data-changing command missing here would be triggerable via GET without the CSRF token. */
    public function testGetUnsafeGetCommandsListsEveryDataChangingCommand(): void
    {
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);

        $this->assertSame(
            ['save', 'upload', 'maintainExecute', 'saveSettings', 'saveNewEntry'],
            $gui->getUnsafeGetCommands()
        );
    }

    /** No POST command is exempt from the CSRF token. */
    public function testGetSafePostCommandsIsEmpty(): void
    {
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);

        $this->assertSame([], $gui->getSafePostCommands());
    }

    /** @return array<string, array{0: string, 1: \Closure}> */
    public static function importedMessageProvider(): array
    {
        return [
            'HTML-significant characters are escaped' => [
                '<script>x</script>.lang',
                static function (self $test, string $message): void {
                    $test->assertStringNotContainsString('<script>', $message);
                    $test->assertStringContainsString('&lt;script&gt;', $message);
                    $test->assertStringContainsString('&lt;/script&gt;', $message);
                },
            ],
            'an ordinary file name is left unchanged' => [
                'ilias_de.lang',
                static function (self $test, string $message): void {
                    $test->assertStringContainsString('ilias_de.lang', $message);
                },
            ],
        ];
    }

    /** Calls the real private importedMessage() via reflection: an uploaded file name must come out HTML-escaped. */
    #[DataProvider('importedMessageProvider')]
    public function testImportedMessageEscapesHtmlSignificantCharactersAndKeepsTheLngText(
        string $file_name,
        \Closure $assertOnMessage
    ): void {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(
            static fn(string $key): string => $key === 'language_file_imported' ? 'Imported %s.' : $key
        );

        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        $method = new ReflectionMethod(ilObjLanguageExtGUI::class, 'importedMessage');
        $message = $method->invoke($gui, $file_name);

        $this->assertStringStartsWith('Imported ', $message);
        $assertOnMessage($this, $message);
    }

    /** @param array<string, string> $comments */
    private function callRemarkOf(array $comments, string $name): string
    {
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'lng', $this->createStub(ilLanguage::class));

        return (new ReflectionMethod(ilObjLanguageExtGUI::class, 'remarkOf'))->invoke($gui, $comments, $name);
    }

    public function testRemarkOfReturnsTheCommentOfTheGivenKeyItselfWhenPresent(): void
    {
        $this->assertSame(
            'Bitte prüfen',
            $this->callRemarkOf(['pilot#:#greeting' => 'Bitte prüfen'], 'pilot#:#greeting')
        );
    }

    /** A plural form ("identifier [form]") without a comment of its own falls back to the comment of its identifier. */
    public function testRemarkOfFallsBackToTheIdentifiersCommentForAPluralFormKeyWithoutItsOwn(): void
    {
        $this->assertSame(
            'Bitte prüfen',
            $this->callRemarkOf(['pilot#:#item' => 'Bitte prüfen'], 'pilot#:#item [1]')
        );
    }

    /** A plural form's own comment wins over the identifier's. */
    public function testRemarkOfPrefersThePluralFormsOwnCommentOverTheIdentifiersWhenBothArePresent(): void
    {
        $this->assertSame(
            'Form-spezifisch',
            $this->callRemarkOf(
                ['pilot#:#item' => 'Identifier-Bemerkung', 'pilot#:#item [1]' => 'Form-spezifisch'],
                'pilot#:#item [1]'
            )
        );
    }

    public function testRemarkOfIsEmptyWithoutAnyMatchingComment(): void
    {
        $this->assertSame('', $this->callRemarkOf([], 'pilot#:#unknown'));
        $this->assertSame('', $this->callRemarkOf(['pilot#:#other' => 'X'], 'pilot#:#greeting'));
    }

    /** An ordinary identifier without a comment must not fall back to anything. */
    public function testRemarkOfIsEmptyForAnOrdinaryIdentifierWithoutAComment(): void
    {
        $this->assertSame('', $this->callRemarkOf(['pilot#:#item' => 'Bemerkung'], 'pilot#:#greeting'));
    }
}
