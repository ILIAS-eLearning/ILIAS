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

namespace ILIAS\Object\Properties\CoreProperties\TileImage;

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;

class ilObjectTileImageFlavourDefinition implements FlavourDefinition
{
    private const ID = '5ae5cfe34279f9edfade75fdec92ad55757dfe4fc99722a32dc53cc3d7c72fe4';

    private int $quality = 70;

    private array $widths = [
        'xl' => 1920,
        'l' => 960,
        'm' => 480,
        's' => 240,
        'xs' => 120
    ];

    private float $ratio = 3 / 2;

    public function __construct(
    ) {
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getFlavourMachineId(): string
    {
        return ilObjectTileImageFlavourMachine::ID;
    }

    public function getInternalName(): string
    {
        return 'object_tile_image';
    }

    public function getVariantName(): ?string
    {
        return json_encode([
            'width' => $this->widths,
            'ratio' => $this->ratio,
            'quality' => $this->quality
        ]);
    }

    public function persist(): bool
    {
        return true;
    }

    public function getWidths(): array
    {
        return $this->widths;
    }

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }
}
