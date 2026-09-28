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

use ILIAS\Mail\Mime\Presentation\Asset\AssetFile;
use ILIAS\Mail\Mime\Presentation\Asset\InlineImage;
use ILIAS\Mail\Mime\Presentation\Asset\InlineImages;
use ILIAS\Mail\Mime\Presentation\Asset\LogoByFileName;
use ILIAS\Mail\Mime\Presentation\Asset\MailAssets;
use ILIAS\Mail\Mime\Presentation\Asset\SkinnedMailAssets;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

final class SkinnedMailAssetsTest extends TestCase
{
    private const string SKIN = 'custom';
    private const string STYLE = 'delos';
    private const string DEFAULT_SKIN = 'default';
    private const string SKIN_DIRECTORY = 'skins';
    private const string STYLE_SHEET = 'mail.css';
    private const string FALLBACK_LOGO = 'default.png';
    private const string SKIN_LOGO = 'skin.png';
    private const string STYLE_LOGO = 'style.png';
    private const string NON_IMAGE = 'readme.txt';

    public function testStyleSheetUsesTheStyleWhenStyleAndSkinCssExist(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                self::STYLE_SHEET => 'skin-css',
                self::STYLE => [
                    self::STYLE_SHEET => 'style-css',
                ],
            ],
        ]);

        $style_sheet = $this->sut($this->fallback())->styleSheet();

        $this->assertSame($this->styleCssPath(), $style_sheet->path());
    }

    public function testStyleSheetUsesTheSkinWhenOnlySkinCssExists(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                self::STYLE_SHEET => 'skin-css',
                self::STYLE => [],
            ],
        ]);

        $style_sheet = $this->sut($this->fallback())->styleSheet();

        $this->assertSame($this->skinCssPath(), $style_sheet->path());
    }

    public function testStyleSheetUsesTheFallbackWhenNoCustomCssExists(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                self::STYLE => [],
            ],
        ]);

        $style_sheet = $this->sut($this->fallback())->styleSheet();

        $this->assertSame($this->fallbackCssPath(), $style_sheet->path());
    }

    public function testInlineImagesUseTheSkinWhenTheSkinHasALogo(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                'images' => [
                    'logo' => [
                        self::SKIN_LOGO => 'png',
                    ],
                ],
                self::STYLE => [
                    'images' => [
                        'logo' => [],
                    ],
                ],
            ],
        ]);

        $images = $this->sut($this->fallback())->inlineImages();

        $this->assertCount(1, $images);
        $this->assertSame(self::SKIN_LOGO, $images->first()?->name());
        $this->assertSame($this->skinLogoPath(), $images->first()?->path());
    }

    public function testInlineImagesUseTheStyleWhenOnlyTheStyleHasALogo(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                'images' => [
                    'logo' => [],
                ],
                self::STYLE => [
                    'images' => [
                        'logo' => [
                            self::STYLE_LOGO => 'png',
                        ],
                    ],
                ],
            ],
        ]);

        $images = $this->sut($this->fallback())->inlineImages();

        $this->assertCount(1, $images);
        $this->assertSame(self::STYLE_LOGO, $images->first()?->name());
        $this->assertSame($this->styleLogoPath(), $images->first()?->path());
    }

    public function testInlineImagesUseTheFallbackWhenCustomLogoDirectoriesAreEmpty(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                'images' => [
                    'logo' => [],
                ],
                self::STYLE => [
                    'images' => [
                        'logo' => [],
                    ],
                ],
            ],
        ]);

        $fallback_images = $this->fallbackImages();
        $images = $this->sut($this->fallback($fallback_images))->inlineImages();

        $this->assertSame($fallback_images, $images);
    }

    public function testInlineImagesUseTheFallbackWhenTheSkinIsDefault(): void
    {
        $this->setUpVfs([
            self::DEFAULT_SKIN => [
                'images' => [
                    'logo' => [
                        self::SKIN_LOGO => 'png',
                    ],
                ],
                self::STYLE => [
                    'images' => [
                        'logo' => [
                            self::STYLE_LOGO => 'png',
                        ],
                    ],
                ],
            ],
        ]);

        $fallback_images = $this->fallbackImages();
        $images = $this->sut(
            $this->fallback($fallback_images),
            self::DEFAULT_SKIN
        )->inlineImages();

        $this->assertSame($fallback_images, $images);
    }

    public function testInlineImagesIgnoreNonImageFiles(): void
    {
        $this->setUpVfs([
            self::SKIN => [
                'images' => [
                    'logo' => [
                        self::SKIN_LOGO => 'png',
                        self::NON_IMAGE => 'not-an-image',
                    ],
                ],
            ],
        ]);

        $images = $this->sut($this->fallback())->inlineImages();

        $this->assertCount(1, $images);
        $this->assertSame(self::SKIN_LOGO, $images->first()?->name());
    }

    /**
     * @param array<string, mixed> $skin_contents
     */
    private function setUpVfs(array $skin_contents): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfsStream::setup();
        vfsStream::create([
            'fallback' => [
                self::STYLE_SHEET => 'fallback-css',
                'images' => [
                    'logo' => [
                        self::FALLBACK_LOGO => 'png',
                    ],
                ],
            ],
            self::SKIN_DIRECTORY => $skin_contents,
        ]);
    }

    private function sut(
        MailAssets $fallback,
        string $skin = self::SKIN,
        string $style = self::STYLE
    ): SkinnedMailAssets {
        return new SkinnedMailAssets(
            $fallback,
            vfsStream::url('root/' . self::SKIN_DIRECTORY),
            new LogoByFileName(),
            $skin,
            $style
        );
    }

    private function fallback(?InlineImages $images = null): MailAssets
    {
        $fallback = $this->createStub(MailAssets::class);
        $fallback->method('styleSheet')->willReturn(new AssetFile($this->fallbackCssPath()));
        $fallback->method('inlineImages')->willReturn($images ?? $this->fallbackImages());

        return $fallback;
    }

    private function fallbackImages(): InlineImages
    {
        return InlineImages::of(InlineImage::at($this->fallbackLogoPath()));
    }

    private function fallbackCssPath(): string
    {
        return vfsStream::url('root/fallback/' . self::STYLE_SHEET);
    }

    private function skinCssPath(): string
    {
        return vfsStream::url('root/' . self::SKIN_DIRECTORY . '/' . self::SKIN . '/' . self::STYLE_SHEET);
    }

    private function styleCssPath(): string
    {
        return vfsStream::url(
            'root/' . self::SKIN_DIRECTORY . '/' . self::SKIN . '/' . self::STYLE . '/' . self::STYLE_SHEET
        );
    }

    private function fallbackLogoPath(): string
    {
        return vfsStream::url('root/fallback/images/logo/' . self::FALLBACK_LOGO);
    }

    private function skinLogoPath(): string
    {
        return vfsStream::url(
            'root/' . self::SKIN_DIRECTORY . '/' . self::SKIN . '/images/logo/' . self::SKIN_LOGO
        );
    }

    private function styleLogoPath(): string
    {
        return vfsStream::url(
            'root/' . self::SKIN_DIRECTORY . '/' . self::SKIN . '/' . self::STYLE . '/images/logo/' . self::STYLE_LOGO
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
