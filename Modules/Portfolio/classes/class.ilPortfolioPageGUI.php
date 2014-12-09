<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

/**
 * Portfolio page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageObjectGUI, ilObjBlogGUI, ilBlogPostingGUI
 * @ilCtrl_Calls ilPortfolioPageGUI: ilCalendarMonthGUI, ilConsultationHoursGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioPageGUI extends ilPageObjectGUI
{
	const EMBEDDED_NO_OUTPUT = -99;
	
	protected $js_onload_code = array();
	protected $additional = array();
	protected $export_material = array("js"=>array(), "images"=>array(), "files"=>array());
	
	
	/**
	 * Constructor
	 */
	function __construct($a_portfolio_id, $a_id = 0, $a_old_nr = 0, $a_enable_comments = true)
	{
		global $tpl;

		$this->portfolio_id = (int)$a_portfolio_id;
		$this->enable_comments = (bool)$a_enable_comments;
		
		parent::__construct($this->getParentType(), $a_id, $a_old_nr);
		$this->getPageObject()->setPortfolioId($this->portfolio_id);
		
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
	}
	
	function getParentType()
	{
		return "prtf";
	}
	
	protected function getPageContentUserId($a_user_id)
	{
		// user id from content-xml
		return $a_user_id;
	}
	
	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl, $ilUser;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{					
			case "ilobjbloggui":
				// #12879 - we need the wsp-id for the keywords
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
				$wsp_tree = new ilWorkspaceTree($ilUser->getId());
				$blog_obj_id = (int)$this->getPageObject()->getTitle();
				$blog_node_id = $wsp_tree->lookupNodeId($blog_obj_id);
					
				include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
				$blog_gui = new ilObjBlogGUI($blog_node_id,	ilObjBlogGUI::WORKSPACE_NODE_ID);
				$blog_gui->disableNotes(!$this->enable_comments);
				return $ilCtrl->forwardCommand($blog_gui);		
				
			case "ilcalendarmonthgui":
				// booking action
				if($cmd && $cmd != "preview")
				{
					include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');				
					$month_gui = new ilCalendarMonthGUI(new ilDate());	
					return $ilCtrl->forwardCommand($month_gui);
				}
				// calendar month navigation
				else
				{
					$ilCtrl->setParameter($this, "cmd", "preview");
					return self::EMBEDDED_NO_OUTPUT;	
				}
			
			case "ilpageobjectgui":
				die("Deprecated. ilPortfolioPage gui forwarding to ilpageobject");
				return;
				
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
		global $ilUser;
		
		if(!$this->getPageObject())
		{
			return;
		}
		
		switch($this->getPageObject()->getType())
		{
			case ilPortfolioPage::TYPE_BLOG;
				return $this->renderBlog($ilUser->getId(), (int)$this->getPageObject()->getTitle());
				
			default:
				$this->setTemplateOutput(false);
				// $this->setPresentationTitle($this->getPageObject()->getTitle());
				$output = parent::showPage();

				return $output;
		}		
	}

	/**
	 * Set all tabs
	 *
	 * @param
	 * @return
	 */
	function getTabs($a_activate = "")
	{		
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
	
	function getJsOnloadCode()
	{
		return $this->js_onload_code;
	}
	
	function postOutputProcessing($a_output)
	{		
		$parts = array(
			"Profile" => array("0-9", "a-z", "0-9a-z_;\W"), // user, mode, fields
			"Verification" => array("0-9", "a-z", "0-9"), // user, type, id
			"Blog" => array("0-9", "0-9", "0-9;\W"),  // user, blog id, posting ids
			"BlogTeaser" => array("0-9", "0-9", "0-9;\W"),  // user, blog id, posting ids
			"Skills" => array("0-9", "0-9"),  // user, skill id
			"SkillsTeaser" => array("0-9", "0-9"),  // user, skill id
			"ConsultationHours" => array("0-9", "a-z", "0-9;\W"),  // user, mode, group ids
			"ConsultationHoursTeaser" => array("0-9", "a-z", "0-9;\W"),  // user, mode, group ids
			"MyCourses" => array("0-9"),  // user
			"MyCoursesTeaser" => array("0-9")  // user
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
						case "ConsultationHours":
						case "ConsultationHoursTeaser":
						case "MyCourses":
						case "MyCoursesTeaser":
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
				
					$snippet = $this->renderPageElement($type, $snippet);
					$a_output = str_replace($block, $snippet, $a_output);
				}
			}
		}
		
		return $a_output;
	}
	
	protected function renderPageElement($a_type, $a_html)
	{
		return trim($a_html);
	}
	
	protected function renderTeaser($a_type, $a_title, $a_options = null)
	{
		$options = "";
		if($a_options)
		{
			$options = '<div class="il_Footer">'.$this->lng->txt("prtf_page_element_teaser_settings").
				": ".$a_options.'</div>';
		}
		
		return '<div style="margin:5px" class="ilBox"><h3>'.$a_title.'</h3>'.
			'<div class="il_Description_no_margin">'.$this->lng->txt("prtf_page_element_teaser_".$a_type).'</div>'.	
			$options.'</div>';		
	}
	
	protected function renderProfile($a_user_id, $a_type, array $a_fields = null)
	{
		global $ilCtrl;
		
		$user_id = $this->getPageContentUserId($a_user_id);
		
		if($this->getOutputMode() == "offline")
		{
			// profile picture is done in ilPortfolioHTMLExport
			
			$this->export_material["js"][] = "http://maps.google.com/maps/api/js?sensor=false";	
			$this->export_material["js"][] = "./Services/Maps/js/ServiceGoogleMaps.js";
			$this->export_material["js"][] = "./Services/Maps/js/OpenLayers.js";
			$this->export_material["js"][] = "./Services/Maps/js/ServiceOpenLayers.js";								
		}
		
		include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
		$pub_profile = new ilPublicUserProfileGUI($user_id);
		$pub_profile->setEmbedded(true, ($this->getOutputMode() == "offline"));
		
		// full circle: additional was set in the original public user profile call
		$pub_profile->setAdditional($this->getAdditional());

		if($a_type == "manual" && sizeof($a_fields))
		{
			$prefs = array();
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
		
		// not used 
		// $user_id = $this->getPageContentUserId($a_user_id);
		
		$class = "ilObj".$objDefinition->getClassName($a_type)."GUI";
		include_once $objDefinition->getLocation($a_type)."/class.".$class.".php";
		$verification = new $class($a_id, ilObject2GUI::WORKSPACE_OBJECT_ID);
		
		if($this->getOutputMode() != "offline")
		{			
			// direct download link
			$this->ctrl->setParameter($this, "dlid", $a_id);
			$url = $this->ctrl->getLinkTarget($this, "dl".$a_type);
			$this->ctrl->setParameter($this, "dlid", "");
		}
		else
		{
			$file = $verification->object->getFilePath();
			$url = "files/".basename($file);
			
			$this->export_material["files"][] = $file;		
		}		
		
		return $verification->render(true, $url);
	}
	
	protected function dltstv()
	{
		$id = $_GET["dlid"];
		if($id)
		{
			include_once "Modules/Test/classes/class.ilObjTestVerificationGUI.php";
			$verification = new ilObjTestVerificationGUI($id, ilObject2GUI::WORKSPACE_OBJECT_ID);
			$verification->downloadFromPortfolioPage($this->getPageObject());
		}
	}
	
	protected function dlexcv()
	{
		$id = $_GET["dlid"];
		if($id)
		{
			include_once "Modules/Exercise/classes/class.ilObjExerciseVerificationGUI.php";
			$verification = new ilObjExerciseVerificationGUI($id, ilObject2GUI::WORKSPACE_OBJECT_ID);
			$verification->downloadFromPortfolioPage($this->getPageObject());
		}		
	}
	
	protected function dlcrsv()
	{
		$id = $_GET["dlid"];
		if($id)
		{
			include_once "Modules/Course/classes/Verification/class.ilObjCourseVerificationGUI.php";
			$verification = new ilObjCourseVerificationGUI($id, ilObject2GUI::WORKSPACE_OBJECT_ID);
			$verification->downloadFromPortfolioPage($this->getPageObject());
		}
	}
	
	protected function dlscov()
	{
		$id = $_GET["dlid"];
		if($id)
		{
			include_once "Modules/ScormAicc/classes/Verification/class.ilObjSCORMVerificationGUI.php";
			$verification = new ilObjSCORMVerificationGUI($id, ilObject2GUI::WORKSPACE_OBJECT_ID);
			$verification->downloadFromPortfolioPage($this->getPageObject());
		}
	}
	
	protected function renderBlog($a_user_id, $a_blog_id, array $a_posting_ids = null)
	{
		global $ilCtrl;
				
		// not used 
		// $user_id = $this->getPageContentUserId($a_user_id);
		
		// full blog (separate tab/page)
		if(!$a_posting_ids)
		{
			include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
			$blog = new ilObjBlogGUI($a_blog_id, ilObject2GUI::WORKSPACE_OBJECT_ID);
			$blog->disableNotes(!$this->enable_comments);
			$blog->setContentStyleSheet();
			
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
		// not used 
		// $user_id = $this->getPageContentUserId($a_user_id);
		
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
		
		return $this->renderTeaser("blog", $this->lng->txt("obj_blog").' "'.
			ilObject::_lookupTitle($a_blog_id).'"', $postings);
	}	
	
	protected function renderSkills($a_user_id, $a_skills_id)
	{		
		if($this->getOutputMode() == "preview")
		{	
			return $this->renderSkillsTeaser($a_user_id, $a_skills_id);
		}
		
		$user_id = $this->getPageContentUserId($a_user_id);		
	
		include_once "Services/Skill/classes/class.ilPersonalSkillsGUI.php";
		$gui = new ilPersonalSkillsGUI();
		if($this->getOutputMode() == "offline")
		{			
			$gui->setOfflineMode("./files/");
		}		
		$html = $gui->getSkillHTML($a_skills_id, $user_id);
		
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
		// not used 
		// $user_id = $this->getPageContentUserId($a_user_id);
		
		include_once "Services/Skill/classes/class.ilSkillTreeNode.php";
		
		return $this->renderTeaser("skills", $this->lng->txt("skills").' "'.
			ilSkillTreeNode::_lookupTitle($a_skills_id).'"');
	}	
	
	protected function renderConsultationHoursTeaser($a_user_id, $a_mode, $a_group_ids)
	{		
		// not used 
		// $user_id = $this->getPageContentUserId($a_user_id);
		
		if($a_mode == "auto")
		{
			$mode = $this->lng->txt("cont_cach_mode_automatic");
			$groups = null;
		}
		else
		{
			$mode = $this->lng->txt("cont_cach_mode_manual");
			
			include_once "Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php";		
			$groups = array();
			foreach($a_group_ids as $grp_id)
			{
				$groups[] = ilConsultationHourGroups::lookupTitle($grp_id);
			}
			$groups = " (".implode(", ", $groups).")";
		}
		
		$this->lng->loadLanguageModule("dateplaner");
		return $this->renderTeaser("consultation_hours", 
			$this->lng->txt("app_consultation_hours"), $mode.$groups);
	}	
	
	protected function renderConsultationHours($a_user_id, $a_mode, $a_group_ids)
	{		
		global $ilUser;
		
		if($this->getOutputMode() == "preview")
		{	
			return $this->renderConsultationHoursTeaser($a_user_id, $a_mode, $a_group_ids);
		}
		
		if($this->getOutputMode() == "offline")
		{	
			return;
		}
				
		$user_id = $this->getPageContentUserId($a_user_id);
		
		// only if not owner
		if($ilUser->getId() != $user_id)
		{
			$_GET["bkid"] = $user_id;
		}
		
		if($a_mode != "manual")
		{
			$a_group_ids = null;
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		ilCalendarCategories::_getInstance()->setCHUserId($user_id);
		ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION, null, true);
		
		if(!$_REQUEST["seed"])
		{
			$seed = new ilDate(time(), IL_CAL_UNIX);
		}
		else
		{
			$seed = new ilDate($_REQUEST["seed"], IL_CAL_DATE);
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
		$month_gui = new ilCalendarMonthGUI($seed);
		
		// custom schedule filter: handle booking group ids
		include_once('./Services/Calendar/classes/class.ilCalendarScheduleFilterBookings.php');
		$filter = new ilCalendarScheduleFilterBookings($user_id, $a_group_ids);
		$month_gui->addScheduleFilter($filter);
		
		$this->tpl->addCss(ilUtil::getStyleSheetLocation('filesystem','delos.css','Services/Calendar'));
		
		$this->lng->loadLanguageModule("dateplaner");
		return '<h3>'.$this->lng->txt("app_consultation_hours").'</h3>'.
			$this->ctrl->getHTML($month_gui);	
	}	
	
	protected function isMyCoursesActive()
	{
		$prfa_set = new ilSetting("prfa");							
		return (bool)$prfa_set->get("mycrs", true);
	}
	
	protected function renderMyCoursesTeaser($a_user_id)
	{		
		// not used 
		// $user_id = $this->getPageContentUserId($a_user_id);
		
		$title = $this->isMyCoursesActive()
			? "my_courses"
			: "my_courses_inactive";
				
		return $this->renderTeaser($title, 
			$this->lng->txt("prtf_page_element_my_courses_title")); 								
	}	
	
	protected function renderMyCourses($a_user_id)
	{				
		global $ilAccess, $ilUser;
		
		if($this->getOutputMode() == "preview")
		{	
			return $this->renderMyCoursesTeaser($a_user_id);
		}
		
		if(!$this->isMyCoursesActive())
		{	
			return;
		}
		
		$img_path = null;
		if($this->getOutputMode() == "offline")
		{
			$this->export_material["images"][] = "./templates/default/images/icon_crs.svg";
			$this->export_material["images"][] = "./templates/default/images/icon_lobj.svg";
			$this->export_material["images"][] = "./templates/default/images/scorm/complete.svg";
			$this->export_material["images"][] = "./templates/default/images/scorm/not_attempted.svg";
			$this->export_material["images"][] = "./templates/default/images/scorm/failed.svg";
			$this->export_material["images"][] = "./templates/default/images/scorm/incomplete.svg";
			
			$img_path = "images/";
		}
		
		$user_id = $this->getPageContentUserId($a_user_id);
		
		$data = $this->getCoursesOfUser($user_id);
		if(sizeof($data))
		{			
			$tpl = new ilTemplate("tpl.pc_my_courses.html", true, true, "Modules/Portfolio");
			$tpl->setVariable("TITLE", $this->lng->txt("prtf_page_element_my_courses_title"));
			$tpl->setVariable("INFO", $this->lng->txt("prtf_page_element_my_courses_info")); // #14464
		
			include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
			$this->lng->loadLanguageModule("trac");
			$this->lng->loadLanguageModule("crs");
			
			include_once("./Services/Container/classes/class.ilContainerObjectiveGUI.php");
			include_once("./Services/Link/classes/class.ilLink.php");
	
			foreach($data as $course)
			{								
				if(isset($course["lp_status"]))
				{					
					$lp_icon = ilLearningProgressBaseGUI::_getImagePathForStatus($course["lp_status"]);
					$lp_alt = ilLearningProgressBaseGUI::_getStatusText($course["lp_status"]);
					
					if($img_path)
					{
						$lp_icon = $img_path.basename($lp_icon);
					}
					
					$tpl->setCurrentBlock("lp_bl");
					$tpl->setVariable("LP_ICON_URL", $lp_icon);
					$tpl->setVariable("LP_ICON_ALT", $lp_alt);
					$tpl->parseCurrentBlock();	
				}
				
				if(isset($course["objectives"]))
				{
					include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
					$loc_settings = ilLOSettings::getInstanceByObjId($course["obj_id"]);			
					$has_initial_test = (bool)$loc_settings->getInitialTest();
					
					foreach($course["objectives"] as $objtv)
					{				
						$objtv_icon = ilUtil::getTypeIconPath("lobj", $objtv["id"]);
						if($img_path)
						{
							$objtv_icon = $img_path.basename($objtv_icon);
						}
						
						$tpl->setCurrentBlock("objective_bl");
						$tpl->setVariable("OBJECTIVE_TITLE", $objtv["title"]);				
						$tpl->setVariable("OBJTV_ICON_URL", $objtv_icon);				
						$tpl->setVariable("OBJTV_ICON_ALT", $this->lng->txt("crs_objectives"));
						
						if($objtv["type"])
						{
							$tpl->setVariable("LP_OBJTV_PROGRESS", 
								ilContainerObjectiveGUI::buildObjectiveProgressBar($has_initial_test, $objtv["id"], $objtv, true));
						}
						
						$tpl->parseCurrentBlock();	
					}
				}
				
				// always check against current user
				if($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "", $course["ref_id"], "crs") ||
					($ilAccess->checkAccessOfUser($ilUser->getId(), "visible", "", $course["ref_id"], "crs") &&
					$ilAccess->checkAccessOfUser($ilUser->getId(), "join", "", $course["ref_id"], "crs")))	
				{
					$tpl->setCurrentBlock("course_link_bl");
					$tpl->setVariable("COURSE_LINK_TITLE", $course["title"]);
					$tpl->setVariable("COURSE_LINK_URL", ilLink::_getLink($course["ref_id"]));
					$tpl->parseCurrentBlock();			
				}
				else
				{
					$tpl->setCurrentBlock("course_nolink_bl");
					$tpl->setVariable("COURSE_NOLINK_TITLE", $course["title"]);
					$tpl->parseCurrentBlock();		
				}
				
				$crs_icon = ilUtil::getTypeIconPath("crs", $course["obj_id"]);
				if($img_path)
				{
					$crs_icon = $img_path.basename($crs_icon);
				}
				
				$tpl->setCurrentBlock("course_bl");
				$tpl->setVariable("CRS_ICON_URL", $crs_icon);				
				$tpl->setVariable("CRS_ICON_ALT", $this->lng->txt("obj_crs"));
				$tpl->parseCurrentBlock();				
			}
			
			return $tpl->get();					
		}					
	}	
	
	protected function getCoursesOfUser($a_user_id)
	{		
		global $tree;
		
		// see ilPDSelectedItemsBlockGUI
		
		include_once 'Modules/Course/classes/class.ilObjCourseAccess.php';
		include_once 'Services/Membership/classes/class.ilParticipants.php';
		$items = ilParticipants::_getMembershipByType($a_user_id, 'crs');
				
		$references = $lp_obj_refs = array();
		foreach($items as $obj_id)
		{
			$ref_id = ilObject::_getAllReferences($obj_id);						
			if(is_array($ref_id) && count($ref_id))
			{
				$ref_id = array_pop($ref_id);
				if(!$tree->isDeleted($ref_id))
				{
					$visible = false;
					$active = ilObjCourseAccess::_isActivated($obj_id, $visible, false);
					if($active && $visible)
					{
						$references[$ref_id] = array(
							'ref_id' => $ref_id,
							'obj_id' => $obj_id, 							
							'title' => ilObject::_lookupTitle($obj_id)
						);	
						$lp_obj_refs[$obj_id] = $ref_id;	
					}
				}	
			}		
		}								
		
		// get lp data for valid courses
		
		if(sizeof($lp_obj_refs))
		{
			// lp must be active, personal and not anonymized
			include_once "Services/Tracking/classes/class.ilObjUserTracking.php";
			if (ilObjUserTracking::_enabledLearningProgress() &&
				ilObjUserTracking::_enabledUserRelatedData() &&
				ilObjUserTracking::_hasLearningProgressLearner())
			{				
				// see ilLPProgressTableGUI
				include_once "Services/Tracking/classes/class.ilTrQuery.php";
				include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";				
				$lp_data = ilTrQuery::getObjectsStatusForUser($a_user_id, $lp_obj_refs);
				foreach($lp_data as $item)
				{
					$ref_id = $item["ref_ids"];
					$references[$ref_id]["lp_status"] = $item["status"];							
					
					// add objectives
					if($item["u_mode"] == ilLPObjSettings::LP_MODE_OBJECTIVES)
					{					
						// we need the collection for the correct order
						include_once "Services/Tracking/classes/collection/class.ilLPCollectionOfObjectives.php";
						$coll_objtv = new ilLPCollectionOfObjectives($item["obj_id"], $item["u_mode"]);
						$coll_objtv = $coll_objtv->getItems();
						if($coll_objtv)
						{
							// #13373
							$lo_results = $this->parseLOUserResults($item["obj_id"], $a_user_id);
																			
							$tmp = array();
							
							include_once "Modules/Course/classes/class.ilCourseObjective.php";
							foreach($coll_objtv as $objective_id)
							{			
								$tmp[$objective_id] = array(
									"id" => $objective_id,
									"title" => ilCourseObjective::lookupObjectiveTitle($objective_id));
								
								if(array_key_exists($objective_id, $lo_results))
								{
									$lo_result = $lo_results[$objective_id];									
									$tmp[$objective_id]["result_perc"] = $lo_result["result_perc"];
									$tmp[$objective_id]["limit_perc"] = $lo_result["limit_perc"];
									$tmp[$objective_id]["status"] = $lo_result["status"];
									$tmp[$objective_id]["type"] = $lo_result["type"];
								}												
							}	
														
							// order
							foreach($coll_objtv as $objtv_id)
							{								
								$references[$ref_id]["objectives"][] = $tmp[$objtv_id];
							}
						}
					}
				}												
			}									
		}		
		
		$references = ilUtil::sortArray($references, "title", "ASC");
		
		return $references;
	}
	
	// see ilContainerObjectiveGUI::parseLOUserResults()
	protected function parseLOUserResults($a_course_obj_id, $a_user_id)
	{		
		$res = array();
		
		include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
		$lur = new ilLOUserResults($a_course_obj_id, $a_user_id);		
		foreach($lur->getCourseResultsForUserPresentation() as $objective_id => $types)
		{
			// show either initial or qualified for objective
			if(isset($types[ilLOUserResults::TYPE_INITIAL]))
			{
				$initial_status = $types[ilLOUserResults::TYPE_INITIAL]["status"];
			}
			
			// qualified test has priority
			if(isset($types[ilLOUserResults::TYPE_QUALIFIED]))
			{
				$result = $types[ilLOUserResults::TYPE_QUALIFIED];	
				$result["type"] = ilLOUserResults::TYPE_QUALIFIED;				
			}
			else
			{
				$result = $types[ilLOUserResults::TYPE_INITIAL];
				$result["type"] = ilLOUserResults::TYPE_INITIAL;
			}		
						
			$result["initial_status"] = $initial_status;
									
			$res[$objective_id] = $result;
		}
		
		return $res;
	}
	
	public function getExportMaterial()
	{
		return $this->export_material;
	}
}

?>