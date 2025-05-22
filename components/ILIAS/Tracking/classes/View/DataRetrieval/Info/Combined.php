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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval\Info;

use ILIAS\Tracking\View\DataRetrieval\Info\CombinedInterface;

class Combined implements CombinedInterface
{
    public function __construct(
        protected LPInterface $lp_info,
        protected ObjectDataInterface $obj_info
    ) {
    }

    public function getLPInfo(): LPInterface
    {
        return $this->lp_info;
    }

    public function getObjectInfo(): ObjectDataInterface
    {
        return $this->obj_info;
    }
}
