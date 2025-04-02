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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Flavour\Engine\ExifEngine;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class CropSquare extends AbstractMachine implements FlavourMachine
{
    use GdImageToStreamTrait;

    public const ID = 'crop_square';
    public const QUALITY = 30;

    public function getId(): string
    {
        return self::ID;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof CropToSquare;
    }

    public function dependsOnEngine(): ?string
    {
        return GDEngine::class;
    }

    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        if (!$for_definition instanceof \ILIAS\ResourceStorage\Flavour\Definition\CropToSquare) {
            throw new \InvalidArgumentException('Invalid definition');
        }
        $image = $this->from($stream);
        if (!is_resource($image) && !$image instanceof \GdImage) {
            return;
        }

        $stream_path = $stream->getMetadata()['uri'] ?? '';
        $must_flip = $this->maybeRotate($stream_path, $image);

        if ($stream_path === 'php://memory') {
            [$width, $height] = getimagesizefromstring((string) $stream);
        } else {
            [$width, $height] = getimagesize($stream_path);
        }

        if ($must_flip) {
            $tmp = $width;
            $width = $height;
            $height = $tmp;
        }

        if ($width > $height) {
            $y = 0;
            $x = (int) (($width - $height) / 2);
            $smallest_side = (int) $height;
        } else {
            $x = 0;
            $y = (int) (($height - $width) / 2);
            $smallest_side = (int) $width;
        }

        $size = (int) $for_definition->getMaxSize();

        $thumb = imagecreatetruecolor($size, $size);

        imagecopyresampled(
            $thumb,
            $image,
            0,
            0,
            $x,
            $y,
            $size,
            $size,
            $smallest_side,
            $smallest_side
        );

        imagedestroy($image);

        $stream = $this->to($thumb, $for_definition->getQuality());

        yield new Result(
            $for_definition,
            $stream,
            0,
            $for_definition->persist()
        );
    }

    protected function maybeRotate(string $stream_path, \GdImage &$image): bool
    {
        // if PHP exif is installed, this is quite easy
        $exif = new ExifEngine();
        if ($exif->isRunning() && ($exif_data = $exif->read($stream_path)) !== []) {
            switch ($exif_data['Orientation'] ?? null) {
                case 8:
                    $image = imagerotate($image, 90, 0);
                    return true;
                case 3:
                    $image = imagerotate($image, 180, 0);
                    return false;
                case 6:
                    $image = imagerotate($image, -90, 0);
                    return true;
                default:
                    return false;
            }
        }
        // otherwise we can use Imagick (if installed)
        $imagick = new ImagickEngine();
        if ($imagick->isRunning()) {
            $imagick = new \Imagick($stream_path);
            $image_orientation = $imagick->getImageOrientation();
            switch ($image_orientation) {
                case \Imagick::ORIENTATION_RIGHTTOP:
                    $imagick->rotateImage('none', 90);
                    $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                    $image = $this->from(Streams::ofString($imagick->getImageBlob()));
                    return true;
                case \Imagick::ORIENTATION_BOTTOMRIGHT:
                    $imagick->rotateImage('none', 180);
                    $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                    $image = $this->from(Streams::ofString($imagick->getImageBlob()));
                    return false;
                case \Imagick::ORIENTATION_LEFTBOTTOM:
                    $imagick->rotateImage('none', -90);
                    $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                    $image = $this->from(Streams::ofString($imagick->getImageBlob()));
                    return true;
                default:
                    return false;
            }
        }

        // we did not find any way to rotate the image
        return false;
    }
}
