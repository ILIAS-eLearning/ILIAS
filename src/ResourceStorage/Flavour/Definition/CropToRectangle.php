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

use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropRectangle as MaxRectangleSizeMachine;

/**
 * @author       Stephan Kergomard <webmaster@kergomard.ch>
 * @noinspection AutoloadingIssuesInspection
 */
class CropToRectangle implements FlavourDefinition
{
    public const FOREVER_ID = '0fca4b6cf274bd4aac78caef9494f6f19e67366118548d5556f89fda5f683826';

    public function __construct(
        protected bool $persist = false,
        protected int $max_width = 512,
        protected float $ratio = 16 / 9,
        protected int $quality = 75
    ) {
    }

    public function getId(): string
    {
        return self::FOREVER_ID;
    }


    public function getFlavourMachineId(): string
    {
        return MaxRectangleSizeMachine::ID;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function getMaxWidth(): int
    {
        return $this->max_width;
    }

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function getInternalName(): string
    {
        return 'crop_to_rectangle';
    }

    public function getVariantName(): ?string
    {
        return $this->max_size . '_' . $this->ratio . '_' . $this->quality;
    }

    public function persist(): bool
    {
        return $this->persist;
    }
}
