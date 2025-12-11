<?php

namespace ILIAS\BookingManager\Common\Table;

use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;

/**
 * @property TableActions $table_actions;
 */
trait TableActionExecutorTrait
{
    public function execute(URLBuilder $url_builder): ?Modal
    {
        return $this->table_actions->execute(...$this->acquireParameters($url_builder));
    }

    abstract protected function acquireParameters(URLBuilder $url_builder): array;
}
