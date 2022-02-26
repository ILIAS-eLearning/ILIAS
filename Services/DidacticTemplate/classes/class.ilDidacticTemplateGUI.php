<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for didactic template settings inside repository objects
 * @author            Stefan Meyer <meyer@leifos.com>
 * @ingroup           ServicesDidacticTemplate
 * @ilCtrl_IsCalledBy ilDidacticTemplateGUI: ilPermissionGUI
 */
class ilDidacticTemplateGUI
{
    private object $parent_object;
    private ilLanguage $lng;
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs;
    private ilGlobalTemplateInterface $tpl;
    protected int $requested_template_id = 0;

    /**
     * Constructor
     */
    public function __construct(object $a_parent_obj, int $requested_template_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('didactic');
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->parent_object = $a_parent_obj;
        $this->requested_template_id = (int) ($_REQUEST['tplid'] ?? 0);
        if ($requested_template_id > 0) {
            $this->requested_template_id = $requested_template_id;
        }
    }

    public function getParentObject() : object
    {
        return $this->parent_object;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = 'overview';
                }
                $this->$cmd();

                break;
        }
    }

    public function appendToolbarSwitch(ilToolbarGUI $toolbar, string $a_obj_type, int $a_ref_id) : bool
    {
        $tpls = ilDidacticTemplateSettings::getInstanceByObjectType($a_obj_type)->getTemplates();
        $value = ilDidacticTemplateObjSettings::lookupTemplateId($this->getParentObject()->getObject()->getRefId());

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
            if ($tpl->isEffective($a_ref_id)) {
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
    protected function confirmTemplateSwitch() : void
    {
        // Check if template is changed
        $new_tpl_id = $this->requested_template_id;
        if ($new_tpl_id == ilDidacticTemplateObjSettings::lookupTemplateId($this->getParentObject()->getObject()->getRefId())) {
            $this->logger->debug('Template id: ' . $new_tpl_id);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('didactic_not_changed'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('didactic_confirm_apply_new_template'));
        $confirm->setConfirm($this->lng->txt('apply'), 'switchTemplate');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        if ($new_tpl_id) {
            $dtpl = new ilDidacticTemplateSetting($new_tpl_id);

            $confirm->addItem(
                'tplid',
                (string) $new_tpl_id,
                $dtpl->getPresentationTitle() .
                '<div class="il_Description">' .
                $dtpl->getPresentationDescription() . ' ' .
                '</div>'
            );
        } else {
            $confirm->addItem(
                'tplid',
                (string) $new_tpl_id,
                $this->lng->txt('default') . ' ' .
                '<div class="il_Description">' .
                sprintf(
                    $this->lng->txt('didactic_default_type_info'),
                    $this->lng->txt('objs_' . $this->getParentObject()->getObject()->getType())
                ) .
                '</div>'
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Return to parent gui
     */
    protected function cancel() : void
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * Switch Template
     */
    protected function switchTemplate() : void
    {
        $new_tpl_id = $this->requested_template_id;
        ilDidacticTemplateUtils::switchTemplate($this->getParentObject()->object->getRefId(), $new_tpl_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('didactic_template_applied'), true);
        $this->ctrl->returnToParent($this);
    }
}
