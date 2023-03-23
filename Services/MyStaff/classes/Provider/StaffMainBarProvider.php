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
 ********************************************************************
 */

namespace ILIAS\MyStaff\Provider;

use ilDashboardGUI;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ilMyStaffCachedAccessDecorator;
use ILIAS\MyStaff\ListUsers\ilMStListUsers;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilMStListCertificatesGUI;
use ilMStListCompetencesGUI;
use ilMStListCompetencesSkillsGUI;
use ilMStListCoursesGUI;
use ilMStListUsersGUI;
use ilMyStaffGUI;
use ilUtil;

/**
 * Class StaffMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StaffMainBarProvider extends AbstractStaticMainMenuProvider
{
    /**
     * @inheritDoc
     */
    final public function getStaticTopItems(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    final public function getStaticSubItems(): array
    {
        $this->dic->language()->loadLanguageModule('mst');
        $dic = $this->dic;
        $items = [];
        $top = StandardTopItemsProvider::getInstance()->getOrganisationIdentification();

        $title = $this->dic->language()->txt("mm_staff_list");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(
            ilUtil::getImagePath("icon_stff.svg"),
            $title
        );

        // My Staff
        $items[] = $this->mainmenu->link($this->if->identifier('mm_pd_mst'))
            ->withSymbol($icon)
            ->withTitle($title)
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilMyStaffGUI::class,
                ilMStListUsersGUI::class,
            ], ilMStListUsersGUI::CMD_INDEX))
            ->withParent($top)
            ->withPosition(10)
            ->withAvailableCallable(
                static function () use ($dic) {
                    return (bool) ($dic->settings()->get('enable_my_staff'));
                }
            )
            ->withVisibilityCallable(
                function (): bool {
                    return (
                        new ilMyStaffCachedAccessDecorator($this->dic, ilMyStaffAccess::getInstance())
                    )->hasCurrentUserAccessToMyStaff();
                }
            )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"));

        $title = $this->dic->language()->txt("mm_enrolments");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(
            ilUtil::getImagePath("icon_enrl.svg"),
            $title
        );

        // My Enrolments
        $items[] = $this->mainmenu->link($this->if->identifier('mm_pd_enrol'))
            ->withSymbol($icon)
            ->withTitle($title)
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilMyStaffGUI::class,
                ilMStListCoursesGUI::class,
            ], ilMStListCoursesGUI::CMD_INDEX))
            ->withParent($top)
            ->withPosition(20)
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) ($dic->settings()->get("enable_my_staff"));
                }
            )
            ->withVisibilityCallable(
                function (): bool {
                    return (
                        new ilMyStaffCachedAccessDecorator($this->dic, ilMyStaffAccess::getInstance())
                    )->hasCurrentUserAccessToCourseMemberships();
                }
            )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"));

        // My Certificates
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CERT, $title);
        $items[] = $this->mainmenu->link($this->if->identifier("mm_pd_cert"))
            ->withSymbol($icon)
            ->withTitle($this->dic->language()->txt("mm_certificates"))
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilMyStaffGUI::class,
                ilMStListCertificatesGUI::class,
            ], ilMStListCertificatesGUI::CMD_INDEX))
            ->withParent($top)
            ->withPosition(30)
            ->withAvailableCallable(
                function (): bool {
                    return boolval($this->dic->settings()->get("enable_my_staff"));
                }
            )
            ->withVisibilityCallable(
                function (): bool {
                    return (
                        new ilMyStaffCachedAccessDecorator($this->dic, ilMyStaffAccess::getInstance())
                    )->hasCurrentUserAccessToCertificates();
                }
            )->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt("component_not_active")}"));

        // My Competences
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::SKMG, $title);
        $items[] = $this->mainmenu->link($this->if->identifier("mm_pd_comp"))
            ->withSymbol($icon)
            ->withTitle($this->dic->language()->txt("mm_skills"))
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilMyStaffGUI::class,
                ilMStListCompetencesGUI::class,
            ], ilMStListCompetencesGUI::CMD_INDEX))
            ->withParent($top)
            ->withPosition(30)
            ->withAvailableCallable(
                function (): bool {
                    return boolval($this->dic->settings()->get("enable_my_staff"));
                }
            )
            ->withVisibilityCallable(
                function (): bool {
                    return (
                        new ilMyStaffCachedAccessDecorator($this->dic, ilMyStaffAccess::getInstance())
                    )->hasCurrentUserAccessToCompetences();
                }
            )->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt("component_not_active")}"));

        return $items;
    }
}
