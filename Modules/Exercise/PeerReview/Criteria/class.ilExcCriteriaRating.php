<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Exercise\GUIRequest;

/**
 * Class ilExcCriteriaRating
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCriteriaRating extends ilExcCriteria
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilCustomInputGUI $form_item;
    protected GUIRequest $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        parent::__construct();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->exercise()->internal()->gui()->request();
    }

    public function getType() : string
    {
        return "rating";
    }
    
    
    // PEER REVIEW
    
    public function addToPeerReviewForm($a_value = null) : void
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
        // TODO PHP8
        if (is_array($_POST) &&
            array_key_exists("cmd", $_POST)) {
            if ($this->isRequired() && !$this->hasValue($a_value)) {
                $input->setAlert($this->lng->txt("msg_input_is_required"));
            }
        }
        
        $this->form_item = $input;
    }
    
    protected function getRatingSubType() : string
    {
        return $this->getId()
            ? "peer_" . $this->getId()
            : "peer"; // no catalogue / v1
    }
    
    protected function renderWidget(bool $a_read_only = false) : string
    {
        $rating = new ilRatingGUI();
        $rating->setObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType()
        );
        $rating->setUserId($this->giver_id);
        
        $ajax_id = $this->getId()
            ?: "'rating'";
        
        if (!$a_read_only) {
            $html = '<div class="crit_widget">' .
                $rating->getHTML(false, true, "il.ExcPeerReview.saveCrit(this, " . $this->peer_id . ", " . $ajax_id . ", %rating%)") .
            '</div>';
        } else {
            $html = $rating->getHTML(false, false);
        }
        
        return $html;
    }
    
    public function importFromPeerReviewForm() : void
    {
        // see updateFromAjax()
    }
    
    public function updateFromAjax() : string
    {
        // save rating
        ilRating::writeRatingForUserAndObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType(),
            $this->giver_id,
            $this->request->getRatingValue()
        );
                
        // render current rating
        return $this->renderWidget();
    }
    
    public function validate($a_value) : bool
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
    
    public function hasValue($a_value) : bool
    {
        return (bool) ilRating::getRatingForUserAndObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType(),
            $this->giver_id
        );
    }
    
    public function getHTML($a_value) : string
    {
        return $this->renderWidget(true);
    }
        
    public function resetReview() : void
    {
        ilRating::resetRatingForUserAndObject(
            $this->ass->getId(),
            "ass",
            $this->peer_id,
            $this->getRatingSubType(),
            $this->giver_id
        );
    }
}
