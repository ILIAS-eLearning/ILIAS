<?php

namespace ILIAS\Membership\Changelog\Query;

/**
 * Class QueryService
 *
 * @package ILIAS\Membership\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QueryFactory
{

    use SingletonTrait;


    /**
     * @return Filter
     */
    public function filter() : Filter
    {
        return new Filter();
    }


    /**
     * @return Options
     */
    public function options() : Options
    {
        return new Options();
    }
}