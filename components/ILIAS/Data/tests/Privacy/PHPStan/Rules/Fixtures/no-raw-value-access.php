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

namespace ILIAS\Data\Privacy\PHPStan\Rules\Fixtures;

use ILIAS\Data\Privacy\Types\PostalAddress;

function exposeRawValues(PostalAddress $address, string $harmless, callable $callable): void
{
    var_dump($address);
    print_r($address);
    var_export($address);
    json_encode($address);
    serialize($address);
    var_dump($harmless);
    strlen($harmless);
    var_dump($harmless, $address);
    $callable($address);
    $dumper = var_dump(...);
    $dumper($harmless);
}
