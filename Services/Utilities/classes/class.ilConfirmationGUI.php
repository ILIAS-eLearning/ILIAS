<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTableGUI.php");

/**
* Confirmation screen class.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
* @ingroup ServicesUtilities
*/
class ilConfirmationGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    private $hidden_item = array();
    private $item = array();
    private $use_images = false;
    private $buttons = array();
    private $form_name;
    
    /**
    * Constructor
    *
    */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    final public function setFormAction($a_form_action)
    {
        $this->form_action = $a_form_action;
    }
    
    final public function getFormAction()
    {
        return $this->form_action;
    }

    /**
    * Set Set header text.
    *
    * @param	string	$a_headertext	Set header text
    */
    public function setHeaderText($a_headertext)
    {
        $this->headertext = $a_headertext;
    }

    /**
    * Get Set header text.
    *
    * @return	string	Set header text
    */
    public function getHeaderText()
    {
        return $this->headertext;
    }

    /**
    * Set cancel button command and text
    *
    * @param	string		cancel text
    * @param	string		cancel command
    */
    final public function addButton($a_txt, $a_cmd)
    {
        $this->buttons[] = array(
            "txt" => $a_txt, "cmd" => $a_cmd);
    }

    /**
    * Set cancel button command and text
    *
    * @param	string		cancel text
    * @param	string		cancel command
    */
    final public function setCancel($a_txt, $a_cmd, $a_id = "")
    {
        $this->cancel_txt = $a_txt;
        $this->cancel_cmd = $a_cmd;
        $this->cancel_id = $a_id;
    }

    /**
    * Set confirmation button command and text
    *
    * @param	string		confirmation button text
    * @param	string		confirmation button command
    */
    final public function setConfirm($a_txt, $a_cmd, $a_id = "")
    {
        $this->confirm_txt = $a_txt;
        $this->confirm_cmd = $a_cmd;
        $this->confirm_id = $a_id;
    }

    /**
    * Add row item.
    *
    * @param	string	name of post variable used for id (e.g. "id[]")
    * @param	mixed	id value
    * @param	string	item text
    * @param	string	item image path
    */
    public function addItem(
        $a_post_var,
        $a_id,
        $a_text,
        $a_img = "",
        $a_alt = ""
    ) {
        $this->item[] = array("var" => $a_post_var, "id" => $a_id,
            "text" => $a_text, "img" => $a_img, "alt" => $a_alt);
        if ($a_img != "") {
            $this->use_images = true;
        }
    }
    
    /**
     * Add hidden item.
     *
     * @param	string	name of post variable used for id (e.g. "id[]")
     * @param	mixed	value
     */
    public function addHiddenItem($a_post_var, $a_value)
    {
        $this->hidden_item[] = array("var" => $a_post_var, "value" => $a_value);
    }

    /**
    * Get confirmation screen HTML.
    *
    * @return	string		HTML code.
    */
    final public function getHTML()
    {
        $lng = $this->lng;
        
        ilUtil::sendQuestion($this->getHeaderText());
        
        include_once("./Services/Utilities/classes/class.ilConfirmationTableGUI.php");

        // delete/handle items
        if (count($this->item) > 0) {
            $ctab = new ilConfirmationTableGUI($this->use_images);
            $ctab->setData($this->item);

            // other buttons
            foreach ($this->buttons as $b) {
                $ctab->addCommandButton($b["cmd"], $b["txt"]);
            }
            $ctab->addCommandButton($this->confirm_cmd, $this->confirm_txt);
            $ctab->addCommandButton($this->cancel_cmd, $this->cancel_txt);
            $ctab->setFormAction($this->getFormAction());
            foreach ($this->hidden_item as $hidden_item) {
                $ctab->addHiddenInput($hidden_item["var"], $hidden_item["value"]);
            }
            
            if ($this->form_name) {
                $ctab->setFormName($this->form_name);
            }
            
            return $ctab->getHTML();
        } else { // simple version, just ask for confirmation
            $tb = new ilToolbarGUI();
            $tb->setPreventDoubleSubmission(true);
            $tb->setFormAction($this->getFormAction());
            if ($this->hidden_item) {
                require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
                foreach ($this->hidden_item as $hidden_item) {
                    $hiddenInput = new ilHiddenInputGUI($hidden_item['var']);
                    $hiddenInput->setValue($hidden_item['value']);
                    $tb->addInputItem($hiddenInput);
                }
            }
            require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
            $confirm = ilSubmitButton::getInstance();
            $confirm->setCommand($this->confirm_cmd);
            $confirm->setCaption($this->confirm_txt, false);
            $confirm->setId($this->confirm_id);

            $cancel = ilSubmitButton::getInstance();
            $cancel->setCommand($this->cancel_cmd);
            $cancel->setCaption($this->cancel_txt, false);
            $cancel->setId($this->cancel_id);

            $tb->addStickyItem($confirm);
            $tb->addStickyItem($cancel);

            return $tb->getHTML();
        }
    }
    
    /**
     * Set form name
     *
     * @param string $a_name
     */
    public function setFormName($a_name)
    {
        $this->form_name = $a_name;
    }
}
