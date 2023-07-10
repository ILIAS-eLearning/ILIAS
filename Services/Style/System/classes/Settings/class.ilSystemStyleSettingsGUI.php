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
use ILIAS\UI\Renderer;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use Psr\Http\Message\ServerRequestInterface;

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
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ServerRequestInterface $request;
    protected string $style_id;
    protected ilSkinStyleContainer $style_container;
    protected ilSystemStyleMessageStack $message_stack;

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
        ilObjUser $user,
        ServerRequestInterface $request,
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
        $this->user = $user;
        $this->request = $request;
        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);

        $this->style_container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);
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
                $this->save();
                break;
            case 'edit':
            default:
                $this->edit();
                break;
        }
    }

    protected function setSubStyleSubTabs(string $active = ''): void
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

    protected function edit(): void
    {
        $form = $this->editSystemStyleForm();
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function save(): void
    {
        $new_skin = $this->style_container->getSkin();
        $new_style = $new_skin->getStyle($this->style_id);
        $old_skin = clone $new_skin;
        $old_style = clone $new_style;

        $form = $this->editSystemStyleForm();
        $form = $form->withRequest($this->request);
        $result = $form->getData();

        if ($result) {
            try {
                $new_skin->updateParentStyleOfSubstyles($old_style->getId(), $new_style->getId());
                $this->style_container->updateSkin($old_skin);
                $this->style_container->updateStyle($new_style->getId(), $old_style);

                if ($old_style->isSubstyle()) {
                    $new_style->setSubstyleOf($old_style->getSubstyleOf());
                    ilSystemStyleSettings::updateSubStyleIdfSubStyleCategoryAssignments(
                        $old_style->getId(),
                        $new_style->getId()
                    );
                } else {
                    ilSystemStyleSettings::updateSkinIdAndStyleIDOfSubStyleCategoryAssignments(
                        $old_skin->getId(),
                        $old_style->getId(),
                        $new_skin->getId(),
                        $new_style->getId()
                    );

                    $this->handleStyleActivation(
                        $result['activation_section'],
                        $new_skin->getId(),
                        $new_style->getId(),
                        $this->message_stack
                    );
                }

                $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $new_skin->getId());
                $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $new_style->getId());
                $this->message_stack->prependMessage(new ilSystemStyleMessage($this->lng->txt('msg_sys_style_update')));
                $this->message_stack->sendMessages();
                $this->ctrl->redirectByClass('ilSystemStyleSettingsGUI');
            } catch (ilSystemStyleException $e) {
                $this->message_stack->prependMessage(new ilSystemStyleMessage(
                    $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
            }
        }

        $this->tpl->setContent($this->renderer->render(
            array_merge($this->message_stack->getUIComponentsMessages($this->ui_factory), [$form])
        ));
    }

    protected function handleStyleActivation(
        ?array $activation_values,
        string $skin_id,
        string $style_id,
        ilSystemStyleMessageStack $message_stack
    ): void {
        $active = false;
        $personal = false;
        $default = false;

        if (is_array($activation_values)) {
            $active = true;
            $personal = (bool) $activation_values['personal'];
            $default = (bool) $activation_values['default'];
        }
        if ($active) {
            ilSystemStyleSettings::_activateStyle($skin_id, $style_id);
            if ($personal) {
                ilSystemStyleSettings::setCurrentUserPrefStyle($skin_id, $style_id);
            }
            if ($default) {
                ilSystemStyleSettings::setCurrentDefaultStyle($skin_id, $style_id);
            }
        } else {
            ilSystemStyleSettings::_deactivateStyle($skin_id, $style_id);
        }

        $system_style_conf = new ilSystemStyleConfig();

        //If style has been unset as personal style
        if (!$personal && $this->user->getPref('skin') == $skin_id) {
            //Reset to default if possible, else change to delos
            if (!$default) {
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
                    $this->lng->txt('personal_style_set_to') . ' ' .
                    ilSystemStyleSettings::getCurrentUserPrefSkin() . ':' . ilSystemStyleSettings::getCurrentUserPrefStyle(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
        if (!$default && ilSystemStyleSettings::getCurrentDefaultSkin() == $skin_id) {
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
    }

    /**
     * @throws ilSystemStyleException
     */
    protected function editSystemStyleForm(): Form
    {
        $f = $this->ui_factory->input();
        $skin = $this->style_container->getSkin();
        $style = $skin->getStyle($this->style_id);

        if (!$style->isSubstyle()) {
            $skin_fields = [];
            $skin_fields[] = $f->field()->text($this->lng->txt('skin_id'), $this->lng->txt('skin_id_description'))
                               ->withRequired(true)
                               ->withValue($skin->getId())
                               ->withAdditionalTransformation($this->refinery->custom()->transformation(
                                   function ($v) use ($skin) {
                                       $skin->setId($v);
                                   }
                               ));

            $skin_fields[] = $f->field()->text($this->lng->txt('skin_name'), $this->lng->txt('skin_name_description'))
                               ->withRequired(true)
                               ->withValue($skin->getName())
                               ->withAdditionalTransformation($this->refinery->custom()->transformation(
                                   function ($v) use ($skin) {
                                       $skin->setName($v);
                                   }
                               ));

            if ($skin->isVersionChangeable()) {
                $skin_fields[] = $f->field()->text(
                    $this->lng->txt('skin_version'),
                    $this->lng->txt('skin_version_description')
                )
                                   ->withDisabled(true)
                                   ->withValue($skin->getVersion())
                                   ->withAdditionalTransformation($this->refinery->custom()->transformation(
                                       function ($v) use ($skin) {
                                           $skin->getVersionStep($v);
                                       }
                                   ));
            }
            $sections[] = $f->field()->section($skin_fields, $this->lng->txt('skin'));
        }

        $style_id = $f->field()->text($this->lng->txt('style_id'), $this->lng->txt('style_id_description'))
                      ->withRequired(true)
                      ->withValue($style->getId())
                      ->withAdditionalTransformation($this->refinery->custom()->transformation(
                          function ($v) use ($style) {
                              $style->setId($v);
                              $style->setCssFile($v);
                          }
                      ));
        $style_name = $f->field()->text($this->lng->txt('style_name'), $this->lng->txt('style_name_description'))
                        ->withRequired(true)
                        ->withValue($style->getName())
                        ->withAdditionalTransformation($this->refinery->custom()->transformation(
                            function ($v) use ($style) {
                                $style->setName($v);
                            }
                        ));
        $image_dir = $f->field()->text($this->lng->txt('image_dir'), $this->lng->txt('image_dir_description'))
                       ->withValue($style->getImageDirectory())
                       ->withAdditionalTransformation($this->refinery->custom()->transformation(
                           function ($v) use ($style) {
                               $style->setImageDirectory($v);
                           }
                       ));
        $font_dir = $f->field()->text($this->lng->txt('font_dir'), $this->lng->txt('font_dir_description'))
                      ->withValue($style->getFontDirectory())
                      ->withAdditionalTransformation($this->refinery->custom()->transformation(
                          function ($v) use ($style) {
                              $style->setFontDirectory($v);
                          }
                      ));
        $sound_dir = $f->field()->text($this->lng->txt('image_dir'), $this->lng->txt('sound_dir_description'))
                       ->withValue($style->getSoundDirectory())
                       ->withAdditionalTransformation($this->refinery->custom()->transformation(
                           function ($v) use ($style) {
                               $style->setSoundDirectory($v);
                           }
                       ));
        $section_name = $this->lng->txt('style');
        if ($style->isSubstyle()) {
            $this->lng->txt('sub_style');
        }
        $sections[] = $f->field()->section([$style_id, $style_name, $image_dir, $font_dir, $sound_dir], $section_name);

        if (!$style->isSubstyle()) {
            $active = ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId());
            $activation_values = null;
            if ($active) {
                $is_personal_style = $this->user->getPref('skin') == $skin->getId()
                    && $this->user->getPref('style') == $style->getId();
                $is_default_style = ilSystemStyleSettings::getCurrentDefaultSkin() == $skin->getId()
                    && ilSystemStyleSettings::getCurrentDefaultStyle() == $style->getId();
                $activation_values = ['default' => $is_default_style, 'personal' => $is_personal_style];
            }

            $default = $f->field()->checkbox(
                $this->lng->txt('default'),
                $this->lng->txt('system_style_default_description')
            );

            $personal = $f->field()->checkbox(
                $this->lng->txt('personal'),
                $this->lng->txt('system_style_personal_description')
            );

            $activation = $f->field()->optionalGroup(
                ['default' => $default, 'personal' => $personal],
                $this->lng->txt('system_style_activation'),
                $this->lng->txt('system_style_activation_description')
            )->withValue($activation_values);
            $sections['activation_section'] = $f->field()->section(
                ['activation' => $activation],
                $this->lng->txt('system_style_activation')
            )
                                                ->withAdditionalTransformation($this->refinery->custom()->transformation(
                                                    function ($v) {
                                                        return $v['activation'];
                                                    }
                                                ));
        }

        return $f->container()->form()->standard($this->ctrl->getFormActionByClass(
            'ilsystemstylesettingsgui',
            'save'
        ), $sections);
    }
}
