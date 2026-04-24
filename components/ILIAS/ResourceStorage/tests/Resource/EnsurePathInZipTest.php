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

namespace ILIAS\ResourceStorage\Resource;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * Regression tests for Mantis 0045580 / 0047237: entries written to a
 * ZIP container must not start with a leading slash, otherwise some
 * extraction tools (Windows Explorer in particular) will not list them.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class EnsurePathInZipTest extends TestCase
{
    private string $zip_file;
    private ZipArchive $zip;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zip_file = tempnam(sys_get_temp_dir(), 'irss_zip_test_');
        $this->zip = new ZipArchive();
        $this->zip->open($this->zip_file, ZipArchive::OVERWRITE);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->zip->filename !== '') {
            @$this->zip->close();
        }
        @unlink($this->zip_file);
    }

    #[DataProvider('filePathProvider')]
    public function testFilePathHasNoLeadingSlash(string $input, string $expected): void
    {
        $method = new \ReflectionMethod(ResourceBuilder::class, 'ensurePathInZIP');
        $builder = (new \ReflectionClass(ResourceBuilder::class))->newInstanceWithoutConstructor();

        $result = $method->invoke($builder, $this->zip, $input, true);

        $this->assertSame($expected, $result);
        $this->assertStringStartsNotWith('/', $result);
    }

    public static function filePathProvider(): \Iterator
    {
        yield 'root file, no slash' => ['index.html', 'index.html'];
        yield 'root file, leading slash' => ['/index.html', 'index.html'];
        yield 'subdir file, no slash' => ['assets/style.css', 'assets/style.css'];
        yield 'subdir file, leading slash' => ['/assets/style.css', 'assets/style.css'];
        yield 'nested file, no slash' => ['assets/css/osd.css', 'assets/css/osd.css'];
        yield 'nested file, leading slash' => ['/assets/css/osd.css', 'assets/css/osd.css'];
    }

    public function testDirectoryPathHasNoLeadingSlash(): void
    {
        $method = new \ReflectionMethod(ResourceBuilder::class, 'ensurePathInZIP');
        $builder = (new \ReflectionClass(ResourceBuilder::class))->newInstanceWithoutConstructor();

        $result = $method->invoke($builder, $this->zip, '/dir/sub', false);

        $this->assertStringStartsNotWith('/', $result);
        $this->assertSame('dir/sub/', $result);
    }

    public function testWritingRootFileProducesRelativeEntry(): void
    {
        $method = new \ReflectionMethod(ResourceBuilder::class, 'ensurePathInZIP');
        $builder = (new \ReflectionClass(ResourceBuilder::class))->newInstanceWithoutConstructor();

        $entry = $method->invoke($builder, $this->zip, '/index.html', true);
        $this->zip->addFromString($entry, '<html></html>');
        $this->zip->close();

        $verify = new ZipArchive();
        $verify->open($this->zip_file);
        $names = [];
        for ($i = 0; $i < $verify->numFiles; $i++) {
            $names[] = $verify->getNameIndex($i);
        }
        $verify->close();

        foreach ($names as $name) {
            $this->assertStringStartsNotWith('/', $name, "ZIP entry must not start with '/': {$name}");
        }
        $this->assertContains('index.html', $names);
    }
}
