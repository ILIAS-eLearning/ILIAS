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
 * Settings of the tool an object launches, reachable from the object for the user who defined the
 * tool. A tool released for everybody is only changed in the administration.
 *
 * A tool that offers Deep Linking lets the user pick more of its content from here, for new objects
 * next to this one.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolSettingsGUI
{
    public const string CMD_SHOW = 'showToolSettings';
    public const string CMD_SAVE = 'saveToolSettings';

    private const string SUBTAB_TOOL = 'subtab_provider_settings';

    private readonly ILIAS\DI\Container $dic;

    public function __construct(private readonly ilObjLTITool $object)
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     * @throws ilMDServicesException
     */
    public function executeCommand(): void
    {
        $this->assertOwnTool();
        $this->dic->tabs()->activateSubTab(self::SUBTAB_TOOL);

        match ($this->dic->ctrl()->getCmd(self::CMD_SHOW)) {
            self::CMD_SAVE => $this->save(),
            default => $this->show(),
        };
    }

    /**
     * @throws ilCtrlException
     */
    private function show(): void
    {
        $this->render($this->buildForm()->getForm($this->getFormAction()));
    }

    /**
     * @throws ilCtrlException
     * @throws ilMDServicesException
     */
    private function save(): void
    {
        $with_errors = $this->buildForm()->save($this->getFormAction(), $this->dic->http()->request());
        if ($with_errors !== null) {
            $this->render($with_errors);
            return;
        }

        // setting the id again drops the tool read before saving, so the keywords are the new ones
        $this->object->setToolId($this->object->getToolId());
        $this->object->syncKeywordsFromTool();

        $this->dic->ui()->mainTemplate()->setOnScreenMessage('success', $this->dic->language()->txt('settings_saved'), true);
        $this->dic->ctrl()->redirect($this, self::CMD_SHOW);
    }

    private function render(object $form): void
    {
        $tool = $this->object->getTool();
        $notice = ilLTIToolForm::getDeprecationNotice(
            $this->dic->ui()->factory(),
            $this->dic->language(),
            $tool->getLtiVersion()
        );

        $this->addSelectContentButton($tool);
        $this->dic->ui()->mainTemplate()->setContent(
            $this->dic->ui()->renderer()->render($notice === null ? [$form] : [$notice, $form])
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function addSelectContentButton(ilLTITool $tool): void
    {
        $parent_ref_id = (int) $this->dic->repositoryTree()->getParentId($this->object->getRefId());
        if (!$tool->offersDeepLinking() || !$this->dic->access()->checkAccess('create_lti', '', $parent_ref_id)) {
            return;
        }

        $ctrl = $this->dic->ctrl();
        $ctrl->setParameterByClass(ilObjLTIToolGUI::class, 'ref_id', $parent_ref_id);
        $ctrl->setParameterByClass(ilObjLTIToolGUI::class, 'new_type', $this->object->getType());
        $ctrl->setParameterByClass(ilObjLTIToolGUI::class, 'tool_id', $tool->getId());
        $ctrl->setParameterByClass(ilObjLTIToolGUI::class, ilObjLTIToolGUI::ORIGIN_PARAM, $this->object->getRefId());
        $url = $ctrl->getLinkTargetByClass([ilRepositoryGUI::class, ilObjLTIToolGUI::class], ilObjLTIToolGUI::CMD_SELECT_CONTENT);
        foreach (['new_type', 'tool_id', ilObjLTIToolGUI::ORIGIN_PARAM] as $parameter) {
            $ctrl->setParameterByClass(ilObjLTIToolGUI::class, $parameter, null);
        }
        $ctrl->setParameterByClass(ilObjLTIToolGUI::class, 'ref_id', $this->object->getRefId());

        $this->dic->toolbar()->addComponent(
            $this->dic->ui()->factory()->button()->standard($this->dic->language()->txt('lti_deep_linking_select'), $url)
        );
    }

    private function buildForm(): ilLTIToolForm
    {
        $tool = $this->object->getTool();

        return new ilLTIToolForm(
            $this->dic->language(),
            $this->dic->ui()->factory(),
            $this->dic->refinery(),
            $this->dic->user(),
            $tool->getId(),
            $tool->getLtiVersion()
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function getFormAction(): string
    {
        return $this->dic->ctrl()->getFormAction($this, self::CMD_SAVE);
    }

    /**
     * @throws ilObjectException
     */
    private function assertOwnTool(): void
    {
        $tool = $this->object->getTool();
        if ($tool->isGlobal() || $tool->getCreator() !== $this->dic->user()->getId()) {
            throw new ilObjectException('the tool of this object is not yours to change');
        }
    }
}
