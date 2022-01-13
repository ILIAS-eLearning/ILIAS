<?php declare(strict_types=1);

use ILIAS\UI\Component\Input;

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

    /**
     * @var ilLSGlobalSettingsDB
     */
    protected $settings_db;

    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = 'lsos';

        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->rbacsystem = $DIC['rbacsystem'];
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->settings_db = new ilLSGlobalSettingsDB($DIC['ilSetting']);
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
        $this->request = $DIC->http()->request();
    }

    public function getAdminTabs()
    {
        $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass(self::class, self::CMD_EDIT));
        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
        }
    }

    public function executeCommand()
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
                function ($v) {
                    return (float) $v;
                }
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
        $form = $this->ui_factory->input()->container()->form()
            ->standard($target, [$section])
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function ($data) {
                        return array_shift($data);
                    }
                )
            );

        return $form;
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
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        }
        $this->show($form);
    }
}
