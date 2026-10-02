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

namespace ILIAS\Search\Presentation\Result\ViewControls;

use ILIAS\Search\GUI\Global\Param;
use ILIAS\Data\URI;
use ILIAS\Search\Presentation\Result\Sortation;

class SortationInfosImpl implements SortationInfos
{
    public function __construct(
        protected Sortation $sortation,
        protected URI $sortation_action,
        protected Param $sortation_param
    ) {
    }

    public function sortation(): Sortation
    {
        return $this->sortation;
    }

    public function sortationAction(): URI
    {
        return $this->sortation_action;
    }

    public function sortationParam(): Param
    {
        return $this->sortation_param;
    }
}
