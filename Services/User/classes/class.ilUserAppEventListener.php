<?php

declare(strict_types=1);

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

/**
 * Class ilUserAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAppEventListener implements ilAppEventListener
{
    /**
     * @param array<string,mixed>  $a_parameter
     */
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $user_starting_point_repository = new ilUserStartingPointRepository(
            $DIC['ilUser'],
            $DIC['ilDB'],
            $DIC->logger(),
            $DIC['tree'],
            $DIC['rbacreview'],
            $DIC['rbacsystem'],
            $DIC['ilSetting']
        );

        if ('Services/Object' === $component && 'beforeDeletion' === $event) {
            if (isset($parameter['object']) && $parameter['object'] instanceof ilObjRole) {
                $user_starting_point_repository->onRoleDeleted($parameter['object']);
            }
        }
    }
}
