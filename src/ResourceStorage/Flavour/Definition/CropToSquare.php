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

namespace ILIAS\ResourceStorage\Flavour\Definition;

use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropSquare as MaxSquareSizeMachine;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class CropToSquare implements FlavourDefinition
{
    public const FOREVER_ID = '013dd0d556b5716fe54f554623f92449bd0c86d80c698eaf3959b057b7a069a0';
    protected int $max_size = 500;
    protected int $quality = 50;

    public function __construct(int $max_size = null)
    {
        $this->max_size = $max_size ?? $this->max_size;
    }

    public function getId(): string
    {
        return self::FOREVER_ID;
    }


    public function getFlavourMachineId(): string
    {
        return MaxSquareSizeMachine::ID;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function getMaxSize(): int
    {
        return $this->max_size;
    }

    public function getInternalName(): string
    {
        return 'crop_to_square';
    }

    public function getVariantName(): ?string
    {
        return $this->max_size . 'x' . $this->max_size;
    }

    public function persist(): bool
    {
        return false;
    }
}
