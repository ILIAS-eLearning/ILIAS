<?php
include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
/**
 * GUI clas for exercise assignments
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 *
 * @ilCtrl_Calls ilExAssignmentGUI: ilShopPurchaseGUI
 */
class ilExAssignmentGUI
{

	/**
	 * Constructor
	 */
	function __construct($a_exc)
	{
		$this->exc = $a_exc;
	}
	
	
	/**
	 * Get assignment header for overview
	 */
	function getOverviewHeader($a_data)
	{
		global $lng, $ilUser;
		
		$lng->loadLanguageModule("exc");
		
		$tpl = new ilTemplate("tpl.assignment_head.html", true, true, "Modules/Exercise");

		if (($a_data["deadline"] > 0) && $a_data["deadline"] - time() <= 0)
		{
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_ended_on"));
			$tpl->setVariable("PROP_VAL",
				ilDatePresentation::formatDate(new ilDateTime($a_data["deadline"],IL_CAL_UNIX)));
			$tpl->parseCurrentBlock();
		}
		else if ($a_data["start_time"] > 0 && time() - $a_data["start_time"] <= 0)
		{
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_starting_on"));
			$tpl->setVariable("PROP_VAL",
				ilDatePresentation::formatDate(new ilDateTime($a_data["start_time"],IL_CAL_UNIX)));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$time_str = $this->getTimeString($a_data["deadline"]);
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_time_to_send"));
			$tpl->setVariable("PROP_VAL", $time_str);
			$tpl->parseCurrentBlock();
	
			if ($a_data["deadline"] > 0)
			{
				$tpl->setCurrentBlock("prop");
				$tpl->setVariable("PROP", $lng->txt("exc_edit_until"));
				$tpl->setVariable("PROP_VAL",
					ilDatePresentation::formatDate(new ilDateTime($a_data["deadline"],IL_CAL_UNIX)));
				$tpl->parseCurrentBlock();
			}
			
		}

		$mand = "";
		if ($a_data["mandatory"])
		{
			$mand = " (".$lng->txt("exc_mandatory").")";
		}
		$tpl->setVariable("TITLE", $a_data["title"].$mand);

		// status icon
		$stat = ilExAssignment::lookupStatusOfUser($a_data["id"], $ilUser->getId());
		switch ($stat)
		{
			case "passed": 	$pic = "scorm/passed.svg"; break;
			case "failed":	$pic = "scorm/failed.svg"; break;
			default: 		$pic = "scorm/not_attempted.svg"; break;
		}
		$tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
		$tpl->setVariable("ALT_STATUS", $lng->txt("exc_".$stat));

		return $tpl->get();
	}

	/**
	 * Get assignment body for overview
	 */
	function getOverviewBody($a_data)
	{
		global $lng, $ilCtrl, $ilUser;
		
		$tpl = new ilTemplate("tpl.assignment_body.html", true, true, "Modules/Exercise");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");

		if(IS_PAYMENT_ENABLED)
		{
			include_once './Services/Payment/classes/class.ilPaymentObject.php';
		}
		
		$info = new ilInfoScreenGUI(null);
		$info->setTableClass("");
		
		$not_started_yet = false;
		if ($a_data["start_time"] > 0 && time() - $a_data["start_time"] <= 0)
		{
			$not_started_yet = true;
		}

		if (!$not_started_yet)
		{
			// instructions
			$info->addSection($lng->txt("exc_instruction"));
			
			$is_html = (strlen($a_data["instruction"]) != strlen(strip_tags($a_data["instruction"])));
			if(!$is_html)
			{
				$a_data["instruction"] = nl2br(ilUtil::makeClickable($a_data["instruction"], true));
			}						
			$info->addProperty("", $a_data["instruction"]);
		}
		
		// schedule
		$info->addSection($lng->txt("exc_schedule"));
		if ($a_data["start_time"] > 0)
		{
			$info->addProperty($lng->txt("exc_start_time"),
				ilDatePresentation::formatDate(new ilDateTime($a_data["start_time"],IL_CAL_UNIX)));
		}
		if ($a_data["deadline"] > 0)
		{
			$info->addProperty($lng->txt("exc_edit_until"),
				ilDatePresentation::formatDate(new ilDateTime($a_data["deadline"],IL_CAL_UNIX)));
		}
		$time_str = $this->getTimeString($a_data["deadline"]);
		if (!$not_started_yet)
		{
			$info->addProperty($lng->txt("exc_time_to_send"),
				"<b>".$time_str."</b>");
		}
		
		// public submissions
		if ($this->exc->getShowSubmissions())
		{
			$ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_data["id"]);
			if ($a_data["deadline"] - time() <= 0)
			{				
				$button = ilLinkButton::getInstance();				
				$button->setCaption("exc_list_submission");
				$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "listPublicSubmissions"));							
				
				$info->addProperty($lng->txt("exc_public_submission"), $button->render());
			}
			else
			{
				$info->addProperty($lng->txt("exc_public_submission"),
					$lng->txt("exc_msg_public_submission"));
			}
			$ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $_GET["ass_id"]);
		}

		$ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_data["id"]);

		if (!$not_started_yet)
		{
			// download files
			$files = ilExAssignment::getFiles($a_data["exc_id"], $a_data["id"]);
			if (count($files) > 0)
			{
				$info->addSection($lng->txt("exc_files"));
				foreach($files as $file)
				{
					// if download must be purchased first show a "buy"-button
					if(IS_PAYMENT_ENABLED && (ilPaymentObject::_isBuyable($_GET['ref_id'],'download') &&
					   !ilPaymentObject::_hasAccess($_GET['ref_id'],'','download')))
					{
						$info->addProperty($file["name"],
							$lng->txt("buy"),
							$ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails"));
					}
					else
					{
						$ilCtrl->setParameterByClass("ilobjexercisegui", "file", urlencode($file["name"]));
						$info->addProperty($file["name"],
							$lng->txt("download"),
							$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "downloadFile"));
						$ilCtrl->setParameterByClass("ilobjexercisegui", "file", "");
					}
				}
			}
	
			// submission
			
			// if submission must be purchased first
			if(IS_PAYMENT_ENABLED
				&& (ilPaymentObject::_isBuyable($_GET['ref_id'],'upload')
				&& !ilPaymentObject::_hasAccess($_GET['ref_id'],'','upload')))
			{
				$info->addSection($lng->txt("exc_your_submission"));

				$ilCtrl->clearParameters($this);

				$ilCtrl->setParameter($this, "ref_id", $_GET['ref_id']);
				$ilCtrl->setParameter($this,'subtype','upload');
				$info->addProperty($lng->txt('exc_hand_in'),
					$lng->txt("buy"),
					$ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails"));
			}
			else 
			{
				$info->addSection($lng->txt("exc_your_submission"));
				
				$delivered_files = ilExAssignment::getDeliveredFiles($a_data["exc_id"], $a_data["id"], $ilUser->getId());

				$times_up = false;
				if(($a_data["deadline"] > 0) && $a_data["deadline"] - time() < 0)
				{
					$times_up = true;
				}
		
				$team_members = null;
				switch($a_data["type"])
				{
					case ilExAssignment::TYPE_UPLOAD_TEAM:	
						$no_team_yet = false;						
						$team_members = ilExAssignment::getTeamMembersByAssignmentId($a_data["id"], $ilUser->getId());
						if(sizeof($team_members))
						{
							$team = array();						
							foreach($team_members as $member_id)
							{
								$team[] = ilObjUser::_lookupFullname($member_id);
							}
							$info->addProperty($lng->txt("exc_team_members"), implode(", ", $team));	
						}
						else
						{
							$no_team_yet = true;
							
							if(!$times_up)
							{
								if(!sizeof($delivered_files))
								{
									 $team_info = $lng->txt("exc_no_team_yet_notice");								
								}
								else
								{
									$team_info = '<span class="warning">'.$lng->txt("exc_no_team_yet_notice").'</span>';		
								}	
																	
								$button = ilLinkButton::getInstance();
								$button->setPrimary(true);
								$button->setCaption("exc_create_team");
								$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "createTeam"));							
								$team_info .= " ".$button->render();		
														
								$team_info .= '<div class="ilFormInfo">'.$lng->txt("exc_no_team_yet_info").'</div>';
							}
							else
							{
								$team_info = '<span class="warning">'.$lng->txt("exc_create_team_times_up_warning").'</span>';
							}
							
							$info->addProperty($lng->txt("exc_team_members"), $team_info);
						}
						// fallthrough
						
					case ilExAssignment::TYPE_UPLOAD:					
						$titles = array();
						foreach($delivered_files as $file)
						{
							$titles[] = $file["filetitle"];
						}
						$files_str = implode($titles, ", ");
						if ($files_str == "")
						{
							$files_str = $lng->txt("message_no_delivered_files");
						}
	
						// no team == no submission
						if(!$no_team_yet)
						{
							$ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_data["id"]);

							if (!$times_up)
							{
								$title = (count($titles) == 0
									? $lng->txt("exc_hand_in")
									: $lng->txt("exc_edit_submission"));												

								$button = ilLinkButton::getInstance();
								$button->setPrimary(true);
								$button->setCaption($title, false);
								$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "submissionScreen"));							
								$files_str.= " ".$button->render();								
							}
							else
							{
								if (count($titles) > 0)
								{								
									$button = ilLinkButton::getInstance();								
									$button->setCaption("already_delivered_files");
									$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "submissionScreen"));											
									$files_str.= " ".$button->render();
								}
							}
						}
	
						$info->addProperty($lng->txt("exc_files_returned"),
							$files_str);						
						break;
						
					case ilExAssignment::TYPE_BLOG:													
						include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";					
						$wsp_tree = new ilWorkspaceTree($ilUser->getId());
						
						// #12939
						if(!$wsp_tree->getRootId())
						{
							$wsp_tree->createTreeForUser($ilUser->getId());
						}
						
						$files_str = "";
						$valid_blog = false;
						if(sizeof($delivered_files))
						{													
							$delivered_files = array_pop($delivered_files);
							$blog_id = (int)$delivered_files["filetitle"];																						
							$node = $wsp_tree->getNodeData($blog_id);							
							if($node["title"])
							{
								// #10116						
								$ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", $blog_id);
								$blog_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjbloggui"), "");
								$ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", "");
								$files_str = '<a href="'.$blog_link.'">'.
									$node["title"].'</a>';
								$valid_blog = true;
							}	
							// remove invalid resource if no upload yet (see download below)
							else if(substr($delivered_files["filename"], -1) == "/")
							{								
								$this->exc->deleteResourceObject($delivered_files["ass_id"],
									$ilUser->getId(), $delivered_files["returned_id"]); 
							}
						}						
						if(!$times_up)
						{
							if(!$valid_blog)
							{				
								$button = ilLinkButton::getInstance();							
								$button->setCaption("exc_create_blog");
								$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "createBlog"));							
								$files_str.= $button->render();								
							}							
							// #10462
							$blogs = sizeof($wsp_tree->getObjectsFromType("blog"));						
							if((!$valid_blog && $blogs) 
								|| ($valid_blog && $blogs > 1))
							{							
								$button = ilLinkButton::getInstance();							
								$button->setCaption("exc_select_blog".($valid_blog ? "_change" : ""));
								$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "selectBlog"));									
								$files_str.= " ".$button->render();
							}
						}
						if($files_str)
						{
							$info->addProperty($lng->txt("exc_blog_returned"), $files_str);		
						}
						if($delivered_files && substr($delivered_files["filename"], -1) != "/")
						{							
							$ilCtrl->setParameterByClass("ilobjexercisegui", "delivered", $delivered_files["returned_id"]);
							$dl_link = $ilCtrl->getLinkTargetByClass("ilobjexercisegui", "download");
							$ilCtrl->setParameterByClass("ilobjexercisegui", "delivered", "");
							
							$button = ilLinkButton::getInstance();							
							$button->setCaption("download");
							$button->setUrl($dl_link);		
							
							$info->addProperty($lng->txt("exc_files_returned"),
								$button->render());		
						}							
						break;
						
					case ilExAssignment::TYPE_PORTFOLIO:
						include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
						
						$files_str = "";
						$valid_prtf = false;
						if(sizeof($delivered_files))
						{
							$delivered_files = array_pop($delivered_files);
							$portfolio_id = (int)$delivered_files["filetitle"];
							
							// #11746
							if(ilObject::_exists($portfolio_id, false, "prtf"))
							{									
								$portfolio = new ilObjPortfolio($portfolio_id, false);											
								if($portfolio->getTitle())
								{								
									// #10116 / #12791														
									$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $portfolio_id);
									$prtf_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "view");
									$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");

									$files_str = '<a href="'.$prtf_link.
										'">'.$portfolio->getTitle().'</a>';
									$valid_prtf = true;
								}
							}
							// remove invalid resource if no upload yet (see download below)
							else if(substr($delivered_files["filename"], -1) == "/")
							{		
								$this->exc->deleteResourceObject($delivered_files["ass_id"],
									$ilUser->getId(), $delivered_files["returned_id"]);							
							}
						}
						if(!$times_up)
						{
							if(!$valid_prtf)
							{
								// if there are portfolio templates available show form first
								include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
								$has_prtt = sizeof(ilObjPortfolioTemplate::getAvailablePortfolioTemplates())
									? "Template"
									: "";
								
								$button = ilLinkButton::getInstance();							
								$button->setCaption("exc_create_portfolio");
								$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "createPortfolio".$has_prtt));										
								$files_str .= $button->render();
							}
							// #10462
							$prtfs = sizeof(ilObjPortfolio::getPortfoliosOfUser($ilUser->getId()));		
							if((!$valid_prtf && $prtfs) 
								|| ($valid_prtf && $prtfs > 1))
							{		
								$button = ilLinkButton::getInstance();							
								$button->setCaption("exc_select_portfolio".($valid_prtf ? "_change" : ""));
								$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "selectPortfolio"));	
								$files_str.= " ".$button->render();
							}
						}
						if($files_str)
						{
							$info->addProperty($lng->txt("exc_portfolio_returned"), $files_str);	
						}
						if($delivered_files && substr($delivered_files["filename"], -1) != "/")
						{							
							$ilCtrl->setParameterByClass("ilobjexercisegui", "delivered", $delivered_files["returned_id"]);
							$dl_link = $ilCtrl->getLinkTargetByClass("ilobjexercisegui", "download");
							$ilCtrl->setParameterByClass("ilobjexercisegui", "delivered", "");
							
							$button = ilLinkButton::getInstance();							
							$button->setCaption("download");
							$button->setUrl($dl_link);		
							
							$info->addProperty($lng->txt("exc_files_returned"),
								$button->render());									
						}			
						break;			
						
					case ilExAssignment::TYPE_TEXT:						
						$ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_data["id"]);
						
						if(!$times_up)
						{
							$button = ilLinkButton::getInstance();
							$button->setPrimary(true);
							$button->setCaption("exc_text_assignment_edit");
							$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "editAssignmentText"));							
							$files_str = $button->render();							
						}
						else
						{
							$button = ilLinkButton::getInstance();
							$button->setCaption("exc_text_assignment_show");
							$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "showAssignmentText"));							
							$files_str = $button->render();														
						}
						
						$info->addProperty($lng->txt("exc_files_returned_text"),
							$files_str);											
						break;
				}
				
				
				$last_sub = ilExAssignment::getLastSubmission($a_data["id"], $ilUser->getId());
				if ($last_sub)
				{
					$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
				}
				else
				{
					$last_sub = "---";
				}

				if ($last_sub != "---")
				{
					$info->addProperty($lng->txt("exc_last_submission"),
						$last_sub);
				}
				
																								
				// peer feedback
				if($times_up && $a_data["peer"])
				{								
					$nr_missing_fb = ilExAssignment::getNumberOfMissingFeedbacks($a_data["id"], $a_data["peer_min"]);
									
					if(!$a_data["peer_dl"] || $a_data["peer_dl"] > time())
					{			
						$dl_info = "";
						if($a_data["peer_dl"])
						{
							$dl_info = " (".sprintf($lng->txt("exc_peer_review_deadline_info_button"), 
								ilDatePresentation::formatDate(new ilDateTime($a_data["peer_dl"], IL_CAL_UNIX))).")";							
						}
						
						$button = ilLinkButton::getInstance();
						$button->setPrimary($nr_missing_fb);
						$button->setCaption($lng->txt("exc_peer_review_give").$dl_info, false);
						$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "editPeerReview"));							
						$edit_pc = $button->render();													
					}
					else if($a_data["peer_dl"])
					{
						$edit_pc = $lng->txt("exc_peer_review_deadline_reached");
					}
					if((!$a_data["peer_dl"] || $a_data["peer_dl"] < time()) && 
						!$nr_missing_fb)
					{						
						$button = ilLinkButton::getInstance();					
						$button->setCaption("exc_peer_review_show");
						$button->setUrl($ilCtrl->getLinkTargetByClass("ilobjexercisegui", "showPersonalPeerReview"));							
						$view_pc = $button->render();							
					}
					/*
					else 
					{
						$view_pc = $lng->txt("exc_peer_review_show_not_rated_yet");
					}
					*/
					
					$info->addProperty($lng->txt("exc_peer_review"),
						$edit_pc." ".$view_pc);																									
				}				
				
				
				// feedback from tutor
				if($a_data["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$feedback_id = "t".ilExAssignment::getTeamIdByAssignment($a_data["id"], $ilUser->getId());
				}
				else
				{
					$feedback_id = $ilUser->getId();
				}
				
				// global feedback / sample solution
				if($a_data["fb_date"] == ilExAssignment::FEEDBACK_DATE_DEADLINE)
				{
					$show_global_feedback = ($times_up && $a_data["fb_file"]);
				}
				else
				{
					$show_global_feedback = ($last_sub != "---" && $a_data["fb_file"]);
				}								
				
				$storage = new ilFSStorageExercise($a_data["exc_id"], $a_data["id"]);					
				$cnt_files = $storage->countFeedbackFiles($feedback_id);
				$lpcomment = ilExAssignment::lookupCommentForUser($a_data["id"], $ilUser->getId());
				$mark = ilExAssignment::lookupMarkOfUser($a_data["id"], $ilUser->getId());
				$status = ilExAssignment::lookupStatusOfUser($a_data["id"], $ilUser->getId());				
				if ($lpcomment != "" || $mark != "" || $status != "notgraded" || 
					$cnt_files > 0 || $show_global_feedback)
				{
					$info->addSection($lng->txt("exc_feedback_from_tutor"));
					if ($lpcomment != "")
					{
						$info->addProperty($lng->txt("exc_comment"),
							$lpcomment);
					}
					if ($mark != "")
					{
						$info->addProperty($lng->txt("exc_mark"),
							$mark);
					}
		
					if ($status == "") 
					{
	//				  $info->addProperty($lng->txt("status"),
	//						$lng->txt("message_no_delivered_files"));				
					}
					else if ($status != "notgraded")
					{
						$img = '<img src="'.ilUtil::getImagePath("scorm/".$status.".svg").'" '.
							' alt="'.$lng->txt("exc_".$status).'" title="'.$lng->txt("exc_".$status).
							'" />';
						$info->addProperty($lng->txt("status"),
							$img." ".$lng->txt("exc_".$status));
					}
					
					if ($cnt_files > 0)
					{
						$info->addSection($lng->txt("exc_fb_files").
							'<a name="fb'.$a_data["id"].'"></a>');
						
						if($cnt_files > 0)
						{
							$files = $storage->getFeedbackFiles($feedback_id);
							foreach($files as $file)
							{
								$ilCtrl->setParameterByClass("ilobjexercisegui", "file", urlencode($file));
								$info->addProperty($file,
									$lng->txt("download"),
									$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "downloadFeedbackFile"));
								$ilCtrl->setParameterByClass("ilobjexercisegui", "file", "");
							}
						}												
					}	
					
					// #15002 - global feedback																	
					if($show_global_feedback)
					{
						$info->addSection($lng->txt("exc_global_feedback_file"));
						
						$info->addProperty($a_data["fb_file"],
							$lng->txt("download"),
							$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "downloadGlobalFeedbackFile"));								
					}
				}								
			}
		}

		$tpl->setVariable("CONTENT", $info->getHTML());
		
		return $tpl->get();
	}
	
	/**
	 * Get time string for deadline
	 */
	function getTimeString($a_deadline)
	{
		global $lng;
		
		if ($a_deadline == 0)
		{
			return $lng->txt("exc_no_deadline_specified");
		}
		
		if ($a_deadline - time() <= 0)
		{
			$time_str = $lng->txt("exc_time_over_short");
		}
		else
		{
			$time_diff = ilUtil::int2array($a_deadline - time(),null);	
			// #11576  - order ascending!
			if (isset($time_diff['minutes']))
			{
				unset($time_diff['seconds']);
			}			
			if (isset($time_diff['days']))
			{
				unset($time_diff['minutes']);
			}
			if (isset($time_diff['months']))
			{
				unset($time_diff['hours']);
			}		
			$time_str = ilUtil::timearray2string($time_diff);
		}

		return $time_str;
	}
	
	
}
