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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\ComponentDecoratorTrait;
use ILIAS\GlobalScreen\Scope\VisibilityAvailabilityTrait;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractBaseItem implements isItem
{
    use ComponentDecoratorTrait;
    use VisibilityAvailabilityTrait;

    private int $position = 0;

    public function __construct(private IdentificationInterface $provider_identification)
    {
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function withPosition(int $position): isItem
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function getProviderIdentification(): IdentificationInterface
    {
        return $this->provider_identification;
    }

}
