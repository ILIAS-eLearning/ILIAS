<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php");

/**
* Class ilDataCollectionRecordViewViewdefinitionGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilDataCollectionRecordViewViewdefinitionGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilDataCollectionRecordViewViewdefinitionGUI: ilPublicUserProfileGUI, ilPageObjectGUI
*/
class ilDataCollectionRecordViewViewdefinitionGUI extends ilPageObjectGUI
{
	protected $obj_id; // [int]
	protected $table_id; // [int]
	
	/**
	 * Constructor
	 *
	 * @param	object	$a_parent_obj
	 * @param	int $table_id 
	 * @param	int $a_definition_id 
	*/
	public function __construct($a_parent_obj, $table_id, $a_definition_id = 0)
	{
		global $tpl;
		
		//TODO Permission-Check
		$this->obj_id = $a_parent_obj->obj_id;
		$this->table_id = $table_id;
			
		$a_definition_id = ilDataCollectionRecordViewViewdefinition::getIdByTableId($this->table_id);
		
		// we always need a page object - create on demand
		if(!$a_definition_id)
		{
			$viewdef = new ilDataCollectionRecordViewViewdefinition(0, $this->table_id);
			$viewdef->create();
			$a_definition_id = $viewdef->getId();
		}
		
		parent::__construct("dclf", $a_definition_id);

		// content style (using system defaults)
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
		
		// config
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledPCTabs(true);
		$this->setEnabledMaps(false);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledWikiLinks(false);						
		$this->setEnabledActivation(false);
	}
	
	/**
	 * Init internal data object
	 *
	 * @param string $a_parent_type
	 * @param int $a_id
	 * @param int $a_old_nr
	 */
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$this->setPageObject(new ilDataCollectionRecordViewViewdefinition($a_id, $this->table_id));
	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilCtrl, $ilLocator, $tpl, $lng;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		$viewdef = $this->getPageObject();		
		if($viewdef)
		{
			$ilCtrl->setParameter($this, "dclv", $viewdef->getId());				 
			// $title = $viewdef->getTitle();
			$title = $lng->txt("dcl_view_viewdefinition");
		}
		
		switch($next_class)
		{			
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("dclf",
					$this->getPageObject()->getId(),
					$this->getPageObject()->old_nr);
				if($viewdef)
				{
					$this->setPresentationTitle($title);
				}
				return $ilCtrl->forwardCommand($page_gui);
				
			default:
				if($viewdef)
				{					
					$this->setPresentationTitle($title);					
					$tpl->setTitle($title);
					
					$ilLocator->addItem($title,
						$ilCtrl->getLinkTarget($this, "preview"));							
				}
				return parent::executeCommand();
		}
	}
	
	/**
	 * Finalizing output processing
	 *
	 * @param string $a_output
	 * @return string
	 */
	function postOutputProcessing($a_output)
	{
		//Get fields
		// require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
		// $fields = ilDataCollectionField::getAll($this->table_id);

		//DEBUG
		//print_r($fields);

		//TODO das Array enthält die Felder der Tabelle. Diese sind als Platzhalterwerte darzustellen.
		//Bezeichnung des Platzhalters: Title; Wert welcher beim Speichern übermittelt werden soll id

		// Wir schlagen vor: für die Felder werden einfach Klammern genutzt: [Name], [Anrede], ...
		//Für die Formular-Funkionen werden #...# verwendet:
		//	#More#
		//	#Save#
		//	#Approve#
		//	#Delete#
		//	#Edit#
		//	#Search#

		
		
		// You can use this to parse placeholders and the like before outputting		
		
		// user view
		if($this->getOutputMode() == IL_PAGE_PREVIEW)
		{
			// [id] to [field]
			// var_dump($a_output);
		}
		// editor
		else if($this->getOutputMode() == IL_PAGE_EDIT)
		{
			// [id] to [fieldname]
			// var_dump($a_output);
		}
		
		return $a_output;
	}
}

?>