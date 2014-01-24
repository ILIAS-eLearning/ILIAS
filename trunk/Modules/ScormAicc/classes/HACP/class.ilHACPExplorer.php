<?php

/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
require_once("./Modules/ScormAicc/classes/AICC/class.ilAICCTree.php");
require_once("./Modules/ScormAicc/classes/AICC/class.ilAICCExplorer.php");

/**
* @ingroup ModulesScormAicc
*/
class ilHACPExplorer extends ilAICCExplorer
{

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilHACPExplorer($a_target, &$a_slm_obj)
	{
		parent::ilExplorer($a_target);
		$this->slm_obj =& $a_slm_obj;
		$this->tree = new ilAICCTree($a_slm_obj->getId());
		$this->root_id = $this->tree->readRootId();
		$this->checkPermissions(false);
		$this->outputIcons(true);
		$this->setOrderColumn("");
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
		//echo "hacp: ".$a_option["title"]." >> ".implode(", ",$a_option["tab"])."<br>";

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
				if ($picture == 'plus')
				{
					$target = $this->createTarget('+',$a_node_id);
					$tpl->setCurrentBlock("expander");
					$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
					$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/plus.png"));
					$tpl->parseCurrentBlock();
				}
	
				if ($picture == 'minus' && $this->show_minus)
				{
					$target = $this->createTarget('-',$a_node_id);
					$tpl->setCurrentBlock("expander");
					$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
					$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/minus.png"));
					$tpl->parseCurrentBlock();
				}
	
				if ($picture == 'blank' or $picture == 'winkel'
				   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke'
				   or ($picture == 'minus' &&  !$this->show_minus))
				{
					$picture = 'blank';
					$tpl->setCurrentBlock("lines");
					$tpl->setVariable("IMGPATH_LINES", ilUtil::getImagePath("browser/".$picture.".png"));
					$tpl->parseCurrentBlock();
				}
			}
		}
		
		if ($this->output_icons)	{
			if ($this->isClickable($a_option["c_type"], $a_node_id) && $a_option["c_type"]!="sbl")
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
						include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php");
						$unit =& new ilAICCUnit($a_node_id);
						
						//guess the url to be able to launch most contents
						$url=$unit->getCommand_line();
						if (strlen($url)==0)
							$url=$unit->getFilename();

						
						//relative path?	
						if (substr($url,0,7)!="http://")
							$url=$this->slm_obj->getDataDirectory("output")."/".$url;
							
						if (strlen($unit->getWebLaunch())>0)
							$url.="?".$unit->getWebLaunch();
						
						$hacpURL=ILIAS_HTTP_PATH."/Modules/ScormAicc/sahs_server.php";
						
						//$url.="?aicc_url=$hacpURL&aicc_sid=".$this->slm_obj->ref_id;
						//$aicc_sid=$this->slm_obj->ref_id."%20".session_id();
						$aicc_sid=implode("_", array(session_id(), CLIENT_ID, $this->slm_obj->ref_id, $a_node_id));
						if (strlen($unit->getWebLaunch())>0)
							$url.="&";
						else
							$url.="?";
						$url.="aicc_url=$hacpURL&aicc_sid=$aicc_sid";

/*					
						foreach ($this->slm_obj as $key=>$value)
							$output.="key=$key value=$value<br>";
						$tpl->setVariable("TITLE", $output);
*/	
						$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"]." ($a_node_id)", $this->textwidth, true));
						$tpl->setVariable("LINK_TARGET", "javascript:void(0);");
						$tpl->setVariable("ONCLICK", " onclick=\"parent.$frame_target.location.href='$url'\"");

						
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
