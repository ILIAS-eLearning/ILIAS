<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Data\Row;

/**
 * Class PropertyRowData
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Data\Row
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PropertyRowData extends AbstractRowData
{

    /**
     * @inheritDoc
     */
    public function __invoke(string $key)
    {
        return $this->getOriginalData()->{$key};
    }
}
