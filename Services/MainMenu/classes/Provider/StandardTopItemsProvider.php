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

        $repository = $this->mainmenu->topParentItem($this->getRepositoryIdentification())
            ->withTitle($f("mm_repository"))
            ->withPosition(10)
            ->withVisibilityCallable($this->getVisibilityCallable());

        $personal_workspace = $this->mainmenu->topParentItem($this->getPersonalWorkspaceIdentification())
            ->withTitle($f("mm_personal_workspace"))
            ->withPosition(20)
            ->withVisibilityCallable($this->getVisibilityCallable());

        $achievements = $this->mainmenu->topParentItem($this->getAchievementsIdentification())
            ->withTitle($f("mm_achievements"))
            ->withPosition(30)
            ->withVisibilityCallable($this->getVisibilityCallable());

        $communication = $this->mainmenu->topParentItem($this->getCommunicationIdentification())
            ->withTitle($f("mm_communication"))
            ->withPosition(40)
            ->withVisibilityCallable($this->getVisibilityCallable());

        $organisation = $this->mainmenu->topParentItem($this->getOrganisationIdentification())
            ->withTitle($f("mm_organisation"))
            ->withPosition(50)
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) ($dic->settings()->get("enable_my_staff"));
                }
            )
            ->withVisibilityCallable($this->getVisibilityCallable());

        $administration = $this->mainmenu->topParentItem($this->getAdministrationIdentification())
            ->withTitle($f("mm_administration"))
            ->withPosition(60)
            ->withVisibilityCallable(
                $this->getVisibilityCallable(function () use ($dic) { return (bool) ($dic->access()->checkAccess('visible', '', SYSTEM_FOLDER_ID)); })
            );

        return [
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


    private function getVisibilityCallable(Callable $existing = null) : Callable
    {
        return function () use ($existing): bool {
            if (is_callable($existing)) {
                return (!$this->dic->user()->isAnonymous() && $existing());
            }

            return (!$this->dic->user()->isAnonymous());
        };
    }
}
