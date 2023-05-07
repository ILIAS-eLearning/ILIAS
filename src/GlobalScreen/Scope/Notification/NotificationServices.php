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
/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Scope\Notification;

use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use ILIAS\GlobalScreen\SingletonTrait;

/**
 * Class NotificationServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationServices
{
    /**
     * @var \ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory
     */
    private $notification_factory;

    public function __construct()
    {
        $this->notification_factory = new NotificationFactory();
    }


    /**
     * @return NotificationFactory
     */
    public function factory() : NotificationFactory
    {
        return $this->notification_factory;
    }
}
