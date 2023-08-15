<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use Pimple\Container;

class ilTestDIC
{
    public static ?Container $dic = null;

    public static function dic(): Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
    }

    protected static function buildDIC(): Container
    {
        global $DIC;
        $dic = $DIC;
        $container = new Container();

        $dic['shuffler'] = static function ($c) use ($dic): ilTestShuffler {
            return new ilTestShuffler(
                $dic['refinery']
            );
        };
        $dic['factory.results'] = static function ($c) use ($dic): ilTestResultsFactory {
            return new ilTestResultsFactory(
                $c['shuffler'],
                $dic['ui.factory'],
                $dic['ui.renderer'],
                $dic['refinery'],
                new ILIAS\Data\Factory(),
                $dic['http'],
                $dic['lng']
            );
        };

        return $dic;
    }
}
