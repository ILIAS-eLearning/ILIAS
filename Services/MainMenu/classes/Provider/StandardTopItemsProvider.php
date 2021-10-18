<?php namespace ILIAS\MainMenu\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Class StandardTopItemsProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardTopItemsProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @var StandardTopItemsProvider
     */
    private static $instance;
    /**
     * @var BasicAccessCheckClosures
     */
    private $basic_access_helper;
    /**
     * @var IdentificationInterface
     */
    private $administration_identification;
    /**
     * @var IdentificationInterface
     */
    private $organisation_identification;
    /**
     * @var IdentificationInterface
     */
    private $communication_identification;
    /**
     * @var IdentificationInterface
     */
    private $achievements_identification;
    /**
     * @var IdentificationInterface
     */
    private $personal_workspace_identification;
    /**
     * @var IdentificationInterface
     */
    private $repository_identification;


    /**
     * @return StandardTopItemsProvider
     */
    public static function getInstance()
    {
        global $DIC;
        if (!isset(self::$instance)) {
            self::$instance = new self($DIC);
        }

        return self::$instance;
    }


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->basic_access_helper = BasicAccessCheckClosures::getInstance();
        $this->repository_identification = $this->if->identifier('repository');
        $this->personal_workspace_identification = $this->if->identifier('personal_workspace');
        $this->achievements_identification = $this->if->identifier('achievements');
        $this->communication_identification = $this->if->identifier('communication');
        $this->organisation_identification = $this->if->identifier('organisation');
        $this->administration_identification = $this->if->identifier('administration');
    }


    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        $f = function ($id) {
            return $this->dic->language()->txt($id);
        };
        $dic = $this->dic;

        // Dashboard
        $title = $this->dic->language()->txt("mm_dashboard");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::DSHS, $title)->withIsOutlined(true);
        $dashboard = $this->mainmenu->topLinkItem($this->if->identifier('mm_pd_crs_grp'))
            ->withSymbol($icon)
            ->withTitle($title)
            ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToMemberships")
            ->withPosition(10)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return true;

                    return $dic->settings()->get('disable_my_memberships', 0) == 0;
                }
            )
            ->withVisibilityCallable(
                $this->basic_access_helper->isUserLoggedIn()
            );

        $title = $f("mm_repository");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::REP, $title)->withIsOutlined(true);

        $repository = $this->mainmenu->topParentItem($this->getRepositoryIdentification())
            ->withVisibilityCallable($this->basic_access_helper->isRepositoryReadable())
            ->withSymbol($icon)
            ->withTitle($title)
            ->withPosition(20);

        $title = $f("mm_personal_workspace");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_wksp.svg"), $title);

        $personal_workspace = $this->mainmenu->topParentItem($this->getPersonalWorkspaceIdentification())
            ->withVisibilityCallable($this->basic_access_helper->isUserLoggedIn())
            ->withSymbol($icon)
            ->withTitle($title)
            ->withPosition(30);

        $title = $f("mm_achievements");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_achv.svg"), $title);

        $achievements = $this->mainmenu->topParentItem($this->getAchievementsIdentification())
            ->withVisibilityCallable($this->basic_access_helper->isUserLoggedIn())
            ->withSymbol($icon)
            ->withTitle($title)
            ->withPosition(40);

        $title = $f("mm_communication");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_comu.svg"), $title);

        $communication = $this->mainmenu->topParentItem($this->getCommunicationIdentification())
            ->withVisibilityCallable($this->basic_access_helper->isUserLoggedIn())
            ->withSymbol($icon)
            ->withTitle($title)
            ->withPosition(50);

        $title = $f("mm_organisation");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_orga.svg"), $title);

        $organisation = $this->mainmenu->topParentItem($this->getOrganisationIdentification())
            ->withVisibilityCallable($this->basic_access_helper->isUserLoggedIn(static function () : bool {
                return (bool) ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
            }))
            ->withSymbol($icon)
            ->withTitle($title)
            ->withPosition(60)
            ->withAvailableCallable(
                static function () : bool {
                    return (bool) ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
                });

        $title = $f("mm_administration");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("adm", $title)->withIsOutlined(true);

        $administration = $this->mainmenu->topParentItem($this->getAdministrationIdentification())
            ->withSupportsAsynchronousLoading(false)
            ->withAvailableCallable($this->basic_access_helper->isUserLoggedIn())
            ->withSymbol($icon)
            ->withTitle($title)
            ->withPosition(70)
            ->withVisibilityCallable($this->basic_access_helper->hasAdministrationAccess());

        return [
            $dashboard,
            $repository,
            $personal_workspace,
            $achievements,
            $communication,
            $organisation,
            $administration,
        ];
    }


    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return "Default";
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        return [];
    }


    /**
     * @return IdentificationInterface
     */
    public function getAdministrationIdentification() : IdentificationInterface
    {
        return $this->administration_identification;
    }


    /**
     * @return IdentificationInterface
     */
    public function getOrganisationIdentification() : IdentificationInterface
    {
        return $this->organisation_identification;
    }


    /**
     * @return IdentificationInterface
     */
    public function getCommunicationIdentification() : IdentificationInterface
    {
        return $this->communication_identification;
    }


    /**
     * @return IdentificationInterface
     */
    public function getAchievementsIdentification() : IdentificationInterface
    {
        return $this->achievements_identification;
    }


    /**
     * @return IdentificationInterface
     */
    public function getPersonalWorkspaceIdentification() : IdentificationInterface
    {
        return $this->personal_workspace_identification;
    }


    /**
     * @return IdentificationInterface
     */
    public function getRepositoryIdentification() : IdentificationInterface
    {
        return $this->repository_identification;
    }
}
