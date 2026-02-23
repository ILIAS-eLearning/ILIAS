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

namespace ILIAS\Help\GlobalScreen;

use ilADNNotification;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use Closure;
use ILIAS\DI\Container;

/**
 * Class ADNProvider
 */
class NotificationProvider extends AbstractNotificationProvider
{
    protected \ILIAS\Help\GuidedTour\Admin\AdminManager $gd_admin;
    protected \ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures $access;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->access = BasicAccessCheckClosuresSingleton::getInstance();
        $this->gd_admin = $dic->help()->internal()->domain()->guidedTour()->admin();
    }

    public function getNotifications(): array
    {
        return [];
    }

    public function getAdministrativeNotifications(): array
    {
        $screen_id_visible = $this->gd_admin->areIdentifiersVisible();
        if (defined("OH_REF_ID") && (int) OH_REF_ID > 0) {
            $screen_id_visible = true;
        }
        if (!$screen_id_visible) {
            return [];
        }

        $adns = [];
        $i = fn(string $id): IdentificationInterface => $this->if->identifier($id);

        /** @var \ilHelpGUI $help_gui */
        $help_gui = $this->dic->help();
        $mt = $this->dic->ui()->mainTemplate();
        $help_gui->initHelp($mt, "#");
        $adn = $this->notification_factory->administrative($i("help_screen_id"))->withTitle("Screen ID:")->withSummary($help_gui->getScreenId());
        $is_visible = static fn(): bool => true;
        $adns[] = $adn->withVisibilityCallable($is_visible);
        return $adns;
    }
}
