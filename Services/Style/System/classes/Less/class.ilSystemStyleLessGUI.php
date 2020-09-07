<?php
include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Style/System/classes/Less/class.ilSystemStyleLessFile.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessageStack.php");


/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleLessGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilSystemStyleSkinContainer
     */
    protected $style_container;

    /**
     * @var ilSystemStyleLessFile
     */
    protected $less_file;

    /**
     * @var ilSystemStyleMessageStack
     */
    protected $message_stack;


    /**
     * ilSystemStyleLessGUI constructor.
     * @param string $skin_id
     * @param string $style_id
     */
    public function __construct($skin_id = "", $style_id = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->setMessageStack(new ilSystemStyleMessageStack());

        if ($skin_id == "") {
            $skin_id = $_GET["skin_id"];
        }
        if ($style_id == "") {
            $style_id = $_GET["style_id"];
        }

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
    public function executeCommand()
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


    /**
     * @return bool
     */
    protected function checkRequirements()
    {
        $style_id = $_GET['style_id'];
        $less_path = $this->getStyleContainer()->getLessFilePath($style_id);

        $pass = $this->checkLessInstallation();

        if (file_exists($less_path)) {
            $less_variables_name = $this->getStyleContainer()->getLessVariablesName($style_id);
            $content = "";
            try {
                $content = file_get_contents($less_path);
            } catch (Exception $e) {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage($this->lng->txt("can_not_read_less_file") . " " . $less_path, ilSystemStyleMessage::TYPE_ERROR)
                );
                $pass = false;
            }
            if ($content) {
                $reg_exp = "/" . preg_quote($less_variables_name, "/") . "/";

                if (!preg_match($reg_exp, $content)) {
                    $this->getMessageStack()->addMessage(
                        new ilSystemStyleMessage($this->lng->txt("less_variables_file_not_included") . " " . $less_variables_name
                            . " " . $this->lng->txt("in_main_less_file") . " " . $less_path, ilSystemStyleMessage::TYPE_ERROR)
                    );
                    $pass = false;
                }
            }
        } else {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("less_file_does_not_exist") . $less_path, ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        }
        return $pass;
    }

    /**
     * @return bool
     */
    protected function checkLessInstallation()
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
                new ilSystemStyleMessage($this->lng->txt("provided_less_path") . " " . PATH_TO_LESSC, ilSystemStyleMessage::TYPE_ERROR)
            );
            $pass = false;
        }

        if (!$pass && shell_exec("which lessc")) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage($this->lng->txt("less_less_installation_detected") . shell_exec("which lessc"), ilSystemStyleMessage::TYPE_ERROR)
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
    public function initSystemStyleLessForm($modify = true)
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

    /**
     * @return ilSystemStyleSkinContainer
     */
    public function getStyleContainer()
    {
        return $this->style_container;
    }

    /**
     * @param ilSystemStyleSkinContainer $style_container
     */
    public function setStyleContainer($style_container)
    {
        $this->style_container = $style_container;
    }

    /**
     * @return ilSystemStyleLessFile
     */
    public function getLessFile()
    {
        return $this->less_file;
    }

    /**
     * @param ilSystemStyleLessFile $less_file
     */
    public function setLessFile($less_file)
    {
        $this->less_file = $less_file;
    }

    /**
     * @return ilSystemStyleMessageStack
     */
    public function getMessageStack()
    {
        return $this->message_stack;
    }

    /**
     * @param ilSystemStyleMessageStack $message_stack
     */
    public function setMessageStack($message_stack)
    {
        $this->message_stack = $message_stack;
    }
}
