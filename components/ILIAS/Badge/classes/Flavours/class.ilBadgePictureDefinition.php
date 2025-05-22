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

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;

class ilBadgePictureDefinition implements FlavourDefinition
{
    private const ID = 'badge_image_resize_flavor';

    private int $quality = 50;
    /** @var array{"xl": int, "l": int, "m": int, "s": int, "xs": int} */
    private array $widths = [
        'xl' => 1920,
        'l' => 960,
        'm' => 480,
        's' => 240,
        'xs' => 120
    ];

    public function getId(): string
    {
        return self::ID;
    }

    public function getFlavourMachineId(): string
    {
        return ilBadgePictureMachine::ID;
    }

    public function getInternalName(): string
    {
        return 'badge_picture';
    }

    public function getVariantName(): ?string
    {
        return json_encode([
            'quality' => $this->quality,
            'sizes' => $this->widths
        ], JSON_THROW_ON_ERROR);
    }

    public function persist(): bool
    {
        return true;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @return array{"xl": int, "l": int, "m": int, "s": int, "xs": int}
     */
    public function getWidths(): array
    {
        return $this->widths;
    }
}
