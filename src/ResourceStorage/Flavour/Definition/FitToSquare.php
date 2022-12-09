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

use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\FitSquare;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class FitToSquare implements FlavourDefinition
{
    public const FOREVER_ID = '5cd8cb02a6382208da39cc142cbf993f42995452bb6c8d9897a2e227fb238c62';

    public function __construct(
        protected bool $persist = false,
        protected int $max_size = 512,
        protected int $quality = 75
    ) {
    }

    public function getId(): string
    {
        return self::FOREVER_ID;
    }


    public function getFlavourMachineId(): string
    {
        return FitSquare::ID;
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
        return 'fit_to_square';
    }

    public function getVariantName(): ?string
    {
        return $this->max_size . '_' . $this->quality;
    }


    public function persist(): bool
    {
        return $this->persist;
    }
}
