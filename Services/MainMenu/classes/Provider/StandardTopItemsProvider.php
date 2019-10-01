<?php namespace ILIAS\MainMenu\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

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
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/home.svg"), "");
        $dashboard = $this->mainmenu->topLinkItem($this->if->identifier('mm_pd_crs_grp'))
            ->withTitle($this->dic->language()->txt("mm_dashboard"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships")
            ->withPosition(10)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return $dic->settings()->get('disable_my_memberships', 0) == 0;
                }
            )
            ->withVisibilityCallable(
                function () use ($dic) {
                    $pdItemsViewSettings = new \ilPDSelectedItemsBlockViewSettings($dic->user());

                    return (bool) $pdItemsViewSettings->allViewsEnabled() || $pdItemsViewSettings->enabledMemberships();
                }
            );

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/layers.svg"), "");

        $repository = $this->mainmenu->topParentItem($this->getRepositoryIdentification())
            ->withSymbol($icon)
            ->withTitle($f("mm_repository"))
            ->withPosition(20);

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/user.svg"), "");

        $personal_workspace = $this->mainmenu->topParentItem($this->getPersonalWorkspaceIdentification())
            ->withSymbol($icon)
            ->withTitle($f("mm_personal_workspace"))
            ->withPosition(30);

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/trophy.svg"), "");

        $achievements = $this->mainmenu->topParentItem($this->getAchievementsIdentification())
            ->withSymbol($icon)
            ->withTitle($f("mm_achievements"))
            ->withPosition(40);

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/bubbles.svg"), "");

        $communication = $this->mainmenu->topParentItem($this->getCommunicationIdentification())
            ->withSymbol($icon)
            ->withTitle($f("mm_communication"))
            ->withPosition(50);

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/organization.svg"), "");

        $organisation = $this->mainmenu->topParentItem($this->getOrganisationIdentification())
            ->withSymbol($icon)
            ->withTitle($f("mm_organisation"))
            ->withPosition(60)
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) ($dic->settings()->get("enable_my_staff"));
                }
            );

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/settings.svg"), "");

        $administration = $this->mainmenu->topParentItem($this->getAdministrationIdentification())
            ->withSymbol($icon)
            ->withTitle($f("mm_administration"))
            ->withPosition(70)
            ->withVisibilityCallable(
                function () use ($dic) { return (bool) ($dic->access()->checkAccess('visible', '', SYSTEM_FOLDER_ID)); }
            );

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
