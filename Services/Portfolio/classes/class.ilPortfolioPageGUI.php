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
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageObjectGUI, ilPublicUserProfileGUI, ilObjBlogGUI, ilBlogPostingGUI
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioPageGUI extends ilPageObjectGUI
{
	protected $js_onload_code = array();
	protected $additional = array();
	
	/**
	 * Constructor
	 */
	function __construct($a_portfolio_id, $a_id = 0, $a_old_nr = 0, $a_enable_comments = true)
	{
		global $tpl, $ilSetting;

		$this->portfolio_id = (int)$a_portfolio_id;
		$this->enable_comments = (bool)$a_enable_comments;
		
		parent::__construct("prtf", $a_id, $a_old_nr);
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
				
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
		
		// $this->setEnabledMaps(true);
		// $this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledPCTabs(true);
		$this->setEnabledProfile(true);
		
		if(!$ilSetting->get('disable_wsp_certificates'))
		{
			$this->setEnabledVerification(true);
		}
		
		/* embedding blog postings currently disabled 
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$this->setEnabledBlog(true);
		}		
		*/
		
		$skmg_set = new ilSetting("skmg");
		if($skmg_set->get("enable_skmg"))
		{
			$this->setEnabledSkills(true);
		}
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
			case "ilobjbloggui":
				include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
				$blog_gui = new ilObjBlogGUI((int)$this->getPageObject()->getTitle(),
					ilObjBlogGUI::WORKSPACE_OBJECT_ID);
				$blog_gui->disableNotes(!$this->enable_comments);
				return $ilCtrl->forwardCommand($blog_gui);
				
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
		global $tpl, $ilCtrl, $ilUser;
		
		if(!$this->getPageObject())
		{
			return;
		}
		
		// render blog directly
		if($this->getPageObject()->getType() == ilPortfolioPage::TYPE_BLOG)
		{
			return $this->renderBlog($ilUser->getId(), (int)$this->getPageObject()->getTitle());
		}
		
		$this->setTemplateOutput(false);
		// $this->setPresentationTitle($this->getPageObject()->getTitle());
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
		
		if(!$this->embedded)
		{
			parent::getTabs($a_activate);
		}
	}
	
	/**
	 * Set embedded mode: will suppress tabs
	 * 
	 * @param bool $a_value	 
	 */
	function setEmbedded($a_value)
	{
		$this->embedded = (bool)$a_value;
	}
	
	/**
	* Set Additonal Information.
	*
	* @param	array	$a_additional	Additonal Information
	*/
	function setAdditional($a_additional)
	{
		$this->additional = $a_additional;
	}

	/**
	* Get Additonal Information.
	*
	* @return	array	Additonal Information
	*/
	function getAdditional()
	{
		return $this->additional;
	}
	
	function postOutputProcessing($a_output)
	{		
		$parts = array(
			"Profile" => array("0-9", "a-z", "a-z_;\W"), // user, mode, fields
			"Verification" => array("0-9", "a-z", "0-9"), // user, type, id
			"Blog" => array("0-9", "0-9", "0-9;\W"),  // user, blog id, posting ids
			"BlogTeaser" => array("0-9", "0-9", "0-9;\W"),  // user, blog id, posting ids
			"Skills" => array("0-9", "0-9"),  // user, skill id
			"SkillsTeaser" => array("0-9", "0-9")  // user, skill id
			);
			
		foreach($parts as $type => $def)
		{			
			$def = implode("]+)#([", $def);					
			if(preg_match_all("/".$this->pl_start.$type."#([".$def.
					"]+)".$this->pl_end."/", $a_output, $blocks))
			{
				foreach($blocks[0] as $idx => $block)
				{
					switch($type)
					{
						case "Profile":
						case "Blog":
						case "BlogTeaser":
						case "Skills":
						case "SkillsTeaser":
							$subs = null;
							if(trim($blocks[3][$idx]))
							{
								foreach(explode(";", $blocks[3][$idx]) as $sub)
								{
									if(trim($sub))
									{
										$subs[] = trim($sub);
									}
								}
							}			
							$snippet = $this->{"render".$type}($blocks[1][$idx], 
								$blocks[2][$idx], $subs);
							break;
						
						default:
							$snippet = $this->{"render".$type}($blocks[1][$idx], 
								$blocks[2][$idx], $blocks[3][$idx]);
							break;
					}
				
					$a_output = str_replace($block, $snippet, $a_output);
				}
			}
		}
		
		return $a_output;
	}
	
	protected function renderProfile($a_user_id, $a_type, array $a_fields = null)
	{
		global $ilCtrl;
		
		include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
		$pub_profile = new ilPublicUserProfileGUI($a_user_id);
		$pub_profile->setEmbedded(true, ($this->getOutputMode() == "offline"));
		
		// full circle: additional was set in the original public user profile call
		$pub_profile->setAdditional($this->getAdditional());

		if($a_type == "manual" && sizeof($a_fields))
		{
			foreach($a_fields as $field)
			{
				$field = trim($field);
				if($field)
				{
					$prefs["public_".$field] = "y";
				}
			}

			$pub_profile->setCustomPrefs($prefs);
		}

		if($this->getOutputMode() != "offline")
		{
			return $ilCtrl->getHTML($pub_profile);
		}
		else
		{
			return $pub_profile->getEmbeddable();
		}
	}
	
	protected function renderVerification($a_user_id, $a_type, $a_id)
	{
		global $objDefinition;
		
		$class = "ilObj".$objDefinition->getClassName($a_type)."GUI";
		include_once $objDefinition->getLocation($a_type)."/class.".$class.".php";
		$verification = new $class($a_id, ilObject2GUI::WORKSPACE_OBJECT_ID);

		return $verification->render(true);
	}	
	
	protected function renderBlog($a_user_id, $a_blog_id, array $a_posting_ids = null)
	{
		global $ilCtrl;
				
		// :TODO: what about user?
		
		// full blog (separate tab/page)
		if(!$a_posting_ids)
		{
			include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
			$blog = new ilObjBlogGUI($a_blog_id, ilObject2GUI::WORKSPACE_OBJECT_ID);
			$blog->disableNotes(!$this->enable_comments);
			
			if($this->getOutputMode() != "offline")
			{			
				return $ilCtrl->getHTML($blog);
			}
			else
			{
				
			}
		}
		// embedded postings
		else
		{
			$html = array();
			
			include_once "Modules/Blog/classes/class.ilObjBlog.php";
			$html[] = ilObjBlog::_lookupTitle($a_blog_id);
			
			include_once "Modules/Blog/classes/class.ilBlogPostingGUI.php";
			foreach($a_posting_ids as $post)
			{				
				$page = new ilBlogPostingGUI(0, null, $post);
				if($this->getOutputMode() != "offline")
				{	
					$page->setOutputMode(IL_PAGE_PREVIEW);
				}
				else
				{
					$page->setOutputMode("offline");
				}
				$html[] = $page->showPage();
			}		
			
			return implode("\n", $html);
		}
	}	
	
	protected function renderBlogTeaser($a_user_id, $a_blog_id, array $a_posting_ids = null)
	{
		global $lng;
		
		$postings = "";
		if($a_posting_ids)
		{
			$postings = array("<ul>");
			include_once "Modules/Blog/classes/class.ilBlogPosting.php";
			foreach($a_posting_ids as $post)
			{				
				$post = new ilBlogPosting($post);
				$postings[] = "<li>".$post->getTitle()." - ".
					ilDatePresentation::formatDate($post->getCreated())."</li>";
			}
			$postings[] = "</ul>";
			$postings = implode("\n", $postings);	
		}
		
		return "<div style=\"margin:5px\">".$lng->txt("obj_blog").": \"".
				ilObject::_lookupTitle($a_blog_id)."\"".$postings."</div>";
	}	
	
	protected function renderSkills($a_user_id, $a_skills_id)
	{
		include_once "Services/Skill/classes/class.ilPersonalSkillsGUI.php";
		$gui = new ilPersonalSkillsGUI();
		if($this->getOutputMode() == "offline")
		{			
			$gui->setOfflineMode("./files/");
		}		
		$html = $gui->getSkillHTML($a_skills_id, $a_user_id);
		
		if($this->getOutputMode() == "offline")
		{
			$js = $gui->getTooltipsJs();
			if(sizeof($js))
			{
				$this->js_onload_code = array_merge($this->js_onload_code, $js);
			}
		}
			
		return $html;
	}
	
	protected function renderSkillsTeaser($a_user_id, $a_skills_id)
	{
		global $lng;
		
		include_once "Services/Skill/classes/class.ilSkillTreeNode.php";
		
		return "<div style=\"margin:5px\">".$lng->txt("skills").": \"".
				ilSkillTreeNode::_lookupTitle($a_skills_id)."\"</div>";
	}	
	
	function getJsOnloadCode()
	{
		return $this->js_onload_code;
	}
}
?>