<?php

declare(strict_types=1);

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

use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\EmployeeTalk\Metadata\MetadataHandlerInterface;
use ILIAS\EmployeeTalk\Metadata\MetadataHandler;

/**
 * Class ilObjTalkTemplateGUI
 *
 * @author            : Nicolas Schaefli <nick@fluxlabs.ch>
 *
 * @ilCtrl_IsCalledBy ilObjTalkTemplateGUI: ilAdministrationGUI, ilObjTalkTemplateAdministrationGUI
 * @ilCtrl_Calls      ilObjTalkTemplateGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjTalkTemplateGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI
 * @ilCtrl_Calls      ilObjTalkTemplateGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjTalkTemplateGUI: ilInfoScreenGUI
 */
final class ilObjTalkTemplateGUI extends ilContainerGUI
{
    protected MetadataHandlerInterface $md_handler;

    public function __construct()
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];
        $lng = $container->language();
        $refId = $container
            ->http()
            ->wrapper()
            ->query()
            ->retrieve("ref_id", $container->refinery()->kindlyTo()->int());
        parent::__construct([], $refId, true, false);


        $this->type = 'talt';

        $lng->loadLanguageModule("etal");
        $lng->loadLanguageModule("meta");

        $this->md_handler = new MetadataHandler();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        if (!$next_class && ($cmd === 'create' || $cmd === 'save')) {
            $this->setCreationMode();
        }

        switch ($next_class) {
            case 'ilinfoscreengui':
                parent::prepareOutput();
                $this->tabs_gui->activateTab('info_short');
                $ilInfoScreenGUI = new ilInfoScreenGUI($this);
                $this->ctrl->forwardCommand($ilInfoScreenGUI);
                break;
            default:
                parent::executeCommand();
        }
    }

    public function viewObject(): void
    {
        $this->tabs_gui->activateTab('view_content');

        $form = $this->md_handler->getDisabledEditForm(
            $this->object->getType(),
            $this->object->getId(),
            ilObjEmployeeTalk::TYPE,
            0
        );

        $this->tpl->setContent($form->render());
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $form = parent::initEditForm();
        $form->addCommandButton("view", $this->lng->txt("cancel"));
        return $form;
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form): void
    {
        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'activation_online');
        $online->setInfo($this->lng->txt('talt_activation_online_info'));
        $a_form->addItem($online);

        parent::initEditCustomForm($a_form);
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        $a_values['activation_online'] = !boolval($this->object->getOfflineStatus());

        parent::getEditFormCustomValues($a_values);
    }

    public function addExternalEditFormCustom(ilPropertyFormGUI $form): void
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setParentForm($form);
        $header->setTitle("Metadata");

        $this->md_handler->attachSelectionToForm(
            $this->object->getType(),
            $this->object->getId(),
            ilObjEmployeeTalk::TYPE,
            0,
            $form
        );

        parent::addExternalEditFormCustom($form);
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $this->object->setOfflineStatus(!boolval($form->getInput('activation_online')));

        $this->md_handler->saveSelectionFromForm(
            $this->object->getType(),
            $this->object->getId(),
            ilObjEmployeeTalk::TYPE,
            0,
            $form
        );

        parent::updateCustom($form);
    }

    /**
     * infoScreen redirect handling of ObjListGUI
     */
    public function infoScreenObject(): void
    {
        $this->ctrl->redirectByClass(strtolower(ilInfoScreenGUI::class), "showSummary");
    }

    protected function getTabs(): void
    {
        $read_access_ref_id = $this->rbacsystem->checkAccess('visible,read', $this->object->getRefId());
        if ($read_access_ref_id) {
            $this->tabs_gui->addTab('view_content', $this->lng->txt("content"), $this->ctrl->getLinkTarget($this, "view"));
            $this->tabs_gui->addTab("info_short", "Info", $this->ctrl->getLinkTargetByClass(strtolower(ilInfoScreenGUI::class), "showSummary"));
        }

        if ($this->rbacsystem->checkAccess('write', $this->object->getRefId(), $this->type)) {
            $this->tabs_gui->addTab('settings', $this->lng->txt("settings"), $this->ctrl->getLinkTarget($this, "edit"));
        }
    }

    protected function initCreationForms(string $new_type): array
    {
        return [
            self::CFORM_NEW => $this->initCreateForm($new_type)
        ];
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        parent::addAdminLocatorItems(true);

        $this->ctrl->setParameterByClass(
            strtolower(ilObjTalkTemplateAdministrationGUI::class),
            'ref_id',
            ilObjTalkTemplateAdministration::getRootRefId()
        );
        $this->locator->addItem(
            $this->lng->txt('obj_tala'),
            $this->ctrl->getLinkTargetByClass(
                ilObjTalkTemplateAdministrationGUI::class,
                ControlFlowCommand::INDEX
            )
        );
        $this->ctrl->clearParameterByClass(
            strtolower(ilObjTalkTemplateAdministrationGUI::class),
            'ref_id'
        );

        $this->locator->addItem(
            ilObject::_lookupTitle(
                ilObject::_lookupObjId($this->object->getRefId())
            ),
            $this->ctrl->getLinkTargetByClass([
                strtolower(ilAdministrationGUI::class),
                strtolower(ilObjTalkTemplateAdministrationGUI::class),
                strtolower(self::class),
            ], ControlFlowCommand::INDEX)
        );
    }

    public static function _goto(string $refId): void
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];
        if (!ilObject::_exists((int) $refId, true)) {
            $container["tpl"]->setOnScreenMessage(
                'failure',
                $container->language()->txt("permission_denied"),
                true
            );
            $container->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
        $container->ctrl()->setParameterByClass(strtolower(self::class), 'ref_id', $refId);
        $container->ctrl()->redirectByClass([
            strtolower(ilAdministrationGUI::class),
            strtolower(ilObjTalkTemplateAdministrationGUI::class),
            strtolower(self::class),
        ], ControlFlowCommand::INDEX);
    }
}
