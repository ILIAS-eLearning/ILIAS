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
//		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("accordion_arrow.png"));
		
		// status icon
		$stat = ilExAssignment::lookupStatusOfUser($a_data["id"], $ilUser->getId());
		switch ($stat)
		{
			case "passed": 	$pic = "scorm/passed.png"; break;
			case "failed":	$pic = "scorm/failed.png"; break;
			default: 		$pic = "scorm/not_attempted.png"; break;
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
			$info->addProperty("",
				nl2br(ilUtil::makeClickable($a_data["instruction"], true)));
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
				$link = '<a class="submit" href="'.
					$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "listPublicSubmissions").'">'.
						$lng->txt("exc_list_submission").'</a>';
				$info->addProperty($lng->txt("exc_public_submission"), $link);
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
						$team_members = ilExAssignment::getTeamMembersByAssignmentId($a_data["id"], $ilUser->getId());
						if(sizeof($team_members) > 1)
						{
							$team = array();						
							foreach($team_members as $member_id)
							{
								$team[] = ilObjUser::_lookupFullname($member_id);
							}
							$info->addProperty($lng->txt("exc_team_members"), implode(", ", $team));	
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
	
						$ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_data["id"]);
	
						if (!$times_up)
						{
							$files_str.= ' <a class="submit" href="'.
								$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "submissionScreen").'">'.
								(count($titles) == 0
									? $lng->txt("exc_hand_in")
									: $lng->txt("exc_edit_submission")).'</a>';
						}
						else
						{
							if (count($titles) > 0)
							{
								$files_str.= ' <a class="submit" href="'.
									$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "submissionScreen").'">'.
									$lng->txt("already_delivered_files").'</a>';
							}
						}
	
						$info->addProperty($lng->txt("exc_files_returned"),
							$files_str);						
						break;
						
					case ilExAssignment::TYPE_BLOG:
						$files_str = "";
						$valid_blog = false;
						if(sizeof($delivered_files))
						{													
							$delivered_files = array_pop($delivered_files);
							$blog_id = (int)$delivered_files["filetitle"];																
														
							include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
							include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";						
							$wsp_tree = new ilWorkspaceTree($ilUser->getId());
							$node = $wsp_tree->getNodeData($blog_id);
							
							if($node["title"])
							{
								// #10116
								// $blog_link = ilWorkspaceAccessHandler::getGotoLink($blog_id, $node["obj_id"]);							
								$ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", $blog_id);
								$blog_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjbloggui"), "");
								$ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", "");
								$files_str = '<a href="'.$blog_link.'">'.
									$node["title"].'</a>';
								$valid_blog = true;
							}						
						}						
						if(!$times_up)
						{
							if(!$valid_blog)
							{							
								$files_str .= '<a class="submit" href="'.
									$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "createBlog").'">'.
									$lng->txt("exc_create_blog").'</a>';
							}					
							$files_str .=' <a class="submit" href="'.
									$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "selectBlog").'">'.
									$lng->txt("exc_select_blog".($valid_blog ? "_change" : "")).'</a>';
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
							
							$info->addProperty($lng->txt("exc_files_returned"),
								"<a href=\"".$dl_link."\">".$lng->txt("download")."</a>");		
						}							
						break;
						
					case ilExAssignment::TYPE_PORTFOLIO:
						$files_str = "";
						$valid_prtf = false;
						if(sizeof($delivered_files))
						{
							$delivered_files = array_pop($delivered_files);
							$portfolio_id = (int)$delivered_files["filetitle"];
							
							include_once "Services/Portfolio/classes/class.ilObjPortfolio.php";
							include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";	
							$portfolio = new ilObjPortfolio($portfolio_id, false);											
							
							if($portfolio->getTitle())
							{								
								// #10116
								// $prtf_link = ilWorkspaceAccessHandler::getGotoLink($portfolio_id, $portfolio_id)						
								$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $portfolio_id);
								$prtf_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilobjportfoliogui"), "pages");
								$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");
								
								$files_str = '<a href="'.$prtf_link.
									'">'.$portfolio->getTitle().'</a>';
								$valid_prtf = true;
							}
						}
						if(!$times_up)
						{
							if(!$valid_prtf)
							{
								$files_str .= '<a class="submit" href="'.
									$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "createPortfolio").'">'.
									$lng->txt("exc_create_portfolio").'</a>';
							}
							$files_str .= ' <a class="submit" href="'.
									$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "selectPortfolio").'">'.
									$lng->txt("exc_select_portfolio".($valid_prtf ? "_change" : "")).'</a>';
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
							
							$info->addProperty($lng->txt("exc_files_returned"),
								"<a href=\"".$dl_link."\">".$lng->txt("download")."</a>");		
						}			
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
				
				// feedback from tutor
				if($a_data["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$feedback_id = "t".ilExAssignment::getTeamIdByAssignment($a_data["id"], $ilUser->getId());
				}
				else
				{
					$feedback_id = $ilUser->getId();
				}
				$storage = new ilFSStorageExercise($a_data["exc_id"], $a_data["id"]);					
				$cnt_files = $storage->countFeedbackFiles($feedback_id);
				$lpcomment = ilExAssignment::lookupCommentForUser($a_data["id"], $ilUser->getId());
				$mark = ilExAssignment::lookupMarkOfUser($a_data["id"], $ilUser->getId());
				$status = ilExAssignment::lookupStatusOfUser($a_data["id"], $ilUser->getId());
				if ($lpcomment != "" || $mark != "" || $status != "notgraded" || $cnt_files > 0)
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
						$img = '<img border="0" src="'.ilUtil::getImagePath("scorm/".$status.".png").'" '.
							' alt="'.$lng->txt("exc_".$status).'" title="'.$lng->txt("exc_".$status).
							'" style="vertical-align:middle;"/>';
						$info->addProperty($lng->txt("status"),
							$img." ".$lng->txt("exc_".$status));
					}
					
					if ($cnt_files > 0)
					{
						$info->addSection($lng->txt("exc_fb_files"));
						$files = $storage->getFeedbackFiles($feedback_id);
						foreach($files as $file)
						{
							$ilCtrl->setParameterByClass("ilobjexercisegui", "file", urlencode($file));
							$info->addProperty($file,
								$lng->txt("download"),
								$ilCtrl->getLinkTargetByClass("ilobjexercisegui", "downloadFeedbackFile"));
							$ilCtrl->setParameter($this, "file", "");
						}
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
			if (isset($time_diff['days']))
			{
				unset($time_diff['minutes']);
			}
			if (isset($time_diff['months']))
			{
				unset($time_diff['hours']);
			}
			if (isset($time_diff['minutes']))
			{
				unset($time_diff['seconds']);
			}
			$time_str = ilUtil::timearray2string($time_diff);
		}

		return $time_str;
	}
	
	
}
