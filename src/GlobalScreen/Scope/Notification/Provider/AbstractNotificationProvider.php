<?php declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;

/**
 * Interface AbstractNotificationProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractNotificationProvider extends AbstractProvider implements NotificationProvider
{
    protected Container $dic;
    protected IdentificationProviderInterface $if;
    protected NotificationFactory $notification_factory;

    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->notification_factory = $this->globalScreen()->notifications()->factory();
        $this->if = $this->globalScreen()->identification()->core($this);
    }

    /**
     * @inheritDoc
     */
    public function getAdministrativeNotifications() : array
    {
        return [];
    }
}
