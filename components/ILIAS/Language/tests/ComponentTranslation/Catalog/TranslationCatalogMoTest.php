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

namespace ILIAS\Language\Tests\ComponentTranslation\Catalog;

use Gettext\Translations;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * TranslationCatalog's `.mo` side: toMoString() (what the build and the overlay compile, see
 * ShippedTranslations::compileCatalog()), read back with the test fixture's reader - the runtime
 * reads a `.mo` through native gettext, the adapter has no reader of its own any more.
 */
class TranslationCatalogMoTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/ilias_translation_catalog_mo_' . bin2hex(random_bytes(4));
        mkdir($this->directory, 0775);
    }

    protected function tearDown(): void
    {
        \MigratedPoFixture::removeDirectory($this->directory);
    }

    private static function entry(?string $context, string $id, string $translation): TranslationEntry
    {
        $entry = new TranslationEntry($context, $id);
        $entry->translate($translation);

        return $entry;
    }

    private static function sampleCatalog(): TranslationCatalog
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Language', 'de');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $catalog->add(self::entry('mod', 'zeta', 'Z'));
        $catalog->add(self::entry('mod', 'alpha', "Mehr\nzeilig"));
        $catalog->add(self::entry(null, 'no_context', 'ohne Kontext'));
        $catalog->add(self::entry('mod', 'zero', '0'));
        $fuzzy = self::entry('mod', 'fuzzy', 'Hello');
        $fuzzy->addFlag('fuzzy');
        $catalog->add($fuzzy);
        $catalog->add(self::entry('mod', 'untranslated', ''));

        return $catalog;
    }

    /**
     * @return array<string, string>
     */
    private function read(string $mo): array
    {
        return \MigratedPoFixture::readMoTranslations($mo);
    }

    /**
     * The originals of a little endian `.mo`, in file order (independent of the code under test).
     *
     * @return list<string>
     */
    private static function originals(string $mo): array
    {
        ['count' => $count, 'offset' => $table] = unpack('Vcount/Voffset', $mo, 8);
        $originals = [];
        for ($i = 0; $i < $count; $i++) {
            ['length' => $length, 'offset' => $offset] = unpack('Vlength/Voffset', $mo, $table + $i * 8);
            $originals[] = substr($mo, $offset, $length);
        }

        return $originals;
    }

    /**
     * Fuzzy messages are compiled (unlike msgfmt's default - "fuzzy" marks an unreviewed legacy
     * value, leaving it out would show "-identifier-"), untranslated ones are left out, the context
     * is dropped and "0" is served as "0".
     */
    public function testReadMoTranslationsServesEveryTranslatedMessageByIdentifier(): void
    {
        $this->assertSame(
            [
                'alpha' => "Mehr\nzeilig",
                'fuzzy' => 'Hello',
                'zero' => '0',
                'zeta' => 'Z',
                'no_context' => 'ohne Kontext',
            ],
            $this->read(self::sampleCatalog()->toMoString())
        );
    }

    /**
     * msgfmt-compatible layout: the header message ("" => headers) comes first, the originals are
     * sorted byte-wise (C gettext looks them up by binary search), untranslated messages are absent.
     */
    public function testTheOriginalsAreTheHeaderAndTheTranslatedMessagesSortedByteWise(): void
    {
        $catalog = new TranslationCatalog();
        foreach (['b', 'B', 'a', '10', '9'] as $id) {
            $catalog->add(self::entry(null, $id, 'x'));
        }
        $catalog->add(self::entry('mod', 'a', 'x'));
        $catalog->add(self::entry('mod', 'empty', ''));

        $this->assertSame(['', '10', '9', 'B', 'a', 'b', "mod\x04a"], self::originals($catalog->toMoString()));
    }

    /**
     * The "0" workaround stores "0" plus its NUL; C gettext and readMoTranslations() end the string
     * there. The next message must not be affected.
     */
    public function testTheValueZeroDoesNotAffectTheFollowingMessage(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(self::entry('mod', 'a', '0'));
        $catalog->add(self::entry('mod', 'b', 'bee'));

        $this->assertSame(['a' => '0', 'b' => 'bee'], $this->read($catalog->toMoString()));
    }

    public function testNumericIdentifiersAreServed(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(self::entry('mod', '0', 'null'));
        $catalog->add(self::entry('mod', '10', 'zehn'));

        $translations = $this->read($catalog->toMoString());

        $this->assertSame('null', $translations['0'] ?? null);
        $this->assertSame('zehn', $translations['10'] ?? null);
        $this->assertCount(2, $translations);
    }

    /**
     * A plural message is served with its default form (msgstr[1] with at least two forms, see PluralForms); every form
     * is available through readMoMessages().
     */
    public function testReadMoTranslationsServesTheDefaultFormOfAPluralMessage(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgid \"\"\nmsgstr \"\"\n\"Plural-Forms: nplurals=2; plural=(n != 1);\\n\"\n\n"
            . "msgctxt \"mod\"\nmsgid \"item\"\nmsgid_plural \"items\"\nmsgstr[0] \"Eintrag\"\nmsgstr[1] \"Einträge\"\n"
        );

        $mo = $catalog->toMoString();

        $this->assertSame(['item' => 'Einträge'], $this->read($mo));
        $this->assertSame(['item' => ['Eintrag', 'Einträge']], \MigratedPoFixture::readMoPluralForms($mo));
        $this->assertSame(['', "mod\x04item\x00items"], self::originals($mo));
        $this->assertStringContainsString("Eintrag\x00Einträge\x00", $mo);
    }

    /**
     * A language with a single plural form (nplurals=1, e.g. Japanese) writes only msgstr[0] to the
     * `.mo` (prepareMoPluralForms() pads/cuts to "as many forms as the header declares") - with no
     * form left after it, the message is compiled as an ordinary singular one (see toMoString()'s
     * docblock), so it is served as a plain translation, not through getPluralTranslations().
     */
    public function testAPluralMessageOfALanguageWithASingleFormIsCompiledAsSingular(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgid \"\"\nmsgstr \"\"\n\"Plural-Forms: nplurals=1; plural=0;\\n\"\n\n"
            . "msgctxt \"mod\"\nmsgid \"item\"\nmsgid_plural \"items\"\nmsgstr[0] \"Eintrag\"\nmsgstr[1] \"Einträge\"\n"
        );

        $mo = $catalog->toMoString();

        $this->assertSame(['item' => 'Eintrag'], $this->read($mo));
        $this->assertSame([], \MigratedPoFixture::readMoPluralForms($mo));
    }

    /**
     * An empty form between two non-empty ones (e.g. Russian's msgstr[1] left blank while msgstr[0]
     * and msgstr[2] are set) must roundtrip as an empty string at that index, not shift the later
     * forms or get treated as "message ends here".
     */
    public function testAnEmptyIntermediateFormOfAPluralMessageRoundTrips(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgid \"\"\nmsgstr \"\"\n"
            . "\"Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4"
            . " && (n%100<10 || n%100>=20) ? 1 : 2);\\n\"\n\n"
            . "msgctxt \"mod\"\nmsgid \"item\"\nmsgid_plural \"items\"\n"
            . "msgstr[0] \"eins\"\nmsgstr[1] \"\"\nmsgstr[2] \"viele\"\n"
        );

        $mo = $catalog->toMoString();

        $this->assertSame(
            ['item' => ['eins', '', 'viele']],
            \MigratedPoFixture::readMoPluralForms($mo)
        );
        // the default form is the last one (index 2, count 3) - it is not empty here, so no fallback
        // search is even needed (see PluralFormsTest for that)
        $this->assertSame(['item' => 'viele'], $this->read($mo));
    }

    /**
     * The LAST form (not msgstr[1]) is the default - and when it is itself empty, the search falls
     * back downwards to the closest non-empty one before it (here: msgstr[1], skipping the always-
     * empty msgstr[2] slot of this fixture's Czech-shaped rule).
     */
    public function testReadMoTranslationsFallsBackDownwardsWhenTheLastFormIsEmpty(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgid \"\"\nmsgstr \"\"\n"
            . "\"Plural-Forms: nplurals=3; plural=((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2);\\n\"\n\n"
            . "msgctxt \"mod\"\nmsgid \"item\"\nmsgid_plural \"items\"\n"
            . "msgstr[0] \"jeden\"\nmsgstr[1] \"dva\"\nmsgstr[2] \"\"\n"
        );

        $mo = $catalog->toMoString();

        $this->assertSame(['item' => 'dva'], $this->read($mo));
    }

    /**
     * An invalid "Plural-Forms" header (here: nplurals=999, far beyond PluralForms::MAX_COUNT of 10 -
     * the gettext/gettext library itself does not enforce that bound) must not make toMoString()
     * compile 999 forms: prepareMoPluralForms() falls back to the same Germanic rule PluralForms
     * itself falls back to, both for how many forms are actually written (surplus ones dropped) and
     * for the "Plural-Forms" header the `.mo` ends up with.
     */
    public function testAnInvalidNpluralsHeaderIsCappedToTheGermanicFallbackWhenCompiling(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgid \"\"\nmsgstr \"\"\n\"Plural-Forms: nplurals=999; plural=n%999;\\n\"\n\n"
            . "msgid \"item\"\nmsgid_plural \"items\"\nmsgstr[0] \"A\"\nmsgstr[1] \"B\"\nmsgstr[2] \"C\"\n"
        );

        $mo = $catalog->toMoString();
        $this->assertSame('nplurals=2; plural=(n != 1);', \MigratedPoFixture::moPluralFormsHeader($mo));
        $this->assertSame(['item' => ['A', 'B']], \MigratedPoFixture::readMoPluralForms($mo), 'the surplus form C is dropped');
        $this->assertSame(['item' => 'B'], $this->read($mo));
    }

    /**
     * In a plural message the NUL of the "0" workaround would become an additional, empty plural
     * form - compiling it throws instead (ILIAS itself uses no plurals).
     */
    public function testASingularTranslationZeroOfAPluralMessageCannotBeCompiled(): void
    {
        $catalog = TranslationCatalog::fromPoString("msgid \"a\"\nmsgid_plural \"as\"\nmsgstr[0] \"0\"\nmsgstr[1] \"1\"\n");

        $this->expectException(RuntimeException::class);
        $catalog->toMoString();
    }

    public function testAnEmptyCatalogCompilesToAValidMoWithoutMessages(): void
    {
        $mo = (new TranslationCatalog())->toMoString();

        $this->assertSame([''], self::originals($mo), 'only the (empty) header');
        $this->assertSame([], $this->read($mo));
    }

    /**
     * MigratedLanguageFileSync::sync() decides by byte comparison whether the `.mo` is current - so
     * the same content must always compile to the same bytes, independent of the insertion order
     * and of how often it was compiled.
     */
    public function testToMoStringIsDeterministic(): void
    {
        $build = static function (array $ids): TranslationCatalog {
            $catalog = new TranslationCatalog();
            $catalog->setHeader('Language', 'de');
            foreach ($ids as $id) {
                $catalog->add(self::entry('mod', $id, $id === 'zero' ? '0' : 'Wert ' . $id));
            }
            return $catalog;
        };
        $catalog = $build(['b', 'zero', 'a', '10', '9', 'B']);

        $mo = $catalog->toMoString();

        $this->assertSame($mo, $catalog->toMoString());
        $this->assertSame($mo, $build(['b', 'zero', 'a', '10', '9', 'B'])->toMoString());
        $this->assertSame($mo, $build(['9', 'B', 'zero', '10', 'a', 'b'])->toMoString());
        $this->assertSame($mo, TranslationCatalog::fromPoString($catalog->toPoString())->toMoString());
        $this->assertNotSame($mo, $build(['b', 'zero', 'a', '10', '9'])->toMoString());
    }

    /**
     * The `.mo` content depends on everything ilLanguage serves - a changed value must produce
     * different bytes, or the self-healing byte comparison of sync() would keep a stale `.mo`.
     */
    public function testAChangedValueChangesTheMo(): void
    {
        $catalog = self::sampleCatalog();
        $mo = $catalog->toMoString();

        $catalog->find('mod', 'zeta')?->translate('Y');

        $this->assertNotSame($mo, $catalog->toMoString());
    }

    /**
     * The library's PHP warnings/notices say "this file is broken" and become a RuntimeException.
     * The adapter's structural pre-checks keep MoLoader from ever raising one for a real file, so
     * this is exercised directly on the private helper.
     */
    #[DataProvider('libraryWarnings')]
    public function testLibraryWarningsAndNoticesBecomeRuntimeExceptions(int $level): void
    {
        $helper = new ReflectionMethod(TranslationCatalog::class, 'withLibraryErrorsAsExceptions');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('library message');
        $helper->invoke(null, static function () use ($level): Translations {
            trigger_error('library message', $level);
            return Translations::create();
        });
    }

    public static function libraryWarnings(): array
    {
        return [
            'warning' => [E_USER_WARNING],
            'notice' => [E_USER_NOTICE],
        ];
    }

    /**
     * A deprecation only says something about the library on this PHP version, not about the file:
     * the load succeeds and the deprecation goes to the error handler registered before (ILIAS' own
     * or, in a test run, PHPUnit's) - which is active again afterward.
     */
    public function testLibraryDeprecationsArePassedOnToThePreviousHandlerAndDoNotFailTheLoad(): void
    {
        $helper = new ReflectionMethod(TranslationCatalog::class, 'withLibraryErrorsAsExceptions');
        $received = [];
        $previous_handler = static function (int $severity, string $message, string $file, int $line) use (&$received): bool {
            $received[] = [$severity, $message, $file, $line];
            return true;
        };
        set_error_handler($previous_handler);
        try {
            $result = $helper->invoke(null, static function (): Translations {
                trigger_error('library deprecation', E_USER_DEPRECATED);
                return Translations::create()->setDomain('loaded');
            });
            $trigger_line = __LINE__ - 3;

            $current = set_error_handler(null);
            restore_error_handler();
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(Translations::class, $result);
        $this->assertSame('loaded', $result->getDomain());
        $this->assertSame([[E_USER_DEPRECATED, 'library deprecation', __FILE__, $trigger_line]], $received);
        $this->assertSame($previous_handler, $current, 'the previous handler is restored');
    }

    /**
     * A previous handler that declines (returns false) leaves the deprecation to PHP's standard
     * handling (here: the error log) - still without failing the load.
     */
    public function testALibraryDeprecationDeclinedByThePreviousHandlerGoesToPhpsStandardHandling(): void
    {
        $helper = new ReflectionMethod(TranslationCatalog::class, 'withLibraryErrorsAsExceptions');
        $log = $this->directory . '/error.log';
        $ini = ['error_log' => $log, 'log_errors' => '1', 'display_errors' => '0'];
        $previous = [];
        foreach ($ini as $name => $value) {
            $previous[$name] = ini_set($name, $value);
        }
        $previous_level = error_reporting(E_ALL);
        $calls = 0;
        set_error_handler(static function () use (&$calls): bool {
            $calls++;
            return false;
        });
        try {
            $result = $helper->invoke(null, static function (): Translations {
                trigger_error('declined library deprecation', E_USER_DEPRECATED);
                return Translations::create()->setDomain('loaded');
            });
        } finally {
            restore_error_handler();
            error_reporting($previous_level);
            foreach ($previous as $name => $value) {
                ini_set($name, (string) $value);
            }
        }

        $this->assertSame('loaded', $result->getDomain());
        $this->assertSame(1, $calls);
        $this->assertStringContainsString('declined library deprecation', (string) @file_get_contents($log));
    }

    /**
     * Passing deprecations on must not pass on warnings: those still become a RuntimeException and
     * never reach the previous handler.
     */
    public function testLibraryWarningsDoNotReachThePreviousHandler(): void
    {
        $helper = new ReflectionMethod(TranslationCatalog::class, 'withLibraryErrorsAsExceptions');
        $calls = 0;
        set_error_handler(static function () use (&$calls): bool {
            $calls++;
            return true;
        });
        try {
            $helper->invoke(null, static function (): Translations {
                trigger_error('library warning', E_USER_WARNING);
                return Translations::create();
            });
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertSame('library warning', $e->getMessage());
        } finally {
            restore_error_handler();
        }

        $this->assertSame(0, $calls);
    }

    public function testThrowablesOfTheLibraryBecomeRuntimeExceptions(): void
    {
        $helper = new ReflectionMethod(TranslationCatalog::class, 'withLibraryErrorsAsExceptions');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('type error');
        $helper->invoke(null, static function (): Translations {
            throw new \TypeError('type error');
        });
    }
}
