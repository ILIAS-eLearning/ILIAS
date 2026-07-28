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

namespace ILIAS\Filesystem\Stream;

use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * Regression tests for Mantis 48047: entries inside a container are stored relative
 * since Mantis 45580 / 47237. Containers written before that may hold both variants
 * of the same file, and in that case the relative entry is the up to date one.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class FileInsideZIPTest extends TestCase
{
    private string $zip_file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zip_file = tempnam(sys_get_temp_dir(), 'irss_zip_read_test_');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        @unlink($this->zip_file);
    }

    /**
     * @param array<string, string> $entries
     */
    private function buildZip(array $entries): void
    {
        $zip = new ZipArchive();
        $zip->open($this->zip_file, ZipArchive::OVERWRITE);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
    }

    public function testRelativeEntryIsRead(): void
    {
        $this->buildZip(['style.css' => 'relative']);

        $stream = Streams::ofFileInsideZIP($this->zip_file, 'style.css');

        $this->assertSame('relative', (string) $stream);
    }

    public function testLegacyEntryIsStillRead(): void
    {
        $this->buildZip(['/style.css' => 'legacy']);

        $stream = Streams::ofFileInsideZIP($this->zip_file, 'style.css');

        $this->assertSame('legacy', (string) $stream);
    }

    public function testRelativeEntryWinsOverLegacyEntry(): void
    {
        $this->buildZip(['/style.css' => 'legacy', 'style.css' => 'relative']);

        $stream = Streams::ofFileInsideZIP($this->zip_file, 'style.css');

        $this->assertSame('relative', (string) $stream);
    }

    public function testRelativeEntryWinsAlsoIfTheRequestedPathHasALeadingSlash(): void
    {
        $this->buildZip(['/style.css' => 'legacy', 'style.css' => 'relative']);

        $stream = Streams::ofFileInsideZIP($this->zip_file, '/style.css');

        $this->assertSame('relative', (string) $stream);
    }

    public function testUnknownEntryThrows(): void
    {
        $this->buildZip(['style.css' => 'relative']);

        $this->expectException(\InvalidArgumentException::class);
        Streams::ofFileInsideZIP($this->zip_file, 'does_not_exist.css');
    }
}
