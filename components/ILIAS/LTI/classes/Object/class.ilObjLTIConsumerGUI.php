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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;

/**
 * Screens of the repository object type lti. The object is created for one tool, which cannot be
 * changed afterwards, and opening it launches that tool.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 *
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIObjectSettingsGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIToolLaunchGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIToolSettingsGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilPermissionGUI
 */
class ilObjLTIConsumerGUI extends ilObject2GUI
{
    public const string CMD_LAUNCH = 'launch';

    private const string CMD_SAVE_OWN_TOOL = 'saveOwnTool';
    private const string VERSION_PARAM = 'version';
    private const string TAB_CONTENT = 'tab_content';
    private const string TAB_SETTINGS = 'tab_settings';
    private const string TAB_PERMISSIONS = 'id_permissions';

    public function getType(): string
    {
        return 'lti';
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule('lti');
        $this->prepareOutput();

        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilCommonActionDispatcherGUI::class):
                $this->ctrl->forwardCommand(ilCommonActionDispatcherGUI::getInstanceFromAjaxCall());
                break;

            case strtolower(ilLTIToolLaunchGUI::class):
                $this->tabs_gui->activateTab(self::TAB_CONTENT);
                $this->ctrl->forwardCommand(new ilLTIToolLaunchGUI($this->getLTIObject()));
                break;

            case strtolower(ilLTIObjectSettingsGUI::class):
                $this->checkPermission('write');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->ctrl->forwardCommand(new ilLTIObjectSettingsGUI($this->getLTIObject()));
                break;

            case strtolower(ilLTIToolSettingsGUI::class):
                $this->checkPermission('write');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->ctrl->forwardCommand(new ilLTIToolSettingsGUI($this->getLTIObject()));
                break;

            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab(self::TAB_PERMISSIONS);
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            default:
                $this->{$this->ctrl->getCmd(self::CMD_LAUNCH)}();
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function setTabs(): void
    {
        if ($this->object instanceof ilObjLTIConsumer && $this->checkPermissionBool('read')) {
            $this->tabs_gui->addTab(
                self::TAB_CONTENT,
                $this->lng->txt('tab_content'),
                $this->ctrl->getLinkTarget($this, self::CMD_LAUNCH)
            );
        }

        if ($this->object instanceof ilObjLTIConsumer && $this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                self::TAB_SETTINGS,
                $this->lng->txt('tab_settings'),
                $this->ctrl->getLinkTargetByClass(ilLTIObjectSettingsGUI::class, ilLTIObjectSettingsGUI::CMD_SHOW)
            );
        }

        parent::setTabs();
    }

    /**
     * Offers the tools the user may pick and, with the permission to define tools, a form for one of
     * their own. There is no title to fill in: an object is named after its tool.
     *
     * @throws ilCtrlException
     */
    public function create(): void
    {
        if (!$this->checkPermissionBool('create', '', $this->getType())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ctrl->saveParameter($this, 'crtptrefid');
        $this->ctrl->saveParameter($this, 'crtcb');
        $this->ctrl->setParameter($this, 'new_type', $this->getType());
        $this->tpl->setTitleIcon(ilObject::getIconForType($this->getType()));
        $this->tpl->setTitle($this->lng->txt('obj_' . $this->getType()));
        $this->tabs_gui->setBackTarget($this->lng->txt('cancel'), $this->ctrl->getLinkTargetByClass(static::class, 'cancel'));

        $this->renderCreation();
    }

    /**
     * The ways of creating an object, one section each, with the first one open. The section of an own
     * tool opens instead once it has been used, that is when its form comes back with errors or when
     * the LTI version has been switched.
     *
     * @throws ilCtrlException
     */
    private function renderCreation(?Form $own_form = null): void
    {
        $this->tpl->addCss('./assets/css/lti_creation.css');

        $accordion = new ilAccordionGUI();
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);
        $accordion->setContentClass('il-lti-creation');
        // the section a user works in stays open, which is also what lets a section be forced open
        $accordion->setId('lti_create');
        $accordion->setUseSessionStorage(true);
        $accordion->addItem(
            $this->lng->txt($this->getType() . '_select_provider'),
            $this->ui_renderer->render($this->buildToolSelection())
        );

        if (ilObjLTIAdministrationAccess::hasOwnToolCreationAccess()) {
            $accordion->addItem(
                $this->lng->txt('lti_custom_new'),
                $this->ui_renderer->render($this->buildOwnTool($own_form)),
                $own_form !== null || $this->request_wrapper->has(self::VERSION_PARAM)
            );
        }

        $this->tpl->setContent($accordion->getHTML());
    }

    /**
     * Creates the object for the tool of the request.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    public function save(): void
    {
        if (!$this->checkPermissionBool('create', '', $this->getType())) {
            $this->error->raiseError($this->lng->txt('no_create_permission'), $this->error->MESSAGE);
        }

        $tool = new ilLTITool($this->getIntParameter('tool_id'));
        if ($tool->getId() === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('lti_no_provider_selected'));
            $this->create();
            return;
        }

        $this->createForTool($tool);
    }

    /**
     * Stores a tool of the user and creates the object for it.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function saveOwnTool(): void
    {
        if (!ilObjLTIAdministrationAccess::hasOwnToolCreationAccess()) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ctrl->setParameter($this, 'new_type', $this->getType());
        $form = $this->buildOwnToolForm();
        $with_errors = $form->save($this->getOwnToolAction(), $this->request, true);

        if ($with_errors !== null) {
            $this->renderCreation($with_errors);
            return;
        }

        $this->createForTool(new ilLTITool($form->getSavedId()));
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function launch(): void
    {
        $this->checkPermission('read');
        $this->tabs_gui->activateTab(self::TAB_CONTENT);
        $this->ctrl->forwardCommand(new ilLTIToolLaunchGUI($this->getLTIObject()));
    }

    /**
     * @throws ilCtrlException
     */
    private function buildToolSelection(): array
    {
        global $DIC;

        $table = ilLTIToolTable::forSelection(
            $this->lng,
            $this->user,
            $this->ui_factory,
            $this->ui_renderer,
            $DIC->uiService(),
            $this->tpl,
            $this->ctrl,
            $this->request,
            $this,
            'save'
        );
        $filter = $table->getFilter($this->ctrl->getLinkTarget($this, 'create'));

        return [$filter, $table->getTable($filter)];
    }

    /**
     * @throws ilCtrlException
     */
    /**
     * Each LTI version has its own form, because they authenticate the tool differently. The control on
     * top switches between them.
     *
     * @return array
     * @throws ilCtrlException
     */
    private function buildOwnTool(?Form $form): array
    {
        $version = $this->getRequestedVersion();
        $content = [$this->buildVersionControl($version)];

        $notice = ilLTIToolForm::getDeprecationNotice($this->ui_factory, $this->lng, $version);
        if ($notice !== null) {
            $content[] = $notice;
        }

        $content[] = $form ?? $this->buildOwnToolForm()->getForm(
            $this->getOwnToolAction(),
            $this->lng->txt('lti_add_own_provider')
        );

        return $content;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildVersionControl(string $active): Component
    {
        // the tool of the last row of the selection must not end up in the links of this control
        $this->ctrl->setParameter($this, 'tool_id', null);

        $labels = [
            ilLTITool::VERSION_ADVANTAGE => $this->lng->txt('lti_version_advantage'),
            ilLTITool::VERSION_1P1 => $this->lng->txt('lti_version_1p1_deprecated'),
        ];

        $actions = [];
        foreach ($labels as $version => $label) {
            $this->ctrl->setParameter($this, self::VERSION_PARAM, $version);
            $actions[$label] = $this->ctrl->getLinkTarget($this, 'create');
        }
        $this->ctrl->setParameter($this, self::VERSION_PARAM, $active);

        return $this->ui_factory->viewControl()
            ->mode($actions, $this->lng->txt('lti_con_version'))
            ->withActive($labels[$active]);
    }

    private function buildOwnToolForm(): ilLTIToolForm
    {
        return new ilLTIToolForm(
            $this->lng,
            $this->ui_factory,
            $this->refinery,
            $this->user,
            0,
            $this->getRequestedVersion()
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function getOwnToolAction(): string
    {
        $this->ctrl->setParameter($this, self::VERSION_PARAM, $this->getRequestedVersion());

        return $this->ctrl->getFormAction($this, self::CMD_SAVE_OWN_TOOL);
    }

    private function getRequestedVersion(): string
    {
        $version = $this->request_wrapper->has(self::VERSION_PARAM)
            ? $this->request_wrapper->retrieve(self::VERSION_PARAM, $this->refinery->kindlyTo()->string())
            : '';

        return $version === ilLTITool::VERSION_1P1 ? ilLTITool::VERSION_1P1 : ilLTITool::VERSION_ADVANTAGE;
    }

    /**
     * An object carries the title and the description of its tool, as there is nothing else to name it after.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    private function createForTool(ilLTITool $tool): void
    {
        $object = new ilObjLTIConsumer();
        $object->setType($this->getType());
        $object->processAutoRating();
        $object->setTitle($tool->getTitle());
        $object->setDescription($tool->getDescription());
        $object->setToolId($tool->getId());
        $object->create();

        $this->ctrl->setParameter($this, 'new_type', '');
        $this->putObjectInTree($object);

        // a new object is offline and named after its tool, so its settings are where it is finished
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_added'), true);
        $this->ctrl->setParameterByClass(ilLTIObjectSettingsGUI::class, 'ref_id', $object->getRefId());
        $this->ctrl->redirectByClass(
            [ilObjLTIConsumerGUI::class, ilLTIObjectSettingsGUI::class],
            ilLTIObjectSettingsGUI::CMD_SHOW
        );
    }

    private function getIntParameter(string $name): int
    {
        return $this->request_wrapper->has($name)
            ? $this->request_wrapper->retrieve($name, $this->refinery->kindlyTo()->int())
            : 0;
    }

    /**
     * @throws ilObjectException
     */
    private function getLTIObject(): ilObjLTIConsumer
    {
        if (!$this->object instanceof ilObjLTIConsumer) {
            throw new ilObjectException('no LTI object given');
        }

        return $this->object;
    }
}
