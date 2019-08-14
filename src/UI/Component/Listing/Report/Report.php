<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Report;

use ILIAS\UI\Component\Component;

/**
 * Interface Report
 * @package ILIAS\UI\Component\Listing\Report
 */
interface Report extends Component
{
    /**
     * Gets the key value pair as items for the list. Key is used as label for the value.
     * @return array $items string => Component | string
     */
    public function getItems();
}