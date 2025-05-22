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

/**
 * Class ilObjTalkTemplateAdministrationGUI GUI class
 * @author            : Nicolas Schaefli <ns@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilObjTalkTemplateAdministrationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjTalkTemplateAdministrationGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjTalkTemplateAdministrationGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI
 * @ilCtrl_Calls      ilObjTalkTemplateAdministrationGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjTalkTemplateAdministrationGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjTalkTemplateAdministrationGUI: ilObjTalkTemplateGUI
 * @ilCtrl_Calls      ilObjTalkTemplateAdministrationGUI: ilObjEmployeeTalkSeriesGUI
 */
final class ilObjTalkTemplateAdministrationGUI extends ilContainerGUI
{
    public function __construct()
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];
        $language = $container->language();
        $refId = $container
            ->http()
            ->wrapper()
            ->query()
            ->retrieve("ref_id", $container->refinery()->kindlyTo()->int());
        parent::__construct([], $refId, true, false);

        $this->type = 'tala';

        $language->loadLanguageModule("tala");
        $language->loadLanguageModule('etal');
    }

    protected function supportsPageEditor(): bool
    {
        return false;
    }

    public function isActiveAdministrationPanel(): bool
    {
        return false;
    }

    public function setContentSubTabs(): void
    {
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);


        switch ($next_class) {
            case 'ilpermissiongui':
                parent::prepareOutput();
                $this->tabs_gui->activateTab('perm_settings');
                $ilPermissionGUI = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($ilPermissionGUI);
                break;
            case 'ilinfoscreengui':
                parent::prepareOutput();
                $this->tabs_gui->activateTab('info_short');
                $ilInfoScreenGUI = new ilInfoScreenGUI($this);
                $this->ctrl->forwardCommand($ilInfoScreenGUI);
                break;
            case strtolower(ilObjTalkTemplateGUI::class):
                $ilTalkTemplateGUI = new ilObjTalkTemplateGUI();
                $ilTalkTemplateGUI->setAdminMode($this->admin_mode);
                $this->ctrl->setParameter(
                    $this,
                    'ref_id',
                    ilObjTalkTemplateAdministration::getRootRefId()
                );
                $this->tabs->setBackTarget(
                    $this->lng->txt('obj_tala'),
                    $this->ctrl->getLinkTarget($this, 'view')
                );
                $this->ctrl->clearParameters($this);
                $this->ctrl->forwardCommand($ilTalkTemplateGUI);
                break;
            default:
                parent::executeCommand();
        }
    }

    /**
     * called by prepare output
     */
    protected function setTitleAndDescription(): void
    {
        # all possible create permissions
        parent::setTitleAndDescription();
        $this->tpl->setTitle($this->lng->txt("objs_tala"));
        $this->tpl->setDescription($this->lng->txt("objs_tala"));

        $this->tpl->setTitleIcon("", $this->lng->txt("obj_" . $this->object->getType()));
    }

    protected function showPossibleSubObjects(): void
    {
        $subtypes = $this->getCreatableObjectTypes();
        $gui = new ILIAS\ILIASObject\Creation\AddNewItemGUI(
            [$this->buildGroup(
                self::class,
                array_keys($subtypes),
                $this->lng->txt('other'),
                $subtypes
            )]
        );
        $gui->render();
    }

    protected function getCreatableObjectTypes(): array
    {
        $subtypes = $this->obj_definition->getCreatableSubObjects(
            $this->object->getType(),
            ilObjectDefinition::MODE_ADMINISTRATION,
            $this->object->getRefId()
        );
        unset($subtypes[ilObjEmployeeTalkSeries::TYPE]);

        return array_filter(
            $subtypes,
            fn($key) => $this->access->checkAccess('create_' . $key, '', $this->ref_id, $this->type),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function viewObject(): void
    {
        $this->tabs_gui->activateTab('view_content');

        if (!$this->rbacsystem->checkAccess("read", $this->getRefId())) {
            if ($this->rbacsystem->checkAccess("visible", $this->getRefId())) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_read"));
                $this->ctrl->redirectByClass(strtolower(ilInfoScreenGUI::class), '');
            }

            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->WARNING);
        }

        parent::renderObject();
    }

    /**
     * Filter the view by talk templates because the talk series objects are also children of the talk template administration.
     * @TODO this is super messy, and should be refactored completely...
     *
     * @return ilContainerContentGUI
     */
    public function getContentGUI(): ilContainerContentGUI
    {
        $this->container_user_filter = new ilContainerUserFilter([
            'std_' . ilContainerFilterField::STD_FIELD_OBJECT_TYPE => ilObjTalkTemplate::TYPE
        ]);
        return new ilContainerByTypeContentGUI(
            $this,
            $this->getItemPresentation()
        );
    }

    /**
     * Not completely sure why this is necessary, after creation of talk templates
     * the redirect leads here.
     */
    public function returnObject(): void
    {
        $this->viewObject();
    }

    protected function getTabs(): void
    {
        $read_access_ref_id = $this->rbacsystem->checkAccess('visible,read', $this->object->getRefId());
        if ($read_access_ref_id) {
            $this->tabs_gui->addTab('view_content', $this->lng->txt("content"), $this->ctrl->getLinkTarget($this, "view"));
            $this->tabs_gui->addTab(
                "info_short",
                $this->lng->txt('tab_info'),
                $this->ctrl->getLinkTargetByClass([
                    strtolower(self::class),
                    strtolower(ilInfoScreenGUI::class)
                ], "showSummary")
            );
        }
        if ($this->tree->getSavedNodeData($this->object->getRefId())) {
            $this->tabs_gui->addTarget('trash', $this->ctrl->getLinkTarget($this, 'trash'), 'trash', get_class($this));
        }
        parent::getTabs();
    }

    /**
     * @param ilTabsGUI $tabs_gui
     */
    public function getAdminTabs(): void
    {
        $this->getTabs();
    }
}
