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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilUserProfilePictureDefinition implements FlavourDefinition
{
    private const ID = '90ffcb9513daf30de2f5b5de82d7edee162f5dbc9fd16713810cf75326849dc2';

    private int $quality = 50;

    private array $sizes = [
        'big' => 512,
        'small' => 100,
        'xsmall' => 75,
        'xxsmall' => 30
    ];

    public function __construct(
    ) {
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getFlavourMachineId(): string
    {
        return ilUserProfilePictureMachine::ID;
    }

    public function getInternalName(): string
    {
        return 'profile_picture';
    }

    public function getVariantName(): ?string
    {
        return json_encode([
            'quality' => $this->quality,
            'sizes' => $this->sizes
        ]);
    }

    public function persist(): bool
    {
        return true;
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }
}
