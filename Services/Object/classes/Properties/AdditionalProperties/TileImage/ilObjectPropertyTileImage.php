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

use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\FileUpload\MimeType;

/**
 * @author Stephan Kergomard
 */
class ilObjectPropertyTileImage implements ilObjectProperty
{
    public const SUPPORTED_MIME_TYPES = [MimeType::IMAGE__PNG, MimeType::IMAGE__JPEG];
    private const SUPPORTED_FILE_EXTENSIONS = ['png', 'jpg', 'jpeg'];

    protected const INPUT_LABEL = 'obj_tile_image';
    protected const INPUT_BYLINE = 'obj_tile_image_info';

    private bool $deleted_flag = false;
    private ?string $temp_file_name = null;

    public function __construct(
        private ?ilObjectTileImage $tile_image = null
    ) {
    }

    public function getTileImage(): ?ilObjectTileImage
    {
        return $this->tile_image;
    }

    public function getDeletedFlag(): bool
    {
        return $this->deleted_flag;
    }

    public function withDeletedFlag(): self
    {
        $clone = clone $this;
        $clone->deleted_flag = true;
        return $clone;
    }

    public function getTempFileName(): ?string
    {
        return $this->temp_file_name;
    }

    public function withTempFileName(string $name): self
    {
        $clone = $this;
        $clone->temp_file_name = $name;
        return $clone;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): File {
        $trafo = $refinery->custom()->transformation(
            function ($v): ?ilObjectProperty {
                $property_tile_image = new ilObjectPropertyTileImage(
                    $this->tile_image
                );

                if (count($v) > 0 && $v[0] !== 'tile_image') {
                    return $property_tile_image
                        ->withTempFileName($v[0]);
                }

                if (count($v) === 0 && $this->tile_image->exists()) {
                    return $property_tile_image
                        ->withDeletedFlag();
                }

                return $property_tile_image;
            }
        );

        $tile_image = $field_factory
            ->file(new ilObjectTileImageUploadHandlerGUI($this->tile_image), $language->txt(self::INPUT_LABEL), $language->txt(self::INPUT_BYLINE))
            ->withAcceptedMimeTypes(self::SUPPORTED_MIME_TYPES)
            ->withMaxFileSize((int) ilFileUtils::getUploadSizeLimitBytes())
            ->withAdditionalTransformation($trafo);

        if (!$this->tile_image->exists()) {
            return $tile_image;
        }

        return $tile_image->withValue(['tile_image']);
    }

    public function toLegacyForm(
        \ilLanguage $language
    ): ilImageFileInputGUI {
        $timg = new ilImageFileInputGUI($language->txt(self::INPUT_LABEL), 'tile_image');
        $timg->setInfo($language->txt(self::INPUT_BYLINE));
        $timg->setSuffixes(self::SUPPORTED_FILE_EXTENSIONS);
        $timg->setUseCache(false);
        if ($this->tile_image->exists()) {
            $timg->setImage($this->tile_image->getFullPath());
        } else {
            $timg->setImage('');
        }
        return $timg;
    }
}
