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

/** ilObjLanguageExt::withFuzzyMarkers(): the "not translated yet" marker falls back to the default language. */
class FuzzyMarkerDefaultLanguageTest extends ilLanguageBaseTestCase
{
    /** @var list<string> */
    private array $queries = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
    }

    /** @return array<string, string> */
    private function markersWith(string $user_text, ?string $default_text): array
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturn($user_text);
        $lng->method('getDefaultLanguage')->willReturn('en');
        $this->setGlobalVariable('lng', $lng);

        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value): string => "'" . (string) $value . "'");
        $db->method('query')->willReturnCallback(function (string $sql) use ($statement) {
            $this->queries[] = $sql;

            return $statement;
        });
        $db->method('fetchAssoc')->willReturn($default_text === null ? null : ['value' => $default_text]);
        $this->setGlobalVariable('ilDB', $db);

        $method = new ReflectionMethod(ilObjLanguageExt::class, 'withFuzzyMarkers');

        return $method->invoke(null, ['lng.#:#.a' => 'note'], ['fuzzy' => ['lng.#:#.a', 'lng.#:#.b']]);
    }

    public function testAMarkerMissingInTheUserLanguageComesFromTheDefaultLanguage(): void
    {
        // "lng" is shipped: the default language comes from the build (none here), never from lng_data
        $this->expectOutputRegex('/There is no build of the language files/');
        $comments = $this->markersWith('-lng_not_translated-', 'Not translated en');

        $this->assertSame(['lng.#:#.a' => 'note - -lng_not_translated-', 'lng.#:#.b' => '-lng_not_translated-'], $comments);
        $this->assertSame([], $this->queries);
    }

    public function testAMarkerOfTheUserLanguageIsUsedWithoutFurtherQuery(): void
    {
        $comments = $this->markersWith('Nicht uebersetzt', 'Not translated en');

        $this->assertSame(['lng.#:#.a' => 'note - Nicht uebersetzt', 'lng.#:#.b' => 'Nicht uebersetzt'], $comments);
        $this->assertSame([], $this->queries);
    }

    public function testAMarkerMissingEverywhereStaysTheMissingKeyMarker(): void
    {
        $this->expectOutputRegex('/There is no build of the language files/');
        $comments = $this->markersWith('-lng_not_translated-', null);

        $this->assertSame('-lng_not_translated-', $comments['lng.#:#.b']);
        $this->assertSame('note - -lng_not_translated-', $comments['lng.#:#.a']);
    }
}
