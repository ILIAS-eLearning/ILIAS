<?php

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;

class ilSystemStyleSettingsGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected ilSkinFactory $skin_factory;
    protected WrapperFactory $request_wrapper;
    protected Refinery $refinery;
    protected ilToolbarGUI $toolbar;
    protected ilTree $tree;
    protected string $style_id;
    protected ilSkinStyleContainer $style_container;

    public function __construct(
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilTabsGUI $tabs,
        Factory $ui_factory,
        Renderer $renderer,
        ilSkinFactory $skin_factory,
        WrapperFactory $request_wrapper,
        Refinery $refinery,
        ilToolbarGUI $toolbar,
        ilTree $tree,
        string $skin_id,
        string $style_id
    ) {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        $this->ui_factory = $ui_factory;
        $this->skin_factory = $skin_factory;
        $this->request_wrapper = $request_wrapper;
        $this->refinery = $refinery;
        $this->toolbar = $toolbar;
        $this->tree = $tree;
        $this->style_id = $style_id;
        $this->renderer = $renderer;

        $this->style_container = $this->skin_factory->skinStyleContainerFromId($skin_id);
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd() ? $this->ctrl->getCmd() : 'edit';
        $style = $this->style_container->getSkin()->getStyle($this->style_id);

        if ($style->isSubstyle()) {
            if ($cmd == 'edit' || $cmd == 'view') {
                $this->setSubStyleSubTabs('edit');
            } else {
                $this->setSubStyleSubTabs('assignStyle');
            }
        }

        $assign_gui = new ilSubStyleAssignmentGUI(
            $this,
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->toolbar,
            $this->tree,
            $this->request_wrapper,
            $this->refinery,
            $this->ui_factory
        );

        switch ($cmd) {
            case 'deleteAssignments':
                $assign_gui->deleteAssignments($this->style_container->getSkin(), $style);
                break;
            case 'saveAssignment':
                $assign_gui->saveAssignment($this->style_container->getSkin(), $style);
                break;
            case 'addAssignment':
                $assign_gui->addAssignment();
                break;
            case 'assignStyle':
                $assign_gui->assignStyle($this->style_container->getSkin(), $style);
                break;
            case 'save':
            case 'edit':
                $this->$cmd();
                break;
            default:
                $this->edit();
                break;
        }
    }

    protected function setSubStyleSubTabs(string $active = '')
    {
        $this->tabs->addSubTab(
            'edit',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass('ilsystemstylesettingsgui')
        );
        $this->tabs->addSubTab(
            'assignStyle',
            $this->lng->txt('assignment'),
            $this->ctrl->getLinkTargetByClass('ilsystemstylesettingsgui', 'assignStyle')
        );

        $this->tabs->activateSubTab($active);
    }

    protected function edit()
    {
        $form = $this->editSystemStyleForm();
        //$this->getPropertiesValues($form);
        $this->tpl->setContent($this->renderer->render($form));
    }

    /**
     * Get values for edit properties form
     */
    public function getPropertiesValues($form)
    {
        global $DIC;

        $skin = $this->style_container->getSkin();
        $style = $skin->getStyle($this->style_id);

        $values['skin_id'] = $skin->getId();
        $values['skin_name'] = $skin->getName();
        $values['style_id'] = $style->getId();
        $values['style_name'] = $style->getName();
        $values['image_dir'] = $style->getImageDirectory();
        $values['font_dir'] = $style->getFontDirectory();
        $values['sound_dir'] = $style->getSoundDirectory();

        if ($style->isSubstyle()) {
            $values['parent_style'] = $style->getSubstyleOf();
        } else {
            $values['active'] = ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId());
            $is_personal_style = $DIC->user()->getPref('skin') == $skin->getId() && $DIC->user()->getPref('style') == $style->getId();
            $values['personal'] = $is_personal_style;
            $is_default_style = ilSystemStyleSettings::getCurrentDefaultSkin() == $skin->getId() && ilSystemStyleSettings::getCurrentDefaultStyle() == $style->getId();
            $values['default'] = $is_default_style;
        }

        $form->setValuesByArray($values);
    }

    protected function save()
    {
        $form = $this->editSystemStyleForm();

        $message_stack = new ilSystemStyleMessageStack();
        if ($form->checkInput()) {
            try {
                $skin = $this->style_container->getSkin();
                $style = $skin->getStyle($this->style_id);

                if ($style->isSubstyle()) {
                    $this->saveSubStyle($message_stack);
                } else {
                    $this->saveStyle($message_stack);
                }

                $message_stack->prependMessage(new ilSystemStyleMessage($this->lng->txt('msg_sys_style_update')));
                $message_stack->getUIComponentsMessages($this->ui_factory);
                $this->ctrl->redirectByClass('ilSystemStyleSettingsGUI');
            } catch (ilSystemStyleException $e) {
                $message_stack->prependMessage(new ilSystemStyleMessage(
                    $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
            }
        }

        $message_stack->getUIComponentsMessages($this->ui_factory);

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param ilSystemStyleMessageStack $message_stack
     * @throws ilSystemStyleException
     */
    protected function saveStyle(ilSystemStyleMessageStack $message_stack)
    {
        global $DIC;

        $old_skin = clone $this->style_container->getSkin();
        $old_style = clone $old_skin->getStyle($this->style_id);

        $new_skin = $this->style_container->getSkin();
        $new_skin->setId($_POST['skin_id']);
        $new_skin->setName($_POST['skin_name']);
        $new_skin->getVersionStep($_POST['skin_version']);

        $new_style_id = $_POST['style_id'];
        $new_skin->updateParentStyleOfSubstyles($old_style->getId(), $new_style_id);

        $new_style = $new_skin->getStyle($_GET['style_id']);
        $new_style->setId($new_style_id);
        $new_style->setName($_POST['style_name']);
        $new_style->setCssFile($_POST['style_id']);
        $new_style->setImageDirectory($_POST['image_dir']);
        $new_style->setSoundDirectory($_POST['sound_dir']);
        $new_style->setFontDirectory($_POST['font_dir']);

        $this->style_container->updateSkin($old_skin);
        $this->style_container->updateStyle($new_style->getId(), $old_style);

        ilSystemStyleSettings::updateSkinIdAndStyleIDOfSubStyleCategoryAssignments(
            $old_skin->getId(),
            $old_style->getId(),
            $new_skin->getId(),
            $new_style->getId()
        );

        if ($_POST['active'] == 1) {
            ilSystemStyleSettings::_activateStyle($new_skin->getId(), $new_style->getId());
            if ($_POST['personal'] == 1) {
                ilSystemStyleSettings::setCurrentUserPrefStyle($new_skin->getId(), $new_style->getId());
            }
            if ($_POST['default'] == 1) {
                ilSystemStyleSettings::setCurrentDefaultStyle($new_skin->getId(), $new_style->getId());
            }
        } else {
            ilSystemStyleSettings::_deactivateStyle($new_skin->getId(), $new_style->getId());
            $_POST['personal'] = 0;
            $_POST['default'] = 0;
        }

        $system_style_conf = new ilSystemStyleConfig();

        //If style has been unset as personal style
        if (!$_POST['personal'] && $DIC->user()->getPref('skin') == $new_skin->getId()) {
            //Reset to default if possible, else change to delos
            if (!$_POST['default']) {
                ilSystemStyleSettings::setCurrentUserPrefStyle(
                    ilSystemStyleSettings::getCurrentDefaultSkin(),
                    ilSystemStyleSettings::getCurrentDefaultStyle()
                );
            } else {
                ilSystemStyleSettings::setCurrentUserPrefStyle(
                    $system_style_conf->getDefaultSkinId(),
                    $system_style_conf->getDefaultStyleId()
                );
            }
            $message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('personal_style_set_to') . ' ' . ilSystemStyleSettings::getCurrentUserPrefSkin() . ':' . ilSystemStyleSettings::getCurrentUserPrefStyle(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
        if (!$_POST['default'] && ilSystemStyleSettings::getCurrentDefaultSkin() == $new_skin->getId()) {
            ilSystemStyleSettings::setCurrentDefaultStyle(
                $system_style_conf->getDefaultSkinId(),
                $system_style_conf->getDefaultStyleId()
            );
            $message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('default_style_set_to') . ' ' . $system_style_conf->getDefaultSkinId() . ': ' . $system_style_conf->getDefaultStyleId(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $new_skin->getId());
        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $new_style->getId());
    }

    /**
     * @param $message_stack
     * @throws ilSystemStyleException
     */
    protected function saveSubStyle(ilSystemStyleMessageStack $message_stack)
    {
        $skin = $this->style_container->getSkin();
        $old_substyle = clone $skin->getStyle($this->style_id);

        $new_substyle = $skin->getStyle($this->style_id);
        $new_substyle->setId($_POST['style_id']);
        $new_substyle->setName($_POST['style_name']);
        $new_substyle->setCssFile($_POST['style_id']);
        $new_substyle->setImageDirectory($_POST['image_dir']);
        $new_substyle->setSoundDirectory($_POST['sound_dir']);
        $new_substyle->setFontDirectory($_POST['font_dir']);
        $new_substyle->setSubstyleOf($old_substyle->getSubstyleOf());

        $this->style_container->updateSkin($skin);
        $this->style_container->updateStyle($new_substyle->getId(), $old_substyle);

        ilSystemStyleSettings::updateSubStyleIdfSubStyleCategoryAssignments(
            $old_substyle->getId(),
            $new_substyle->getId()
        );

        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $skin->getId());
        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $new_substyle->getId());
    }

    /**
     * @throws ilSystemStyleException
     */
    protected function editSystemStyleForm(): Form
    {
        $f = $this->ui_factory->input();
        $skin = $this->style_container->getSkin();
        $style = $skin->getStyle($this->style_id);

        if (true) {//!$style->isSubstyle()) {
            $skin_id = $f->field()->text($this->lng->txt('skin_id'), $this->lng->txt('skin_id_description'))
                         ->withRequired(true)
                         ->withValue('');

            $skin_name = $f->field()->text($this->lng->txt('skin_name'), $this->lng->txt('skin_name_description'))
                           ->withRequired(true)
                           ->withValue('');

            $skin_version = $f->field()->text(
                $this->lng->txt('skin_version'),
                $this->lng->txt('skin_version_description')
            )
                              ->withRequired(true)
                              ->withDisabled(true)
                              ->withValue('1');
            $skin_section = $f->field()->section([$skin_id, $skin_name, $skin_version], $this->lng->txt('skin'));

            $style_id = $f->field()->text($this->lng->txt('style_id'), $this->lng->txt('style_id_description'))
                          ->withRequired(true)
                          ->withValue('');
            $style_name = $f->field()->text($this->lng->txt('style_name'), $this->lng->txt('style_name_description'))
                            ->withRequired(true)
                            ->withValue('');
            $image_dir = $f->field()->text($this->lng->txt('image_dir'), $this->lng->txt('image_dir_description'))
                           ->withValue('');
            $font_dir = $f->field()->text($this->lng->txt('font_dir'), $this->lng->txt('font_dir_description'))
                          ->withValue('');
            $sound_dir = $f->field()->text($this->lng->txt('image_dir'), $this->lng->txt('sound_dir_description'))
                           ->withValue('');
            $style_section = $f->field()->section(
                [$style_id, $style_name, $image_dir, $font_dir, $sound_dir],
                $this->lng->txt('style')
            );

            $default = $f->field()->checkbox(
                $this->lng->txt('default'),
                $this->lng->txt('system_style_default_description')
            );

            $personal = $f->field()->checkbox(
                $this->lng->txt('personal'),
                $this->lng->txt('system_style_personal_description')
            );

            $activation = $f->field()->optionalGroup(
                ['default' => $default, $personal],
                $this->lng->txt('system_style_activation'),
                $this->lng->txt('system_style_activation_description')
            );

            $activation_section = $f->field()->section([$activation], $this->lng->txt('system_style_activation'));
        }
        return $f->container()->form()->standard(
            $this->ctrl->getFormActionByClass('ilsystemstylesettingsgui'),
            [$skin_section, $style_section, $activation_section]
        );
    }
}
