<?php declare(strict_types=1);

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
 
use ILIAS\UI\Component\Input;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * LearningSequence Administration Settings
 *
 * @ilCtrl_Calls ilObjLearningSequenceAdminGUI: ilPermissionGUI
 */
class ilObjLearningSequenceAdminGUI extends ilObjectGUI
{
    const CMD_VIEW = 'view';
    const CMD_EDIT = 'edit';
    const CMD_SAVE = 'save';
    const F_POLL_INTERVAL = 'polling';

    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected \ILIAS\Refinery\Factory $refinery;
    protected ilLSGlobalSettingsDB $settings_db;

    public function __construct($data, int $id, bool $call_by_reference = true, bool $prepare_output = true)
    {
        $this->type = 'lsos';

        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];

        parent::__construct($data, $id, $call_by_reference, $prepare_output);

        $this->settings_db = new ilLSGlobalSettingsDB($DIC['ilSetting']);
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
        $this->request = $DIC->http()->request();
    }

    public function getAdminTabs() : void
    {
        $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass(self::class, self::CMD_EDIT));
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
        }
    }

    public function executeCommand() : void
    {
        $this->checkPermission('read');
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                switch ($cmd) {
                    case self::CMD_VIEW:
                    case self::CMD_EDIT:
                        $this->edit();
                        break;
                    case self::CMD_SAVE:
                        $this->save();
                        break;
                    default:
                        throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
                }
        }
    }

    protected function getForm(array $values = []) : Input\Container\Form\Form
    {
        $target = $this->ctrl->getLinkTargetByClass(self::class, self::CMD_SAVE);
        $poll_interval = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt("lso_admin_interval_label"),
            $this->lng->txt("lso_admin_interval_byline")
        )
        ->withAdditionalTransformation(
            $this->refinery->int()->isGreaterThan(0)
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn ($v) => (float) $v
            )
        );

        if (isset($values[self::F_POLL_INTERVAL])) {
            $poll_interval = $poll_interval->withValue($values[self::F_POLL_INTERVAL]);
        }

        $section = $this->ui_factory->input()->field()->section(
            [self::F_POLL_INTERVAL => $poll_interval],
            $this->lng->txt("lso_admin_form_title"),
            $this->lng->txt("lso_admin_form_byline")
        );

        return $this->ui_factory->input()->container()->form()
            ->standard($target, [$section])
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn ($data) => array_shift($data)
                )
            );
    }

    protected function show(Input\Container\Form\Form $form) : void
    {
        $this->tpl->setContent(
            $this->ui_renderer->render($form)
        );
    }

    protected function edit() : void
    {
        $values = [
            self::F_POLL_INTERVAL => $this->settings_db->getSettings()->getPollingIntervalSeconds()
        ];
        $form = $this->getForm($values);
        $this->show($form);
    }

    protected function save() : void
    {
        $form = $this->getForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data) {
            $settings = $this->settings_db->getSettings()
                ->withPollingIntervalSeconds($data[self::F_POLL_INTERVAL]);
            $this->settings_db->storeSettings($settings);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }
        $this->show($form);
    }
}
