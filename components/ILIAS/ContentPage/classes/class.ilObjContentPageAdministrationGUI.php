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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\ContentPage\GlobalSettings\Storage;
use ILIAS\ContentPage\GlobalSettings\StorageImpl;
use ILIAS\UI\Component\Component;
use ILIAS\HTTP\GlobalHttpState;

/**
 * @ilCtrl_Calls ilObjContentPageAdministrationGUI: ilPermissionGUI
 */
class ilObjContentPageAdministrationGUI extends ilObjectGUI
{
    private const CMD_VIEW = 'view';
    private const CMD_EDIT = 'edit';
    private const CMD_SAVE = 'save';
    private const F_READING_TIME = 'reading_time';

    private readonly Storage $settings_storage;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;

        $this->type = 'cpad';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule($this->type);

        $this->settings_storage = new StorageImpl($DIC->settings());
    }

    public function getAdminTabs(): void
    {
        if ($this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass(self::class, self::CMD_EDIT));
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
                [],
                ilPermissionGUI::class
            );
        }
    }

    public function executeCommand(): void
    {
        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $nextClass = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd() ?? '';
        $this->prepareOutput();

        switch (strtolower($nextClass)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                match ($cmd) {
                    self::CMD_VIEW, self::CMD_EDIT => $this->edit(),
                    self::CMD_SAVE => $this->save(),
                    default => throw new RuntimeException(__METHOD__ . ' :: Unknown command ' . $cmd),
                };
        }
    }

    private function getForm(array $values = []): Form
    {
        $may_write = $this->rbac_system->checkAccess('write', $this->object->getRefId());

        $action = $this->ctrl->getLinkTargetByClass(self::class, self::CMD_SAVE);
        if (!$may_write) {
            $action = $this->ctrl->getLinkTargetByClass(self::class, self::CMD_VIEW);
        }

        $readingTimeStatus = $this->ui_factory
            ->input()
            ->field()
            ->checkbox(
                $this->lng->txt('cpad_reading_time_status'),
                $this->lng->txt('cpad_reading_time_status_desc')
            )
            ->withDisabled(!$may_write);

        if (isset($values[self::F_READING_TIME])) {
            $readingTimeStatus = $readingTimeStatus->withValue($values[self::F_READING_TIME]);
        }

        $section = $this->ui_factory->input()->field()->section(
            [self::F_READING_TIME => $readingTimeStatus],
            $this->lng->txt('settings')
        )->withDisabled(!$may_write);

        $form = $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard($action, [$section])
            ->withAdditionalTransformation($this->refinery->custom()->transformation(static function ($values): array {
                return array_merge(...$values);
            }));

        if (!$may_write) {
            $form = $form->withSubmitLabel($this->lng->txt('refresh'));
        }

        return $form;
    }

    /**
     * @param list<Component> $components
     */
    private function show(array $components): void
    {
        $this->tpl->setContent(
            $this->ui_renderer->render($components)
        );
    }

    private function edit(): void
    {
        $values = [
            self::F_READING_TIME => $this->settings_storage->getSettings()->isReadingTimeEnabled(),
        ];

        $form = $this->getForm($values);

        $this->show([$form]);
    }

    private function save(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $form = $this->getForm()->withRequest($this->http->request());
        $data = $form->getData();

        if ($data === null || $this->request->getMethod() !== 'POST') {
            $this->show([$form]);
            return;
        }

        $readingTime = $data[self::F_READING_TIME];
        $settings = $this->settings_storage
            ->getSettings()
            ->withDisabledReadingTime();
        if ($readingTime) {
            $settings = $settings->withEnabledReadingTime();
        }
        $this->settings_storage->store($settings);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }
}
