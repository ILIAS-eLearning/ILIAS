<?php namespace ILIAS\Membership\GlobalScreen;

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
        $dic = $this->dic;

        $title = $this->dic->language()->txt("my_courses_groups");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/user-following.svg"), $title);

        // derived tasks list
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_memberships'))
            ->withTitle($title)
            ->withPosition(40)
            ->withSymbol($icon)
            ->withAction($dic->ctrl()->getLinkTargetByClass(["ilMembershipOverviewGUI"], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getRepositoryIdentification())
            ->withVisibilityCallable(
                function () use ($dic) {
                    return true;
                }
            );

        return $entries;
    }
}
