<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';

/**
 * GUI class for didactic template settings inside repository objects
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 * @ilCtrl_IsCalledBy ilDidacticTemplateGUI: ilPermissionGUI
 */
class ilDidacticTemplateGUI
{
    private $parent_object;
    private $lng;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $this->parent_object = $a_parent_obj;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('didactic');
    }

    public function getParentObject()
    {
        return $this->parent_object;
    }

    /**
     * Execute command
     * @return <type>
     */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = 'overview';
                }
                $this->$cmd();

                break;
        }
        return true;
    }

    public function appendToolbarSwitch(ilToolbarGUI $toolbar, $a_obj_type, $a_ref_id)
    {
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettings.php';
        $tpls = ilDidacticTemplateSettings::getInstanceByObjectType($a_obj_type)->getTemplates();

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
        $value = ilDidacticTemplateObjSettings::lookupTemplateId($this->getParentObject()->object->getRefId());

        if (!count($tpls) && !$value) {
            return false;
        }

        // Add template switch
        $toolbar->addText($this->lng->txt('didactic_selected_tpl_option'));

        // Show template options
        $options = array(0 => $this->lng->txt('didactic_default_type'));
        $excl_tpl = false;

        foreach ($tpls as $tpl) {
            //just add if template is effective except template is already applied to this object
            if ($tpl->isEffective($_GET['ref_id'])) {
                $options[$tpl->getId()] = $tpl->getPresentationTitle();

                if ($tpl->isExclusive()) {
                    $excl_tpl = true;
                }
            }
        }

        if ($excl_tpl && $value != 0) {
            //remove default entry if an exclusive template exists but only if the actual entry isn't the default
            unset($options[0]);
        }

        if (!in_array($value, array_keys($options)) || ($excl_tpl && $value == 0)) {
            $options[$value] = $this->lng->txt('not_available');
        }

        if (count($options) < 2) {
            return false;
        }

        include_once './Services/Form/classes/class.ilSelectInputGUI.php';
        $tpl_selection = new ilSelectInputGUI(
            '',
            'tplid'
        );
        $tpl_selection->setOptions($options);
        $tpl_selection->setValue($value);
        $toolbar->addInputItem($tpl_selection);

        // Apply templates switch
        $toolbar->addFormButton($this->lng->txt('change'), 'confirmTemplateSwitch');
        return true;
    }

    /*
     * Show didactic template switch confirmation screen
     */
    protected function confirmTemplateSwitch()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';

        // Check if template is changed
        $new_tpl_id = (int) $_REQUEST['tplid'];
        if ($new_tpl_id == ilDidacticTemplateObjSettings::lookupTemplateId($this->getParentObject()->object->getRefId())) {
            ilLoggerFactory::getLogger('otpl')->debug('Template id: ' . $new_tpl_id);
            ilUtil::sendInfo($this->lng->txt('didactic_not_changed'), true);
            $ilCtrl->returnToParent($this);
        }

        $ilTabs->clearTargets();
        $ilTabs->clearSubTabs();

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($ilCtrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('didactic_confirm_apply_new_template'));
        $confirm->setConfirm($this->lng->txt('apply'), 'switchTemplate');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        if ($new_tpl_id) {
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';
            $dtpl = new ilDidacticTemplateSetting($new_tpl_id);

            $confirm->addItem(
                'tplid',
                $new_tpl_id,
                $dtpl->getPresentationTitle() .
                '<div class="il_Description">' .
                $dtpl->getPresentationDescription() . ' ' .
                '</div>'
            );
        } else {
            $confirm->addItem(
                'tplid',
                $new_tpl_id,
                $this->lng->txt('default') . ' ' .
                '<div class="il_Description">' .
                sprintf(
                    $this->lng->txt('didactic_default_type_info'),
                    $this->lng->txt('objs_' . $this->getParentObject()->object->getType())
                ) .
                '</div>'
            );
        }
        $tpl->setContent($confirm->getHTML());
    }

    /**
     * Return to parent gui
     */
    protected function cancel()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->returnToParent($this);
    }

    /**
     * Switch Template
     */
    protected function switchTemplate()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $new_tpl_id = (int) $_REQUEST['tplid'];

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateUtils.php';
        ilDidacticTemplateUtils::switchTemplate($this->getParentObject()->object->getRefId(), $new_tpl_id);

        ilUtil::sendSuccess($this->lng->txt('didactic_template_applied'), true);
        $ilCtrl->returnToParent($this);
    }
}
