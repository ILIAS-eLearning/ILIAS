<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Notifications;

use ilGlobalTemplateInterface;
use ILIAS\DI\UIServices;
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
    protected ilObjUser $user;
    protected ilGlobalTemplateInterface $page;
    protected ilLanguage $lng;

    public function __construct(ilGlobalTemplateInterface $page, ilLanguage $language)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->page = $page;
        $this->lng = $language;
    }

    /**
     *
     */
    public function populatePage() : void
    {
        if ($this->user->isAnonymous() || 0 === $this->user->getId()) {
            return;
        }

        $notificationSettings = new ilSetting('notifications');

        $osdTemplate = new ilTemplate('tpl.osd_notifications.js', true, true, 'Services/Notifications');

        $osdTemplate->setVariable(
            'OSD_INTERVAL',
            $notificationSettings->get('osd_interval') ? : '60'
        );
        $osdTemplate->setVariable(
            'OSD_PLAY_SOUND',
            $notificationSettings->get('play_sound') && $this->user->getPref('play_sound') ? 'true' : 'false'
        );

        iljQueryUtil::initjQuery($this->page);
        ilPlayerUtil::initMediaElementJs($this->page);

        $this->page->addJavaScript('Services/Notifications/templates/default/notifications.js');
        $this->page->addCSS('Services/Notifications/templates/default/osd.css');
        $this->page->addOnLoadCode($osdTemplate->get());
    }
}
