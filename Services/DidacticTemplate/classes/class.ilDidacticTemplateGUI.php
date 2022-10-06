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
    private int $requested_template_id;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;
    private ilLogger $logger;

    public function __construct(object $a_parent_obj, int $requested_template_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('didactic');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->logger = $DIC->logger()->otpl();

        $this->parent_object = $a_parent_obj;
        if ($requested_template_id === 0) {
            $this->initTemplateIdFromPost();
        } else {
            $this->requested_template_id = $requested_template_id;
        }
    }

    protected function initTemplateIdFromPost()
    {
        $this->requested_template_id = 0;
        if ($this->http->wrapper()->post()->has('tplid')) {
            $this->requested_template_id = $this->http->wrapper()->post()->retrieve(
                'tplid',
                $this->refinery->kindlyTo()->int()
            );
        }
    }

    public function getParentObject(): object
    {
        return $this->parent_object;
    }

    public function executeCommand(): void
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

    public function appendToolbarSwitch(ilToolbarGUI $toolbar, string $a_obj_type, int $a_ref_id): bool
    {
        $tpls = ilDidacticTemplateSettings::getInstanceByObjectType($a_obj_type)->getTemplates();
        $value = ilDidacticTemplateObjSettings::lookupTemplateId($this->getParentObject()->getObject()->getRefId());

        if (0 === $value && 0 === count($tpls)) {
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

        if ($excl_tpl && $value !== 0) {
            //remove default entry if an exclusive template exists but only if the actual entry isn't the default
            unset($options[0]);
        }

        if (($excl_tpl && $value === 0) || !array_key_exists($value, $options)) {
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
        $tpl_selection->setValue((string) $value);
        $toolbar->addInputItem($tpl_selection);

        // Apply templates switch
        $toolbar->addFormButton($this->lng->txt('change'), 'confirmTemplateSwitch');
        return true;
    }

    /**
     * Show didactic template switch confirmation screen
     */
    protected function confirmTemplateSwitch(): void
    {
        // Check if template is changed
        $new_tpl_id = $this->requested_template_id;
        if ($new_tpl_id === ilDidacticTemplateObjSettings::lookupTemplateId($this->getParentObject()->getObject()->getRefId())) {
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
    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function switchTemplate(): void
    {
        $new_tpl_id = $this->requested_template_id;
        ilDidacticTemplateUtils::switchTemplate($this->getParentObject()->getObject()->getRefId(), $new_tpl_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('didactic_template_applied'), true);
        $this->ctrl->returnToParent($this);
    }
}
