<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Confirmation screen class.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilConfirmationGUI
{
    protected ilLanguage $lng;
    private array $hidden_item = [];
    private array $item = [];
    private bool $use_images = false;
    private array $buttons = [];
    private ?string $form_name = null;
    protected ?string $form_action = null;
    protected ?string $headertext = null;
    protected ?string $cancel_txt = null;
    protected ?string $cancel_cmd = null;
    protected ?string $cancel_id = null;
    protected ?string $confirm_txt = null;
    protected ?string $confirm_cmd = null;
    protected ?string $confirm_id = null;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
    }

    final public function setFormAction(string $a_form_action) : void
    {
        $this->form_action = $a_form_action;
    }
    
    final public function getFormAction() : ?string
    {
        return $this->form_action;
    }

    public function setHeaderText(string $a_headertext) : void
    {
        $this->headertext = $a_headertext;
    }

    public function getHeaderText() : ?string
    {
        return $this->headertext;
    }

    final public function addButton(string $a_txt, string $a_cmd) : void
    {
        $this->buttons[] = array(
            "txt" => $a_txt, "cmd" => $a_cmd);
    }

    // Set cancel button command and text
    final public function setCancel(
        string $a_txt,
        string $a_cmd,
        string $a_id = ""
    ) : void {
        $this->cancel_txt = $a_txt;
        $this->cancel_cmd = $a_cmd;
        $this->cancel_id = $a_id;
    }

    final public function setConfirm(
        string $a_txt,
        string $a_cmd,
        string $a_id = ""
    ) : void {
        $this->confirm_txt = $a_txt;
        $this->confirm_cmd = $a_cmd;
        $this->confirm_id = $a_id;
    }

    /**
     * Add row item.
     */
    public function addItem(
        string $a_post_var,
        string $a_id,
        string $a_text,
        string $a_img = "",
        string $a_alt = ""
    ) : void {
        $this->item[] = array("var" => $a_post_var, "id" => $a_id,
            "text" => $a_text, "img" => $a_img, "alt" => $a_alt);
        if ($a_img !== "") {
            $this->use_images = true;
        }
    }
    
    public function addHiddenItem(
        string $a_post_var,
        string $a_value
    ) : void {
        $this->hidden_item[] = array("var" => $a_post_var, "value" => $a_value);
    }

    final public function getHTML() : string
    {
        if ($this->headertext === null || $this->headertext === '') {
            throw new RuntimeException('Please provide a header text before rendering the confirmation dialogue');
        }

        if ($this->form_action === null || $this->form_action === '') {
            throw new RuntimeException('Please provide a form action before rendering the confirmation dialogue');
        }

        if ($this->confirm_txt === null || $this->confirm_cmd === null || $this->confirm_txt === '' || $this->confirm_cmd === '') {
            throw new RuntimeException('Please provide a confirmation button label and command before rendering  the confirmation dialogue');
        }

        if ($this->cancel_txt === null || $this->cancel_cmd === null || $this->cancel_txt === '' || $this->cancel_cmd === '') {
            throw new RuntimeException('Please provide a cancel button label and command before rendering the confirmation dialogue');
        }
        
        ilUtil::sendQuestion($this->getHeaderText());
        
        // delete/handle items
        if (count($this->item) > 0) {
            $ctab = new ilConfirmationTableGUI($this->use_images);
            $ctab->setData($this->item);

            foreach ($this->buttons as $b) {
                $ctab->addCommandButton($b["cmd"], $b["txt"]);
            }
            $ctab->addCommandButton($this->confirm_cmd, $this->confirm_txt);
            $ctab->addCommandButton($this->cancel_cmd, $this->cancel_txt);
            $ctab->setFormAction($this->getFormAction());
            foreach ($this->hidden_item as $hidden_item) {
                $ctab->addHiddenInput($hidden_item["var"], $hidden_item["value"]);
            }
            
            if (!is_null($this->form_name)) {
                $ctab->setFormName($this->form_name);
            }
            
            return $ctab->getHTML();
        }

        // simple version, just ask for confirmation
        $tb = new ilToolbarGUI();
        $tb->setPreventDoubleSubmission(true);
        $tb->setFormAction($this->getFormAction());
        if ($this->hidden_item) {
            foreach ($this->hidden_item as $hidden_item) {
                $hiddenInput = new ilHiddenInputGUI($hidden_item['var']);
                $hiddenInput->setValue($hidden_item['value']);
                $tb->addInputItem($hiddenInput);
            }
        }
        $confirm = ilSubmitButton::getInstance();
        $confirm->setCommand($this->confirm_cmd);
        $confirm->setCaption($this->confirm_txt, false);
        $confirm->setId($this->confirm_id);

        $cancel = ilSubmitButton::getInstance();
        $cancel->setCommand($this->cancel_cmd);
        $cancel->setCaption($this->cancel_txt, false);

        if ($this->cancel_id !== '') {
            $cancel->setId($this->cancel_id);
        }

        $tb->addStickyItem($confirm);
        $tb->addStickyItem($cancel);

        return $tb->getHTML();
    }
    
    public function setFormName(string $a_name) : void
    {
        $this->form_name = $a_name;
    }
}
