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

use PHPUnit\Framework\TestCase;

/**
 * Runs the sanitizer against the real list arithmetic of ilFileServicesSettings instead of
 * a mocked white list, see https://mantis.ilias.de/view.php?id=47828
 */
final class ilFileServicesFilenameSanitizerTest extends TestCase
{
    public function testBypassIgnoresTheNegativeList(): void
    {
        $sanitizer = $this->sanitizerFor('zip', '', true);

        $this->assertSame('/lib/scorm.zip', $sanitizer->sanitize('/lib/scorm.zip'));
    }

    public function testNegativeListAppliesWithoutBypass(): void
    {
        $sanitizer = $this->sanitizerFor('zip', '', false);

        $this->assertSame('/lib/scormzip.sec', $sanitizer->sanitize('/lib/scorm.zip'));
    }

    public function testBypassDoesNotLiftTheProhibitedSuffixes(): void
    {
        $sanitizer = $this->sanitizerFor('zip', 'zip', true);

        $this->assertSame('/lib/scormzip.sec', $sanitizer->sanitize('/lib/scorm.zip'));
    }

    public function testBypassDoesNotAddSuffixesThatWereNeverAllowed(): void
    {
        $sanitizer = $this->sanitizerFor('exe', '', true);

        $this->assertSame('/lib/setupexe.sec', $sanitizer->sanitize('/lib/setup.exe'));
    }

    public function testBypassStillRejectsPhp(): void
    {
        $sanitizer = $this->sanitizerFor('php', '', true, 'php');

        $this->assertSame('/lib/shellphp.sec', $sanitizer->sanitize('/lib/shell.php'));
    }

    private function sanitizerFor(
        string $negative,
        string $prohibited,
        bool $bypass,
        string $positive = ''
    ): ilFileServicesFilenameSanitizer {
        $il_setting = $this->createMock(ilSetting::class);
        $il_setting->method('get')->willReturnCallback(
            fn(string $key): string => match ($key) {
                'suffix_repl_additional' => $negative,
                'suffix_custom_white_list' => $positive,
                'suffix_custom_expl_black' => $prohibited,
                default => '',
            }
        );

        // the constructor needs the database and the general file settings, neither of
        // which matters for the list arithmetic
        $reflection = new ReflectionClass(ilFileServicesSettings::class);
        $settings = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('settings')->setValue($settings, $il_setting);
        $reflection->getProperty('white_list_default')->setValue(
            $settings,
            include __DIR__ . '/../defaults/default_whitelist.php'
        );
        $reflection->getProperty('bypass')->setValue($settings, $bypass);
        $reflection->getMethod('read')->invoke($settings);

        return new ilFileServicesFilenameSanitizer($settings);
    }
}
