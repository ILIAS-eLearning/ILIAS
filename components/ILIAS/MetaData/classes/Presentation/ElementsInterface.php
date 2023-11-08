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

namespace ILIAS\MetaData\Presentation;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Elements\ElementInterface;

interface ElementsInterface
{
    public function name(
        BaseElementInterface $element,
        bool $plural = false
    ): string;

    public function nameWithParents(
        BaseElementInterface $element,
        ?BaseElementInterface $cut_off = null,
        bool $plural = false,
        bool $skip_initial = false
    ): string;
}
