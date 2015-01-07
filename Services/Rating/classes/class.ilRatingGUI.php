<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Rating/classes/class.ilRating.php");
include_once("./Services/Rating/classes/class.ilRatingCategory.php");

/**
 * Class ilRatingGUI. User interface class for rating.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilRatingGUI: ilRatingCategoryGUI
 *
 * @ingroup ServicesRating
 */
class ilRatingGUI
{
	protected $id = "rtg_";
	protected $export_callback;
	protected $export_subobj_title;
	protected $ctrl_path;

	function __construct()
	{
		global $lng;
		
		$lng->loadLanguageModule("rating");
	}
	
	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		switch($next_class)
		{
			case "ilratingcategorygui":
				include_once("./Services/Rating/classes/class.ilRatingCategoryGUI.php");
				$gui = new ilRatingCategoryGUI($this->obj_id, $this->export_callback, $this->export_subobj_title);
				$ilCtrl->forwardCommand($gui);				
				break;
			
			default:
				return $this->$cmd();
				break;
		}
	}

	/**
	* Set Object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	*/
	function setObject($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
	{
		global $ilUser;
		
		// db-column is defined as not null, el stupido
		if(!trim($a_sub_obj_type))
		{
			$a_sub_obj_type = "-";
		}

		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->sub_obj_id = $a_sub_obj_id;
		$this->sub_obj_type = $a_sub_obj_type;
		$this->id = "rtg_".$this->obj_id."_".$this->obj_type."_".$this->sub_obj_id."_".
			$this->sub_obj_type;
		
		$this->setUserId($ilUser->getId());
	}
	
	/**
	* Set User ID.
	*
	* @param	int	$a_userid	User ID
	*/
	function setUserId($a_userid)
	{
		$this->userid = $a_userid;
	}

	/**
	* Get User ID.
	*
	* @return	int	User ID
	*/
	function getUserId()
	{
		return $this->userid;
	}
	
	/**
	 * Set "Your Rating" text
	 *
	 * @param string $a_val text	
	 */
	function setYourRatingText($a_val)
	{
		$this->your_rating_text = $a_val;
	}
	
	/**
	 * Get "Your Rating" text
	 *
	 * @return string text
	 */
	function getYourRatingText()
	{
		return $this->your_rating_text;
	}
	
	/**
	 * Toggle categories status
	 * 
	 * @param bool  $a_value 
	 */
	function enableCategories($a_value)
	{
		$this->enable_categories = (bool)$a_value;
	}
	
	/**
	 * ilCtrl path
	 * 
	 * @param array $a_value
	 */
	public function setCtrlPath(array $a_value)
	{
		$this->ctrl_path = $a_value;
	}
	
	/**
	 * Render rating details
	 * 
	 * @param string $a_js_id
	 * @param bool $a_may_rate
	 * @param array $a_categories 
	 * @param bool $a_onclick 
	 * @param bool $a_average 
	 * @return string
	 */
	protected function renderDetails($a_js_id, $a_may_rate, array $a_categories = null, $a_onclick = null, $a_average = false)
	{
		global $lng, $ilCtrl;
		
		$ttpl = new ilTemplate("tpl.rating_details.html", true, true, "Services/Rating");		
		
		$rate_text = null;
		if($this->getYourRatingText() != "#")
		{
			$rate_text = ($this->getYourRatingText() != "")
				? $this->getYourRatingText()
				: $lng->txt("rating_your_rating");
		}
						
		// no categories: 1 simple rating (link)
		if(!$a_categories)
		{										
			if ($a_may_rate)
			{									
				$rating = ilRating::getRatingForUserAndObject($this->obj_id, $this->obj_type,
					$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(), 0);
				
				if((bool)$a_average)
				{					
					$overall_rating = ilRating::getOverallRatingForObject($this->obj_id, $this->obj_type,
						$this->sub_obj_id, $this->sub_obj_type);						
				}

				// user rating links
				for($i = 1; $i <= 5; $i++)
				{					
					if ((bool)$a_average &&
						$i == $rating)
					{							
						$ttpl->setCurrentBlock("rating_mark_simple");
						$ttpl->setVariable("SRC_MARK_SIMPLE",
							ilUtil::getImagePath("icon_rate_marker.svg"));
						$ttpl->parseCurrentBlock();
					}
					
					$ttpl->setCurrentBlock("rating_link_simple");						
					if(stristr($a_onclick, "%rating%"))
					{
						$url_save = "#";
					}
					else 
					{
						$ilCtrl->setParameter($this, "rating", $i);					
						if(!$this->ctrl_path)
						{
							$url_save = $ilCtrl->getLinkTarget($this, "saveRating");
						}
						else
						{
							$url_save = $ilCtrl->getLinkTargetByClass($this->ctrl_path, "saveRating");
						}														
					}		
					$ttpl->setVariable("HREF_RATING", $url_save);
					
					if($a_onclick)
					{
						$onclick = str_replace("%rating%", $i, $a_onclick);						
						$ttpl->setVariable("ONCLICK_RATING", ' onclick="'.$onclick.'"');
					}
					
					if((bool)$a_average)
					{
						$ref_rating = $overall_rating["avg"];						
					}
					else
					{
						$ref_rating = $rating;
					}
					
					if ($ref_rating >= $i)
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_on.svg"));
					}
					else
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_off.svg"));
					}
					$ttpl->setVariable("ALT_ICON", "(".$i."/5)");
					$ttpl->parseCurrentBlock();
				}
				
				// remove
				if($rating)
				{
					$ttpl->setCurrentBlock("rating_simple_del_bl");	
					$ttpl->setVariable("CAPTION_RATING_DEL", $lng->txt("rating_remove"));
					
					if(stristr($a_onclick, "%rating%"))
					{
						$url_save = "#";
					}
					else 
					{
						$ilCtrl->setParameter($this, "rating", 0);					
						if(!$this->ctrl_path)
						{
							$url_save = $ilCtrl->getLinkTarget($this, "saveRating");
						}
						else
						{
							$url_save = $ilCtrl->getLinkTargetByClass($this->ctrl_path, "saveRating");
						}														
					}											
					$ttpl->setVariable("HREF_RATING_DEL", $url_save);					
				
					if($a_onclick)
					{
						$onclick = str_replace("%rating%", 0, $a_onclick);						
						$ttpl->setVariable("ONCLICK_RATING_DEL", ' onclick="'.$onclick.'"');
					}
					
					$ttpl->parseCurrentBlock();
				}
				
				if($rate_text)
				{
					$ttpl->setCurrentBlock("rating_simple_title");
					$ttpl->setVariable("TXT_RATING_SIMPLE", $rate_text);
					$ttpl->parseCurrentBlock();
				}
				
				// user rating text
				$ttpl->setCurrentBlock("user_rating_simple");
								
				if((bool)$a_average &&
					$overall_rating["cnt"])
				{
					$ttpl->setVariable("NUMBER_VOTES_SIMPLE", $overall_rating["cnt"]);
				}

				$ttpl->parseCurrentBlock();
			}
		}
		// categories: overall & user (form)
		else
		{							
			$has_user_rating = false;
			foreach($a_categories as $category)
			{	
				$user_rating = round(ilRating::getRatingForUserAndObject($this->obj_id, $this->obj_type,
					$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(), $category["id"]));

				$overall_rating = ilRating::getOverallRatingForObject($this->obj_id, $this->obj_type,
					$this->sub_obj_id, $this->sub_obj_type, $category["id"]);
				
				for($i = 1; $i <= 5; $i++)
				{					
					if ($a_may_rate && $i == $user_rating)
					{
						$has_user_rating = true;
						
						$ttpl->setCurrentBlock("rating_mark");
						$ttpl->setVariable("SRC_MARK",
							ilUtil::getImagePath("icon_rate_marker.svg"));
						$ttpl->parseCurrentBlock();
					}
					
					$ttpl->setCurrentBlock("user_rating_icon");
					if ($overall_rating["avg"] >= $i)
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_on.svg"));
					}
					else if ($overall_rating["avg"] + 1 <= $i)
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_off.svg"));
					}
					else
					{
						$nr = round(($overall_rating["avg"] + 1 - $i) * 10);
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_$nr.svg"));
					}
					$ttpl->setVariable("ALT_ICON", "(".$i."/5)");
					
					if ($a_may_rate)
					{
						$ttpl->setVariable("HREF_RATING", "il.Rating.setValue(".$category["id"].",".$i.", '".$a_js_id."')");
						$ttpl->setVariable("CATEGORY_ID", $category["id"]);
						$ttpl->setVariable("ICON_VALUE", $i);
						$ttpl->setVariable("JS_ID", $a_js_id);
						$ttpl->setVariable("ICON_MOUSEACTION", " onmouseover=\"il.Rating.toggleIcon(this,".$i.")\"".
							" onmouseout=\"il.Rating.toggleIcon(this,".$i.",1)\"");
					}
					
					$ttpl->parseCurrentBlock();					
				}
				
				if($a_may_rate)
				{
					$ttpl->setCurrentBlock("user_rating_category_column");	
					$ttpl->setVariable("JS_ID", $a_js_id);
					$ttpl->setVariable("CATEGORY_ID", $category["id"]);
					$ttpl->setVariable("CATEGORY_VALUE", $user_rating);
					$ttpl->parseCurrentBlock();	
				}
				
				// category title
				$ttpl->setCurrentBlock("user_rating_category");
				$ttpl->setVariable("TXT_RATING_CATEGORY", $category["title"]);				
				$ttpl->parseCurrentBlock();		
			}
			
			if($overall_rating["cnt"])
			{
				$ttpl->setCurrentBlock("votes_number_bl");	
				$ttpl->setVariable("NUMBER_VOTES", sprintf($lng->txt("rating_number_votes"), $overall_rating["cnt"]));
				$ttpl->parseCurrentBlock();	
			}
				
			if($a_may_rate)
			{												
				// remove
				if($has_user_rating)
				{
					$ttpl->setCurrentBlock("user_rating_categories_del_bl");	
					$ttpl->setVariable("CAPTION_RATING_DEL_CAT", $lng->txt("rating_remove"));
					
					$ilCtrl->setParameter($this, "rating", 0);					
					if(!$this->ctrl_path)
					{
						$url_save = $ilCtrl->getLinkTarget($this, "resetUserRating");
					}
					else
					{
						$url_save = $ilCtrl->getLinkTargetByClass($this->ctrl_path, "resetUserRating");
					}																										
					$ttpl->setVariable("HREF_RATING_DEL_CAT", $url_save);					
				
					$ttpl->parseCurrentBlock();
				}
				
				if(!$this->ctrl_path)
				{
					$url_form = $ilCtrl->getFormAction($this, "saveRating"); 
				}
				else
				{
					$url_form = $ilCtrl->getFormActionByClass($this->ctrl_path, "saveRating"); 
				}								
				$ttpl->setVariable("FORM_ACTION", $url_form);				
				$ttpl->setVariable("TXT_SUBMIT", $lng->txt("rating_overlay_submit"));
				$ttpl->setVariable("CMD_SUBMIT", "saveRating");
				$ttpl->touchBlock("user_rating_categories_form_out");				
				
				// overall / user title
				/*
				$ttpl->setCurrentBlock("user_rating_categories");
				$ttpl->setVariable("TXT_RATING_OVERALL", $lng->txt("rating_overlay_title_overall"));				
				$ttpl->parseCurrentBlock();								 
				*/
			}		
		}
		
		return $ttpl->get();
	}

	/**
	 * Get HTML for rating of an object (and a user)
	 * 
	 * @param bool $a_show_overall
	 * @param bool $a_may_rate
	 * @param string $a_onclick
	 * @param string $a_additional_id
	 * @return string
	 */
	function getHTML($a_show_overall = true, $a_may_rate = true, $a_onclick = null, $a_additional_id = null)
	{
		global $lng;	
		
		$unique_id = $this->id;
		if($a_additional_id)
		{
			$unique_id .= "_".$a_additional_id;
		}
		
		$categories = array();
		if($this->enable_categories)
		{
			$categories = ilRatingCategory::getAllForObject($this->obj_id);		
		}
		
		$may_rate = ($this->getUserId() != ANONYMOUS_USER_ID);	
		if($may_rate && !$a_may_rate)
		{
			$may_rate = false;
		}
		
		$has_overlay = false;				
		if($may_rate || $categories)
		{
			$has_overlay = true;
		}

		$ttpl = new ilTemplate("tpl.rating_input.html", true, true, "Services/Rating");		

		// user rating
		$user_rating = 0;
		if ($may_rate || !$a_show_overall)
		{
			$user_rating = round(ilRating::getRatingForUserAndObject($this->obj_id, $this->obj_type,
				$this->sub_obj_id, $this->sub_obj_type, $this->getUserId()));
		}
		
		// (1) overall rating
		if($a_show_overall)
		{
			$rating = ilRating::getOverallRatingForObject($this->obj_id, $this->obj_type,
				$this->sub_obj_id, $this->sub_obj_type);
		}
		else
		{
			$rating = array("avg"=>$user_rating);
		}
		
		for($i = 1; $i <= 5; $i++)
		{
			if ($a_show_overall &&
				$i == $user_rating)
			{
				$ttpl->setCurrentBlock("rating_mark");
				$ttpl->setVariable("SRC_MARK",
					ilUtil::getImagePath("icon_rate_marker.svg"));
				$ttpl->parseCurrentBlock();
			}

			$ttpl->setCurrentBlock("rating_icon");
			if ($rating["avg"] >= $i)
			{
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_on.svg"));
			}
			else if ($rating["avg"] + 1 <= $i)
			{
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_off.svg"));
			}
			else
			{
				$nr = round(($rating["avg"] + 1 - $i) * 10);
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_$nr.svg"));
			}
			$ttpl->setVariable("ALT_ICON", "(".$i."/5)");
			$ttpl->parseCurrentBlock();
		}		
		$ttpl->setCurrentBlock("rating_icon");
		
		if($a_show_overall)
		{			
			if ($rating["cnt"] == 0)
			{
				$tt = $lng->txt("rat_not_rated_yet");
			}
			else if ($rating["cnt"] == 1)
			{
				$tt = $lng->txt("rat_one_rating");
			}
			else
			{
				$tt = sprintf($lng->txt("rat_nr_ratings"), $rating["cnt"]);
			}		
			include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
			ilTooltipGUI::addTooltip($unique_id."_tt", $tt);

			if ($rating["cnt"] > 0)
			{
				$ttpl->setCurrentBlock("rat_nr");
				$ttpl->setVariable("RT_NR", $rating["cnt"]);
				$ttpl->parseCurrentBlock();
			}
		}

		// add overlay (trigger)
		if ($has_overlay)
		{
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
			$ov = new ilOverlayGUI($unique_id);
			$ov->setTrigger("tr_".$unique_id, "click", "tr_".$unique_id);
			$ov->add();
			
			$ttpl->setCurrentBlock("act_rat_start");
			$ttpl->setVariable("ID", $unique_id);
			$ttpl->parseCurrentBlock();

			$ttpl->touchBlock("act_rat_end");		
		}

		$ttpl->parseCurrentBlock();
		
		
		// (2) user rating			
		
		if($has_overlay)
		{
			$ttpl->setVariable("RATING_DETAILS", 
				$this->renderDetails("rtov_", $may_rate, $categories, $a_onclick));
			
			$ttpl->setCurrentBlock("user_rating");
			$ttpl->setVariable("ID", $unique_id);
			$ttpl->parseCurrentBlock();		
		}

		$ttpl->setVariable("TTID", $unique_id);

		return $ttpl->get();
	}
	
	function getBlockHTML($a_title)
	{
		$categories = array();
		if($this->enable_categories)
		{
			$categories = ilRatingCategory::getAllForObject($this->obj_id);		
		}
		
		$may_rate = ($this->getUserId() != ANONYMOUS_USER_ID);		
		
		$ttpl = new ilTemplate("tpl.rating_block.html", true, true, "Services/Rating");		
		
		$ttpl->setVariable("TITLE", $a_title);
		
		$ttpl->setVariable("RATING_DETAILS", 
				$this->renderDetails("rtsb_", $may_rate, $categories, null, true));
		
		return $ttpl->get();
	}
	
	/**
	* Save Rating
	*/
	function saveRating()
	{		
		global $ilCtrl;
		
		if(!is_array($_REQUEST["rating"]))
		{				
			$rating = (int)ilUtil::stripSlashes($_GET["rating"]);
			if(!$rating)
			{
				$this->resetUserRating();
			}
			else
			{
				ilRating::writeRatingForUserAndObject($this->obj_id, $this->obj_type,
					$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(),
					$rating);
			}
		}
		else
		{
			foreach($_POST["rating"] as $cat_id => $rating)
			{
				ilRating::writeRatingForUserAndObject($this->obj_id, $this->obj_type,
					$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(),
					$rating, $cat_id);					
			}
		}
				
		if($this->update_callback)
		{
			call_user_func($this->update_callback, $this->obj_id, $this->obj_type,
				$this->sub_obj_id, $this->sub_obj_type);			
		}		
		
		if($ilCtrl->isAsynch())
		{
			exit();
		}
	}
	
	public function setUpdateCallback($a_callback)
	{
		$this->update_callback = $a_callback;
	}		
	
	/**
	* Reset Rating
	*/
	function resetUserRating()
	{	
		ilRating::resetRatingForUserAndObject($this->obj_id, $this->obj_type,
				$this->sub_obj_id, $this->sub_obj_type, $this->getUserId());			
	}
	
	function setExportCallback($a_callback, $a_subobj_title)
	{
		$this->export_callback = $a_callback;
		$this->export_subobj_title = $a_subobj_title;
	}
	
	/**
	 * Build list gui property for object
	 * 
	 * @param int $a_ref_id
	 * @param bool $a_may_rate
	 * @param string $a_ajax_hash
	 * @param int $_parent_ref_id
	 * @return string
	 */
	public function getListGUIProperty($a_ref_id, $a_may_rate, $a_ajax_hash, $_parent_ref_id)
	{				
		return $this->getHTML(true, $a_may_rate, 
			"il.Object.saveRatingFromListGUI(".$a_ref_id.", '".$a_ajax_hash."', %rating%);",
			$_parent_ref_id);		
	}
}

?>
