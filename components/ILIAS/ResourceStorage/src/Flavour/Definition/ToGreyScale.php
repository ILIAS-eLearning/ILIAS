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

use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\MakeGreyScale as GreyScaleMachine;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ToGreyScale implements \ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition
{
    public const FOREVER_ID = '0afbf77b53955882c43b6673251261583f3d52ed5e980b6ea6c869c065991406';

    public function __construct(
        protected bool $persist = false,
        protected int $quality = 75
    ) {
    }

    public function getFlavourMachineId(): string
    {
        return GreyScaleMachine::ID;
    }

    public function getId(): string
    {
        return self::FOREVER_ID;
    }


    public function getInternalName(): string
    {
        return 'greyscale';
    }

    public function getVariantName(): ?string
    {
        return null;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function persist(): bool
    {
        return $this->persist;
    }
}
