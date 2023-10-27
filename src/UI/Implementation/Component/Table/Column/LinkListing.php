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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;
use ILIAS\UI\Component\Link\Standard;
use ILIAS\UI\Component\Listing\Ordered;
use ILIAS\UI\Component\Listing\Unordered;
use ILIAS\UI\Component\Component;

class LinkListing extends Column implements C\LinkListing
{
    public function format($value): string|Component
    {
        $value_arr = [$value];
        $types = [Ordered::class, Unordered::class];
        $this->checkArgListElements("value", $value_arr, $types);
        $check = $value->getItems();
        $this->checkArgListElements("list items", $check, Standard::class);
        return $value;
    }
}
