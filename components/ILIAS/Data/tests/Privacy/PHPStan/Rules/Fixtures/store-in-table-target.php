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

use ILIAS\Data\Privacy\Purpose\StoreInTable;
use ILIAS\Data\Privacy\Source\Known\UserSources;

/**
 * @param class-string<StoreInTable> $dynamic_class
 */
function storeInTableTargets(UserSources $user_sources, string $dynamic_class): array
{
    return [
        new StoreInTable($user_sources->postalAddress()),
        new StoreInTable($user_sources->street()),
        new StoreInTable(),
        new StoreInTable('usr_data.street'),
        new $dynamic_class($user_sources->street()),
        new UserSources(),
    ];
}
