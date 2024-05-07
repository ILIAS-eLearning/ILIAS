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

require_once(__DIR__ . "/../vendor/composer/vendor/autoload.php");

echo <<<INFO
Hello there.

This builds the bootstrap of ILIAS by reading all components and resolving the
dependencies. If you want to use custom directives to resolve ambiguous dependencies,
call this script with a parameter pointing a file defining the resolution.

INFO;

if (count($argv) !== 3) {
    die("php cli/build_bootstrap.php \$dependency_resolution \$name");
}

$resolution_file = $argv[1];
$name = $argv[2];


if (!file_exists($resolution_file)) {
    die("Cannot find resolution file at {$resolution_file}.\n");
}

$reader = new ILIAS\Component\Dependencies\Reader();
$resolver = new ILIAS\Component\Dependencies\Resolver();
$renderer = new ILIAS\Component\Dependencies\Renderer();

$component_info = [];

$vendors = new \DirectoryIterator(realpath(__DIR__ . "/../components"));
echo "Reading Vendors from \"./components\"...\n";
foreach ($vendors as $vendor) {
    if (str_starts_with($vendor->getFilename(), ".")) {
        continue;
    }
    if (!$vendor->isDir()) {
        echo "    unexpected non-directory: {$vendor->getPathName()}\n";
        continue;
    }
    echo "    Reading Components from Vendor \"{$vendor->getFilename()}\"...\n";
    $components = new \DirectoryIterator($vendor->getPathName());
    foreach ($components as $component) {
        if (str_starts_with($component->getFilename(), ".")) {
            continue;
        }
        if (!$vendor->isDir()) {
            echo "        unexpected non-directory: {$component->getPathName()}\n";
            continue;
        }
        if (!file_exists($component->getPathName() . "/" . $component->getFileName() . ".php")) {
            echo "        expected \"Component.php\" in {$component->getPathName()}\n";
            continue;
        }
        require_once($component->getPathName() . "/" . $component->getFileName() . ".php");
        $component_name = $vendor->getFilename() . "\\" . $component->getFileName();
        echo "        Reading Component \"$component_name\"...\n";
        $component_info[] = $reader->read(new $component_name());
    }
}

echo "Resolving Dependency using {$resolution_file}...\n";
$disambiguation = require_once($resolution_file);

$component_info = $resolver->resolveDependencies($disambiguation, ...$component_info);

echo "Writing bootstrap to artifacts/bootstrap.php\n";

$bootstrap = $renderer->render(...$component_info);
if (!is_dir(__DIR__ . "/../artifacts")) {
    mkdir(__DIR__ . "/../artifacts", 0755, true);
}
file_put_contents(__DIR__ . "/../artifacts/bootstrap_$name.php", $bootstrap);
