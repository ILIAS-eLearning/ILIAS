<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilCustomInputGUI.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");

/**
 * Class ilDataCollectionDatatype
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 *
 *
 */
class ilDataCollectionTreePickInputGUI extends ilCustomInputGUI{

	private $customHTML;

	public function __construct($title, $post_var){
		parent::__construct($title, $post_var);
	}

	public function getHtml(){
		global $ilCtrl;
		$title_input = new ilTextInputGUI($this->getTitle(), "display_".$this->getPostVar());
		$title_input->setDisabled(true);
		$search_input = new ilTextInputGUI($this->getTitle(), "search_".$this->getPostVar());
		$search_input->setDisabled(false);
		$hidden_input = new ilHiddenInputGUI($this->getPostVar());

		$tpl = new ilTemplate("tpl.dcl_tree.html", true, true, "Modules/DataCollection");
		$tpl->setVariable("SEARCH_HTML", "hello world!");
		$tpl->setVariable("OUTPUT_ID", $this->getPostVar());
		$tpl->setVariable("AJAX_LINK", $ilCtrl->getLinkTargetByClass("ilDataCollectionRecordEditGUI", "searchObjects"));

		//TODO: implement picker!
		return $title_input->getToolbarHTML()."<br /><br />".$search_input->getToolbarHTML().$hidden_input->getToolbarHTML().$tpl->get();
	}

}

?>