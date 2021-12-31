<?php declare(strict_types=1);

use ILIAS\FileUpload\Location;

/**
 * @ilCtrl_Calls ilSystemStyleIconsGUI:
 */
class ilSystemStyleIconsGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalPageTemplate $tpl;
    protected ilSystemStyleSkinContainer $style_container;
    protected ilSystemStyleIconFolder $icon_folder;
    protected ilTabsGUI $tabs;
    protected \ILIAS\UI\Factory $f;

    public function __construct(string $skin_id = "", string $style_id = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->f = $DIC->ui()->factory();

        if ($skin_id == "") {
            $skin_id = $_GET["skin_id"];
        }
        if ($style_id == "") {
            $style_id = $_GET["style_id"];
        }
        $this->setStyleContainer(ilSystemStyleSkinContainer::generateFromId($skin_id));
        if ($this->ctrl->getCmd() != "reset") {
            try {
                $this->setIconFolder(new ilSystemStyleIconFolder($this->getStyleContainer()->getImagesSkinPath($style_id)));
            } catch (ilSystemStyleExceptionBase $e) {
                ilUtil::sendFailure($e->getMessage());
                $this->ctrl->setCmd("fail");
            }
        }
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        $this->setSubStyleSubTabs($cmd);

        switch ($cmd) {
            case "fail":
                $this->fail();
                break;
            case "cancelIcon":
                $this->editIcon();
                break;
            case "save":
            case "edit":
            case "editIcon":
            case "update":
            case "reset":
            case "preview":
            case "updateIcon":
                $this->$cmd();
                break;
            default:
                $this->edit();
                break;
        }
    }

    protected function fail() : void
    {
        $form = $this->initByColorForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function setSubStyleSubTabs(string $active = "") : void
    {
        $this->tabs->addSubTab('edit', $this->lng->txt('edit_by_color'), $this->ctrl->getLinkTarget($this, 'edit'));
        $this->tabs->addSubTab('editIcon', $this->lng->txt('edit_by_icon'), $this->ctrl->getLinkTarget($this, 'editIcon'));
        $this->tabs->addSubTab('preview', $this->lng->txt('icons_gallery'), $this->ctrl->getLinkTarget($this, "preview"));

        if ($active == "preview") {
            $this->tabs->activateSubTab($active);
        } elseif ($active == "cancelIcon" || $active == "editIcon") {
            $this->tabs->activateSubTab("editIcon");
        } else {
            $this->tabs->activateSubTab("edit");
        }
    }

    protected function edit() : void
    {
        $form = $this->initByColorForm();
        $this->getByColorValues($form);
        $this->tpl->setContent($form->getHTML());
    }

    protected function preview() : void
    {
        $this->tpl->setContent($this->renderIconsPreviews());
    }

    public function initByColorForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt("adapt_icons"));
        $form->setDescription($this->lng->txt("adapt_icons_description"));

        $color_set = [];

        if ($this->getIconFolder()) {
            try {
                $color_set = $this->getIconFolder()->getColorSet()->getColorsSortedAsArray();
            } catch (ilSystemStyleExceptionBase $e) {
                ilUtil::sendFailure($e->getMessage());
            }
        }

        foreach ($color_set as $type => $colors) {
            $section = new ilFormSectionHeaderGUI();
            $title = "";

            if ($type == ilSystemStyleIconColor::GREY) {
                $title = $this->lng->txt("grey_color");
                $section->setTitle($this->lng->txt("grey_colors"));
                $section->setInfo($this->lng->txt("grey_colors_description"));
                $section->setSectionAnchor($this->lng->txt("grey_colors"));
            }
            if ($type == ilSystemStyleIconColor::RED) {
                $title = $this->lng->txt("red_color");
                $section->setTitle($this->lng->txt("red_colors"));
                $section->setInfo($this->lng->txt("red_colors_description"));
                $section->setSectionAnchor($this->lng->txt("red_colors"));
            }
            if ($type == ilSystemStyleIconColor::GREEN) {
                $title = $this->lng->txt("green_color");
                $section->setTitle($this->lng->txt("green_colors"));
                $section->setInfo($this->lng->txt("green_colors_description"));
                $section->setSectionAnchor($this->lng->txt("green_colors"));
            }
            if ($type == ilSystemStyleIconColor::BLUE) {
                $title = $this->lng->txt("blue_color");
                $section->setTitle($this->lng->txt("blue_colors"));
                $section->setInfo($this->lng->txt("blue_colors_description"));
                $section->setSectionAnchor($this->lng->txt("blue_colors"));
            }
            $form->addItem($section);

            foreach ($colors as $id => $color) {
                /**
                 * @var ilSystemStyleIconColor $color
                 */
                $input = new ilColorPickerInputGUI($title . " " . ($id + 1), $color->getId());
                $input->setRequired(true);
                $input->setInfo("Usages: " . $this->getIconFolder()->getUsagesOfColorAsString($color->getId()));
                $form->addItem($input);
            }
        }

        $has_icons = $this->getIconFolder() && count($this->getIconFolder()->getIcons()) > 0;

        if ($has_icons) {
            $form->addCommandButton("update", $this->lng->txt("update_colors"));
        }
        $form->addCommandButton("reset", $this->lng->txt("reset_icons"));
        if ($has_icons) {
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public function getByColorValues(ilPropertyFormGUI $form) : void
    {
        $values = [];

        if ($this->getIconFolder()) {
            $colors = $this->getIconFolder()->getColorSet()->getColors();
            foreach ($colors as $color) {
                $id = $color->getId();
                if ($colors[$color->getId()]) {
                    $values[$id] = $colors[$color->getId()]->getColor();
                } else {
                    $values[$id] = $color->getColor();
                }
            }
        }

        $form->setValuesByArray($values);
    }

    public function reset() : void
    {
        $style = $this->getStyleContainer()->getSkin()->getStyle($_GET["style_id"]);
        $this->getStyleContainer()->resetImages($style);
        $this->setIconFolder(new ilSystemStyleIconFolder($this->getStyleContainer()->getImagesSkinPath($style->getId())));
        $message_stack = new ilSystemStyleMessageStack();
        $message_stack->addMessage(new ilSystemStyleMessage(
            $this->lng->txt("color_reset"),
            ilSystemStyleMessage::TYPE_SUCCESS
        ));
        $message_stack->getUIComponentsMessages( $this->f);

        $this->ctrl->redirect($this, "edit");
    }

    public function update() : void
    {
        $form = $this->initByColorForm();
        if ($form->checkInput()) {
            $message_stack = new ilSystemStyleMessageStack();

            $color_changes = [];
            foreach ($this->getIconFolder()->getColorSet()->getColors() as $old_color) {
                $new_color = $form->getInput($old_color->getId());
                if (!preg_match("/[\dabcdef]{6}/i", $new_color)) {
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt("invalid_color") . $new_color,
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                } elseif ($new_color != $old_color->getColor()) {
                    $color_changes[$old_color->getColor()] = $new_color;
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt("color_changed_from") . " " . $old_color->getColor() . " " .
                        $this->lng->txt("color_changed_to") . " " . $new_color,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    ));
                }
            }
            $this->getIconFolder()->changeIconColors($color_changes);
            $this->setIconFolder(new ilSystemStyleIconFolder($this->getStyleContainer()->getImagesSkinPath($_GET["style_id"])));
            $skin = $this->getStyleContainer()->getSkin();
            $skin->getVersionStep($skin->getVersion());
            $this->getStyleContainer()->updateSkin($skin);
            $message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt("color_update"),
                ilSystemStyleMessage::TYPE_SUCCESS
            ));
            $message_stack->getUIComponentsMessages( $this->f);
            $this->ctrl->redirect($this, "edit");
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }


    protected function editIcon() : void
    {
        $icon_name = $_POST['selected_icon']?$_POST['selected_icon']:$_GET['selected_icon'];

        $this->addSelectIconToolbar($icon_name);

        if ($icon_name) {
            $icon = $this->getIconFolder()->getIconByPath($icon_name);
            $form = $this->initByIconForm($icon);
            $this->tpl->setContent($form->getHTML() . $this->renderIconPreview($icon));
        }
    }

    protected function addSelectIconToolbar(?string $icon_name = "")
    {
        global $DIC;

        $toolbar = $DIC->toolbar();

        $si = new ilSelectInputGUI($this->lng->txt("select_icon"), "selected_icon");

        $options = array();
        $this->getIconFolder()->sortIconsByPath();
        $substr_len = strlen($this->getIconFolder()->getPath()) + 1;
        foreach ($this->getIconFolder()->getIcons() as $icon) {
            if ($icon->getType() == "svg") {
                $options[$icon->getPath()] = substr($icon->getPath(), $substr_len);
            }
        }

        $si->setOptions($options);

        $si->setValue($icon_name);

        $toolbar->addInputItem($si, true);

        $select_btn = ilSubmitButton::getInstance();
        $select_btn->setCaption($this->lng->txt("select"), false);
        $toolbar->addButtonInstance($select_btn);
        $toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'editIcon'));
    }

    public function initByIconForm(ilSystemStyleIcon $icon) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt("adapt_icon") . " " . $icon->getName());
        $form->setDescription($this->lng->txt("adapt_icon_description"));

        $title = $this->lng->txt("color");
        $id = 1;
        foreach ($icon->getColorSet()->getColors() as $color) {
            /**
             * @var ilSystemStyleIconColor $color
             */
            $input = new ilColorPickerInputGUI($title . " " . $id, $color->getId());
            $input->setRequired(true);
            $input->setValue($color->getColor());
            $form->addItem($input);
            $id++;
        }

        $upload = new ilFileInputGUI($this->lng->txt("change_icon"), "changed_icon");
        $upload->setSuffixes(["svg"]);
        $form->addItem($upload);

        $hidden_path = new ilHiddenInputGUI("selected_icon");
        $hidden_path->setValue($icon->getPath());
        $form->addItem($hidden_path);

        if ($this->getIconFolder() && count($this->getIconFolder()->getIcons()) > 0) {
            $form->addCommandButton("updateIcon", $this->lng->txt("update_icon"));
            $form->addCommandButton("cancelIcon", $this->lng->txt("cancel"));
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public function updateIcon() : void
    {
        global $DIC;

        $icon_path = $_POST['selected_icon'];
        $icon = $this->getIconFolder()->getIconByPath($icon_path);

        $form = $this->initByIconForm($icon);

        if ($form->checkInput()) {
            $message_stack = new ilSystemStyleMessageStack();

            $color_changes = [];
            foreach ($icon->getColorSet()->getColors() as $old_color) {
                $new_color = $form->getInput($old_color->getId());
                if (!preg_match("/[\dabcdef]{6}/i", $new_color)) {
                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt("invalid_color") . $new_color,
                        ilSystemStyleMessage::TYPE_ERROR
                    ));
                } elseif ($new_color != $old_color->getColor()) {
                    $color_changes[$old_color->getColor()] = $new_color;

                    $message_stack->addMessage(new ilSystemStyleMessage(
                        $this->lng->txt("color_changed_from") . " " . $old_color->getColor() . " " .
                        $this->lng->txt("color_changed_to") . " " . $new_color,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    ));
                }
            }
            $icon->changeColors($color_changes);

            if ($_POST["changed_icon"]) {
                /**
                 * @var \ILIAS\FileUpload\FileUpload $upload
                 */
                $upload = $DIC->upload();
                $upload->process();
                $old_icon = $this->getIconFolder()->getIconByName($icon_path);
                $result = $upload->getResults();

                $upload->moveOneFileTo(
                    array_pop($result),
                    $old_icon->getDirRelToCustomizing(),
                    Location::CUSTOMIZING,
                    $old_icon->getName(),
                    true
                );
            }

            $message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt("color_update"), ilSystemStyleMessage::TYPE_SUCCESS));

            foreach ($message_stack->getJoinedMessages() as $type => $message) {
                if ($type == ilSystemStyleMessage::TYPE_SUCCESS) {
                    $skin = $this->getStyleContainer()->getSkin();
                    $skin->getVersionStep($skin->getVersion());
                    $this->getStyleContainer()->updateSkin($skin);
                    continue;
                }
            }
            $message_stack->getUIComponentsMessages( $this->f);
            $this->ctrl->setParameter($this, "selected_icon", $icon->getPath());
            $this->ctrl->redirect($this, "editIcon");
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function renderIconPreview(ilSystemStyleIcon $icon) : string
    {
        global $DIC;

        $f = $DIC->ui()->factory();

        $icon_image = $f->image()->standard($icon->getPath(), $icon->getName());

        $card = $f->card()->standard(
            $icon->getName(),
            $icon_image
        );

        $report = $f->panel()->standard($this->lng->txt("preview"), $f->deck([$card]));

        return $DIC->ui()->renderer()->render($report);
    }

    protected function renderIconsPreviews() : string
    {
        global $DIC;

        $f = $DIC->ui()->factory();


        $sub_panels = [];
        foreach ($this->getIconFolder()->getIconsSortedByFolder() as $folder_name => $icons) {
            $cards = [];

            foreach ($icons as $icon) {
                /**
                 * @var ilSystemStyleIcon $icon
                 */
                $icon_image = $f->image()->standard($icon->getPath(), $icon->getName());
                $card = $f->card()->standard(
                    $icon->getName(),
                    $icon_image
                );
                $colors = $icon->getColorSet()->asString();
                if ($colors) {
                    $card = $card->withSections(array(
                        $f->listing()->descriptive(array($this->lng->txt("used_colors") => $colors))
                    ));
                }
                $cards[] = $card;
            }
            $sub_panels[] = $f->panel()->sub($folder_name, $f->deck($cards));
        }

        $report = $f->panel()->report($this->lng->txt("icons"), $sub_panels);

        return $DIC->ui()->renderer()->render($report);
    }

    public function getStyleContainer() : ilSystemStyleSkinContainer
    {
        return $this->style_container;
    }

    public function setStyleContainer(ilSystemStyleSkinContainer $style_container)
    {
        $this->style_container = $style_container;
    }

    public function getIconFolder() : ilSystemStyleIconFolder
    {
        return $this->icon_folder;
    }

    public function setIconFolder(ilSystemStyleIconFolder $icon_folder)
    {
        $this->icon_folder = $icon_folder;
    }
}
