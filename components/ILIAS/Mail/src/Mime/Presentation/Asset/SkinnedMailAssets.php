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

namespace ILIAS\Mail\Mime\Presentation\Asset;

/**
 * Prefers the mail assets of a Customizing skin and falls back to the assets it
 * decorates. Adding a location is a new decoration, not a change here.
 */
final class SkinnedMailAssets implements MailAssets
{
    private const string STYLE_SHEET = 'mail.css';
    private const string LOGO_DIRECTORY = 'images/logo';
    private const string IMAGE_PATTERN = '/\.(jpg|jpeg|gif|svg|png)$/i';

    /** @var list<string> */
    private readonly array $locations;

    public function __construct(
        private readonly MailAssets $fallback,
        private readonly string $skin_directory,
        private readonly LogoByFileName $logo,
        string $skin,
        string $style
    ) {
        $this->locations = $skin === 'default' ? [] : [$skin, $skin . '/' . $style];
    }

    public function styleSheet(): AssetFile
    {
        foreach (array_reverse($this->locations) as $location) {
            $path = $this->skin_directory . '/' . $location . '/' . self::STYLE_SHEET;

            if (is_file($path) && is_readable($path)) {
                return new AssetFile($path);
            }
        }

        return $this->fallback->styleSheet();
    }

    public function inlineImages(): InlineImages
    {
        $images = InlineImages::none();

        foreach ($this->locations as $location) {
            $directory = $this->skin_directory . '/' . $location . '/' . self::LOGO_DIRECTORY;

            if (is_dir($directory) && is_readable($directory)) {
                $images = $images->with(...$this->imagesIn($directory));
            }
        }

        if ($images->isEmpty()) {
            return $this->fallback->inlineImages();
        }

        return $this->logo->markIn($images);
    }

    /**
     * @return list<InlineImage>
     */
    private function imagesIn(string $directory): array
    {
        $paths = [];
        $files = new \RegexIterator(
            new \FilesystemIterator(
                $directory,
                \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
            ),
            self::IMAGE_PATTERN
        );

        foreach ($files as $path) {
            if (is_file($path) && is_readable($path)) {
                $paths[] = $path;
            }
        }

        sort($paths);

        return array_map(static fn(string $path): InlineImage => InlineImage::at($path), $paths);
    }
}
