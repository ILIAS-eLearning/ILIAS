<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Table;

use Exception;

interface RowFactory
{
    /**
     * @throws Exception if record cannot be processed to row
     */
    public function map(array $record) : array;
}
