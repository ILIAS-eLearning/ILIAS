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
use ILIAS\Filesystem\Util\Convert\ImageConversionOptions;
use ILIAS\Filesystem\Util\Convert\ImageConverter;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Util\Convert\Images;
use ILIAS\Filesystem\Util\Convert\ResizeImageConverter;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ImageConversionTest extends TestCase
{
    use MemoryStreamToTempFileStream;

    protected const BY_WIDTH_FINAL = 256;
    protected const BY_HEIGHT_FINAL = 756;
    protected const W = 'width';
    protected const H = 'height';
    protected const IMAGE_JPEG = 'image/jpeg';
    protected const IMAGE_PNG = 'image/png';
    protected const IMAGE_WEBP = 'image/webp';
    protected Images $images;

    protected function setUp(): void
    {
        $this->checkImagick();
        $this->images = new Images(
            true,
        );
    }

    public function testImageThumbnailActualImage(): void
    {
        $img = __DIR__ . '/img/robot.jpg';
        $this->assertFileExists($img);
        $getimagesize = getimagesize($img);
        $original_width = $getimagesize[0]; // should be 600
        $original_height = $getimagesize[1]; // should be 800
        $this->assertEquals(600, $original_width);
        $this->assertEquals(800, $original_height);

        // make tumbnail
        $original_stream = Streams::ofResource(fopen($img, 'rb'));

        $thumbnail_converter = $this->images->thumbnail(
            $original_stream,
            100
        );
        $this->assertTrue($thumbnail_converter->isOK());
        $this->assertNull($thumbnail_converter->getThrowableIfAny());
        $converted_stream = $thumbnail_converter->getStream();

        $getimagesizefromstring = getimagesizefromstring((string)$converted_stream);

        $this->assertEquals(75, $getimagesizefromstring[0]); // width
        $this->assertEquals(100, $getimagesizefromstring[1]); // height
    }

    public function testImageSquareActualImage(): void
    {
        $img = __DIR__ . '/img/robot.jpg';
        $this->assertFileExists($img);
        $getimagesize = getimagesize($img);
        $original_width = $getimagesize[0]; // should be 600
        $original_height = $getimagesize[1]; // should be 800
        $this->assertEquals(600, $original_width);
        $this->assertEquals(800, $original_height);

        // make tumbnail
        $original_stream = Streams::ofResource(fopen($img, 'rb'));

        $thumbnail_converter = $this->images->croppedSquare(
            $original_stream,
            200
        );
        $this->assertTrue($thumbnail_converter->isOK());
        $this->assertNull($thumbnail_converter->getThrowableIfAny());

        $getimagesizefromstring = $this->getImageSizeFromStream($thumbnail_converter->getStream());

        $this->assertEquals(200, $getimagesizefromstring[self::W]);
        $this->assertEquals(200, $getimagesizefromstring[self::H]);
    }

    public function getImageSizesByWidth(): array
    {
        return [
            [400, 300, self::BY_WIDTH_FINAL, 192],
            [300, 400, self::BY_WIDTH_FINAL, 341],
            [543, 431, self::BY_WIDTH_FINAL, 203],
            [200, 200, self::BY_WIDTH_FINAL, 256],
        ];
    }

    /**
     * @dataProvider getImageSizesByWidth
     */
    public function testResizeToFitWidth(
        int $width,
        int $height,
        int $final_width,
        int $final_height
    ): void {
        $stream = $this->createTestImageStream($width, $height);
        $dimensions = $this->getImageSizeFromStream($stream);
        $this->assertEquals($width, $dimensions[self::W]);
        $this->assertEquals($height, $dimensions[self::H]);

        // resize to fit width
        $resized = $this->images->resizeByWidth($stream, self::BY_WIDTH_FINAL);
        $this->assertTrue($resized->isOK());
        $new_dimensions = $this->getImageSizeFromStream($resized->getStream());

        $this->assertEquals($final_width, $new_dimensions[self::W]);
        $this->assertEquals($final_height, $new_dimensions[self::H]);

        // check aspect ratio
        $this->assertEquals(
            round($width > $height),
            round($final_width > $final_height)
        );
        $this->assertEquals(
            round($width / $height),
            round($new_dimensions[self::W] / $new_dimensions[self::H])
        );
        $this->assertEquals(
            $width > $height,
            $width > $height
        );
        $this->assertEquals(
            $width > $height,
            $new_dimensions[self::W] > $new_dimensions[self::H]
        );
    }

    public function getImageSizesByHeight(): array
    {
        return [
            [400, 300, self::BY_HEIGHT_FINAL, 1008],
            [300, 400, self::BY_HEIGHT_FINAL, 567],
            [200, 200, self::BY_HEIGHT_FINAL, 756],
            [248, 845, self::BY_HEIGHT_FINAL, 221],
        ];
    }


    /**
     * @dataProvider getImageSizesByHeight
     */
    public function testResizeToFitHeight(
        int $width,
        int $height,
        int $final_height,
        int $final_width
    ): void {
        $stream = $this->createTestImageStream($width, $height);
        $dimensions = $this->getImageSizeFromStream($stream);
        $this->assertEquals($width, $dimensions[self::W]);
        $this->assertEquals($height, $dimensions[self::H]);

        // resize to fit
        $resized = $this->images->resizeByHeight($stream, self::BY_HEIGHT_FINAL);
        $this->assertTrue($resized->isOK());
        $new_dimensions = $this->getImageSizeFromStream($resized->getStream());

        $this->assertEquals($final_width, $new_dimensions[self::W]);
        $this->assertEquals($final_height, $new_dimensions[self::H]);

        // check aspect ratio
        $this->assertEquals(
            round($width > $height),
            round($final_width > $final_height)
        );
        $this->assertEquals(
            round($width / $height),
            round($new_dimensions[self::W] / $new_dimensions[self::H])
        );
        $this->assertEquals(
            $width > $height,
            $width > $height
        );
        $this->assertEquals(
            $width > $height,
            $new_dimensions[self::W] > $new_dimensions[self::H]
        );
    }

    public function getImageSizesByFixed(): array
    {
        return [
            [1024, 768, 300, 100, true],
            [1024, 768, 300, 100, false],
            [1024, 768, 100, 300, true],
            [1024, 768, 100, 300, false],
            [400, 300, 500, 400, true],
            [400, 300, 500, 400, false],
        ];
    }

    /**
     * @dataProvider getImageSizesByFixed
     */
    public function testResizeByFixedSize(
        int $width,
        int $height,
        int $final_width,
        int $final_height,
        bool $crop
    ): void {
        $stream = $this->createTestImageStream($width, $height);
        $dimensions = $this->getImageSizeFromStream($stream);
        $this->assertEquals($width, $dimensions[self::W]);
        $this->assertEquals($height, $dimensions[self::H]);

        $by_fixed = $this->images->resizeToFixedSize($stream, $final_width, $final_height, $crop);
        $this->assertTrue($by_fixed->isOK());
        $new_dimensions = $this->getImageSizeFromStream($by_fixed->getStream());

        $this->assertEquals($final_width, $new_dimensions[self::W]);
        $this->assertEquals($final_height, $new_dimensions[self::H]);
    }

    public function getImageOptions(): array
    {
        $options = new ImageOutputOptions();
        return [
            [$options, self::IMAGE_JPEG, 75],
            [$options->withPngOutput()->withQuality(22), self::IMAGE_PNG, 0],
            [$options->withJpgOutput()->withQuality(100), self::IMAGE_JPEG, 100],
            [$options->withFormat('png')->withQuality(50), self::IMAGE_PNG, 0],
            [$options->withFormat('jpg')->withQuality(87), self::IMAGE_JPEG, 87],
            [$options->withQuality(5)->withJpgOutput(), self::IMAGE_JPEG, 5],
            [$options->withQuality(10)->withJpgOutput(), self::IMAGE_JPEG, 10],
            [$options->withQuality(35)->withJpgOutput(), self::IMAGE_JPEG, 35],
            [$options->withQuality(0)->withWebPOutput(), self::IMAGE_WEBP, 0],
            [$options->withQuality(100)->withWebPOutput(), self::IMAGE_WEBP, 100],
        ];
    }


    /**
     * @dataProvider getImageOptions
     */
    public function testImageOutputOptions(
        ImageOutputOptions $options,
        string $expected_mime_type,
        int $expected_quality
    ): void {
        $resized = $this->images->resizeToFixedSize(
            $this->createTestImageStream(10, 10),
            5,
            5,
            true,
            $options
        );

        $this->assertEquals($expected_mime_type, $this->getImageTypeFromStream($resized->getStream()));
        $this->assertEquals($expected_quality, $this->getImageQualityFromStream($resized->getStream()));
    }

    public function testImageOutputOptionSanity(): void
    {
        $options = new ImageOutputOptions();

        // Defaults
        $this->assertEquals('jpg', $options->getFormat());
        $this->assertEquals(75, $options->getQuality());

        $png = $options->withPngOutput();
        $this->assertEquals('png', $png->getFormat());
        $this->assertEquals('jpg', $options->getFormat()); // original options should not change
        $png_explicit = $options->withFormat('png');
        $this->assertEquals('png', $png_explicit->getFormat());

        $jpg = $options->withJpgOutput();
        $this->assertEquals('jpg', $jpg->getFormat());
        $jpg_explicit = $options->withFormat('jpg');
        $this->assertEquals('jpg', $jpg_explicit->getFormat());
        $jpeg = $options->withFormat('jpeg');
        $this->assertEquals('jpg', $jpeg->getFormat());

        // Quality
        $low = $options->withQuality(5);
        $this->assertEquals(5, $low->getQuality());
        $this->assertEquals(75, $options->getQuality()); // original options should not change
    }

    public function getWrongFormats(): array
    {
        return [
            ['gif'],
            ['bmp'],
            ['jpg2000'],
        ];
    }

    /**
     * @dataProvider getWrongFormats
     */
    public function testWrongFormats(string $format): void
    {
        $options = new ImageOutputOptions();
        $this->expectException(\InvalidArgumentException::class);
        $wrong = $options->withFormat($format);
    }

    public function getWrongQualites(): array
    {
        return [
            [-1],
            [101],
            [102],
        ];
    }

    /**
     * @dataProvider getWrongQualites
     */
    public function testWrongQualities(int $quality): void
    {
        $options = new ImageOutputOptions();
        $this->expectException(\InvalidArgumentException::class);
        $wrong = $options->withQuality($quality);
    }

    public function testFormatConvert(): void
    {
        $jpg = $this->createTestImageStream(10, 10);
        $png = $this->images->convertToFormat(
            $jpg,
            'png'
        );

        $this->assertEquals(self::IMAGE_PNG, $this->getImageTypeFromStream($png->getStream()));
        $size = $this->getImageSizeFromStream($png->getStream());
        $this->assertEquals(10, $size[self::W]);
        $this->assertEquals(10, $size[self::H]);

        // With Dimensions
        $jpg = $this->createTestImageStream(10, 10);
        $png = $this->images->convertToFormat(
            $jpg,
            'png',
            20,
            20
        );

        $this->assertEquals(self::IMAGE_PNG, $this->getImageTypeFromStream($png->getStream()));
        $size = $this->getImageSizeFromStream($png->getStream());
        $this->assertEquals(20, $size[self::W]);
        $this->assertEquals(20, $size[self::H]);
    }

    public function testFailed(): void
    {
        $false_stream = Streams::ofString('false');
        $images = new Images(
            false,
            false
        );

        $resized = $images->resizeToFixedSize(
            $false_stream,
            5,
            5
        );
        $this->assertFalse($resized->isOK());
        $this->assertInstanceOf(\Throwable::class, $resized->getThrowableIfAny());
    }

    public function getColors(): array
    {
        return [
            [null],
            ['#000000'],
            ['#ff0000'],
            ['#00ff00'],
            ['#0000ff'],
            ['#ffffff'],
            ['#A3BF5A'],
            ['#E9745A'],
            ['#5A5AE9'],
            ['#5AE9E9'],
            ['#E95AE9'],
            ['#E9E95A'],
        ];
    }

    private function colorDiff(string $hex_color_one, string $hex_color_two): int
    {
        preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex_color_one, $rgb_one);
        preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex_color_two, $rgb_two);

        return abs(hexdec($rgb_one[1]) - hexdec($rgb_two[1]))
            + abs(hexdec($rgb_one[2]) - hexdec($rgb_two[2]))
            + abs(hexdec($rgb_one[3]) - hexdec($rgb_two[3]));
    }

    /**
     * @dataProvider getColors
     */
    public function testBackgroundColor(?string $color): void
    {
        $transparent_png = __DIR__ . '/img/transparent.png';
        $this->assertFileExists($transparent_png);
        $png = Streams::ofResource(fopen($transparent_png, 'rb'));

        $converter_options = (new ImageConversionOptions())
            ->withThrowOnError(true)
            ->withFixedDimensions(100, 100);

        if ($color !== null) {
            $converter_options = $converter_options->withBackgroundColor($color);
        } else {
            $color = '#ffffff';
        }

        $output_options = (new ImageOutputOptions())
            ->withQuality(100)
            ->withJpgOutput();

        $converter = new ImageConverter($converter_options, $output_options, $png);
        $this->assertTrue($converter->isOK());
        $converted_stream = $converter->getStream();
        $gd_image = imagecreatefromstring((string)$converted_stream);
        $colors = imagecolorsforindex($gd_image, imagecolorat($gd_image, 1, 1));

        $color_in_converted_picture = sprintf("#%02x%02x%02x", $colors['red'], $colors['green'], $colors['blue']);
        $color_diff = $this->colorDiff($color, $color_in_converted_picture);

        $this->assertLessThan(3, $color_diff);
    }

    public function testWriteImage(): void
    {
        $img = $this->createTestImageStream(10, 10);

        $output_path = __DIR__ . '/img/output.jpg';
        $converter_options = (new ImageConversionOptions())
            ->withThrowOnError(true)
            ->withMakeTemporaryFiles(false)
            ->withFixedDimensions(100, 100)
            ->withOutputPath($output_path);

        $output_options = (new ImageOutputOptions())
            ->withQuality(10)
            ->withJpgOutput();

        $converter = new ImageConverter($converter_options, $output_options, $img);
        $this->assertTrue($converter->isOK());

        $this->assertFileExists($output_path);
        $stream = $converter->getStream();
        $this->assertEquals($output_path, $stream->getMetadata('uri'));

        unlink($output_path);
    }


    protected function checkImagick(): void
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('Imagick not installed');
        }
    }

    protected function getImageSizeFromStream(FileStream $stream): array
    {
        $getimagesizefromstring = getimagesizefromstring((string)$stream);
        return [
            self::W => (int)round($getimagesizefromstring[0]),
            self::H => (int)round($getimagesizefromstring[1])
        ];
    }

    protected function getImageTypeFromStream(FileStream $stream): string
    {
        return finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $stream->read(255));
    }

    protected function getImageQualityFromStream(FileStream $stream): int
    {
        $stream->rewind();
        $img = new \Imagick();
        $img->readImageBlob((string)$stream);

        return $img->getImageCompressionQuality();
    }

    protected function createTestImageStream(int $width, int $height): FileStream
    {
        $img = new \Imagick();
        $img->newImage($width, $height, new \ImagickPixel('black'));
        $img->setImageFormat('jpg');

        $stream = Streams::ofString($img->getImageBlob());
        $stream->rewind();
        return $stream;
    }
}
