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

use ILIAS\Mail\Mime\Presentation\Asset\InlineImage;
use ILIAS\Mail\Mime\Presentation\Asset\InlineImages;
use ILIAS\Mail\Mime\Presentation\Asset\LogoByFileName;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

final class LogoByFileNameTest extends TestCase
{
    private const string BANNER = 'banner.png';
    private const string DECORATIVE = 'decorative.png';
    private const string LOGO = 'logo.png';
    private const string HEADER_ICON = 'headericon.png';

    private LogoByFileName $logo_by_file_name;

    protected function setUp(): void
    {
        $this->logo_by_file_name = new LogoByFileName();
    }

    public function testMarkInLeavesEmptyImagesUnchanged(): void
    {
        $images = InlineImages::none();

        $result = $this->logo_by_file_name->markIn($images);

        $this->assertSame($images, $result);
        $this->assertTrue($result->isEmpty());
        $this->assertFalse($result->hasLogo());
    }

    public function testMarkInKeepsAnAlreadyMarkedLogo(): void
    {
        $images = $this->images(self::BANNER, self::DECORATIVE);
        $already_marked = $images->withLogo($images->first());

        $result = $this->logo_by_file_name->markIn($already_marked);

        $this->assertSame($already_marked, $result);
        $this->assertSame(self::BANNER, $result->logo()?->name());
    }

    public function testMarkInMarksAFileNamedLogo(): void
    {
        $images = $this->images(self::BANNER, self::LOGO);

        $result = $this->logo_by_file_name->markIn($images);

        $this->assertSame(self::LOGO, $result->logo()?->name());
        $this->assertCount(2, $result);
    }

    public function testMarkInMarksAFileNamedHeadericon(): void
    {
        $images = $this->images(self::BANNER, self::HEADER_ICON);

        $result = $this->logo_by_file_name->markIn($images);

        $this->assertSame(self::HEADER_ICON, $result->logo()?->name());
        $this->assertCount(2, $result);
    }

    public function testMarkInFallsBackToTheFirstImageWhenNoKnownNameExists(): void
    {
        $images = $this->images(self::BANNER, self::DECORATIVE);

        $result = $this->logo_by_file_name->markIn($images);

        $this->assertSame(self::BANNER, $result->logo()?->name());
        $this->assertCount(2, $result);
    }

    private function images(string ...$filenames): InlineImages
    {
        $this->skipIfVfsStreamNotAvailable();

        $tree = ['images' => []];
        foreach ($filenames as $filename) {
            $tree['images'][$filename] = 'png';
        }

        vfsStream::setup();
        vfsStream::create($tree);

        return InlineImages::of(
            ...array_map(
                static fn(string $filename): InlineImage => InlineImage::at(
                    vfsStream::url("root/images/{$filename}")
                ),
                $filenames
            )
        );
    }

    private function skipIfVfsStreamNotAvailable(): void
    {
        if (!class_exists(vfsStreamWrapper::class)) {
            $this->markTestSkipped(
                'vfsStream (https://github.com/bovigo/vfsStream) is required for virtual filesystem tests.'
            );
        }
    }
}
