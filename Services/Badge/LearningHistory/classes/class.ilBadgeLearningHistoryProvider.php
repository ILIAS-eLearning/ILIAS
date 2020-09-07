<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history provider: Badges
 *
 * @author killing@leifos.de
 * @ingroup ServicesTracking
 */
class ilBadgeLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    /**
     * @var ilObjUser
     */
    protected $current_user;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     * @param int $user_id
     * @param ilLearningHistoryFactory $factory
     * @param ilLanguage $lng
     * @param ilTemplate|null $template
     */
    public function __construct(
        $user_id,
        ilLearningHistoryFactory $factory,
        ilLanguage $lng,
        ilTemplate $template = null,
        ilObjUser $current_user = null,
        \ILIAS\DI\UIServices $ui = null
    ) {
        global $DIC;

        parent::__construct($user_id, $factory, $lng, $template);

        if (is_null($current_user)) {
            $current_user = $DIC->user();
        }
        $this->current_user = $current_user;

        if (is_null($ui)) {
            $ui = $DIC->ui();
        }
        $this->ui = $ui;
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        require_once 'Services/Badge/classes/class.ilBadgeHandler.php';
        if (ilBadgeHandler::getInstance()->isActive()) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getEntries($ts_start, $ts_end)
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("badge");
        $completions = ilBadgeAssignment::getBadgesForUser($this->getUserId(), $ts_start, $ts_end);

        $entries = [];
        foreach ($completions as $c) {
            $title = $this->getEmphasizedTitle($c["title"]);
            if ($this->current_user->getId() == $this->getUserId()) {
                $title = $this->ui->renderer()->render($this->ui->factory()->link()->standard(
                    $title,
                    $url = ilLink::_getLink($this->getUserId(), "usr", array(), "_bdg")
                ));
            }
            $text1 = str_replace("$3$", $title, $lng->txt("badge_lhist_badge_completed"));
            $text2 = str_replace("$3$", $title, $lng->txt("badge_lhist_badge_completed_in"));
            $entries[] = $this->getFactory()->entry(
                $text1,
                $text2,
                ilUtil::getImagePath("icon_bdga.svg"),
                $c["tstamp"],
                $c["parent_id"]
            );
        }
        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function getName() : string
    {
        $lng = $this->getLanguage();

        return $lng->txt("obj_bdga");
    }
}
