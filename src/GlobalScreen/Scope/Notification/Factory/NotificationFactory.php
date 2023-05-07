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
namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class NotificationFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationFactory
{
    /**
     * @param IdentificationInterface $identification
     * @return StandardNotification
     */
    public function standard(IdentificationInterface $identification) : StandardNotification
    {
        return new StandardNotification($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return StandardNotificationGroup
     */
    public function standardGroup(IdentificationInterface $identification) : StandardNotificationGroup
    {
        return new StandardNotificationGroup($identification);
    }

    public function administrative(IdentificationInterface $identification) : AdministrativeNotification
    {
        return new AdministrativeNotification($identification);
    }
}
