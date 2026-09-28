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
 * Assets of the shipped default skin, read from the built public asset folder.
 *
 * The relative paths mirror the targets the PublicAssetManager writes to, see
 * {@see \ILIAS\Component\Resource\ComponentCSS::TARGET}. The public directory is passed
 * in, so that this class holds no assumption about the installation layout.
 */
final readonly class PublishedMailAssets implements MailAssets
{
    private const string STYLE_SHEET = 'assets/css/mail.css';
    private const string LOGO = 'assets/images/logo/HeaderIcon.svg';

    public function __construct(private string $public_directory)
    {
    }

    public function styleSheet(): AssetFile
    {
        $path = $this->public_directory . '/' . self::STYLE_SHEET;

        if (!is_file($path) || !is_readable($path)) {
            throw new MailAssetUnavailable(
                "Mail style sheet is not available at '{$path}'. Has the public asset folder been built?"
            );
        }

        return new AssetFile($path);
    }

    public function inlineImages(): InlineImages
    {
        $path = $this->public_directory . '/' . self::LOGO;

        if (!is_file($path) || !is_readable($path)) {
            return InlineImages::none();
        }

        return InlineImages::none()->withLogo(InlineImage::at($path));
    }
}
