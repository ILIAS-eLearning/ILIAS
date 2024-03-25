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
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API.
 */
class ZipStructureDefinition implements FlavourDefinition
{
    public function getId(): string
    {
        return 'a6ac86ca80c33ac3e5bfd4fc8da30f05a888e4cfb17fda479ba9b6f08b1f33ba';
    }

    public function getFlavourMachineId(): string
    {
        return 'zip_structure_reader';
    }

    public function getInternalName(): string
    {
        return 'Container Structure';
    }

    public function getVariantName(): ?string
    {
        return null;
    }

    public function persist(): bool
    {
        return true;
    }

    public function sleep(array $data): string
    {
        return json_encode($data);
    }

    public function wake(string $data): array
    {
        return json_decode($data, true);
    }

}
