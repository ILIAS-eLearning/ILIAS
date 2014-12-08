<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEPView.php";
require_once "Services/TEP/classes/class.ilTEPPermissions.php";

/**
 * TEP GUI 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 * 
 * @ilCtrl_Calls ilTEPGUI: ilTEPEntryGUI, ilTEPOperationDaysGUI, ilFormPropertyDispatchGUI
 */
class ilTEPGUI
{
	protected $permissions; // [ilTEPPermissions]
	protected $view_id; // [int]
	protected $view; // [ilTEPView]
	
	/**
	 * Constructor
	 * 
	 * @return self
	 */
	public function __construct()
	{
		$perm = ilTEPPermissions::getInstance();
		$this->setPermissions($perm);
	}
	
	// 
	// properties
	//
	
	/**
	 * Set permissions
	 * 
	 * @param ilTEPPermissions $a_perms
	 */
	protected function setPermissions(ilTEPPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilTEPPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
	/**
	 * Get current view id
	 * 
	 * @return int
	 */
	protected function getCurrentViewId()
	{
		global $ilCtrl;
	
		$this->view_id = (int)$_REQUEST["vw"];
		
		// default
		if(!$this->view_id)
		{	
			$default = ilTEPView::TYPE_LIST;
			$ilCtrl->setParameter($this, "vw", $default);
			$this->view_id = $default;					
		}
		
		// init view instance
		if(!$this->view)
		{
			$this->view = ilTEPView::getInstance($this->view_id, $this, $this->getPermissions());												
		}
		
		return $this->view_id;
	}
	
	/**
	 * Get current view
	 * 
	 * @return ilTEPView
	 */
	protected function getView()
	{
		$this->getCurrentViewId();
		return $this->view;		
	}
	
	
	//
	// GUI basics
	// 
	
	/**
	 * Execute request command
	 * 
	 * @return boolean
	 */
	public function executeCommand()
	{
		global $ilCtrl, $ilMainMenu, $lng, $tpl;
		$ilMainMenu->setActive("gev_others_menu");
		
		if(!$this->getPermissions()->mayView())
		{
			throw new ilException("ilTEPGUI - insufficient permissions");
		}
		
		$tpl->getStandardTemplate();
		
		$lng->loadLanguageModule("tep");
		$tpl->setTitle($lng->txt("tep_page_title"));
				
		$ilCtrl->saveParameter($this, "vw");		
		$ilCtrl->saveParameter($this, "seed");		
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("view");

		switch($next_class)
		{			
			case "iltepentrygui":
				$this->setTabs();
				$ilCtrl->setReturn($this, "view");
				
				// defaults for new entry, see ilTEPView::prepareDataForPresentation() / ilTEPViewGridBased::renderDayForUser()
				
				$entry_day = trim($_REQUEST["edt"])
					? new ilDate(trim($_REQUEST["edt"]), IL_CAL_DATE)
					: array_shift($this->getView()->getPeriod()); // 1st day of period
				
				$entry_tutor = (int)$_REQUEST["euid"] 
					? (int)$_REQUEST["euid"]
					: array_shift($this->getView()->getTutors()); // 1st tutor
				
				require_once "Services/TEP/classes/class.ilTEPEntryGUI.php";
				$gui = new ilTEPEntryGUI($this->getPermissions());
				$gui->setDefaults(						
						$entry_day
						,$entry_tutor
				);
				$ilCtrl->forwardCommand($gui);
				break;
			
			case "iltepoperationdaysgui":
				$ref_id = $_GET["ref_id"];
				if(!$ref_id)
				{
					throw new ilException("ilTEPOperationDaysGUI - no ref_id");
				}
				$ilCtrl->saveParameterByClass("ilTEPOperationDaysGUI", "ref_id", $ref_id);			
				
				require_once "Modules/Course/classes/class.ilObjCourse.php";
				$course = new ilObjCourse($ref_id);
												
				require_once "Services/TEP/classes/class.ilTEPOperationDaysGUI.php";
				$gui = new ilTEPOperationDaysGUI($course);					
				$gui->setCoursePageTitleAndLocator();				
				$ilCtrl->forwardCommand($gui);
				break;
			
			default:				
				$this->$cmd();
				break;
		}

		$tpl->show();
	}
	
	/**
	 * Set tabs
	 * 
	 * @param string $a_active
	 */
	protected function setTabs($a_active = null)
	{
		global $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->clearTargets();
		
		if(!$a_active)
		{
			$a_active = $this->getCurrentViewId();
		}
		
		$map = array(
			 ilTEPView::TYPE_LIST => "list"
			 ,ilTEPView::TYPE_MONTH => "month"
			 ,ilTEPView::TYPE_HALFYEAR => "halfyear"
		);		
		foreach($map as $id => $name)
		{
			$ilCtrl->setParameter($this, "vw", $id);		
			$ilTabs->addTab("view_".$name,
				$lng->txt("tep_tab_".$name),
				$ilCtrl->getLinkTarget($this, "view"));
			
			if($a_active == $id)
			{
				$ilTabs->activateTab("view_".$name);
			}
		}
		
		$ilCtrl->setParameter($this, "vw", $this->getCurrentViewId());						
	}
	
	
	//
	// presentation
	// 
	
	/**
	 * Render current view
	 */
	protected function view()
	{
		global $tpl;
		
		$this->setTabs();
				
		//$tpl->addCss(ilUtil::getStyleSheetLocation("filesystem", "tep_print.css", "Services/TEP"), "print");	
		$csspath = 'Customizing/global/skin/genv/Services/TEP/tep_print.css';
		$tpl->addCss($csspath, "print");	

		
		$view = $this->getView();
		$has_data = $view->loadData();
		
		$this->renderToolbar($has_data);
	
		$tpl->setContent($view->render());
	}
	
	/**
	 * Render toolbar
	 *
	 * @param bool $a_has_content
	 */
	protected function renderToolbar($a_has_content)
	{
		global $ilToolbar, $ilCtrl, $lng;
		
		if($this->getPermissions()->mayEdit())
		{
			$ilToolbar->addButton($lng->txt("tep_add_new_entry"),
				$ilCtrl->getLinkTargetByClass("ilTEPEntryGUI", "createEntry"));
			// gev-patch start
			$ilToolbar->addButton($lng->txt("gev_create_decentral_training"),
				$ilCtrl->getLinkTargetByClass(array("gevDesktopGUI", "gevDecentralTrainingGUI"), "createTraining"));
			// gev-patch end
		}
		
		if($a_has_content)
		{
			$ilToolbar->addButton($lng->txt("tep_export"),
				$ilCtrl->getLinkTarget($this, "export"));
			
			//$ilToolbar->addButton($lng->txt("tep_print"), "#", "", "", 
			//	' onClick="self.print(); return false;"');
		}
	}
	
	
	//
	// export
	// 
	
	/**
	 * Export current events as XLS
	 */
	protected function export()
	{
		global $lng, $ilCtrl;

		$view = $this->getView();		
		if($view->loadData())
		{
			$view->exportXLS();
		}

		$ilCtrl->redirect($this, "view");
	}		
}