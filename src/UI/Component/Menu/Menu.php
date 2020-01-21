<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Menu;

use ILIAS\UI\Component\Component;

/**
 * This describes a Menu Control
 */
interface Menu extends Component
{
    /**
     * @return Component[]
     */
    public function getItems() : array;
}
