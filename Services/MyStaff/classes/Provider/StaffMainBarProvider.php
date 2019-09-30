<?php namespace ILIAS\MyStaff\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilMyStaffAccess;

/**
 * Class StaffMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StaffMainBarProvider extends AbstractStaticMainMenuProvider
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
        $this->dic->language()->loadLanguageModule('mst');
        $dic = $this->dic;
        $items = [];
        $top = StandardTopItemsProvider::getInstance()->getOrganisationIdentification();

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/people.svg"), "");

        // My Staff
        $items[] = $this->mainmenu->link($this->if->identifier('mm_pd_mst'))
            ->withSymbol($icon)
            ->withTitle($this->dic->language()->txt("mm_staff_list"))
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                \ilPersonalDesktopGUI::class,
                \ilMyStaffGUI::class,
                \ilMStListCoursesGUI::class,
            ], \ilMStListCoursesGUI::CMD_INDEX))
            ->withParent($top)
            ->withPosition(10)
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) ($dic->settings()->get("enable_my_staff"));
                }
            )
            ->withVisibilityCallable(
                function () {
                    return (bool) ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
                }
            )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"));

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/notebook.svg"), "");

        // My Enrollments
        $items[] = $this->mainmenu->link($this->if->identifier('mm_pd_enrol'))
            ->withSymbol($icon)
            ->withTitle($this->dic->language()->txt("mm_enrolments"))
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                \ilPersonalDesktopGUI::class,
                \ilMyStaffGUI::class,
                \ilMStListCoursesGUI::class,
            ], \ilMStListCoursesGUI::CMD_INDEX))
            ->withParent($top)
            ->withPosition(20)
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) ($dic->settings()->get("enable_my_staff"));
                }
            )
            ->withVisibilityCallable(
                function () {
                    return (bool) ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
                }
            )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"));

        return $items;
    }
}
