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

namespace ILIAS\Filesystem\Util;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Util\Convert\Images;
use ILIAS\Filesystem\Util\Convert\LegacyImages;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LegacyImageConversionTest extends TestCase
{
    private LegacyImages $images;


    protected function setUp(): void
    {
        $this->checkImagick();
        $this->images = new LegacyImages();
    }


    public function someDefinitions(): array
    {
        return [
            [100, 100, 'jpg', 'image/jpeg'],
            [256, 25, 'jpg', 'image/jpeg'],
            [1024, 5, 'jpg', 'image/jpeg'],
            [128, 10, 'jpg', 'image/jpeg'],
            [895, 22, 'png', 'image/png'],
            [86, 4, 'png', 'image/png'],
            [147, 8, 'png', 'image/png'],
            [1000, 10, 'png', 'image/png'],
        ];
    }

    /**
     * @dataProvider someDefinitions
     */
    public function testImageThumbnailActualImage(
        int $expected_height,
        int $expected_quality,
        string $format,
        string $expected_mime_type
    ): void {
        $img = __DIR__ . '/img/robot.jpg';
        $this->assertFileExists($img);

        $temp_file = tempnam(sys_get_temp_dir(), 'img');

        $thumbnail = $this->images->thumbnail(
            $img,
            $temp_file,
            $expected_height,
            $format,
            $expected_quality
        );

        $this->assertEquals($temp_file, $thumbnail);

        $test_image = new \Imagick($thumbnail);

        // PNGs do not have a quality setting which can be read by getImageCompressionQuality()
        if ($format === 'png') {
            $expected_quality = 0;
        }

        $this->assertEquals($expected_quality, $test_image->getImageCompressionQuality());
        $this->assertEquals($expected_height, $test_image->getImageHeight());
        $this->assertEquals((int)round($expected_height * 0.75), $test_image->getImageWidth());
        unlink($temp_file);
    }

    protected function checkImagick(): void
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('Imagick not installed');
        }
    }
}
