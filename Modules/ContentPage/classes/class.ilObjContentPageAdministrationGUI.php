<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\ContentPage\GlobalSettings\Storage;
use ILIAS\ContentPage\GlobalSettings\StorageImpl;
use ILIAS\UI\Component\Component;

/**
 * Class ilObjContentPageAdministrationGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls ilObjContentPageAdministrationGUI: ilPermissionGUI
 */
class ilObjContentPageAdministrationGUI extends ilObjectGUI
{
    private const CMD_VIEW = 'view';
    private const CMD_EDIT = 'edit';
    private const CMD_SAVE = 'save';

    private const F_READING_TIME = 'reading_time';

    /** @var ServerRequestInterface */
    private $httpRequest;
    /** @var Factory */
    private $uiFactory;
    /** @var Renderer */
    private $uiRenderer;
    /** @var ILIAS\Refinery\Factory */
    private $refinery;
    /** @var Storage */
    private $settingsStorage;
    /** @var ilErrorHandling */
    private $error;

    /**
     * @ineritdoc
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->type = 'cpad';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule($this->type);

        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->httpRequest = $DIC->http()->request();
        $this->refinery = $DIC->refinery();
        $this->error = $DIC['ilErr'];
        $this->settingsStorage = new StorageImpl($DIC->settings());
    }

    /**
     * @ineritdoc
     */
    public function getAdminTabs()
    {
        if ($this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass(self::class, self::CMD_EDIT));
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), [], 'ilpermissiongui');
        }
    }

    /**
     * @ineritdoc
     */
    public function executeCommand()
    {
        if (!$this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch (strtolower($nextClass)) {
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

    /**
     * @param array $values
     * @return Form
     */
    private function getForm(array $values = []) : Form
    {
        $action = $this->ctrl->getLinkTargetByClass(self::class, self::CMD_SAVE);

        $readingTimeStatus = $this->uiFactory
            ->input()
            ->field()
            ->checkbox(
                $this->lng->txt('cpad_reading_time_status'),
                $this->lng->txt('cpad_reading_time_status_desc')
            );

        if (isset($values[self::F_READING_TIME])) {
            $readingTimeStatus = $readingTimeStatus->withValue($values[self::F_READING_TIME]);
        }

        $section = $this->uiFactory->input()->field()->section(
            [self::F_READING_TIME => $readingTimeStatus],
            $this->lng->txt('settings')
        );

        return $this->uiFactory
            ->input()
            ->container()
            ->form()
            ->standard($action, [$section])
            ->withAdditionalTransformation($this->refinery->custom()->transformation(static function ($values) : array {
                return call_user_func_array('array_merge', $values);
            }));
    }

    /**
     * @param Component[] $components
     */
    protected function show(array $components) : void
    {
        $this->tpl->setContent(
            $this->uiRenderer->render($components)
        );
    }

    protected function edit() : void
    {
        $values = [
            self::F_READING_TIME => $this->settingsStorage->getSettings()->isReadingTimeEnabled(),
        ];

        $form = $this->getForm($values);

        $this->show([$form]);
    }

    protected function save() : void
    {
        if (!$this->checkPermissionBool('write')) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getForm()->withRequest($this->httpRequest);

        $data = $form->getData();
        if ($data) {
            $readingTime = $data[self::F_READING_TIME];
            $settings = $this->settingsStorage->getSettings()
                ->withDisabledReadingTime();
            if ($readingTime) {
                $settings = $settings->withEnabledReadingTime();
            }
            $this->settingsStorage->store($settings);
        }

        $this->show(
            [$this->uiFactory->messageBox()->success($this->lng->txt('saved_successfully')), $form]
        );
    }
}
