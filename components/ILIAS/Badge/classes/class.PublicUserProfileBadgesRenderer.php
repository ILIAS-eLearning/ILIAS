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

namespace ILIAS\Badge;

use ilBadge;
use ilBadgeAssignment;
use ilBadgeRenderer;
use ilGlobalTemplateInterface;
use ILIAS\ResourceStorage\Services as IRSS;
use ilLanguage;
use ilTemplate;

class PublicUserProfileBadgesRenderer
{
    private ilGlobalTemplateInterface $main_tpl;
    private ilLanguage $lng;
    private IRSS $irss;

    public function __construct(
        ?ilGlobalTemplateInterface $main_tpl = null,
        ?ilLanguage $lng = null
    ) {
        global $DIC;
        $this->main_tpl = $main_tpl ?? $DIC->ui()->mainTemplate();
        $this->lng = $lng ?? $DIC->language();
        $this->lng->loadLanguageModule('badge');

        $this->irss = $DIC->resourceStorage();
    }

    public function render(int $user_id): string
    {
        $tpl = new ilTemplate(
            'tpl.usr_public_profile_badges.html',
            true,
            true,
            'components/ILIAS/Badge'
        );

        $this->main_tpl->addJavaScript('assets/js/PublicProfileBadges.js');
        $this->main_tpl->addOnLoadCode('new BadgeToggle("ilbdgprcont", "ilbdgprfhdm", "ilbdgprfhdl", "ilNoDisplay");');
        $user_badges = ilBadgeAssignment::getInstancesByUserId($user_id);
        if ($user_badges) {
            $has_public_badge = false;
            $cnt = 0;

            $cut = 20;

            $badge_by_id = [];
            $image_rids = [];
            foreach ($user_badges as $ass) {
                $badge = new ilBadge($ass->getBadgeId());
                $image_rids[] = $badge->getImageRid();
                $badge_by_id[$badge->getId()] = $badge;
            }

            $this->irss->preload(array_filter($image_rids));

            foreach ($user_badges as $ass) {
                // only active
                if ($ass->getPosition()) {
                    $cnt++;

                    $renderer = new ilBadgeRenderer($ass, $badge_by_id[$ass->getBadgeId()]);

                    // limit to 20, [MORE] link
                    if ($cnt > $cut) {
                        $tpl->setCurrentBlock('hidden_badge');
                        $tpl->touchBlock('hidden_badge');
                        $tpl->parseCurrentBlock();
                    }

                    $tpl->setCurrentBlock('badge_bl');
                    $tpl->setVariable('BADGE', $renderer->getHTML());
                    $tpl->parseCurrentBlock();

                    $has_public_badge = true;
                }
            }

            if ($cnt > $cut) {
                $tpl->setVariable('BADGE_HIDDEN_TXT_MORE', $this->lng->txt('badge_profile_more'));
                $tpl->setVariable('BADGE_HIDDEN_TXT_LESS', $this->lng->txt('badge_profile_less'));
            }

            if ($has_public_badge) {
                $tpl->setVariable('TXT_BADGES', $this->lng->txt('obj_bdga'));
            }
        }

        return $tpl->get();
    }
}
