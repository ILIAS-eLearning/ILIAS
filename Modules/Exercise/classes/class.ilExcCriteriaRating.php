<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaRating
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaRating extends ilExcCriteria
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

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
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    public function getType()
    {
        return "rating";
    }
    
    
    // PEER REVIEW
    
    public function addToPeerReviewForm($a_value = null)
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
            
        $tpl->addJavaScript("Modules/Exercise/js/ilExcPeerReview.js");
        $tpl->addOnLoadCode("il.ExcPeerReview.setAjax('" .
            $ilCtrl->getLinkTargetByClass("ilExPeerReviewGUI", "updateCritAjax", "", true, false) .
            "')");
        
        $field_id = "prccc_rating_" . $this->getId();
        
        $input = new ilCustomInputGUI($this->getTitle(), $field_id);
        $input->setInfo($this->getDescription());
        $input->setRequired($this->isRequired());
        $input->setHtml($this->renderWidget());
        $this->form->addItem($input);
        
        // #16993 - making form checkInput() work
        if (is_array($_POST) &&
            array_key_exists("cmd", $_POST)) {
            $_POST[$field_id] = $this->hasValue($a_value);
        }
        
        $this->form_item = $input;
    }
    
    protected function getRatingSubType()
    {
        return $this->getId()
            ? "peer_" . (int) $this->getId()
            : "peer"; // no catalogue / v1
    }
    
    protected function renderWidget($a_read_only = false)
    {
        include_once './Services/Rating/classes/class.ilRatingGUI.php';
        $rating = new ilRatingGUI();
        $rating->setObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType()
        );
        $rating->setUserId($this->giver_id);
        
        $ajax_id = $this->getId()
            ? (int) $this->getId()
            : "'rating'";
        
        if (!(bool) $a_read_only) {
            $html = '<div class="crit_widget">' .
                $rating->getHTML(false, true, "il.ExcPeerReview.saveCrit(this, " . $this->peer_id . ", " . $ajax_id . ", %rating%)") .
            '</div>';
        } else {
            $html = $rating->getHTML(false, false);
        }
        
        return $html;
    }
    
    public function importFromPeerReviewForm()
    {
        // see updateFromAjax()
    }
    
    public function updateFromAjax()
    {
        // save rating
        include_once './Services/Rating/classes/class.ilRating.php';
        ilRating::writeRatingForUserAndObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType(),
            $this->giver_id,
            $_POST["value"]
        );
                
        // render current rating
        // $ilCtrl->setParameter($this->parent_obj, "peer_id", $peer_id);
        return $this->renderWidget($a_ass, $a_giver_id, $a_peer_id);
    }
    
    public function validate($a_value)
    {
        $lng = $this->lng;
        
        if ($this->isRequired()) {
            if (!$this->hasValue($a_value)) {
                if ($this->form) {
                    $this->form->getItemByPostVar("prccc_rating_" . $this->getId())->setAlert($lng->txt("msg_input_is_required"));
                }
                return false;
            }
        }
        return true;
    }
    
    public function hasValue($a_value)
    {
        include_once './Services/Rating/classes/class.ilRating.php';
        return (bool) ilRating::getRatingForUserAndObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType(),
            $this->giver_id
        );
    }
    
    public function getHTML($a_value)
    {
        return $this->renderWidget($this->ass, $this->giver_id, $this->peer_id, true);
    }
        
    public function resetReview()
    {
        include_once './Services/Rating/classes/class.ilRating.php';
        ilRating::resetRatingForUserAndObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType(),
            $this->giver_id
        );
    }
}
