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
 
/**
 * Class ilExcCriteriaText
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCriteriaText extends ilExcCriteria
{
    public function getType() : string
    {
        return "text";
    }
    
    public function setMinChars(int $a_value) : void
    {
        $this->setDefinition(array("chars" => $a_value));
    }

    public function getMinChars() : int
    {
        $def = $this->getDefinition();
        if (is_array($def)) {
            return (int) $def["chars"];
        }

        return 0;
    }
    
    //
    // ASSIGNMENT EDITOR
    //
    
    public function initCustomForm(ilPropertyFormGUI $a_form) : void
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
    
    public function exportCustomForm(ilPropertyFormGUI $a_form) : void
    {
        $min = $this->getMinChars();
        if ($min) {
            $a_form->getItemByPostVar("peer_char_tgl")->setChecked(true);
            $a_form->getItemByPostVar("peer_char")->setValue($min);
        }
    }
    
    public function importCustomForm(ilPropertyFormGUI $a_form) : void
    {
        $this->setDefinition(null);
        
        if ($a_form->getInput("peer_char_tgl")) {
            $this->setMinChars($a_form->getInput("peer_char"));
        }
    }
    
    
    // PEER REVIEW
    
    public function addToPeerReviewForm($a_value = null) : void
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
        $input->setValue((string) $a_value);
        
        $this->form->addItem($input);
    }
     
    public function importFromPeerReviewForm() : string
    {
        return trim($this->form->getInput("prccc_text_" . $this->getId()));
    }
    
    public function validate($a_value) : bool
    {
        $lng = $this->lng;
        
        if (!$this->hasValue($a_value) &&
            !$this->isRequired()) {
            return true;
        }
        
        $min = $this->getMinChars();
        if ($min) {
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
    
    public function hasValue($a_value) : bool
    {
        return (bool) strlen($a_value);
    }
    
    public function getHTML($a_value) : string
    {
        return nl2br($a_value);
    }
}
