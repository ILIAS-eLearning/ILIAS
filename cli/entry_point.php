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

$info = <<<INFO
Hello there.

This calls an arbitrary entrypoint of ILIAS for testing purpose. For more informations
about entrypoints, have a look into `components/ILIAS/Component/src/EntryPoint.php`.

INFO;

if (count($argv) !== 3) {
    echo $info;
    die("php cli/entry_point.php \$bootstrap \$name\n");
}

$bootstrap = $argv[1];
$entry_point = $argv[2];

require_once(__DIR__ . "/../artifacts/bootstrap_$bootstrap.php");

exit(entry_point($entry_point));
