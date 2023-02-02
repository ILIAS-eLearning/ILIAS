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
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\Data\URI;
use ILIAS\Data\Meta\Html\OpenGraph\Factory;
use ILIAS\Data\Meta\Html\OpenGraph\Link;
use ILIAS\Data\Meta\Html\OpenGraph\Text;
use ILIAS\Data\Meta\Html\OpenGraph\Resource;
use ILIAS\Data\Meta\Html\OpenGraph\Image;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class OpenGraphMetadataTest extends TestCase
{
    protected Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    public function testTextTag(): void
    {
        $property = 'test_property';
        $value = 'test_value';

        $link_tag = new Text($property, $value);

        $this->assertEquals(
            "<meta property=\"$property\" content=\"$value\" />",
            $link_tag->toHtml()
        );
    }

    public function testLinkTag(): void
    {
        $property = 'test_property';
        $value = 'test_value';

        $link_tag = new Link($property, $this->getMockedUrl($value));

        $this->assertEquals(
            "<meta property=\"$property\" content=\"$value\" />",
            $link_tag->toHtml()
        );
    }

    public function testAudioTag(): void
    {
        $expected_url = 'test_url';
        $expected_mime = 'test_mime_type';
        $expected_html =
            "<meta property=\"og:audio\" content=\"$expected_url\" />" . PHP_EOL .
            "<meta property=\"og:audio:type\" content=\"$expected_mime\" />" . PHP_EOL;

        $audio_tag = $this->factory->audio($this->getMockedUrl($expected_url), $expected_mime);

        $this->assertEquals($expected_html, $audio_tag->toHtml());
    }

    public function testImageTag(): void
    {
        $expected_url = 'test_url';
        $expected_mime = 'test_mime_type';
        $expected_alt = 'test_aria_label';
        $expected_width = 200;
        $expected_height = 100;
        $expected_html =
            "<meta property=\"og:image\" content=\"$expected_url\" />" . PHP_EOL .
            "<meta property=\"og:image:type\" content=\"$expected_mime\" />" . PHP_EOL .
            "<meta property=\"og:image:alt\" content=\"$expected_alt\" />" . PHP_EOL .
            "<meta property=\"og:image:width\" content=\"$expected_width\" />" . PHP_EOL .
            "<meta property=\"og:image:height\" content=\"$expected_height\" />" . PHP_EOL;

        $image_tag = $this->factory->image(
            $this->getMockedUrl($expected_url),
            $expected_mime,
            $expected_alt,
            $expected_width,
            $expected_height
        );

        $this->assertEquals($expected_html, $image_tag->toHtml());
    }

    public function testVideoTag(): void
    {
        $expected_url = 'test_url';
        $expected_mime = 'test_mime_type';
        $expected_width = 200;
        $expected_height = 100;
        $expected_html =
            "<meta property=\"og:video\" content=\"$expected_url\" />" . PHP_EOL .
            "<meta property=\"og:video:type\" content=\"$expected_mime\" />" . PHP_EOL .
            "<meta property=\"og:video:width\" content=\"$expected_width\" />" . PHP_EOL .
            "<meta property=\"og:video:height\" content=\"$expected_height\" />" . PHP_EOL;

        $video_tag = $this->factory->video(
            $this->getMockedUrl($expected_url),
            $expected_mime,
            $expected_width,
            $expected_height
        );

        $this->assertEquals($expected_html, $video_tag->toHtml());
    }

    public function testWebsiteTag(): void
    {
        $expected_canonical_url = 'test_canonical_url';
        $expected_image_html = 'test_image_html';
        $expected_object_title = 'test_object_title';
        $expected_website_name = 'test_website_name';
        $expected_description = 'test_description';
        $expected_locale = 'test_locale';
        $expected_locale_alt_1 = 'test_locale_alt_1';
        $expected_locale_alt_2 = 'test_locale_alt_2';
        $expected_additional_resource_html_1 = 'test_additional_resource_html_1';
        $expected_additional_resource_html_2 = 'test_additional_resource_html_2';

        $expected_html =
            "<meta property=\"og:type\" content=\"website\" />" . PHP_EOL .
            "<meta property=\"og:title\" content=\"test_object_title\" />" . PHP_EOL .
            "<meta property=\"og:url\" content=\"test_canonical_url\" />" . PHP_EOL .
            $expected_image_html . PHP_EOL .
            "<meta property=\"og:site_title\" content=\"test_website_name\" />" . PHP_EOL .
            "<meta property=\"og:description\" content=\"test_description\" />" . PHP_EOL .
            "<meta property=\"og:locale\" content=\"test_locale\" />" . PHP_EOL .
            "<meta property=\"og:locale:alternative\" content=\"test_locale_alt_1\" />" . PHP_EOL .
            "<meta property=\"og:locale:alternative\" content=\"test_locale_alt_2\" />" . PHP_EOL .
            $expected_additional_resource_html_1 . PHP_EOL .
            $expected_additional_resource_html_2 . PHP_EOL;

        $website_tag = $this->factory->website(
            $this->getMockedUrl($expected_canonical_url),
            $this->getMockedImage($expected_image_html),
            $expected_object_title,
            $expected_website_name,
            $expected_description,
            $expected_locale,
            [
                $expected_locale_alt_1,
                $expected_locale_alt_2,
            ],
            [
                $this->getMockedResource($expected_additional_resource_html_1),
                $this->getMockedResource($expected_additional_resource_html_2),
            ]
        );

        $this->assertEquals($expected_html, $website_tag->toHtml());
    }

    protected function getMockedResource(string $html): Resource
    {
        $mock_resource = $this->createMock(Resource::class);
        $mock_resource->method('toHtml')->willReturn($html);
        $mock_resource->method('getTags')->willReturnCallback(
            static function () use ($mock_resource): Generator {
                yield $mock_resource;
            }
        );

        return $mock_resource;
    }

    protected function getMockedImage(string $html): Image
    {
        $mock_image = $this->createMock(Image::class);
        $mock_image->method('toHtml')->willReturn($html);
        $mock_image->method('getTags')->willReturnCallback(
            static function () use ($mock_image): Generator {
                yield $mock_image;
            }
        );

        return $mock_image;
    }

    protected function getMockedUrl(string $url): URI
    {
        $mock_url = $this->createMock(URI::class);
        $mock_url->method('__toString')->willReturn($url);

        return $mock_url;
    }
}
