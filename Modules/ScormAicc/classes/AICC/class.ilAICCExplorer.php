<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/*
* Explorer View for AICC Learning Modules
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/

require_once("classes/class.ilExplorer.php");
require_once("./Modules/ScormAicc/classes/AICC/class.ilAICCTree.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMExplorer.php");

class ilAICCExplorer extends ilSCORMExplorer
{

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilAICCExplorer($a_target, &$a_slm_obj)
	{
		parent::ilExplorer($a_target);
		$this->slm_obj =& $a_slm_obj;
		$this->tree = new ilAICCTree($a_slm_obj->getId());
		$this->root_id = $this->tree->readRootId();
		$this->checkPermissions(false);
		$this->outputIcons(false);
		$this->setOrderColumn("");
	}
	
	function getItem($a_node_id) {
		return new ilAICCUnit($a_node_id);
	}
	
	function getIconImagePathPrefix() {
		return "scorm/";
	}
	
	function getNodesToSkip() {
		return 1;
	}

	function isClickable($a_type, $a_id = 0)
	{
		if ($a_type=="sbl") {
			$block_object =& new ilAICCBlock($a_id);
			return (strlen($block_object->getDescription())>0);
		}
		
		if ($a_type != "sau")
		{
			return false;
		}
		else
		{
			$sc_object =& new ilAICCUnit($a_id);
			//if ($sc_object->getIdentifierRef() != "")
			//{
				return true;
			//}
		}
		return false;
	}

	function formatItemTable(&$tpl, $a_id, $a_type)
	{
		global $lng;
/*
		if ($a_type != "sau")
		{
			return;
		}
		else
		{
			$sc_object =& new ilAICCUnit($a_id);
			//if ($sc_object->getIdentifierRef() != "")
			//{
				$trdata = $sc_object->getTrackingDataOfUser();

				// status
				$status = ($trdata["lesson_status"] == "")
					? "not attempted"
					: $trdata["lesson_status"];
				$tpl->setCurrentBlock("item_row");
				$tpl->setVariable("TXT_KEY", $lng->txt("cont_status"));
				$tpl->setVariable("TXT_VALUE",
					$lng->txt("cont_sc_stat_".str_replace(" ", "_", $status)));
				$tpl->parseCurrentBlock();

				// credits
				if ($trdata["mastery_score"] != "")
				{
					$tpl->setCurrentBlock("item_row");
					$tpl->setVariable("TXT_KEY", $lng->txt("cont_credits"));
					$tpl->setVariable("TXT_VALUE", $trdata["mastery_score"]);
					$tpl->parseCurrentBlock();
				}

				// total time
				if ($trdata["total_time"] != "")
				{
					$tpl->setCurrentBlock("item_row");
					$tpl->setVariable("TXT_KEY", $lng->txt("cont_total_time"));
					$tpl->setVariable("TXT_VALUE", $trdata["total_time"]);
					$tpl->parseCurrentBlock();
				}

				$tpl->setCurrentBlock("item_table");
				$tpl->parseCurrentBlock();
			//}
		}
*/		
	}



/**
	* Creates output
	* recursive method
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject($a_node_id,$a_option)
	{
		global $lng;

		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
									"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$tpl = new ilTemplate("tpl.sahs_tree.html", true, true, "Modules/ScormAicc");

	 	if ($a_option["c_type"]=="sos")
			return;

		if ($a_option["c_type"]=="srs")
			return;

		if (is_array($a_option["tab"])) { //test if there are any tabs
			foreach ($a_option["tab"] as $picture)
			{
				$pic = false;
				if ($picture == 'plus')
				{
					$target = $this->createTarget('+',$a_node_id);
					$tpl->setCurrentBlock("expander");
					$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
					$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/plus.gif"));
					$tpl->parseCurrentBlock();
					$pic = true;
				}
	
				if ($picture == 'minus' && $this->show_minus)
				{
					$target = $this->createTarget('-',$a_node_id);
					$tpl->setCurrentBlock("expander");
					$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
					$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/minus.gif"));
					$tpl->parseCurrentBlock();
					$pic = true;
				}

				if (!$pic)
				{
					$picture = 'blank';
					$tpl->setCurrentBlock("lines");
					$tpl->setVariable("IMGPATH_LINES", ilUtil::getImagePath("browser/".$picture.".gif"));
					$tpl->parseCurrentBlock();
				}
			}
		}

		if ($this->output_icons)	{
			if ($this->isClickable($a_option["c_type"], $a_node_id) && !$a_option["c_type"]=="sbl")
				$this->getOutputIcons($tpl, $a_option, $a_node_id);
		}

		if ($this->isClickable($a_option["c_type"], $a_node_id))	// output link
		{
			$tpl->setCurrentBlock("link");
			//$target = (strpos($this->target, "?") === false) ?
			//	$this->target."?" : $this->target."&";
			//$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			//$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"], $this->textwidth, true));
			$frame_target = $this->buildFrameTarget($a_option["c_type"], $a_node_id, $a_option["obj_id"]);
			if ($frame_target != "")
			{
//				if ($this->api == 1)
//				{
//					$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"], $this->textwidth, true));
//					$tpl->setVariable("TARGET", " target=\"".$frame_target."\"");
//					//$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["c_type"]));
//					$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["c_type"]));
//				}
//				else
//				{
					if ($a_option["c_type"]=="sbl") {
						$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"]." ($a_node_id)", $this->textwidth, true));
						$tpl->setVariable("TARGET", " target=\"".$frame_target."\"");
						$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["c_type"]));
						
					} else {
						
						$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"]." ($a_node_id)", $this->textwidth, true));
						$tpl->setVariable("LINK_TARGET", "javascript:void(0);");
						$tpl->setVariable("ONCLICK", " onclick=\"parent.APIFRAME.setupApi();parent.APIFRAME.API.IliasLaunchSahs('".$a_node_id."');return false;\"");
						
//					}
				}

			}
			$tpl->parseCurrentBlock();
		}
		else			// output text only
		{
			$tpl->setCurrentBlock("text");
			$tpl->setVariable("OBJ_TITLE", ilUtil::shortenText($a_option["title"], $this->textwidth, true));
			$tpl->parseCurrentBlock();
		}
		$this->formatItemTable($tpl, $a_node_id, $a_option["c_type"]);

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}
}
?>
