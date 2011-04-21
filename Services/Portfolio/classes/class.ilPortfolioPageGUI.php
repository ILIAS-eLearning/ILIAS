<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Services/Portfolio/classes/class.ilPortfolioPage.php");

/**
 * Portfolio page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageObjectGUI, ilPublicUserProfileGUI
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_portfolio_id, $a_id = 0, $a_old_nr = 0)
	{
		global $tpl;

		$this->portfolio_id = (int)$a_portfolio_id;
		
		parent::__construct("prtf", $a_id, $a_old_nr);
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledPCTabs(true);
		$this->setEnabledProfile(true);
		$this->setEnabledVerification(true);
		$this->setEnabledBlog(true);
	}

	/**
	 * Init page object
	 *
	 * @param	string	parent type
	 * @param	int		id
	 * @param	int		old nr
	 */
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilPortfolioPage($this->portfolio_id, $a_id, $a_old_nr);
		$this->setPageObject($page);
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl, $ilTabs, $ilUser;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{				
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("prtf",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				$page_gui->setPresentationTitle($this->getPageObject()->getTitle());
				return $ilCtrl->forwardCommand($page_gui);
				
			default:				
				$this->setPresentationTitle($this->getPageObject()->getTitle());
				return parent::executeCommand();
		}
	}
	
	/**
	 * Show page
	 *
	 * @return	string	page output
	 */
	function showPage()
	{
		global $tpl, $ilCtrl;
		
		$this->setTemplateOutput(false);
		$this->setPresentationTitle($this->getPageObject()->getTitle());
		$output = parent::showPage();
		
		return $output;
	}

	/**
	 * Set all tabs
	 *
	 * @param
	 * @return
	 */
	function getTabs($a_activate = "")
	{
		global $ilTabs, $ilCtrl;

		parent::getTabs($a_activate);		
	}

	function postOutputProcessing($a_output)
	{
		global $ilCtrl, $objDefinition;
		
		if(preg_match_all("/&#123;&#123;&#123;&#123;&#123;Profile#([0-9]+)#([a-z]+)#([a-z;\W]+)&#125;&#125;&#125;&#125;&#125;/", $a_output, $blocks))
		{
			foreach($blocks[0] as $idx => $block)
			{
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$pub_profile = new ilPublicUserProfileGUI($blocks[1][$idx]);
			
				if($blocks[2][$idx] == "manual")
				{
					foreach(explode(";", $blocks[3][$idx]) as $field)
					{
						$field = trim($field);
						if($field)
						{
							$prefs["public_".$field] = "y";
						}
					}

					$pub_profile->setCustomPrefs($prefs);
				}

				$a_output = str_replace($block, $ilCtrl->getHTML($pub_profile), $a_output);
			}
		}
		
		if(preg_match_all("/&#123;&#123;&#123;&#123;&#123;Verification#([0-9]+)#([a-z]+)#([0-9]+)&#125;&#125;&#125;&#125;&#125;/", $a_output, $blocks))
		{
			foreach($blocks[0] as $idx => $block)
			{
				// user is not used currently
				$user = $blocks[1][$idx];
				
				$type = $blocks[2][$idx];
				$id = $blocks[3][$idx];
				
				$class = "ilObj".$objDefinition->getClassName($type)."GUI";
				include_once $objDefinition->getLocation($type)."/class.".$class.".php";
				$verification = new $class($id, ilObject2GUI::WORKSPACE_OBJECT_ID);
				
				$a_output = str_replace($block, $verification->render(true), $a_output);
			}						
		}
		
		return $a_output;
	}
} 
?>
