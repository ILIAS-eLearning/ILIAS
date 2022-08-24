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
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Renderer;
use ILIAS\FileUpload\FileUpload;

class ilSystemStyleOverviewGUI
{
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSkinFactory $skin_factory;
    protected ilFileSystemHelper $file_system;
    protected ilSkinStyleContainer $style_container;
    protected ilSystemStyleMessageStack $message_stack;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected WrapperFactory $request_wrapper;
    protected Refinery $refinery;
    protected ilSystemStyleConfig $config;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected FileUpload $upload;
    protected string $ref_id;
    protected bool $read_only = true;
    protected bool $management_enabled = false;

    protected string $style_id;

    public function __construct(
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        Factory $ui_factory,
        Renderer $renderer,
        WrapperFactory $request_wrapper,
        ilToolbarGUI $toolbar,
        Refinery $refinery,
        ilSkinFactory $skin_factory,
        FileUpload $upload,
        ilTabsGUI $tabs,
        ilHelpGUI $help,
        string $skin_id,
        string $style_id,
        string $ref_id,
        bool $read_only,
        bool $management_enabled
    ) {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->request_wrapper = $request_wrapper;
        $this->toolbar = $toolbar;
        $this->refinery = $refinery;
        $this->tabs = $tabs;
        $this->style_id = $style_id;
        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);
        $this->skin_factory = $skin_factory;
        $this->style_container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);
        $this->help = $help;
        $this->ref_id = $ref_id;
        $this->file_system = new ilFileSystemHelper($this->lng, $this->message_stack);
        $this->upload = $upload;
        $this->config = new ilSystemStyleConfig();
        $this->setReadOnly($read_only);
        $this->setManagementEnabled($management_enabled);
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd == '') {
            $cmd = $this->isReadOnly() ? 'view' : 'edit';
        }

        switch ($cmd) {
            case 'addSystemStyle':
            case 'addSubStyle':
            case 'saveNewSystemStyle':
            case 'saveNewSubStyle':
            case 'copyStyle':
            case 'importStyle':
            case 'deleteStyles':
            case 'deleteStyle':
            case 'confirmDelete':
                if (!$this->isManagementEnabled()) {
                    throw new ilObjectException($this->lng->txt('permission_denied'));
                }
                $this->$cmd();
                break;
            case 'cancel':
            case 'edit':
            case 'export':
            case 'moveUserStyles':
            case 'saveStyleSettings':
                if ($this->isReadOnly()) {
                    throw new ilObjectException($this->lng->txt('permission_denied'));
                }
                $this->$cmd();
                break;
            case 'view':
                $this->$cmd();
                break;

        }
        $this->message_stack->sendMessages();
    }

    protected function view(): void
    {
        $table = new ilSystemStylesTableGUI($this, 'edit');
        $this->tpl->setContent($table->getHTML());
    }

    protected function cancel(): void
    {
        $this->edit();
    }

    public function edit(): void
    {
        if ($this->isManagementEnabled()) {
            // Add Button for adding skins
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('add_system_style'),
                $this->ctrl->getLinkTarget($this, 'addSystemStyle')
            ));

            // Add Button for adding sub styles
            $add_sub = $this->ui_factory->button()->standard(
                $this->lng->txt('add_substyle'),
                $this->ctrl->getLinkTarget($this, 'addSubStyle')
            );
            if (count(ilStyleDefinition::getAllSkins()) == 1) {
                $add_sub = $add_sub->withUnavailableAction();
            }
            $this->toolbar->addComponent($add_sub);
            $this->toolbar->addSeparator();
        }

        // from styles selector
        $si = new ilSelectInputGUI(
            $this->lng->txt('sty_move_user_styles') . ': ' . $this->lng->txt('sty_from'),
            'from_style'
        );

        $options = [];
        foreach (ilStyleDefinition::getAllSkinStyles() as $id => $skin_style) {
            if (!$skin_style['substyle_of']) {
                $options[$id] = $skin_style['title'];
            }
        }
        $si->setOptions($options + ['other' => $this->lng->txt('other')]);

        $this->toolbar->addInputItem($si, true);

        $si = new ilSelectInputGUI($this->lng->txt('sty_to'), 'to_style');
        $si->setOptions($options);
        $this->toolbar->addInputItem($si, true);
        $this->toolbar->addComponent($this->ui_factory->button()->standard($this->lng->txt('sty_move_style'), ''));

        $this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'moveUserStyles'));

        $table = new ilSystemStylesTableGUI($this, 'edit');
        $table->addActions($this->isManagementEnabled());
        $this->tpl->setContent($table->getHTML());
    }

    public function moveUserStyles(): void
    {
        $to = $this->request_wrapper->post()->retrieve('to_style', $this->refinery->string()->splitString(':'));

        $from_style = $this->request_wrapper->post()->retrieve('from_style', $this->refinery->kindlyTo()->string());

        if ($from_style == 'other') {
            // get all user assigned styles
            $all_user_styles = ilObjUser::_getAllUserAssignedStyles();

            // move users that are not assigned to
            // currently existing style
            foreach ($all_user_styles as $style) {
                if (!ilStyleDefinition::styleExists($style)) {
                    $style_arr = explode(':', $style);
                    ilObjUser::_moveUsersToStyle($style_arr[0], $style_arr[1], $to[0], $to[1]);
                }
            }
        } else {
            $from = explode(':', $from_style);
            ilObjUser::_moveUsersToStyle($from[0], $from[1], $to[0], $to[1]);
        }

        $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('msg_obj_modified')));
        $this->edit();
    }

    public function saveStyleSettings(): void
    {
        $active_styles = $this->request_wrapper->post()->retrieve('st_act', $this->refinery->identity());

        if ($this->checkStyleSettings($this->message_stack, $active_styles)) {
            $all_styles = ilStyleDefinition::getAllSkinStyles();
            foreach ($all_styles as $style) {
                if (!isset($active_styles[$style['id']])) {
                    ilSystemStyleSettings::_deactivateStyle($style['template_id'], $style['style_id']);
                } else {
                    ilSystemStyleSettings::_activateStyle($style['template_id'], $style['style_id']);
                }
            }

            //set default skin and style
            if ($this->request_wrapper->post()->has('default_skin_style')) {
                $sknst = $this->request_wrapper->post()->retrieve(
                    'default_skin_style',
                    $this->refinery->string()->splitString(':')
                );
                ilSystemStyleSettings::setCurrentDefaultStyle($sknst[0], $sknst[1]);
            }
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('msg_obj_modified'),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
        }
        $this->message_stack->sendMessages();
        $this->ctrl->redirect($this, 'edit');
    }

    protected function checkStyleSettings(ilSystemStyleMessageStack $message_stack, array $active_styles): bool
    {
        $passed = true;

        if (count($active_styles) < 1) {
            $passed = false;
            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('at_least_one_style'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        $default_style = $this->request_wrapper->post()->retrieve(
            'default_skin_style',
            $this->refinery->kindlyTo()->string()
        );

        if (!isset($active_styles[$default_style])) {
            $passed = false;
            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('cant_deactivate_default_style'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        // check if a style should be deactivated, that still has
        // a user assigned to
        $all_styles = ilStyleDefinition::getAllSkinStyles();

        foreach ($all_styles as $style) {
            if (!isset($active_styles[$style['id']])) {
                if (ilObjUser::_getNumberOfUsersForStyle($style['template_id'], $style['style_id']) > 0) {
                    $passed = false;
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $style['style_name'] . ': ' . $this->lng->txt('cant_deactivate_if_users_assigned'),
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                }
            }
        }
        return $passed;
    }

    protected function addSystemStyle(): void
    {
        $this->addSystemStyleForms();
    }

    protected function saveNewSystemStyle(): void
    {
        $form = $this->createSystemStyleForm();

        if ($form->checkInput()) {
            $skin_id = $this->request_wrapper->post()->retrieve('skin_id', $this->refinery->kindlyTo()->string());
            $style_id = $this->request_wrapper->post()->retrieve('style_id', $this->refinery->kindlyTo()->string());

            $skin_name = $this->request_wrapper->post()->retrieve('skin_name', $this->refinery->kindlyTo()->string());
            $style_name = $this->request_wrapper->post()->retrieve('style_name', $this->refinery->kindlyTo()->string());

            if (ilStyleDefinition::skinExists($skin_id)) {
                $this->message_stack->addMessage(new ilSystemStyleMessage(
                    $this->lng->txt('skin_id_exists'),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
            } else {
                try {
                    $skin = new ilSkin($skin_id, $skin_name);
                    $style = new ilSkinStyle($style_id, $style_name);
                    $skin->addStyle($style);
                    $container = new ilSkinStyleContainer($this->lng, $skin, $this->message_stack);
                    $container->create($this->message_stack);
                    $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $skin->getId());
                    $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $style->getId());
                    $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('msg_sys_style_created')));
                    $this->message_stack->sendMessages();
                    $this->ctrl->redirectByClass('ilSystemStyleSettingsGUI');
                } catch (ilSystemStyleException $e) {
                    $this->message_stack->addMessage(new ilSystemStyleMessage(
                        $e->getMessage(),
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                }
            }
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function addSystemStyleForms(): void
    {
        $this->tabs->clearTargets();
        /**
         * Since clearTargets also clears the help screen ids
         */
        $this->help->setScreenIdComponent('sty');
        $this->help->setScreenId('system_styles');
        $this->help->setSubScreenId('create');

        $forms = [];

        $forms[] = $this->createSystemStyleForm();
        $forms[] = $this->importSystemStyleForm();
        $forms[] = $this->cloneSystemStyleForm();

        $this->tpl->setContent($this->getCreationFormsHTML($forms));
    }

    protected function createSystemStyleForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('sty_create_new_system_style'));

        $ti = new ilTextInputGUI($this->lng->txt('skin_id'), 'skin_id');
        $ti->setInfo($this->lng->txt('skin_id_description'));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('skin_name'), 'skin_name');
        $ti->setInfo($this->lng->txt('skin_name_description'));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('style_id'), 'style_id');
        $ti->setInfo($this->lng->txt('style_id_description'));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('style_name'), 'style_name');
        $ti->setInfo($this->lng->txt('style_name_description'));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $form->addCommandButton('saveNewSystemStyle', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    protected function importSystemStyleForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'importStyle'));
        $form->setTitle($this->lng->txt('sty_import_system_style'));

        // title
        $file_input = new ilFileInputGUI($this->lng->txt('import_file'), 'importfile');
        $file_input->setRequired(true);
        $file_input->setSuffixes(['zip']);
        $form->addItem($file_input);

        $form->addCommandButton('importStyle', $this->lng->txt('import'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    protected function cloneSystemStyleForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('sty_copy_other_system_style'));

        // source
        $ti = new ilSelectInputGUI($this->lng->txt('sty_source'), 'source_style');
        $ti->setRequired(true);
        $styles = ilStyleDefinition::getAllSkinStyles();
        $options = [];
        foreach ($styles as $id => $style) {
            $system_style_conf = new ilSystemStyleConfig();
            if ($style['skin_id'] != $system_style_conf->getDefaultSkinId()) {
                $options[$id] = $style['title'];
            }
        }
        $ti->setOptions($options);

        $form->addItem($ti);

        $form->addCommandButton('copyStyle', $this->lng->txt('copy'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    protected function getCreationFormsHTML(array $a_forms): string
    {
        include_once('./Services/Accordion/classes/class.ilAccordionGUI.php');

        $acc = new ilAccordionGUI();
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
        $cnt = 1;
        foreach ($a_forms as $form_type => $cf) {
            /**
             * @var ilPropertyFormGUI $cf
             */
            $htpl = new ilTemplate('tpl.creation_acc_head.html', true, true, 'Services/Object');

            // using custom form titles (used for repository plugins)
            $form_title = '';
            if (method_exists($this, 'getCreationFormTitle')) {
                $form_title = $this->getCreationFormTitle($form_type);
            }
            if (!$form_title) {
                $form_title = $cf->getTitle();
            }

            // move title from form to accordion
            $htpl->setVariable('TITLE', $this->lng->txt('option') . ' ' . $cnt . ': ' .
                $form_title);
            $cf->setTitle('');
            $cf->setTitleIcon('');
            $cf->setTableWidth('100%');

            $acc->addItem($htpl->get(), $cf->getHTML());

            $cnt++;
        }

        return "<div class='ilCreationFormSection'>" . $acc->getHTML() . '</div>';
    }

    protected function copyStyle(): void
    {
        $imploded_skin_style_id = $this->request_wrapper->post()->retrieve(
            'source_style',
            $this->refinery->string()->splitString(':')
        );
        $skin_id = $imploded_skin_style_id[0];
        $style_id = $imploded_skin_style_id[1];

        try {
            $container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);
            $new_container = $this->skin_factory->copyFromSkinStyleContainer(
                $container,
                $this->file_system,
                $this->message_stack,
                $this->lng->txt('sty_acopy')
            );
            $this->message_stack->prependMessage(new ilSystemStyleMessage(
                $this->lng->txt('style_copied'),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
            $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $new_container->getSkin()->getId());
            $this->ctrl->setParameterByClass(
                'ilSystemStyleSettingsGUI',
                'style_id',
                $new_container->getSkin()->getStyle($style_id)->getId()
            );
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('directory_created') . ' ' . $new_container->getSkinDirectory(),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
        } catch (Exception $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $e->getMessage(),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }
        $this->message_stack->sendMessages();
        $this->ctrl->redirectByClass('ilSystemStyleSettingsGUI');
    }

    protected function deleteStyle(): void
    {
        $skin_id = $this->style_container->getSkin()->getId();
        $style_id = $this->style_id;

        if ($this->checkDeletable($skin_id, $style_id, $this->message_stack)) {
            $delete_form_table = new ilSystemStyleDeleteGUI($this->lng, $this->ctrl);
            $container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);
            $delete_form_table->addStyle(
                $container->getSkin(),
                $container->getSkin()->getStyle($style_id),
                $container->getImagesSkinPath($style_id) . '/icon_stys.svg'
            );
            $this->tpl->setContent($delete_form_table->getDeleteStyleFormHTML());
        } else {
            $this->message_stack->prependMessage(new ilSystemStyleMessage(
                $this->lng->txt('style_not_deleted'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $this->edit();
        }
    }

    protected function deleteStyles(): void
    {
        $delete_form_table = new ilSystemStyleDeleteGUI($this->lng, $this->ctrl);

        $all_deletable = true;

        $skin_ids = $this->request_wrapper->post()->retrieve('id', $this->refinery->identity());
        foreach ($skin_ids as $skin_style_id) {
            $imploded_skin_style_id = explode(':', $skin_style_id);
            $skin_id = $imploded_skin_style_id[0];
            $style_id = $imploded_skin_style_id[1];
            if (!$this->checkDeletable($skin_id, $style_id, $this->message_stack)) {
                $all_deletable = false;
            }
        }
        if ($all_deletable) {
            foreach ($skin_ids as $skin_style_id) {
                $imploded_skin_style_id = explode(':', $skin_style_id);
                $skin_id = $imploded_skin_style_id[0];
                $style_id = $imploded_skin_style_id[1];
                $container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);
                $delete_form_table->addStyle(
                    $container->getSkin(),
                    $container->getSkin()->getStyle($style_id),
                    $container->getImagesSkinPath($style_id) . '/icon_stys.svg'
                );
            }
            $this->tpl->setContent($delete_form_table->getDeleteStyleFormHTML());
        } else {
            $this->message_stack->prependMessage(new ilSystemStyleMessage(
                $this->lng->txt('styles_not_deleted'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $this->edit();
        }
    }

    protected function checkDeletable(
        string $skin_id,
        string $style_id,
        ilSystemStyleMessageStack $message_stack
    ): bool {
        $passed = true;
        if (ilObjUser::_getNumberOfUsersForStyle($skin_id, $style_id) > 0) {
            $message_stack->addMessage(new ilSystemStyleMessage(
                $style_id . ': ' . $this->lng->txt('cant_delete_if_users_assigned'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $passed = false;
        }
        if (ilSystemStyleSettings::_lookupActivatedStyle($skin_id, $style_id) > 0) {
            $message_stack->addMessage(new ilSystemStyleMessage(
                $style_id . ': ' . $this->lng->txt('cant_delete_activated_style'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $passed = false;
        }
        if (ilSystemStyleSettings::getCurrentDefaultSkin() == $skin_id && ilSystemStyleSettings::getCurrentDefaultSkin() == $style_id) {
            $message_stack->addMessage(new ilSystemStyleMessage(
                $style_id . ': ' . $this->lng->txt('cant_delete_default_style'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $passed = false;
        }

        if ($this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack)->getSkin()->getSubstylesOfStyle($style_id)) {
            $message_stack->addMessage(new ilSystemStyleMessage(
                $style_id . ': ' . $this->lng->txt('cant_delete_style_with_substyles'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
            $passed = false;
        }
        return $passed;
    }

    protected function confirmDelete(): void
    {
        $i = 0;
        while ($this->request_wrapper->post()->has('style_' . $i)) {
            try {
                $skin_style_id = $this->request_wrapper->post()->retrieve(
                    'style_' . $i,
                    $this->refinery->string()->splitString(':')
                );
                $container = $this->skin_factory->skinStyleContainerFromId($skin_style_id[0], $this->message_stack);
                $syle = $container->getSkin()->getStyle($skin_style_id[1]);
                $container->deleteStyle($syle);
                if (!$container->getSkin()->hasStyles()) {
                    $container->delete();
                }
            } catch (Exception $e) {
                $this->message_stack->addMessage(new ilSystemStyleMessage(
                    $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
            }
            $i++;
        }
        $this->message_stack->sendMessages();
        $this->ctrl->redirect($this);
    }

    protected function importStyle(): void
    {
        $form = $this->importSystemStyleForm();

        if ($form->checkInput() && $this->upload->hasUploads()) {
            $this->upload->process();
            /** @var \ILIAS\FileUpload\DTO\UploadResult $result */
            $result = array_values($this->upload->getResults())[0];

            $this->upload->moveOneFileTo(
                $result,
                'global/skin',
                ILIAS\FileUpload\Location::CUSTOMIZING,
                $result->getName(),
                true
            );
            $imported_container = $this->skin_factory->skinStyleContainerFromZip(
                $this->config->getCustomizingSkinPath() . $result->getName(),
                $result->getName(),
                $this->message_stack
            );
            $this->ctrl->setParameterByClass(
                'ilSystemStyleSettingsGUI',
                'skin_id',
                $imported_container->getSkin()->getId()
            );
            $this->ctrl->setParameterByClass(
                'ilSystemStyleSettingsGUI',
                'style_id',
                $imported_container->getSkin()->getDefaultStyle()->getId()
            );
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('style_imported') . ' ' . $imported_container->getSkinDirectory(),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
            $this->message_stack->sendMessages();
            $this->ctrl->redirectByClass('ilSystemStyleSettingsGUI');
        }
        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function export(): void
    {
        $container = $this->skin_factory->skinStyleContainerFromId($this->style_container->getSkin()->getId(), $this->message_stack);
        try {
            $container->export();
        } catch (Exception $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('zip_export_failed') . ' ' . $e->getMessage(),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }
    }

    /**
     *
     */
    protected function addSubStyle(): void
    {
        $this->tabs->clearTargets();
        /**
         * Since clearTargets also clears the help screen ids
         */
        $this->help->setScreenIdComponent('sty');
        $this->help->setScreenId('system_styles');
        $this->help->setSubScreenId('create_sub');

        $form = $this->addSubStyleForms();

        $this->tpl->setContent($form->getHTML());
    }

    protected function addSubStyleForms(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('sty_create_new_system_sub_style'));

        $ti = new ilTextInputGUI($this->lng->txt('sub_style_id'), 'sub_style_id');
        $ti->setInfo($this->lng->txt('sub_style_id_description'));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('sub_style_name'), 'sub_style_name');
        $ti->setInfo($this->lng->txt('sub_style_name_description'));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // source
        $ti = new ilSelectInputGUI($this->lng->txt('parent'), 'parent_style');
        $ti->setRequired(true);
        $ti->setInfo($this->lng->txt('sub_style_parent_style_description'));
        $styles = ilStyleDefinition::getAllSkinStyles();
        $options = [];
        foreach ($styles as $id => $style) {
            $system_style_conf = new ilSystemStyleConfig();
            if ($style['skin_id'] != $system_style_conf->getDefaultSkinId() && !$style['substyle_of']) {
                $options[$id] = $style['title'];
            }
        }
        $ti->setOptions($options);

        $form->addItem($ti);
        $form->addCommandButton('saveNewSubStyle', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    protected function saveNewSubStyle(): void
    {
        $form = $this->addSubStyleForms();

        if ($form->checkInput()) {
            try {
                $skin_style_ids = $this->request_wrapper->post()->retrieve(
                    'parent_style',
                    $this->refinery->string()->splitString(':')
                );

                $parent_skin_id = $skin_style_ids[0];
                $parent_style_id = $skin_style_ids[1];

                $container = $this->skin_factory->skinStyleContainerFromId($parent_skin_id, $this->message_stack);

                $sub_style_id = $this->request_wrapper->post()->retrieve(
                    'sub_style_id',
                    $this->refinery->kindlyTo()->string()
                );

                if (array_key_exists(
                    $sub_style_id,
                    $container->getSkin()->getSubstylesOfStyle($parent_style_id)
                )) {
                    throw new ilSystemStyleException(
                        ilSystemStyleException::SUBSTYLE_ASSIGNMENT_EXISTS,
                        $sub_style_id
                    );
                }

                $sub_style_name = $this->request_wrapper->post()->retrieve(
                    'sub_style_name',
                    $this->refinery->kindlyTo()->string()
                );

                $style = new ilSkinStyle($sub_style_id, $sub_style_name);
                $style->setSubstyleOf($parent_style_id);
                $container->addStyle($style);

                $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $parent_skin_id);
                $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $sub_style_id);
                $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('msg_sub_style_created')));
                $this->message_stack->sendMessages();
                $this->ctrl->redirectByClass('ilSystemStyleSettingsGUI');
            } catch (ilSystemStyleException $e) {
                $this->message_stack->addMessage(new ilSystemStyleMessage(
                    $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
            }
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function isReadOnly(): bool
    {
        return $this->read_only;
    }

    public function setReadOnly(bool $read_only): void
    {
        $this->read_only = $read_only;
    }

    public function isManagementEnabled(): bool
    {
        return $this->management_enabled;
    }

    public function setManagementEnabled(bool $management_enabled): void
    {
        $this->management_enabled = $management_enabled;
    }
}
