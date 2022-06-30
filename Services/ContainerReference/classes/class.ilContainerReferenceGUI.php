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

use ILIAS\ContainerReference\StandardGUIRequest;

/**
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerReferenceGUI extends ilObjectGUI
{
    public const MAX_SELECTION_ENTRIES = 50;
    public const MODE_CREATE = 1;
    public  const MODE_EDIT = 2;

    protected ilTabsGUI $tabs;
    protected ilErrorHandling $error;
    protected array $existing_objects = [];

    protected string $target_type;
    protected string $reference_type;
    protected ilPropertyFormGUI $form;
    protected StandardGUIRequest $cont_request;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->settings = $DIC->settings();
        $lng = $DIC->language();
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $lng->loadLanguageModule('objref');
        $this->cont_request = $DIC
            ->containerReference()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        if ($this->cont_request->getCreationMode() === self::MODE_CREATE) {
            $this->setCreationMode(true);
        }

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->initForm($this->creation_mode ? self::MODE_CREATE : self::MODE_EDIT);
                $this->ctrl->forwardCommand($form);
                break;

            case 'ilpermissiongui':
                $ilTabs->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $ilCtrl->forwardCommand(new ilPermissionGUI($this));
                break;

            default:
                if ($cmd === null || $cmd === '' || $cmd === 'view') {
                    $cmd = "edit";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    protected function addLocatorItems() : void
    {
        $ilLocator = $this->locator;
        
        if ($this->object instanceof ilObject) {
            $ilLocator->addItem($this->object->getPresentationTitle(), $this->ctrl->getLinkTarget($this));
        }
    }
    
    public function redirectObject() : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->object->getTargetRefId());
        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }
    
    public function createObject() : void
    {
        $ilAccess = $this->access;
        $ilErr = $this->error;

        $new_type = $this->cont_request->getNewType();
        if (!$ilAccess->checkAccess(
            "create_" . $this->getReferenceType(),
            '',
            $this->cont_request->getRefId(),
            $new_type
        )) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
        $form = $this->initForm(self::MODE_CREATE);
        $this->tpl->setContent($form->getHTML());
    }
    
    
    public function saveObject() : void
    {
        $ilAccess = $this->access;
        
        if ($this->cont_request->getTargetId() === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->createObject();
            return;
        }
        if (!$ilAccess->checkAccess(
            'visible',
            '',
            $this->cont_request->getTargetId()
        )) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'));
            $this->createObject();
            return;
        }
        
        parent::saveObject();
    }
    
    protected function initCreateForm(string $new_type) : ilPropertyFormGUI
    {
        return $this->initForm(self::MODE_CREATE);
    }

    protected function afterSave(ilObject $new_object) : void
    {
        $target_obj_id = ilObject::_lookupObjId((int) $this->form->getInput('target_id'));
        $new_object->setTargetId($target_obj_id);
        $new_object->setTitleType((int) $this->form->getInput('title_type'));

        if ((int) $this->form->getInput('title_type') === ilContainerReference::TITLE_TYPE_CUSTOM) {
            $new_object->setTitle($this->form->getInput('title'));
        }

        $new_object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->setParameter($this, 'ref_id', $new_object->getRefId());
        $this->ctrl->setParameter($this, 'creation_mode', 0);
        $this->ctrl->redirect($this, 'firstEdit');
    }
    
    protected function firstEditObject() : void
    {
        $this->editObject();
    }

    public function editReferenceObject() : void
    {
        $this->editObject();
    }
    
    public function editObject(ilPropertyFormGUI $form = null) : void
    {
        global $DIC;

        $main_tpl = $DIC->ui()->mainTemplate();

        $ilTabs = $this->tabs;

        $ilTabs->setTabActive('settings');
        
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initForm();
        }
        $main_tpl->setContent($form->getHTML());
    }
    
    protected function initForm(int $a_mode = self::MODE_EDIT) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        if ($a_mode === self::MODE_CREATE) {
            $form->setTitle($this->lng->txt($this->getReferenceType() . '_new'));

            $this->ctrl->setParameter($this, 'creation_mode', $a_mode);
            $this->ctrl->setParameter(
                $this,
                'new_type',
                $this->cont_request->getNewType()
            );
        } else {
            $form->setTitle($this->lng->txt($this->reference_type . '_settings'));
        }

        $form->setFormAction($this->ctrl->getFormAction($this));
        if ($a_mode === self::MODE_CREATE) {
            $lv = $this->getTargetType() . "r_add";   // see also https://mantis.ilias.de/view.php?id=31863
            $form->addCommandButton('save', $this->lng->txt($lv));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        } else {
            $form->addCommandButton('update', $this->lng->txt('save'));
        }

        // title type
        $ttype = new ilRadioGroupInputGUI($this->lng->txt('title'), 'title_type');
        if ($a_mode === self::MODE_EDIT) {
            $ttype->setValue((string) $this->object->getTitleType());
        } else {
            $ttype->setValue((string) ilContainerReference::TITLE_TYPE_REUSE);
        }

        $reuse = new ilRadioOption($this->lng->txt('objref_reuse_title'));
        $reuse->setValue((string) ilContainerReference::TITLE_TYPE_REUSE);
        $ttype->addOption($reuse);
        
        $custom = new ilRadioOption($this->lng->txt('objref_custom_title'));
        $custom->setValue((string) ilContainerReference::TITLE_TYPE_CUSTOM);
        
        // title
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setSize(min(40, ilObject::TITLE_LENGTH));
        $title->setMaxLength(ilObject::TITLE_LENGTH);
        $title->setRequired(true);

        if ($a_mode === self::MODE_EDIT) {
            $title->setValue($this->object->getTitle());
        }

        $custom->addSubItem($title);
        $ttype->addOption($custom);
        $form->addItem($ttype);

        include_once("./Services/Form/classes/class.ilRepositorySelector2InputGUI.php");
        $repo = new ilRepositorySelector2InputGUI($this->lng->txt("objref_edit_ref"), "target_id");
        //$repo->setParent($this);
        $repo->setRequired(true);
        $repo->getExplorerGUI()->setSelectableTypes([$this->getTargetType()]);
        $repo->getExplorerGUI()->setTypeWhiteList(
            array_merge(
                [$this->getTargetType()],
                ["root", "cat", "grp", "fold", "crs"]
            )
        );
        $repo->setInfo($this->lng->txt($this->getReferenceType() . '_edit_info'));

        if ($a_mode === self::MODE_EDIT) {
            $repo->getExplorerGUI()->setPathOpen($this->object->getTargetRefId());
            $repo->setValue($this->object->getTargetRefId());
        }

        $form->addItem($repo);
        $this->form = $form;
        return $form;
    }

    protected function loadPropertiesFromSettingsForm(ilPropertyFormGUI $form) : bool
    {
        global $DIC;

        $ok = true;
        $access = $DIC->access();

        $this->object->setTitleType((int) $form->getInput('title_type'));
        if ((int) $form->getInput('title_type') === ilContainerReference::TITLE_TYPE_CUSTOM) {
            $this->object->setTitle($form->getInput('title'));
        }

        // check access
        if (
            !$access->checkAccess('visible', '', (int) $form->getInput('target_id'))
        ) {
            $ok = false;
            $form->getItemByPostVar('target_id')->setAlert($this->lng->txt('permission_denied'));
        }
        // check target type
        if (ilObject::_lookupType((int) $form->getInput('target_id'), true) !== $this->target_type) {
            $ok = false;
            $form->getItemByPostVar('target_id')->setAlert(
                $this->lng->txt('objref_failure_target_type') .
                ': ' .
                $this->lng->txt('obj_' . $this->target_type)
            );
        }

        $this->object->setTargetId(
            ilObject::_lookupObjId((int) $form->getInput('target_id'))
        );

        return $ok;
    }

    public function updateObject() : void
    {
        $this->checkPermission('write');

        $form = $this->initForm();
        if (
            $form->checkInput() &&
            $this->loadPropertiesFromSettingsForm($form)
        ) {
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'edit');
        }
        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->editObject($form);
    }

    public function getTargetType() : string
    {
        return $this->target_type;
    }
    
    public function getReferenceType() : string
    {
        return $this->reference_type;
    }

    protected function getTabs() : void
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent($this->getReferenceType());

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                [],
                ""
            );
        }
        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], "perm"),
                ["perm", "info", "owner"],
                'ilpermissiongui'
            );
        }
    }

    public function getId() : int
    {
        return $this->obj_id;
    }
}
