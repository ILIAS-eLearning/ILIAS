<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaBool
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaBool extends ilExcCriteria
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
        return "bool";
    }
    
    
    // PEER REVIEW
    
    public function addToPeerReviewForm($a_value = null)
    {
        $lng = $this->lng;
        
        if (!$this->isRequired()) {
            $input = new ilCheckboxInputGUI($this->getTitle(), "prccc_bool_" . $this->getId());
            $input->setInfo($this->getDescription());
            $input->setRequired($this->isRequired());
            $input->setChecked($a_value > 0);
        } else {
            $input = new ilSelectInputGUI($this->getTitle(), "prccc_bool_" . $this->getId());
            $input->setInfo($this->getDescription());
            $input->setRequired($this->isRequired());
            $input->setValue($a_value);
            $options = array();
            if (!$a_value) {
                $options[""] = $lng->txt("please_select");
            }
            $options[1] = $lng->txt("yes");
            $options[-1] = $lng->txt("no");
            $input->setOptions($options);
        }
        $this->form->addItem($input);
    }
    
    public function importFromPeerReviewForm()
    {
        return (int) $this->form->getInput("prccc_bool_" . $this->getId());
    }
    
    public function hasValue($a_value)
    {
        return (int) $a_value;
    }
    
    public function getHTML($a_value)
    {
        $lng = $this->lng;
    
        $caption = null;
        if ($this->isRequired() && $a_value < 0) {
            $caption = $lng->txt("no");
        } elseif ($a_value == 1) {
            $caption = $lng->txt("yes");
        }
        return $caption;
    }
}
