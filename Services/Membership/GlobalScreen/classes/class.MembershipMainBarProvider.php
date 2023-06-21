<?php namespace ILIAS\Membership\GlobalScreen;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Main menu entry for derived tasks
 *
 * @author <killing@leifos.de>
 */
class MembershipMainBarProvider extends AbstractStaticMainMenuProvider
{
    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        if (!$this->dic->settings()->get('mmbr_my_crs_grp', 1)) {
            return [];
        }

        $dic = $this->dic;
        $access_helper = BasicAccessCheckClosures::getInstance();

        $title = $this->dic->language()->txt("my_courses_groups");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_crgr.svg"), $title);

        // derived tasks list
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_memberships'))
            ->withTitle($title)
            ->withPosition(40)
            ->withSymbol($icon)
            ->withAction($dic->ctrl()->getLinkTargetByClass(["ilMembershipOverviewGUI"], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getRepositoryIdentification())
            ->withVisibilityCallable($access_helper->isUserLoggedIn($access_helper->isUserLoggedIn($access_helper->isRepositoryReadable())));

        return $entries;
    }
}
