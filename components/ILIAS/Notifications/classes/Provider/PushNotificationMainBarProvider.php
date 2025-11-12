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

namespace ILIAS\Notifications\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilNotificationGUI;
use ilPersonalNotificationsSettingsGUI;
use ilSetting;

class PushNotificationMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        $this->dic->language()->loadLanguageModule('notifications_adm');
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_push'))
                ->withTitle($this->dic->language()->txt('push_settings'))
                ->withAction($this->dic->ctrl()->getLinkTargetByClass([ilNotificationGUI::class, ilPersonalNotificationsSettingsGUI::class]))
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(60)
                ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard(Standard::NOTA, 'push_notification'))
                ->withAvailableCallable(
                    static fn(): bool => ((new ilSetting('notifications'))->get('enable_push') === '1')
                )
        ];

    }
}
