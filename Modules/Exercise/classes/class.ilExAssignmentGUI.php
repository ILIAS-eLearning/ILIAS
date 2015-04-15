<?php
/**
 * GUI clas for exercise assignments
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilExAssignmentGUI
{
	protected $exc; // [ilObjExercise]
	protected $current_ass_id; // [int]
	
	/**
	 * Constructor
	 */
	function __construct(ilObjExercise $a_exc)
	{
		$this->exc = $a_exc;
	}	
	
	/**
	 * Get assignment header for overview
	 */
	function getOverviewHeader(ilExAssignment $a_ass)
	{
		global $lng, $ilUser;
		
		$lng->loadLanguageModule("exc");
		
		$tpl = new ilTemplate("tpl.assignment_head.html", true, true, "Modules/Exercise");

		if ($a_ass->afterDeadline(true))
		{
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_ended_on"));
			$tpl->setVariable("PROP_VAL",
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX)));
			$tpl->parseCurrentBlock();
		}
		else if ($a_ass->notStartedYet())
		{
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_starting_on"));
			$tpl->setVariable("PROP_VAL",
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getStartTime(),IL_CAL_UNIX)));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$time_str = $this->getTimeString($a_ass->getDeadline());
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_time_to_send"));
			$tpl->setVariable("PROP_VAL", $time_str);
			$tpl->parseCurrentBlock();
	
			if ($a_ass->getDeadline() > 0)
			{
				$tpl->setCurrentBlock("prop");
				$tpl->setVariable("PROP", $lng->txt("exc_edit_until"));
				$tpl->setVariable("PROP_VAL",
					ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX)));
				$tpl->parseCurrentBlock();
			}
			
		}

		$mand = "";
		if ($a_ass->getMandatory())
		{
			$mand = " (".$lng->txt("exc_mandatory").")";
		}
		$tpl->setVariable("TITLE", $a_ass->getTitle().$mand);

		// status icon
		$stat = ilExAssignment::lookupStatusOfUser($a_ass->getId(), $ilUser->getId());
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

	protected function getSubmissionLink($a_cmd, array $a_params = null)
	{
		global $ilCtrl;
		
		if(is_array($a_params))
		{
			foreach($a_params as $name => $value)
			{
				$ilCtrl->setParameterByClass("ilexsubmissiongui", $name, $value);
			}
		}
		
		$ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", $this->current_ass_id);
		$url = $ilCtrl->getLinkTargetByClass("ilexsubmissiongui", $a_cmd);
		$ilCtrl->setParameterByClass("ilexsubmissiongui", "");
		
		if(is_array($a_params))
		{
			foreach($a_params as $name => $value)
			{
				$ilCtrl->setParameterByClass("ilexsubmissiongui", $name, "");
			}
		}
		
		return $url;
	}
	
	protected function getPeerReviewLink($a_cmd)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", $this->current_ass_id);
		$url = $ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", $a_cmd);
		$ilCtrl->setParameterByClass("ilexsubmissiongui", "");
		
		return $url;
	}
	
	/**
	 * Get assignment body for overview
	 */
	function getOverviewBody(ilExAssignment $a_ass)
	{
		global $lng, $ilCtrl, $ilUser;
		
		$this->current_ass_id = $a_ass->getId();
		
		$tpl = new ilTemplate("tpl.assignment_body.html", true, true, "Modules/Exercise");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");

		if(IS_PAYMENT_ENABLED)
		{
			include_once './Services/Payment/classes/class.ilPaymentObject.php';
		}
		
		$info = new ilInfoScreenGUI(null);
		$info->setTableClass("");
		
		if (!$a_ass->notStartedYet())
		{
			// instructions
			$inst = $a_ass->getInstruction();	
			if(trim($inst))
			{				
				$info->addSection($lng->txt("exc_instruction"));

				$is_html = (strlen($inst) != strlen(strip_tags($inst)));
				if(!$is_html)
				{
					$inst = nl2br(ilUtil::makeClickable($inst, true));
				}						
				$info->addProperty("", $inst);
			}
		}
		
		// schedule
		$info->addSection($lng->txt("exc_schedule"));
		if ($a_ass->getStartTime() > 0)
		{
			$info->addProperty($lng->txt("exc_start_time"),
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getStartTime(),IL_CAL_UNIX)));
		}
		if ($a_ass->getDeadline() > 0)
		{
			$info->addProperty($lng->txt("exc_edit_until"),
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX)));
		}
		$time_str = $this->getTimeString($a_ass->getDeadline());
		if (!$a_ass->notStartedYet())
		{
			$info->addProperty($lng->txt("exc_time_to_send"),
				"<b>".$time_str."</b>");
		}
		
		// public submissions
		if ($this->exc->getShowSubmissions())
		{
			if ($a_ass->afterDeadline())
			{				
				$button = ilLinkButton::getInstance();				
				$button->setCaption("exc_list_submission");
				$button->setUrl($this->getSubmissionLink("listPublicSubmissions"));							
				
				$info->addProperty($lng->txt("exc_public_submission"), $button->render());
			}
			else
			{
				$info->addProperty($lng->txt("exc_public_submission"),
					$lng->txt("exc_msg_public_submission"));
			}
		}

		if (!$a_ass->notStartedYet())
		{
			// download files
			$files = ilExAssignment::getFiles($a_ass->getExerciseId(), $a_ass->getId());
			if (count($files) > 0)
			{
				$info->addSection($lng->txt("exc_files"));
				foreach($files as $file)
				{
					// if download must be purchased first show a "buy"-button
					if(IS_PAYMENT_ENABLED && (ilPaymentObject::_isBuyable($this->exc->getRefId(),'download') &&
					   !ilPaymentObject::_hasAccess($this->exc->getRefId(),'','download')))
					{
						$info->addProperty($file["name"],
							$lng->txt("buy"),
							$ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails"));
					}
					else
					{						
						$info->addProperty($file["name"],
							$lng->txt("download"),
							$this->getSubmissionLink("downloadFile", array("file"=>$file["name"])));
					}
				}
			}
	
			// submission
			
			// if submission must be purchased first
			if(IS_PAYMENT_ENABLED
				&& (ilPaymentObject::_isBuyable($this->exc->getRefId(),'upload')
				&& !ilPaymentObject::_hasAccess($this->exc->getRefId(),'','upload')))
			{
				$info->addSection($lng->txt("exc_your_submission"));

				$ilCtrl->clearParameters($this);

				$ilCtrl->setParameter($this, "ref_id", $this->exc->getRefId());
				$ilCtrl->setParameter($this,'subtype','upload');
				$info->addProperty($lng->txt('exc_hand_in'),
					$lng->txt("buy"),
					$ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails"));
			}
			else 
			{
				$info->addSection($lng->txt("exc_your_submission"));
				
				$delivered_files = ilExAssignment::getDeliveredFiles($a_ass->getExerciseId(), $a_ass->getId(), $ilUser->getId());

				$times_up = $a_ass->afterDeadline();
			
				$team_members = null;
				switch($a_ass->getType())
				{
					case ilExAssignment::TYPE_UPLOAD_TEAM:	
						$no_team_yet = false;						
						$team_members = ilExAssignment::getTeamMembersByAssignmentId($a_ass->getId(), $ilUser->getId());
						if(sizeof($team_members))
						{
							$team = array();						
							foreach($team_members as $member_id)
							{
								$team[] = ilObjUser::_lookupFullname($member_id);
							}						
							$team = implode(", ", $team);
							
							$button = ilLinkButton::getInstance();							
							$button->setCaption("exc_manage_team");
							$button->setUrl($this->getSubmissionLink("submissionScreenTeam"));							
							$team .= " ".$button->render();	
							
							$info->addProperty($lng->txt("exc_team_members"), $team);	
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
								$button->setUrl($this->getSubmissionLink("createTeam"));							
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
							if (!$times_up)
							{
								$title = (count($titles) == 0
									? $lng->txt("exc_hand_in")
									: $lng->txt("exc_edit_submission"));												

								$button = ilLinkButton::getInstance();
								$button->setPrimary(true);
								$button->setCaption($title, false);
								$button->setUrl($this->getSubmissionLink("submissionScreen"));							
								$files_str.= " ".$button->render();								
							}
							else
							{
								if (count($titles) > 0)
								{								
									$button = ilLinkButton::getInstance();								
									$button->setCaption("already_delivered_files");
									$button->setUrl($this->getSubmissionLink("submissionScreen"));											
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
								$button->setUrl($this->getSubmissionLink("createBlog"));							
								$files_str.= $button->render();								
							}							
							// #10462
							$blogs = sizeof($wsp_tree->getObjectsFromType("blog"));						
							if((!$valid_blog && $blogs) 
								|| ($valid_blog && $blogs > 1))
							{							
								$button = ilLinkButton::getInstance();							
								$button->setCaption("exc_select_blog".($valid_blog ? "_change" : ""));
								$button->setUrl($this->getSubmissionLink("selectBlog"));									
								$files_str.= " ".$button->render();
							}
						}
						if($files_str)
						{
							$info->addProperty($lng->txt("exc_blog_returned"), $files_str);		
						}
						if($delivered_files && substr($delivered_files["filename"], -1) != "/")
						{														
							$dl_link = $this->getSubmissionLink("download", array("delivered"=>$delivered_files["returned_id"]));
							
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
								$button->setUrl($this->getSubmissionLink("createPortfolio".$has_prtt));										
								$files_str .= $button->render();
							}
							// #10462
							$prtfs = sizeof(ilObjPortfolio::getPortfoliosOfUser($ilUser->getId()));		
							if((!$valid_prtf && $prtfs) 
								|| ($valid_prtf && $prtfs > 1))
							{		
								$button = ilLinkButton::getInstance();							
								$button->setCaption("exc_select_portfolio".($valid_prtf ? "_change" : ""));
								$button->setUrl($this->getSubmissionLink("selectPortfolio"));	
								$files_str.= " ".$button->render();
							}
						}
						if($files_str)
						{
							$info->addProperty($lng->txt("exc_portfolio_returned"), $files_str);	
						}
						if($delivered_files && substr($delivered_files["filename"], -1) != "/")
						{														
							$dl_link = $this->getSubmissionLink("download", array("delivered"=>$delivered_files["returned_id"]));
							
							$button = ilLinkButton::getInstance();							
							$button->setCaption("download");
							$button->setUrl($dl_link);		
							
							$info->addProperty($lng->txt("exc_files_returned"),
								$button->render());									
						}			
						break;			
						
					case ilExAssignment::TYPE_TEXT:												
						if(!$times_up)
						{
							$button = ilLinkButton::getInstance();
							$button->setPrimary(true);
							$button->setCaption("exc_text_assignment_edit");
							$button->setUrl($this->getSubmissionLink("editAssignmentText"));							
							$files_str = $button->render();							
						}
						else
						{
							$button = ilLinkButton::getInstance();
							$button->setCaption("exc_text_assignment_show");
							$button->setUrl($this->getSubmissionLink("showAssignmentText"));							
							$files_str = $button->render();														
						}
						
						$info->addProperty($lng->txt("exc_files_returned_text"),
							$files_str);											
						break;
				}
				
				
				$last_sub = ilExAssignment::getLastSubmission($a_ass->getId(), $ilUser->getId());
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
				if($times_up && $a_ass->getPeerReview())
				{								
					$nr_missing_fb = ilExAssignment::getNumberOfMissingFeedbacks($a_ass->getId(), $a_ass->getPeerReviewMin());
									
					if(!$a_ass->getPeerReviewDeadline() || $a_ass->getPeerReviewDeadline() > time())
					{			
						$dl_info = "";
						if($a_ass->getPeerReviewDeadline())
						{
							$dl_info = " (".sprintf($lng->txt("exc_peer_review_deadline_info_button"), 
								ilDatePresentation::formatDate(new ilDateTime($a_ass->getPeerReviewDeadline(), IL_CAL_UNIX))).")";							
						}
						
						$button = ilLinkButton::getInstance();
						$button->setPrimary($nr_missing_fb);
						$button->setCaption($lng->txt("exc_peer_review_give").$dl_info, false);
						$button->setUrl($this->getPeerReviewLink("editPeerReview"));							
						$edit_pc = $button->render();													
					}
					else if($a_ass->getPeerReviewDeadline())
					{
						$edit_pc = $lng->txt("exc_peer_review_deadline_reached");
					}
					if((!$a_ass->getPeerReviewDeadline() || $a_ass->getPeerReviewDeadline() < time()) && 
						!$nr_missing_fb)
					{						
						$button = ilLinkButton::getInstance();					
						$button->setCaption("exc_peer_review_show");
						$button->setUrl($this->getPeerReviewLink("showPersonalPeerReview"));							
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
				if($a_ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$feedback_id = "t".ilExAssignment::getTeamIdByAssignment($a_ass->getId(), $ilUser->getId());
				}
				else
				{
					$feedback_id = $ilUser->getId();
				}
				
				// global feedback / sample solution
				if($a_ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE)
				{
					$show_global_feedback = ($times_up && $a_ass->getFeedbackFile());
				}
				else
				{
					$show_global_feedback = ($last_sub != "---" && $a_ass->getFeedbackFile());
				}								
				
				$storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());					
				$cnt_files = $storage->countFeedbackFiles($feedback_id);
				$lpcomment = ilExAssignment::lookupCommentForUser($a_ass->getId(), $ilUser->getId());
				$mark = ilExAssignment::lookupMarkOfUser($a_ass->getId(), $ilUser->getId());
				$status = ilExAssignment::lookupStatusOfUser($a_ass->getId(), $ilUser->getId());				
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
							'<a name="fb'.$a_ass->getId().'"></a>');
						
						if($cnt_files > 0)
						{
							$files = $storage->getFeedbackFiles($feedback_id);
							foreach($files as $file)
							{								
								$info->addProperty($file,
									$lng->txt("download"),
									$this->getSubmissionLink("downloadFeedbackFile", array("file"=>$file)));								
							}
						}												
					}	
					
					// #15002 - global feedback																	
					if($show_global_feedback)
					{
						$info->addSection($lng->txt("exc_global_feedback_file"));
						
						$info->addProperty($a_ass->getFeedbackFile(),
							$lng->txt("download"),
							$this->getSubmissionLink("downloadGlobalFeedbackFile"));								
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
