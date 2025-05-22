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

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepositoryDB;
use ILIAS\GlobalScreen_\UI\Translator;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilObjFooterAdministrationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjFooterAdministrationGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjFooterAdministrationGUI: ilFooterGroupsGUI
 */
final class ilObjFooterAdministrationGUI extends ilObject2GUI
{
    /**
     * @var string
     */
    public const CMD_DEFAULT = 'view';
    /**
     * @var string
     */
    public const TAB_INDEX = 'index';
    /**
     * @var string
     */
    public const TAB_PERMISSIONS = 'permissions';
    private readonly Container $container;
    private readonly GroupsRepositoryDB $repository;
    private readonly ilGlobalTemplateInterface $main_tpl;
    private readonly Translator $translator;
    private readonly ilObjFooterUIHandling $ui_handling;

    public function __construct()
    {
        parent::__construct(...func_get_args());
        global $DIC;
        $this->container = $DIC;
        $this->main_tpl = $this->container->ui()->mainTemplate();

        $this->translator = new Translator($DIC);

        $this->ui_handling = new ilObjFooterUIHandling(
            $this->container->ui(),
            $this->container->http(),
            $this->tabs_gui,
            $this->translator,
            $this->ctrl,
            $this->error,
            $this->rbac_system,
            $this->object->getRefId()
        );

        $this->repository = new GroupsRepositoryDB(
            $this->container->database(),
            new ilFooterCustomGroupsProvider($DIC)
        );
    }

    // HELPERS AND NEEDED IMPLEMENATIONS

    #[\Override]
    public function view(): void
    {
        $this->ctrl->redirectByClass(ilFooterGroupsGUI::class);
    }

    public function getType(): string
    {
        return 'gsfo';
    }

    #[\Override]
    public function executeCommand(): void
    {
        $this->ui_handling->requireReadable();
        $this->prepareOutput();

        $this->container->language()->loadLanguageModule('gsfo');

        $this->main_tpl->setTitle($this->translator->translate('obj_gsfo'));
        $this->main_tpl->setDescription($this->translator->translate('obj_gsfo_desc'));

        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);

        switch (strtolower($next_class)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab(self::TAB_PERMISSIONS);
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                return;
            case strtolower(ilFooterGroupsGUI::class):
                $this->tabs_gui->activateTab(self::TAB_INDEX);
                $this->ctrl->forwardCommand(
                    new ilFooterGroupsGUI(
                        $this->container,
                        $this->translator,
                        $this->ui_handling
                    )
                );
                return;
            default:
                $this->{$cmd}();
                return;
        }
    }

    #[\Override]
    public function getAdminTabs(): void
    {
        $this->ui_handling->buildMainTabs();
    }

}
