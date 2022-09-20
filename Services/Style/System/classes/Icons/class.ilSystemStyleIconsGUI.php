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

use ILIAS\FileUpload\Location;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\FileUpload\FileUpload;

/**
 * @ilCtrl_Calls ilSystemStyleIconsGUI:
 */
class ilSystemStyleIconsGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSkinStyleContainer $style_container;
    protected ilSkinFactory $skin_factory;
    protected ilSystemStyleMessageStack $message_stack;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected WrapperFactory $request_wrapper;
    protected ilToolbarGUI $toolbar;
    protected Refinery $refinery;
    protected ilTabsGUI $tabs;
    protected ilSystemStyleIconFolder $icon_folder;
    protected FileUpload $upload;
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
        ilTabsGUI $tabs,
        FileUpload $upload,
        string $skin_id,
        string $style_id
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
        $this->upload = $upload;
        $this->style_id = $style_id;
        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);
        $this->skin_factory = $skin_factory;
        $this->style_container = $this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack);

        $this->setStyleContainer($this->skin_factory->skinStyleContainerFromId($skin_id, $this->message_stack));
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $this->setSubStyleSubTabs($cmd);

        if ($this->ctrl->getCmd() != 'reset') {
            try {
                $this->setIconFolder(new ilSystemStyleIconFolder($this->getStyleContainer()->getImagesSkinPath($this->style_id)));
            } catch (ilSystemStyleExceptionBase $e) {
                $this->message_stack->addMessage(new ilSystemStyleMessage(
                    $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
                $cmd = 'fail';
            }
        }

        switch ($cmd) {
            case 'fail':
                $this->fail();
                break;
            case 'cancelIcon':
                $this->editIcon();
                break;
            case 'save':
            case 'edit':
            case 'editIcon':
            case 'update':
            case 'reset':
            case 'preview':
            case 'updateIcon':
                $this->$cmd();
                break;
            default:
                $this->edit();
                break;
        }
        $this->message_stack->sendMessages();
    }

    protected function fail(): void
    {
        $form = $this->initByColorForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function setSubStyleSubTabs(string $active = ''): void
    {
        $this->tabs->addSubTab('edit', $this->lng->txt('edit_by_color'), $this->ctrl->getLinkTarget($this, 'edit'));
        $this->tabs->addSubTab(
            'editIcon',
            $this->lng->txt('edit_by_icon'),
            $this->ctrl->getLinkTarget($this, 'editIcon')
        );
        $this->tabs->addSubTab(
            'preview',
            $this->lng->txt('icons_gallery'),
            $this->ctrl->getLinkTarget($this, 'preview')
        );

        if ($active == 'preview') {
            $this->tabs->activateSubTab($active);
        } elseif ($active == 'cancelIcon' || $active == 'editIcon') {
            $this->tabs->activateSubTab('editIcon');
        } else {
            $this->tabs->activateSubTab('edit');
        }
    }

    protected function edit(): void
    {
        $form = $this->initByColorForm();
        $this->getByColorValues($form);
        $this->tpl->setContent($form->getHTML());
    }

    protected function preview(): void
    {
        $this->tpl->setContent($this->renderer->render($this->getIconsPreviews()));
    }

    protected function initByColorForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt('adapt_icons'));
        $form->setDescription($this->lng->txt('adapt_icons_description'));

        $color_set = [];

        try {
            $color_set = $this->getIconFolder()->getColorSet()->getColorsSortedAsArray();
        } catch (ilSystemStyleExceptionBase $e) {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $e->getMessage(),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }

        foreach ($color_set as $type => $colors) {
            $section = new ilFormSectionHeaderGUI();
            $title = '';

            if ($type == ilSystemStyleIconColor::GREY) {
                $title = $this->lng->txt('grey_color');
                $section->setTitle($this->lng->txt('grey_colors'));
                $section->setInfo($this->lng->txt('grey_colors_description'));
                $section->setSectionAnchor($this->lng->txt('grey_colors'));
            }
            if ($type == ilSystemStyleIconColor::RED) {
                $title = $this->lng->txt('red_color');
                $section->setTitle($this->lng->txt('red_colors'));
                $section->setInfo($this->lng->txt('red_colors_description'));
                $section->setSectionAnchor($this->lng->txt('red_colors'));
            }
            if ($type == ilSystemStyleIconColor::GREEN) {
                $title = $this->lng->txt('green_color');
                $section->setTitle($this->lng->txt('green_colors'));
                $section->setInfo($this->lng->txt('green_colors_description'));
                $section->setSectionAnchor($this->lng->txt('green_colors'));
            }
            if ($type == ilSystemStyleIconColor::BLUE) {
                $title = $this->lng->txt('blue_color');
                $section->setTitle($this->lng->txt('blue_colors'));
                $section->setInfo($this->lng->txt('blue_colors_description'));
                $section->setSectionAnchor($this->lng->txt('blue_colors'));
            }
            $form->addItem($section);

            foreach ($colors as $id => $color) {
                /**
                 * @var ilSystemStyleIconColor $color
                 */
                $input = new ilColorPickerInputGUI($title . ' ' . ($id + 1), $color->getId());
                $input->setRequired(true);
                $input->setInfo('Usages: ' . $this->getIconFolder()->getUsagesOfColorAsString($color->getId()));
                $form->addItem($input);
            }
        }

        $has_icons = count($this->getIconFolder()->getIcons()) > 0;

        if ($has_icons) {
            $form->addCommandButton('update', $this->lng->txt('update_colors'));
        }
        $form->addCommandButton('reset', $this->lng->txt('reset_icons'));
        if ($has_icons) {
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    protected function getByColorValues(ilPropertyFormGUI $form): void
    {
        $values = [];

        $colors = $this->getIconFolder()->getColorSet()->getColors();
        foreach ($colors as $color) {
            $id = $color->getId();
            if (array_key_exists($color->getId(), $colors)) {
                $values[$id] = $colors[$color->getId()]->getColor();
            } else {
                $values[$id] = $color->getColor();
            }
        }

        $form->setValuesByArray($values);
    }

    protected function reset(): void
    {
        $style = $this->getStyleContainer()->getSkin()->getStyle($this->style_id);
        $this->getStyleContainer()->resetImages($style);
        $this->setIconFolder(new ilSystemStyleIconFolder($this->getStyleContainer()->getImagesSkinPath($style->getId())));
        $message_stack = new ilSystemStyleMessageStack($this->tpl);
        $message_stack->addMessage(new ilSystemStyleMessage(
            $this->lng->txt('color_reset'),
            ilSystemStyleMessage::TYPE_SUCCESS
        ));
        $message_stack->sendMessages();

        $this->ctrl->redirect($this, 'edit');
    }

    protected function update(): void
    {
        $form = $this->initByColorForm();
        if ($form->checkInput()) {
            $message_stack = new ilSystemStyleMessageStack($this->tpl);

            $color_changes = [];
            foreach ($this->getIconFolder()->getColorSet()->getColors() as $old_color) {
                $new_color = $form->getInput($old_color->getId());
                if (!preg_match('/[\dabcdef]{6}/i', $new_color)) {
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt('invalid_color') . $new_color,
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                } elseif ($new_color != $old_color->getColor()) {
                    $color_changes[$old_color->getColor()] = $new_color;
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt('color_changed_from') . ' ' . $old_color->getColor() . ' ' .
                        $this->lng->txt('color_changed_to') . ' ' . $new_color,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    ));
                }
            }
            $this->getIconFolder()->changeIconColors($color_changes);
            $this->setIconFolder(new ilSystemStyleIconFolder($this->getStyleContainer()->getImagesSkinPath($this->style_id)));
            $skin = $this->getStyleContainer()->getSkin();
            $skin->getVersionStep($skin->getVersion());
            $this->getStyleContainer()->updateSkin($skin);
            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('color_update'),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
            $message_stack->sendMessages();
            $this->ctrl->redirect($this, 'edit');
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function editIcon(): void
    {
        $icon_name = '';
        if ($this->request_wrapper->post()->has('selected_icon')) {
            $icon_name = $this->request_wrapper->post()->retrieve(
                'selected_icon',
                $this->refinery->kindlyTo()->string()
            );
        } elseif ($this->request_wrapper->query()->has('selected_icon')) {
            $icon_name = $this->request_wrapper->query()->retrieve(
                'selected_icon',
                $this->refinery->kindlyTo()->string()
            );
        }

        $this->addSelectIconToolbar($icon_name);

        if ($icon_name) {
            $icon = $this->getIconFolder()->getIconByPath($icon_name);
            $form = $this->initByIconForm($icon);
            $this->tpl->setContent($form->getHTML() . $this->renderIconPreview($icon));
        }
    }

    protected function addSelectIconToolbar(?string $icon_name = ''): void
    {
        $si = new ilSelectInputGUI($this->lng->txt('select_icon'), 'selected_icon');

        $options = [];
        $this->getIconFolder()->sortIconsByPath();
        $substr_len = strlen($this->getIconFolder()->getPath()) + 1;
        foreach ($this->getIconFolder()->getIcons() as $icon) {
            if ($icon->getType() == 'svg') {
                $options[$icon->getPath()] = substr($icon->getPath(), $substr_len);
            }
        }

        $si->setOptions($options);

        $si->setValue($icon_name);

        $this->toolbar->addInputItem($si, true);

        $this->toolbar->addComponent($this->ui_factory->button()->standard(
            $this->lng->txt('select'),
            ''
        ));
        $this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'editIcon'));
    }

    protected function initByIconForm(ilSystemStyleIcon $icon): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt('adapt_icon') . ' ' . $icon->getName());
        $form->setDescription($this->lng->txt('adapt_icon_description'));

        $title = $this->lng->txt('color');
        $id = 1;
        foreach ($icon->getColorSet()->getColors() as $color) {
            /**
             * @var ilSystemStyleIconColor $color
             */
            $input = new ilColorPickerInputGUI($title . ' ' . $id, $color->getId());
            $input->setRequired(true);
            $input->setValue($color->getColor());
            $form->addItem($input);
            $id++;
        }

        $upload = new ilFileInputGUI($this->lng->txt('change_icon'), 'changed_icon');
        $upload->setSuffixes(['svg']);
        $form->addItem($upload);

        $hidden_path = new ilHiddenInputGUI('selected_icon');
        $hidden_path->setValue($icon->getPath());
        $form->addItem($hidden_path);

        if (count($this->getIconFolder()->getIcons()) > 0) {
            $form->addCommandButton('updateIcon', $this->lng->txt('update_icon'));
            $form->addCommandButton('cancelIcon', $this->lng->txt('cancel'));
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    protected function updateIcon(): void
    {
        $icon_path = $this->request_wrapper->post()->retrieve(
            'selected_icon',
            $this->refinery->kindlyTo()->string()
        );

        $icon = $this->getIconFolder()->getIconByPath($icon_path);

        $form = $this->initByIconForm($icon);

        if ($form->checkInput()) {
            $message_stack = new ilSystemStyleMessageStack($this->tpl);

            $color_changes = [];
            foreach ($icon->getColorSet()->getColors() as $old_color) {
                $new_color = $form->getInput($old_color->getId());
                if (!preg_match('/[\dabcdef]{6}/i', $new_color)) {
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt('invalid_color') . $new_color,
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                } elseif ($new_color != $old_color->getColor()) {
                    $color_changes[$old_color->getColor()] = $new_color;

                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt('color_changed_from') . ' ' . $old_color->getColor() . ' ' .
                        $this->lng->txt('color_changed_to') . ' ' . $new_color,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    ));
                }
            }
            $icon->changeColors($color_changes);

            if ($this->upload->hasUploads()) {
                $this->upload->process();
                /** @var \ILIAS\FileUpload\DTO\UploadResult $result */
                $result = array_values($this->upload->getResults())[0];

                $old_icon = $this->getIconFolder()->getIconByPath($icon_path);

                $this->upload->moveOneFileTo(
                    $result,
                    $old_icon->getDirRelToCustomizing(),
                    Location::CUSTOMIZING,
                    $old_icon->getName(),
                    true
                );
            }

            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('color_update'),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));

            foreach ($message_stack->getJoinedMessages() as $type => $message) {
                if ($type == ilSystemStyleMessage::TYPE_SUCCESS) {
                    $skin = $this->getStyleContainer()->getSkin();
                    $skin->getVersionStep($skin->getVersion());
                    $this->getStyleContainer()->updateSkin($skin);
                }
            }
            $message_stack->sendMessages();
            $this->ctrl->setParameter($this, 'selected_icon', $icon->getPath());
            $this->ctrl->redirect($this, 'editIcon');
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function renderIconPreview(ilSystemStyleIcon $icon): string
    {
        $icon_image = $this->ui_factory->image()->standard($icon->getPath(), $icon->getName());

        $card = $this->ui_factory->card()->standard(
            $icon->getName(),
            $icon_image
        );

        $report = $this->ui_factory->panel()->standard($this->lng->txt('preview'), $this->ui_factory->deck([$card]));

        return $this->renderer->render($report);
    }

    public function getIconsPreviews(): \ILIAS\UI\Component\Panel\Report
    {
        $sub_panels = [];
        foreach ($this->getIconFolder()->getIconsSortedByFolder() as $folder_name => $icons) {
            $cards = [];

            foreach ($icons as $icon) {
                /**
                 * @var ilSystemStyleIcon $icon
                 */
                $icon_image = $this->ui_factory->image()->standard($icon->getPath(), $icon->getName());
                $card = $this->ui_factory->card()->standard(
                    $icon->getName(),
                    $icon_image
                );
                $colors = $icon->getColorSet()->asString();
                if ($colors) {
                    $card = $card->withSections([
                        $this->ui_factory->listing()->descriptive([$this->lng->txt('used_colors') => $colors])
                    ]);
                }
                $cards[] = $card;
            }
            $sub_panels[] = $this->ui_factory->panel()->sub($folder_name, $this->ui_factory->deck($cards));
        }

        return $this->ui_factory->panel()->report($this->lng->txt('icons'), $sub_panels);
    }

    protected function getStyleContainer(): ilSkinStyleContainer
    {
        return $this->style_container;
    }

    protected function setStyleContainer(ilSkinStyleContainer $style_container): void
    {
        $this->style_container = $style_container;
    }

    protected function getIconFolder(): ilSystemStyleIconFolder
    {
        return $this->icon_folder;
    }

    protected function setIconFolder(ilSystemStyleIconFolder $icon_folder): void
    {
        $this->icon_folder = $icon_folder;
    }
}
