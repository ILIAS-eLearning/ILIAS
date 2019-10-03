<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Table;

/**
 * Class AbstractDataFetcher
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDataFetcher implements DataFetcher
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * AbstractDataFetcher constructor
     *
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }


    /**
     * @inheritDoc
     */
    public function getNoDataText() : string
    {
        return $this->dic->language()->txt(Table::LANG_MODULE . "_no_data");
    }


    /**
     * @inheritDoc
     */
    public function isFetchDataNeedsFilterFirstSet() : bool
    {
        return false;
    }
}
