<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaText
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaText extends ilExcCriteria
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->lng = $DIC->language();
    }

    public function getType()
    {
        return "text";
    }
    
    public function setMinChars($a_value)
    {
        $this->setDefinition(array("chars" => (int) $a_value));
    }
    
    public function getMinChars()
    {
        $def = $this->getDefinition();
        if (is_array($def)) {
            return $def["chars"];
        }
    }
    
    //
    // ASSIGNMENT EDITOR
    //
    
    public function initCustomForm(ilPropertyFormGUI $a_form)
    {
        $lng = $this->lng;
        
        $peer_char_tgl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_min_chars_tgl"), "peer_char_tgl");
        $a_form->addItem($peer_char_tgl);
        
        $peer_char = new ilNumberInputGUI($lng->txt("exc_peer_review_min_chars"), "peer_char");
        $peer_char->setInfo($lng->txt("exc_peer_review_min_chars_info"));
        $peer_char->setRequired(true);
        $peer_char->setSize(3);
        $peer_char_tgl->addSubItem($peer_char);
    }
    
    public function exportCustomForm(ilPropertyFormGUI $a_form)
    {
        $min = $this->getMinChars();
        if ($min) {
            $a_form->getItemByPostVar("peer_char_tgl")->setChecked(true);
            $a_form->getItemByPostVar("peer_char")->setValue($min);
        }
    }
    
    public function importCustomForm(ilPropertyFormGUI $a_form)
    {
        $this->setDefinition(null);
        
        if ($a_form->getInput("peer_char_tgl")) {
            $this->setMinChars($a_form->getInput("peer_char"));
        }
    }
    
    
    // PEER REVIEW
    
    public function addToPeerReviewForm($a_value = null)
    {
        $lng = $this->lng;
        
        $info = array();
        if ($this->getDescription()) {
            $info[] = $this->getDescription();
        }
        if ($this->getMinChars()) {
            $info[] = $lng->txt("exc_peer_review_min_chars") . ": " . $this->getMinChars();
        }
        $info = implode("<br />", $info);
        
        $input = new ilTextAreaInputGUI($this->getTitle(), "prccc_text_" . $this->getId());
        $input->setRows(10);
        $input->setInfo($info);
        $input->setRequired($this->isRequired());
        $input->setValue($a_value);
        
        $this->form->addItem($input);
    }
     
    public function importFromPeerReviewForm()
    {
        return trim($this->form->getInput("prccc_text_" . $this->getId()));
    }
    
    public function validate($a_value)
    {
        $lng = $this->lng;
        
        if (!$this->hasValue($a_value) &&
            !$this->isRequired()) {
            return true;
        }
        
        $min = $this->getMinChars();
        if ($min) {
            include_once "Services/Utilities/classes/class.ilStr.php";
            if (ilStr::strLen($a_value) < $min) {
                if ($this->form) {
                    $mess = sprintf($lng->txt("exc_peer_review_chars_invalid"), $min);
                    $this->form->getItemByPostVar("prccc_text_" . $this->getId())->setAlert($mess);
                }
                return false;
            }
        }
        return true;
    }
    
    public function hasValue($a_value)
    {
        return (bool) strlen($a_value);
    }
    
    public function getHTML($a_value)
    {
        return nl2br($a_value);
    }
}
