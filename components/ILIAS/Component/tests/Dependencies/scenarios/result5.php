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

require_once(__DIR__ . "/../vendor/composer/vendor/autoload.php");

function entry_point(string $name)
{
    $null_dic = new ILIAS\Component\Dependencies\NullDIC();


    $component_0 = new ILIAS\Component\Tests\Dependencies\Scenario5\ComponentA();

    $implement_0 = new ILIAS\Component\Dependencies\RenamingDIC(new Pimple\Container());
    $use = new Pimple\Container();
    $contribute_0 = new ILIAS\Component\Dependencies\RenamingDIC(new Pimple\Container());
    $seek = new Pimple\Container();
    $provide_0 = new Pimple\Container();
    $pull = new Pimple\Container();
    $internal = new Pimple\Container();

    $component_0->init($null_dic, $implement_0, $use, $contribute_0, $seek, $provide_0, $pull, $internal);


    $entry_points = [
        "just a name" => fn() => $contribute_0[ILIAS\Component\EntryPoint::class . "_0"],
    ];

    if (!isset($entry_points[$name])) {
        throw new \LogicException("Unknown entry point: $name.");
    }

    $entry_points[$name]()->enter();
}
