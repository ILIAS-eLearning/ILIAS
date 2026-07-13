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

namespace ILIAS\ILIASObject\Properties\CoreProperties\TileImage;

use ILIAS\ILIASObject\Properties\Property as PropertyInterface;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Stephan Kergomard
 */
class Property implements PropertyInterface
{
    private const SUPPORTED_FILE_EXTENSIONS = ['png', 'jpg', 'jpeg'];

    protected const INPUT_LABEL = 'obj_tile_image';
    protected const INPUT_BYLINE = 'obj_tile_image_info';

    private bool $deleted_flag = false;

    public function __construct(
        private ?TileImage $tile_image = null
    ) {
    }

    public function getTileImage(): ?TileImage
    {
        return $this->tile_image;
    }

    public function withTileImage(TileImage $tile_image): self
    {
        $clone = clone $this;
        $clone->tile_image = $tile_image;
        return $clone;
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

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): File {
        $trafo = $refinery->custom()->transformation(
            function ($v): ?PropertyInterface {
                if ($v === null || $v === []) {
                    return $this->withDeletedFlag();
                }

                if (count($v) > 0 && $v[0] === 'tile_image') {
                    return $this;
                }

                return $this->withTileImage(
                    $this->tile_image->withRid($v[0])
                );
            }
        );

        $tile_image = $field_factory
            ->file(new \TileImageUploadHandlerGUI($this->tile_image), $language->txt(self::INPUT_LABEL), $language->txt(self::INPUT_BYLINE))
            ->withAcceptedMimeTypes(TileImage::SUPPORTED_MIME_TYPES)
            ->withAdditionalTransformation($trafo);

        if ($this->tile_image->getRid() === null
            || $this->tile_image->getRid() === '') {
            return $tile_image;
        }

        return $tile_image->withValue([$this->tile_image->getRid()]);
    }

    public function toLegacyForm(
        \ilLanguage $language
    ): \ilImageFileInputGUI {
        $timg = new \ilImageFileInputGUI($language->txt(self::INPUT_LABEL), 'tile_image');
        $timg->setInfo($language->txt(self::INPUT_BYLINE));
        $timg->setSuffixes(self::SUPPORTED_FILE_EXTENSIONS);
        $timg->setUseCache(false);
        $timg->setImage($this->tile_image?->getSrcUrlForLegacyForm() ?? '');
        $timg->setValue($this->tile_image?->getRid() ?? '');
        return $timg;
    }
}
