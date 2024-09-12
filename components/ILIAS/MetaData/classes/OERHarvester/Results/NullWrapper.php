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

namespace ILIAS\MetaData\OERHarvester\Results;

class NullWrapper implements WrapperInterface
{
    public function get(): \ilCronJobResult
    {
        return new \ilCronJobResult();
    }

    public function withStatus(int $status): WrapperInterface
    {
        return $this;
    }

    public function withMessage(string $message): WrapperInterface
    {
        return $this;
    }
}
