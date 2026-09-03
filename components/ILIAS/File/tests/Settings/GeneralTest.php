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

namespace ILIAS\Tests\File\Settings;

use ILIAS\components\File\Settings\General;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class GeneralTest extends TestCase
{
    private \ilDBInterface|MockObject $db;

    protected function setUp(): void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
    }

    public function testNothingIsReadBeforeASettingIsAsked(): void
    {
        $this->db->expects($this->never())->method('queryF');

        new General($this->db);
    }

    public function testSettingsAreReadOnceForSeveralLookups(): void
    {
        $this->expectSettingsRead(['bg_limit' => '500']);

        $settings = new General($this->db);

        $this->assertSame(500, $settings->getDownloadLimitinMB());
        $this->assertSame(500, $settings->getDownloadLimitinMB());
    }

    public function testStoredValuesAreUsed(): void
    {
        $this->expectSettingsRead([
            'show_amount_of_downloads' => '0',
            'download_ascii_filename' => '0',
            'inline_file_extensions' => 'pdf png',
        ]);

        $settings = new General($this->db);

        $this->assertFalse($settings->isShowAmountOfDownloads());
        $this->assertFalse($settings->isDownloadWithAsciiFileName());
        $this->assertSame(['pdf', 'png'], $settings->getInlineFileExtensions());
    }

    public function testDefaultsApplyWithoutStoredValues(): void
    {
        $this->expectSettingsRead([]);

        $settings = new General($this->db);

        $this->assertTrue($settings->isShowAmountOfDownloads());
        $this->assertTrue($settings->isDownloadWithAsciiFileName());
        $this->assertSame(200, $settings->getDownloadLimitinMB());
        $this->assertSame(
            ['gif', 'jpg', 'jpeg', 'mp3', 'pdf', 'png'],
            $settings->getInlineFileExtensions()
        );
    }

    public function testSettingAValueWritesItAndKeepsItReadable(): void
    {
        $this->expectSettingsRead([]);
        $this->db->expects($this->once())
            ->method('replace')
            ->with(
                'settings',
                [
                    'module' => ['text', General::MODULE_NAME],
                    'keyword' => ['text', General::F_BG_LIMIT],
                ],
                ['value' => ['text', '750']]
            )
            ->willReturn(1);

        $settings = new General($this->db);
        $settings->setDownloadLimitInMB(750);

        $this->assertSame(750, $settings->getDownloadLimitinMB());
    }

    public function testExtensionsAreNormalisedOnWrite(): void
    {
        $this->expectSettingsRead([]);
        $this->db->expects($this->once())
            ->method('replace')
            ->with(
                'settings',
                $this->anything(),
                ['value' => ['text', 'pdf png']]
            )
            ->willReturn(1);

        $settings = new General($this->db);
        $settings->setInlineFileExtensions([' PDF, ', "png\n"]);

        $this->assertSame(['pdf', 'png'], $settings->getInlineFileExtensions());
    }

    /**
     * @param array<string, string> $stored
     */
    private function expectSettingsRead(array $stored): void
    {
        $rows = [];
        foreach ($stored as $keyword => $value) {
            $rows[] = ['keyword' => $keyword, 'value' => $value];
        }
        $rows[] = null;

        $this->db->expects($this->once())
            ->method('queryF')
            ->with($this->stringContains('FROM settings'), ['text'], [General::MODULE_NAME])
            ->willReturn($this->createMock(\ilDBStatement::class));

        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(...$rows);
    }
}
