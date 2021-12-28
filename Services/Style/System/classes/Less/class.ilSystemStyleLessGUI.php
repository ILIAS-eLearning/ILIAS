<?php declare(strict_types=1);

class ilSystemStyleLessGUI
{

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSystemStyleSkinContainer $style_container;
    protected ilSystemStyleLessFile $less_file;
    protected ilSystemStyleMessageStack $message_stack;
    protected string $style_id;

    public function __construct(
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        string $skin_id,
        string $style_id
    ) {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->style_id = $style_id;

        $this->setMessageStack(new ilSystemStyleMessageStack());

        try {
            $this->setStyleContainer(ilSystemStyleSkinContainer::generateFromId($skin_id));
            $less_file = new ilSystemStyleLessFile($this->getStyleContainer()->getLessVariablesFilePath($style_id));
            $this->setLessFile($less_file);
        } catch (ilSystemStyleException $e) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($e->getMessage(), ilSystemStyleMessage::TYPE_ERROR)
            );
        }
    }

    /**
     * Execute command
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case "save":
            case "edit":
            case "reset":
            case "update":
                $this->$cmd();
                break;
            default:
                $this->edit();
                break;
        }
    }

    protected function checkRequirements() : bool
    {
        $less_path = $this->getStyleContainer()->getLessFilePath($this->style_id);

        $pass = $this->checkLessInstallation();

        if (file_exists($less_path)) {
            $less_variables_name = $this->getStyleContainer()->getLessVariablesName($this->style_id);
            $content = "";
            try {
                $content = file_get_contents($less_path);
            } catch (Exception $e) {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage($this->lng->txt("can_not_read_less_file") . " " . $less_path,
                        ilSystemStyleMessage::TYPE_ERROR)
                );
                $pass = false;
            }
            if ($content) {
                $reg_exp = "/" . preg_quote($less_variables_name, "/") . "/";

                if (!preg_match($reg_exp, $content)) {
                    $this->getMessageStack()->addMessage(
                        new ilSystemStyleMessage($this->lng->txt("less_variables_file_not_included") . " " . $less_variables_name
                            . " " . $this->lng->txt("in_main_less_file") . " " . $less_path,
                            ilSystemStyleMessage::TYPE_ERROR)
                    );
                    $pass = false;
                }
            }
        } else {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("less_file_does_not_exist") . $less_path,
                    ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        }
        return $pass;
    }

    protected function checkLessInstallation() : bool
    {
        $pass = true;

        if (!PATH_TO_LESSC) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("no_less_path_set"), ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        } elseif (!shell_exec(PATH_TO_LESSC)) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("invalid_less_path"), ilSystemStyleMessage::TYPE_ERROR)
            );
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("provided_less_path") . " " . PATH_TO_LESSC,
                    ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        }

        if (!$pass && shell_exec("which lessc")) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("less_less_installation_detected") . shell_exec("which lessc"),
                    ilSystemStyleMessage::TYPE_ERROR)
            );
        }

        return $pass;
    }

    protected function edit()
    {
        $modify = true;

        if (!$this->checkRequirements()) {
            $this->getMessageStack()->prependMessage(
                new ilSystemStyleMessage($this->lng->txt("less_can_not_be_modified"), ilSystemStyleMessage::TYPE_ERROR)
            );
            $modify = false;
        }

        if ($this->getLessFile()) {
            $form = $this->initSystemStyleLessForm($modify);
            $this->getVariablesValues($form);
            $this->tpl->setContent($form->getHTML());
        }

        $this->getMessageStack()->sendMessages(true);
    }

    /**
     * @param bool|true $modify
     * @return ilPropertyFormGUI
     */
    public function initSystemStyleLessForm(bool $modify = true)
    {
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt("adapt_less"));
        $form->setDescription($this->lng->txt("adapt_less_description"));
        $focus_variable = $_GET['id_less_variable'];
        if ($focus_variable) {
            $this->tpl->addOnLoadCode("setTimeout(function() { $('#" . $focus_variable . "').focus();}, 100);");
        }

        foreach ($this->getLessFile()->getCategories() as $category) {
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($category->getName());
            $section->setInfo($category->getComment());
            //$section->setSectionAnchor($category->getName());
            $form->addItem($section);
            foreach ($this->getLessFile()->getVariablesPerCategory($category->getName()) as $variable) {
                $input = new ilTextInputGUI($variable->getName(), $variable->getName());
                $input->setRequired(true);
                $input->setDisabled(!$modify);

                $references = $this->getLessFile()->getReferencesToVariableAsString($variable->getName());

                if ($references != "") {
                    if ($variable->getComment()) {
                        $info = $variable->getComment() . "</br>" . $this->lng->txt("usages") . " " . $references;
                    } else {
                        $info = $this->lng->txt("usages") . " " . $references;
                    }
                } else {
                    $info = $variable->getComment();
                }
                $input->setInfo($info);

                $form->addItem($input);
            }
        }

        if ($modify) {
            $form->addCommandButton("update", $this->lng->txt("update_variables"));
            $form->addCommandButton("reset", $this->lng->txt("reset_variables"));
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function getVariablesValues(ilPropertyFormGUI $form)
    {
        $values = [];
        foreach ($this->getLessFile()->getCategories() as $category) {
            foreach ($this->getLessFile()->getVariablesPerCategory($category->getName()) as $variable) {
                $values[$variable->getName()] = $variable->getValue();
            }
        }

        $form->setValuesByArray($values);
    }

    /**
     *
     */
    public function reset()
    {
        $style = $this->getStyleContainer()->getSkin()->getStyle($_GET["style_id"]);
        $this->setLessFile($this->getStyleContainer()->copyVariablesFromDefault($style));
        try {
            ilUtil::sendSuccess($this->lng->txt("less_file_reset"));
            $this->getStyleContainer()->compileLess($style->getId());
        } catch (ilSystemStyleException $e) {
            ilUtil::sendFailure($this->lng->txt($e->getMessage()), true);
        }

        $this->edit();
    }

    public function update()
    {
        $form = $this->initSystemStyleLessForm();
        if (!$form->checkInput()) {
            $empty_fields = [];
            foreach ($this->getLessFile()->getCategories() as $category) {
                foreach ($this->getLessFile()->getVariablesPerCategory($category->getName()) as $variable) {
                    if ($form->getInput($variable->getName()) == "") {
                        $empty_fields[$variable->getName()] = $this->getLessFile()->getVariableByName($variable->getName())->getValue();
                        $item = $form->getItemByPostVar($variable->getName());
                        $item->setAlert($this->lng->txt("less_variable_empty"));
                    }
                }
            }
            if (!empty($empty_fields)) {
                $form->setValuesByPost();
                $form->setValuesByArray($empty_fields, true);
                ilUtil::sendFailure($this->lng->txt("less_variables_empty_might_have_changed"), true);
                $this->tpl->setContent($form->getHTML());
                return;
            }
        } else {
            foreach ($this->getLessFile()->getCategories() as $category) {
                foreach ($this->getLessFile()->getVariablesPerCategory($category->getName()) as $variable) {
                    $variable->setValue($form->getInput($variable->getName()));
                }
            }
            try {
                $this->getLessFile()->write();
                $this->getStyleContainer()->compileLess($_GET["style_id"]);
                $skin = $this->getStyleContainer()->getSkin();
                $skin->getVersionStep($skin->getVersion());
                $this->getStyleContainer()->updateSkin($skin);
                ilUtil::sendSuccess($this->lng->txt("less_file_updated"));
            } catch (Exception $e) {
                ilUtil::sendFailure($this->lng->txt($e->getMessage()), true);
            }
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    protected function getStyleContainer() : ilSystemStyleSkinContainer
    {
        return $this->style_container;
    }

    protected function setStyleContainer(ilSystemStyleSkinContainer $style_container) : void
    {
        $this->style_container = $style_container;
    }

    protected function getLessFile() : ilSystemStyleLessFile
    {
        return $this->less_file;
    }

    protected function setLessFile(ilSystemStyleLessFile $less_file) : void
    {
        $this->less_file = $less_file;
    }

    protected function getMessageStack() : ilSystemStyleMessageStack
    {
        return $this->message_stack;
    }

    protected function setMessageStack(ilSystemStyleMessageStack $message_stack) : void
    {
        $this->message_stack = $message_stack;
    }
}
