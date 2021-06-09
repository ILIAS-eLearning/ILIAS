<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Select taxonomy nodes input GUI
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_IsCalledBy ilTaxSelectInputGUI: ilFormPropertyDispatchGUI
 */
class ilTaxSelectInputGUI extends ilExplorerSelectInputGUI
{
    protected bool $multi_nodes;
    protected ilTaxonomyExplorerGUI $explorer_gui;
    protected ilObjTaxonomy $tax;
    protected int $taxononmy_id;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_taxonomy_id, $a_postvar, $a_multi = false)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $lng->loadLanguageModule("tax");
        $this->multi_nodes = $a_multi;
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $a_postvar);
        $this->explorer_gui = new ilTaxonomyExplorerGUI(
            array("ilformpropertydispatchgui", "iltaxselectinputgui"),
            $this->getExplHandleCmd(),
            $a_taxonomy_id,
            "",
            "",
            "tax_expl_" . $a_postvar
        );
        $this->explorer_gui->setSelectMode($a_postvar . "_sel", $this->multi_nodes);
        $this->explorer_gui->setSkipRootNode(true);

        parent::__construct(ilObject::_lookupTitle($a_taxonomy_id), $a_postvar, $this->explorer_gui, $this->multi_nodes);
        $this->setType("tax_select");
        
        if ((int) $a_taxonomy_id == 0) {
            throw new ilTaxonomyException("No taxonomy ID passed to ilTaxSelectInputGUI.");
        }
        
        $this->setTaxonomyId((int) $a_taxonomy_id);
        $this->tax = new ilObjTaxonomy($this->getTaxonomyId());
    }
    
    /**
     * Set taxonomy id
     *
     * @param int $a_val taxonomy id
     */
    public function setTaxonomyId($a_val)
    {
        $this->taxononmy_id = $a_val;
    }
    
    /**
     * Get taxonomy id
     *
     * @return int taxonomy id
     */
    public function getTaxonomyId()
    {
        return $this->taxononmy_id;
    }
    
    /**
     * Get title for node id (needs to be overwritten, if explorer is not a tree eplorer
     *
     * @param
     * @return
     */
    public function getTitleForNodeId($a_id)
    {
        return ilTaxonomyNode::_lookupTitle($a_id);
    }
}
