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

class ilBadgeLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    private ilObjUser $current_user;
    private \ILIAS\DI\UIServices $ui;

    public function __construct(
        int $user_id,
        ilLearningHistoryFactory $factory,
        ilLanguage $lng,
        ?ilTemplate $template = null,
        ?ilObjUser $current_user = null,
        ?\ILIAS\DI\UIServices $ui = null
    ) {
        global $DIC;

        parent::__construct($user_id, $factory, $lng, $template);

        $this->current_user = $current_user ?? $DIC->user();
        $this->ui = $ui ?? $DIC->ui();
    }

    public function isActive(): bool
    {
        if (ilBadgeHandler::getInstance()->isActive()) {
            return true;
        }
        return false;
    }

    /**
     * @return list<ilLearningHistoryEntry>
     */
    public function getEntries(int $ts_start, int $ts_end): array
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule('badge');
        $completions = ilBadgeAssignment::getBadgesForUser($this->getUserId(), $ts_start, $ts_end);

        $entries = [];
        foreach ($completions as $c) {
            $title = $this->getEmphasizedTitle($c['title']);
            if ($this->current_user->getId() === $this->getUserId()) {
                $title = $this->ui->renderer()->render(
                    $this->ui->factory()->link()->standard(
                        $title,
                        $url = ilLink::_getLink($this->getUserId(), 'usr', [], '_bdg')
                    )
                );
            }
            $text1 = str_replace('$3$', $title, $lng->txt('badge_lhist_badge_completed'));
            $text2 = str_replace('$3$', $title, $lng->txt('badge_lhist_badge_completed_in'));
            $entries[] = $this->getFactory()->entry(
                $text1,
                $text2,
                ilUtil::getImagePath('standard/icon_bdga.svg'),
                $c['tstamp'],
                $c['parent_id']
            );
        }

        return $entries;
    }

    public function getName(): string
    {
        return $this->getLanguage()->txt('obj_bdga');
    }
}
