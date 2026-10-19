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

use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * MigratedLanguageFileSync::loadEditableModuleTranslations(): the content the administration edits, never from the
 * database.
 */
class MigratedLanguageFileSyncEditableTest extends MigratedLanguageFileSyncTestCase
{
    /**
     * @return array<string, string>|null identifier => value
     */
    private function editable(?string $client_data_dir = 'default', string $module = self::MODULE, string $lang_key = 'de', bool $for_writing = false): ?array
    {
        $content = MigratedLanguageFileSync::loadEditableModuleTranslations(
            $this->manager,
            $lang_key,
            $module,
            $client_data_dir === 'default' ? $this->client_data_dir : $client_data_dir,
            $this->ilias_root,
            $for_writing
        );

        return $content === null ? null : array_map(static fn(array $entry): string => $entry['value'], $content);
    }

    public function testTheShippedPoAndItsReadableOverlayAreMerged(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => 'Moin']);

        $this->assertSame(['greeting' => 'Moin', 'farewell' => 'Tschüss'], $this->editable());
    }

    public function testAnUnreadableOverlayYieldsTheShippedPoAloneAndIsReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Moin']);
        file_put_contents($this->overlayBase() . '.po', "msgid \"kaputt\n");

        $this->expectOutputRegex('/The local changes of module "stest" for "de" cannot be read, the shipped values are used/');
        $this->assertSame(['greeting' => 'Hallo'], $this->editable());
    }

    /** For writing, the shipped values alone would drop the other local changes of the overlay: it throws instead. */
    public function testAnUnreadableOverlayThrowsForWritingAndIsNotReportedAsUsable(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Moin']);
        file_put_contents($this->overlayBase() . '.po', "msgid \"kaputt\n");

        $this->expectException(\RuntimeException::class);
        $this->editable('default', self::MODULE, 'de', true);
    }

    public function testForWritingAReadableOverlayIsMergedAsForDisplay(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => 'Moin']);

        $this->assertSame(['greeting' => 'Moin', 'farewell' => 'Tschüss'], $this->editable('default', self::MODULE, 'de', true));
    }

    public function testWithoutClientDataDirectoryTheShippedPoAloneIsReturned(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertSame(['greeting' => 'Hallo'], $this->editable(null));
        $this->expectOutputString('');
    }

    public function testAnUnreadableShippedPoThrows(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");

        $this->expectOutputRegex('/cannot be read/');
        $this->expectException(\RuntimeException::class);
        $this->editable();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function nothingToEdit(): array
    {
        return [
            'module of no directory' => ['unknown', 'de'],
            'no shipped po for the language' => ['stest', 'fr'],
            'invalid language key' => ['stest', '../de'],
        ];
    }

    #[DataProvider('nothingToEdit')]
    public function testWithoutDirectoryShippedPoOrValidKeyThereIsNothingToEdit(string $module, string $lang_key): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertNull($this->editable('default', $module, $lang_key));
    }
}
