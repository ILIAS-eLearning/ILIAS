<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Data\Row;

/**
 * Class GetterRowData
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Data\Row
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class GetterRowData extends AbstractRowData
{

    /**
     * @param string $string
     *
     * @return string
     */
    protected function strToCamelCase(string $string) : string
    {
        return str_replace("_", "", ucwords($string, "_"));
    }


    /**
     * @inheritDoc
     */
    public function __invoke(string $key)
    {
        if (method_exists($this->getOriginalData(), $method = "get" . $this->strToCamelCase($key))) {
            return $this->getOriginalData()->{$method}();
        }

        if (method_exists($this->getOriginalData(), $method = "is" . $this->strToCamelCase($key))) {
            return $this->getOriginalData()->{$method}();
        }

        return null;
    }
}
