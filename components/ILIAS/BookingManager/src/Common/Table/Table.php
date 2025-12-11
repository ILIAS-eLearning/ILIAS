<?php

namespace ILIAS\BookingManager\Common\Table;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\URLBuilder;

interface Table extends DataRetrieval
{
    public function execute(URLBuilder $url_builder): mixed;

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array;
}
