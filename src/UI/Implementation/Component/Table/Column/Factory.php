<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as I;
use ILIAS\UI\NotImplementedException;
use ILIAS\Data\DateFormat\DateFormat;

class Factory implements I\Factory
{
    public function text(string $title): I\Text
    {
        throw new NotImplementedException('NYI');
    }

    public function number(string $title): I\Number
    {
        throw new NotImplementedException('NYI');
    }

    public function date(string $title, DateFormat $format) //:@Todo: Does not yet exit
    {
        throw new NotImplementedException('NYI');
    }
}
