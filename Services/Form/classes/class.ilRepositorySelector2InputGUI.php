<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerSelectInputGUI.php");
include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");

/**
 * Select repository nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_IsCalledBy ilRepositorySelector2InputGUI: ilFormPropertyDispatchGUI
 *
 */
class ilRepositorySelector2InputGUI extends ilExplorerSelectInputGUI
{
    /**
     * @var callable
     */
    protected $title_modifier = null;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     * @param   string  $form
     */
    public function __construct($a_title, $a_postvar, $a_multi = false, $form = ilPropertyFormGUI::class)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilCtrl = $DIC->ctrl();
        $this->multi_nodes = $a_multi;
        $this->postvar = $a_postvar;

        include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
        $this->explorer_gui = new ilRepositorySelectorExplorerGUI(
            [$form, ilFormPropertyDispatchGUI::class, ilRepositorySelector2InputGUI::class],
            $this->getExplHandleCmd(),
            $this,
            "selectRepositoryItem",
            "root_id",
            "rep_exp_sel_" . $a_postvar
        );
        //		$this->explorer_gui->setTypeWhiteList($this->getVisibleTypes());
        //		$this->explorer_gui->setClickableTypes($this->getClickableTypes());
        $this->explorer_gui->setSelectMode($a_postvar . "_sel", $this->multi_nodes);
        //$this->explorer_gui = new ilTaxonomyExplorerGUI(array("ilformpropertydispatchgui", "iltaxselectinputgui"), $this->getExplHandleCmd(), $a_taxonomy_id, "", "",
        //	"tax_expl_".$a_postvar);

        parent::__construct($a_title, $a_postvar, $this->explorer_gui, $this->multi_nodes);
        $this->setType("rep_select");
    }

    /**
     * Set title modifier
     *
     * @param callable $a_val
     */
    public function setTitleModifier(callable $a_val)
    {
        $this->title_modifier = $a_val;
        if ($a_val != null) {
            $this->explorer_gui->setNodeContentModifier(function ($a_node) use ($a_val) {
                return $a_val($a_node["child"]);
            });
        } else {
            $this->explorer_gui->setNodeContentModifier(null);
        }
    }

    /**
     * Get title modifier
     *
     * @return callable
     */
    public function getTitleModifier()
    {
        return $this->title_modifier;
    }

    /**
     * Get title for node id (needs to be overwritten, if explorer is not a tree eplorer
     *
     * @param
     * @return
     */
    public function getTitleForNodeId($a_id)
    {
        $c = $this->getTitleModifier();
        if (is_callable($c)) {
            return $c($a_id);
        }
        return ilObject::_lookupTitle(ilObject::_lookupObjId($a_id));
    }

    /**
     * @return ilRepositorySelectorExplorerGUI
     */
    public function getExplorerGUI()
    {
        return $this->explorer_gui;
    }

    /**
     * Get HTML
     *
     * @param
     * @return
     */
    public function getHTML()
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->postvar);
        $html = parent::getHTML();
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $_REQUEST["postvar"]);
        return $html;
    }

    /**
     * Render item
     */
    public function render($a_mode = "property_form")
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->postvar);
        return parent::render($a_mode);
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $_REQUEST["postvar"]);
    }
}
