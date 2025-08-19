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

use ILIAS\Language\Language;

/**
 * Class ilObjAdvancedEditingGUI
 *
 * @author Helmut Schottmüller <hschottm@gmx.de>
 */
class ilRTESettingsGUI
{
    private ilRTESettings $settings;

    public function __construct(
        private readonly int $ref_id,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
        private readonly Language $lng,
        private readonly ilAccessHandler $access,
        ilObjUser $current_user,
        ilTabsGUI $tabs_gui
    ) {
        $this->settings = new ilRTESettings($this->lng, $current_user);
        $this->addSubTabs($tabs_gui);
    }

    private function addSubtabs(ilTabsGUI $tabs_gui): void
    {
        $tabs_gui->addSubTabTarget(
            'adve_general_settings',
            $this->ctrl->getLinkTargetByClass([ilObjAdvancedEditingGUI::class, self::class], 'settings'),
            ['settings', 'saveSettings'],
            '',
            ''
        );
        $tabs_gui->addSubTabTarget(
            'adve_assessment_settings',
            $this->ctrl->getLinkTargetByClass([ilObjAdvancedEditingGUI::class, self::class], 'assessment'),
            ['assessment', 'saveAssessmentSettings'],
            '',
            ''
        );
        $tabs_gui->addSubTabTarget(
            'adve_frm_post_settings',
            $this->ctrl->getLinkTargetByClass([ilObjAdvancedEditingGUI::class, self::class], 'frmPost'),
            ['frmPost', 'saveFrmPostSettings'],
            '',
            ''
        );
    }

    public function settings(): void
    {
        $tpl = $this->tpl;
        $form = $this->getTinyForm();
        $tpl->setContent($form->getHTML());
    }

    public function getTinyForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("adve_activation"));
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_use_tiny_mce"), "use_tiny");
        if ($this->settings->getRichTextEditor() === "tinymce") {
            $cb->setChecked(true);
        }
        $form->addItem($cb);
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
        }

        return $form;
    }

    public function saveSettings(): void
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_read'), true);
            return;
        }

        $form = $this->getTinyForm();
        $form->checkInput();

        if ($form->getInput('use_tiny')) {
            $this->settings->setRichTextEditor('tinymce');
        } else {
            $this->settings->setRichTextEditor('');
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, 'settings');
    }

    public function assessment(): void
    {
        $form = $this->initTagsForm(
            "assessment",
            "saveAssessmentSettings",
            "advanced_editing_assessment_settings"
        );

        $this->tpl->setContent($form->getHTML());
    }

    public function saveAssessmentSettings(): void
    {
        $form = $this->initTagsForm(
            "assessment",
            "saveAssessmentSettings",
            "advanced_editing_assessment_settings"
        );
        if (!$this->saveTags("assessment", "assessment", $form)) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }

    public function frmPost(): void
    {
        $form = $this->initTagsForm(
            "frm_post",
            "saveFrmPostSettings",
            "advanced_editing_frm_post_settings"
        );

        $this->tpl->setContent($form->getHTML());
    }

    public function saveFrmPostSettings(): void
    {
        $form = $this->initTagsForm(
            "frm_post",
            "saveFrmPostSettings",
            "advanced_editing_frm_post_settings"
        );
        if (!$this->saveTags("frm_post", "frmPost", $form)) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }

    protected function initTagsForm(
        string $a_id,
        string $a_cmd,
        string $a_title
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, $a_cmd));
        $form->setTitle($this->lng->txt($a_title));

        $tags = new ilMultiSelectInputGUI($this->lng->txt("advanced_editing_allow_html_tags"), "html_tags");
        $tags->setHeight(400);
        $tags->enableSelectAll(true);
        $tags->enableSelectedFirst(true);
        $tags->setOptions(
            array_combine($this->settings->getAllAvailableHTMLTags(), $this->settings->getAllAvailableHTMLTags())
        );
        $tags->setValue(ilRTESettings::_getUsedHTMLTags($a_id));
        $form->addItem($tags);

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $form->addCommandButton($a_cmd, $this->lng->txt("save"));
        }

        return $form;
    }

    protected function saveTags(
        string $a_id,
        string $a_cmd,
        ilPropertyFormGUI $form
    ): bool {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_read'), true);
            return false;
        }
        try {
            if ($form->checkInput()) {
                $html_tags = $form->getInput("html_tags");
                // get rid of select all
                if (array_key_exists(0, $html_tags) && (string) $html_tags[0] === '') {
                    unset($html_tags[0]);
                }
                $this->settings->setUsedHTMLTags((array) $html_tags, $a_id);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            } else {
                return false;
            }
        } catch (ilAdvancedEditingRequiredTagsException $e) {
            $this->tpl->setOnScreenMessage('info', $e->getMessage(), true);
        }
        $this->ctrl->redirect($this, $a_cmd);
        return true;
    }
}
