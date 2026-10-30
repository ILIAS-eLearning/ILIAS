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
    use RestoresProcessState;

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

    /** @return array<string, array{0: string|int, 1: string, 2: string}> topic, expected eid parameter, expected list text */
    public static function missingTopicProvider(): array
    {
        return [
            'HTML' => ['<b>x</b>', '%3Cb%3Ex%3C%2Fb%3E', '&lt;b&gt;x&lt;/b&gt;'],
            'quote, ampersand, space and hash' => ['a"b&c d#e', 'a%22b%26c%20d%23e', 'a&quot;b&amp;c d#e'],
            'a numeric topic arrives as int' => [123, '123', '123'],
        ];
    }

    /** The list text is HTML-escaped, the "eid" parameter URL-encoded and the link target safe inside the href attribute. */
    #[DataProvider('missingTopicProvider')]
    public function testBuildMissingEntriesEscapesTheTopicForTextParameterAndLink(string|int $topic, string $eid, string $text): void
    {
        $parameters = [];
        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('setParameter')->willReturnCallback(
            static function (object $gui, string $name, mixed $value) use (&$parameters): void {
                $parameters[] = [$name, $value];
            }
        );
        $ctrl->method('getLinkTarget')->willReturn('ilias.php?a=1&b="2"');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['ilCtrl'] = $ctrl;
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        $html = (new ReflectionMethod(ilObjLanguageExtGUI::class, 'buildMissingEntries'))->invoke($gui, [$topic]);

        $this->assertSame([['eid', $eid], ['eid', '']], $parameters);
        $this->assertStringContainsString('<li>' . $text . ' <a href="ilias.php&#x3F;a&#x3D;1&amp;b&#x3D;&quot;2&quot;">', $html);
    }
}
