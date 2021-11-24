<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Objective;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilComponentAbstractPluginsObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * Feel free to adjust this class, I just didn't want to refactor
 * all the plugin objectives and only wanted to save myself some
 * code for the ilCtrl "initialization".
 */
abstract class ilComponentAbstractPluginsObjective implements Objective
{
    /**
     * Initializes ilCtrl
     *
     * @throws ilCtrlException
     */
    protected function initCtrlService() : void
    {
        $refinery = new Refinery(
            new DataFactory(),
            $GLOBALS["DIC"]["lng"]
        );

        $GLOBALS["DIC"]["refinery"] = static function () use ($refinery) {
            return $refinery;
        };

        (new InitHttpServices())->init($GLOBALS["DIC"]);
        (new InitCtrlService())->init($GLOBALS["DIC"]);
    }
}