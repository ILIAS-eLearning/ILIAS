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

namespace ILIAS\Notifications;

use ilGlobalTemplateInterface;
use iljQueryUtil;
use ilLanguage;
use ilObjUser;
use ilPlayerUtil;
use ilSetting;
use ilTemplate;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilNotificationOSDGUI
{
    final public const DEFAULT_POLLING_INTERVAL = 60000;

    protected ilObjUser $user;

    public function __construct(protected ilGlobalTemplateInterface $page, protected ilLanguage $lng)
    {
        global $DIC;

        $this->user = $DIC->user();
    }

    public function populatePage(): void
    {
        if ($this->user->isAnonymous() || 0 === $this->user->getId()) {
            return;
        }

        $notificationSettings = new ilSetting('notifications');

        $osdTemplate = new ilTemplate('tpl.osd_notifications.js', true, true, 'Services/Notifications');

        $osdTemplate->setVariable(
            'OSD_INTERVAL',
            $notificationSettings->get('osd_interval', (string) self::DEFAULT_POLLING_INTERVAL)
        );
        $osdTemplate->setVariable(
            'OSD_PLAY_SOUND',
            $notificationSettings->get('osd_play_sound') && $this->user->getPref('osd_play_sound') ? 'true' : 'false'
        );

        iljQueryUtil::initjQuery($this->page);
        ilPlayerUtil::initMediaElementJs($this->page);

        $this->page->addJavaScript('Services/Notifications/templates/default/notifications.js');
        $this->page->addCSS('Services/Notifications/templates/default/osd.css');
        $this->page->addOnLoadCode($osdTemplate->get());
    }
}
