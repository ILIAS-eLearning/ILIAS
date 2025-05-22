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

namespace ILIAS\Poll\Image\Repository\Values;

use ILIAS\Poll\Image\I\Repository\Values\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Values\HandlerInterface as ilPollImageRepositoryValuesInterface;
use ILIAS\Poll\Image\Repository\Values\Handler as ilPollImageRepositoryValues;

class Factory implements ilPollImageRepositoryFactoryInterface
{
    public function handler(): ilPollImageRepositoryValuesInterface
    {
        return new ilPollImageRepositoryValues();
    }
}
