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

namespace ILIAS\Badge\GlobalScreen;

use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

class BadgeToastProvider extends AbstractToastProvider
{
    public function getToasts(): array
    {
        $toasts = [];

        if (0 === $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return $toasts;
        }

        foreach ((new BadgeNotificationProvider($this->dic))->getUserOSDNotifications() as $badge_issued_info) {
            $toast = $this->getDefaultToast(
                $badge_issued_info->getObject()->title,
                $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::BDGA, 'badge')
            )->withDescription($badge_issued_info->getObject()->shortDescription);
            foreach ($badge_issued_info->getObject()->links as $link) {
                $toast = $toast->withAdditionalLink(
                    $this->dic->ui()->factory()->link()->standard(
                        $link->getTitle(),
                        $link->getUrl()
                    )
                );
            }
            $toasts[] = $toast;
        }

        return $toasts;
    }
}
