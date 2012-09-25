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
	 * Render rating details
	 * 
	 * @param string $a_js_id
	 * @param bool $a_may_rate
	 * @param array $a_categories 
	 * @return string
	 */
	protected function renderDetails($a_js_id, $a_may_rate, array $a_categories = null)
	{
		global $lng, $ilCtrl;
		
		$ttpl = new ilTemplate("tpl.rating_details.html", true, true, "Services/Rating");		
		
		$rate_text = ($this->getYourRatingText() != "")
			? $this->getYourRatingText()
			: $lng->txt("rating_your_rating");
				
		// no categories: 1 simple rating (link)
		if(!$a_categories)
		{				
			if ($a_may_rate)
			{					
				$rating = ilRating::getRatingForUserAndObject($this->obj_id, $this->obj_type,
					$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(), 0);

				// user rating links
				for($i = 1; $i <= 5; $i++)
				{
					$ttpl->setCurrentBlock("rating_link_simple");					
					$ilCtrl->setParameter($this, "rating", $i);
					$ttpl->setVariable("HREF_RATING", $ilCtrl->getLinkTarget($this, "saveRating"));
					if ($rating >= $i)
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_on.png"));
					}
					else
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_off.png"));
					}
					$ttpl->setVariable("ALT_ICON", "(".$i."/5)");
					$ttpl->parseCurrentBlock();
				}

				// user rating text
				$ttpl->setCurrentBlock("user_rating_simple");
				$ttpl->setVariable("TXT_RATING_SIMPLE", $rate_text);
				$ttpl->parseCurrentBlock();
			}
		}
		// categories: overall & user (form)
		else
		{							
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
						$ttpl->setCurrentBlock("rating_mark");
						$ttpl->setVariable("SRC_MARK",
							ilUtil::getImagePath("icon_rate_marker.png"));
						$ttpl->parseCurrentBlock();
					}
					
					$ttpl->setCurrentBlock("user_rating_icon");
					if ($overall_rating["avg"] >= $i)
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_on.png"));
					}
					else if ($overall_rating["avg"] + 1 <= $i)
					{
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_off.png"));
					}
					else
					{
						$nr = round(($overall_rating["avg"] + 1 - $i) * 10);
						$ttpl->setVariable("SRC_ICON",
							ilUtil::getImagePath("icon_rate_$nr.png"));
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
				$ttpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this, "saveRating"));				
				$ttpl->setVariable("TXT_SUBMIT", $lng->txt("rating_overlay_submit"));
				$ttpl->setVariable("CMD_SUBMIT", "saveRating");
				$ttpl->touchBlock("user_rating_categories_form_out");				
				
				// overall / user title
				$ttpl->setCurrentBlock("user_rating_categories");
				$ttpl->setVariable("TXT_RATING_OVERALL", $lng->txt("rating_overlay_title_overall"));				
				$ttpl->parseCurrentBlock();
			}		
		}
		
		return $ttpl->get();
	}

	/**
	* Get HTML for rating of an object (and a user)
	*/
	function getHTML()
	{
		global $lng;	
		
		$categories = array();
		if($this->enable_categories)
		{
			$categories = ilRatingCategory::getAllForObject($this->obj_id);		
		}
		
		$may_rate = ($this->getUserId() != ANONYMOUS_USER_ID);		
		
		$has_overlay = false;				
		if($may_rate || $categories)
		{
			$has_overlay = true;
		}

		$ttpl = new ilTemplate("tpl.rating_input.html", true, true, "Services/Rating");		

		// user rating
		$user_rating = 0;
		if ($may_rate)
		{
			$user_rating = round(ilRating::getRatingForUserAndObject($this->obj_id, $this->obj_type,
				$this->sub_obj_id, $this->sub_obj_type, $this->getUserId()));
		}
		
		// (1) overall rating
		$rating = ilRating::getOverallRatingForObject($this->obj_id, $this->obj_type,
			$this->sub_obj_id, $this->sub_obj_type);

		for($i = 1; $i <= 5; $i++)
		{
			if ($i == $user_rating)
			{
				$ttpl->setCurrentBlock("rating_mark");
				$ttpl->setVariable("SRC_MARK",
					ilUtil::getImagePath("icon_rate_marker.png"));
				$ttpl->parseCurrentBlock();
			}

			$ttpl->setCurrentBlock("rating_icon");
			if ($rating["avg"] >= $i)
			{
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_on.png"));
			}
			else if ($rating["avg"] + 1 <= $i)
			{
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_off.png"));
			}
			else
			{
				$nr = round(($rating["avg"] + 1 - $i) * 10);
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_$nr.png"));
			}
			$ttpl->setVariable("ALT_ICON", "(".$i."/5)");
			$ttpl->parseCurrentBlock();
		}
		$ttpl->setCurrentBlock("rating_icon");
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
		ilTooltipGUI::addTooltip($this->id."_tt", $tt);
		if ($rating["cnt"] > 0)
		{
			$ttpl->setCurrentBlock("rat_nr");
			$ttpl->setVariable("RT_NR", $rating["cnt"]);
			$ttpl->parseCurrentBlock();
		}

		// add overlay (trigger)
		if ($has_overlay)
		{
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
			$ov = new ilOverlayGUI($this->id);
			$ov->setTrigger("tr_".$this->id, "click", "tr_".$this->id);
			$ov->add();
			
			$ttpl->setCurrentBlock("act_rat_start");
			$ttpl->setVariable("ID", $this->id);
			$ttpl->setVariable("SRC_ARROW", ilUtil::getImagePath("mm_down_arrow_dark.png"));
			$ttpl->parseCurrentBlock();

			$ttpl->setCurrentBlock("act_rat_end");
			$ttpl->setVariable("SRC_ARROW", ilUtil::getImagePath("mm_down_arrow_dark.png"));
			$ttpl->parseCurrentBlock();
		}

		$ttpl->parseCurrentBlock();
		
		
		// (2) user rating			
		
		if($has_overlay)
		{
			$ttpl->setVariable("RATING_DETAILS", 
				$this->renderDetails("rtov_", $may_rate, $categories));
			
			$ttpl->setCurrentBlock("user_rating");
			$ttpl->setVariable("ID", $this->id);
			$ttpl->parseCurrentBlock();		
		}

		$ttpl->setVariable("TTID", $this->id);

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
				$this->renderDetails("rtsb_", $may_rate, $categories));
		
		return $ttpl->get();
	}
	
	/**
	* Save Rating
	*/
	function saveRating()
	{		
		if(!is_array($_REQUEST["rating"]))
		{				
			ilRating::writeRatingForUserAndObject($this->obj_id, $this->obj_type,
				$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(),
				ilUtil::stripSlashes($_GET["rating"]));		
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
	}
	
	function setExportCallback($a_callback, $a_subobj_title)
	{
		$this->export_callback = $a_callback;
		$this->export_subobj_title = $a_subobj_title;
	}
}

?>
