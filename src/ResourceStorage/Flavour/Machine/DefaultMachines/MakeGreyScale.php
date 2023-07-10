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
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\ToGreyScale;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class MakeGreyScale extends AbstractMachine implements \ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine
{
    use GdImageToStreamTrait;

    public const ID = 'greyscale';

    public function getId(): string
    {
        return self::ID;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof ToGreyScale;
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
        /** @var ToGreyScale $for_definition */
        $image = $this->from($stream);
        if (!is_resource($image) && !$image instanceof \GdImage) {
            return;
        }
        imagefilter($image, IMG_FILTER_GRAYSCALE, 50, true);

        yield new Result(
            $for_definition,
            $this->to($image, $for_definition->getQuality()),
            0,
            $for_definition->persist()
        );
    }
}
