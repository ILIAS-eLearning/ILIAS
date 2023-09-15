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

use ilDateTime;
use ILIAS\Awareness\User\Collector;
use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Standard;
use ILIAS\UI\Implementation\Component\Toast\Toast;
use ilSetting;
use ilUserUtil;

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class AwarenessToastProvider extends AbstractToastProvider
{
    private const PROVIDER_KEY = 'awareness';

    public const MAX_ONLINE_USER_COUNT = 20;

    /**
     * @inheritDoc
     */
    public function getToasts(): array
    {
        $this->dic->language()->loadLanguageModule('awrn');
        $settings = new ilSetting('awrn');

        $toasts = [];
        if (
            $settings->get('use_osd', '0') !== '1' ||
            0 === $this->dic->user()->getId() ||
            $this->dic->user()->isAnonymous()
        ) {
            return $toasts;
        }

        $users = Collector::getOnlineUsers();
        unset($users[$this->dic->user()->getId()], $users[ANONYMOUS_USER_ID]);
        $users = array_slice($users, 0, self::MAX_ONLINE_USER_COUNT, true);

        $new_user_ids = [];
        foreach ($users as $id => $user) {
            $time = (new ilDateTime($user['last_login'], IL_CAL_DATETIME, $this->dic->user()->getTimeZone()))->getUnixTime();
            if ($time >= (time() - ($this->dic->http()->request()->getQueryParams()['max_age'] ?? 0))) {
                $new_user_ids[] = $id;
            }
        }

        $new_users = ilUserUtil::getNamePresentation(
            $new_user_ids,
            true,
            false,
            "",
            false,
            false,
            true,
            true
        );

        if ($new_users !== []) {
            $setting = new ilSetting('notifications');
            $toast = $this->toast_factory
                ->standard(
                    $this->if->identifier(self::PROVIDER_KEY . '_' . $this->dic->user()->getId()),
                    $this->dic->language()->txt('awareness_now_online')
                )
                ->withIcon($this->dic->ui()->factory()->symbol()->icon()->standard(Standard::USR, ''))
                ->withVanishTime((int) $setting->get('osd_vanish', (string) Toast::DEFAULT_VANISH_TIME))
                ->withDelayTime((int) $setting->get('osd_delay', (string) Toast::DEFAULT_DELAY_TIME));
            $links = [];
            foreach ($new_users as $user) {
                $uname = "[" . $user['login'] . "]";
                if ($user['public_profile']) {
                    $uname = $user['lastname'] . ", " . $user['firstname'] . " " . $uname;
                }

                $toast = $toast->withAdditionToastAction(
                    $this->toast_factory->action(
                        self::PROVIDER_KEY . '_' . $user['id'],
                        $uname,
                        function () use ($user): void {
                            $this->dic->ctrl()->redirectToURL('/goto.php?target=usr_' . $user['id']);
                        }
                    )
                );
            }

            $toasts = [$toast];
        }

        return $toasts;
    }
}
