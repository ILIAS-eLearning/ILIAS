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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ShippedTranslations: compile() must compile the shipped `.po` of one module exactly like
 * TranslationCatalog::toMoString() (fuzzy included, empty left out), with every value cleaned by
 * TranslationMarkupPolicy first (the runtime serves the build through native gettext, see
 * ShippedTranslationsBuild and MigratedTranslations).
 */
class ShippedTranslationsTest extends TestCase
{
    private string $root;
    private ShippedTranslations $shipped_translations;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        \MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_shipped_translations_' . bin2hex(random_bytes(6));
        mkdir($this->root, 0775, true);
        $this->shipped_translations = new ShippedTranslations();
    }

    protected function tearDown(): void
    {
        \MigratedPoFixture::removeDirectory($this->root);
    }

    private function shippedPoPath(string $module, string $lang_key): string
    {
        return $this->root . '/' . $module . '_' . $lang_key . '.po';
    }

    /**
     * Only the messages of the requested module (context) are compiled - a shipped `.po` may in
     * principle carry other contexts, and they must not leak into a different module's artifact.
     */
    /**
     * @return array<string, string> what native gettext serves for txt() from the compiled `.mo`
     */
    private static function served(string $mo): array
    {
        $served = [];
        foreach (\MigratedPoFixture::moStrings($mo) as [$original, $translated]) {
            if ($original !== '' && !str_contains($original, "\x04")) {
                $served[$original] = $translated;
            }
        }

        return $served;
    }

    public function testCompileOnlyIncludesEntriesOfTheRequestedModuleContext(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(\MigratedPoFixture::entry('itest', 'greeting', 'Hallo'));
        $catalog->add(\MigratedPoFixture::entry('other', 'greeting', 'Servus'));
        \MigratedPoFixture::writePo($this->shippedPoPath('itest', 'de'), $catalog);

        $compiled = $this->shipped_translations->compile($this->shippedPoPath('itest', 'de'), 'itest');

        $this->assertSame(['greeting' => 'Hallo'], self::served($compiled['mo']));
    }

    /**
     * compile() follows TranslationCatalog::toMoString(): fuzzy messages compiled in, empty
     * translations left out - and serves every message without its context.
     */
    public function testCompileMatchesToMoStringSemanticsForFuzzyAndEmptyMessages(): void
    {
        $catalog = \MigratedPoFixture::catalog('itest', [
            'fuzzy_included' => ['value' => 'Ungeprüft', 'fuzzy' => true],
            'empty_excluded' => '',
            'normal' => 'Normal',
        ]);
        \MigratedPoFixture::writePo($this->shippedPoPath('itest', 'de'), $catalog);

        $compiled = $this->shipped_translations->compile($this->shippedPoPath('itest', 'de'), 'itest');

        $this->assertSame(
            ['fuzzy_included' => 'Ungeprüft', 'normal' => 'Normal'],
            self::served($compiled['mo'])
        );
        $this->assertSame(['fuzzy_included' => 'Ungeprüft', 'normal' => 'Normal'], $compiled['values']);
    }

    /**
     * A value with markup TranslationMarkupPolicy does not allow is compiled sanitized, and the
     * callback is invoked with the identifier and the violations that were removed - a value that is
     * already allowed never triggers it.
     */
    public function testCompileSanitizesDisallowedMarkupAndReportsIt(): void
    {
        $catalog = \MigratedPoFixture::catalog('itest', [
            'bad' => '<script>alert(1)</script>Hallo',
            'good' => '<b>Hallo</b>',
        ]);
        \MigratedPoFixture::writePo($this->shippedPoPath('itest', 'de'), $catalog);

        $reported = [];
        $compiled = $this->shipped_translations->compile(
            $this->shippedPoPath('itest', 'de'),
            'itest',
            static function (string $identifier, array $violations) use (&$reported): void {
                $reported[$identifier] = $violations;
            }
        );

        $translations = self::served($compiled['mo']);
        $this->assertStringNotContainsString('<script', $translations['bad']);
        $this->assertStringContainsString('Hallo', $translations['bad']);
        $this->assertSame('<b>Hallo</b>', $translations['good']);
        $this->assertArrayHasKey('bad', $reported);
        $this->assertArrayNotHasKey('good', $reported);
    }

    public function testCompileThrowsForAnUnreadableShippedPo(): void
    {
        $this->expectException(RuntimeException::class);

        $this->shipped_translations->compile($this->shippedPoPath('itest', 'de'), 'itest');
    }
}
