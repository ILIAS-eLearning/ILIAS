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

/**
* Explorer View for AICC Learning Modules
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
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
	function __construct($a_target, &$a_slm_obj)
	{
		parent::__construct($a_target);
		$this->slm_obj = $a_slm_obj;
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
			$block_object = new ilAICCBlock($a_id);
			return (strlen($block_object->getDescription())>0);
		}
		
		if ($a_type != "sau")
		{
			return false;
		}
		else
		{
			$sc_object = new ilAICCUnit($a_id);
			return true;
		}
		return false;
	}

	function formatItemTable(&$tpl, $a_id, $a_type)
	{
		global $lng;
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
					$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/plus.png"));
					$tpl->parseCurrentBlock();
					$pic = true;
				}

				if ($picture == 'minus' && $this->show_minus)
				{
					$target = $this->createTarget('-',$a_node_id);
					$tpl->setCurrentBlock("expander");
					$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
					$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/minus.png"));
					$tpl->parseCurrentBlock();
					$pic = true;
				}

				if (!$pic)
				{
					$picture = 'blank';
					$tpl->setCurrentBlock("lines");
					$tpl->setVariable("IMGPATH_LINES", ilUtil::getImagePath("browser/".$picture.".png"));
					$tpl->parseCurrentBlock();
				}
			}
		}

		if ($this->output_icons) {
			if ($this->isClickable($a_option["c_type"], $a_node_id) && !$a_option["c_type"]=="sbl")
				$this->getOutputIcons($tpl, $a_option, $a_node_id);
		}

		if ($this->isClickable($a_option["c_type"], $a_node_id))	// output link
		{
			$tpl->setCurrentBlock("link");

			$frame_target = $this->buildFrameTarget($a_option["c_type"], $a_node_id, $a_option["obj_id"]);
			if ($frame_target != "")
			{
				if ($a_option["c_type"]=="sbl") {
					$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"]." ($a_node_id)", $this->textwidth, true));
					$tpl->setVariable("TARGET", " target=\"".$frame_target."\"");
					$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["c_type"]));
				} else {
					$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"]." ($a_node_id)", $this->textwidth, true));
					$tpl->setVariable("LINK_TARGET", "javascript:void(0);");
					$tpl->setVariable("ONCLICK", " onclick=\"parent.APIFRAME.setupApi();parent.APIFRAME.API.IliasLaunchSahs('".$a_node_id."');return false;\"");
				}
			}
			$tpl->parseCurrentBlock();
		}
		else // output text only
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

	function setOutput($a_parent_id, $a_depth = 0)
	{
		global $rbacadmin, $rbacsystem;
		static $counter = 0;

		if (!isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::setOutput(): No node_id given!",$this->ilias->error_obj->WARNING);
		}
		if ($this->showChilds($a_parent_id))
		{
			$objects = $this->tree->getChilds($a_parent_id, $this->order_column);
		}
		else
		{
			$objects = array();
		}
		if (count($objects) > 0)
		{

			//moved the scorm-only constant parameter to a function
			//to be able to reuse the code
			//$tab = ++$a_depth - 2;
			$tab = ++$a_depth - $this->getNodesToSkip();

			foreach ($objects as $key => $object) {
				//ask for FILTER
				if ($this->filtered == false or $this->checkFilter($object["c_type"]) == false) {
					if ($this->isVisible($object["obj_id"], $object["c_type"])) {
						$this->addObjectToOutputArray($counter, $tab, $object);

						$this->createPrefixArray($counter, $tab);

						$this->makeObjectNodeExpandable($object["c_type"], $object["obj_id"]);

						$parent_index = $this->getParentIndex($object["child"]);

						if ($parent_index == 0) {
							$this->setParentExpanded($object["parent"]);
						}

						$this->format_options["$counter"]["visible"] = !$this->shouldHideCurrentNode($object["child"], $parent_index, $object["parent"]);

						// if object exists parent is container
						if ($object["child"] != $this->tree->getRootId()) {
							$this->format_options["$parent_index"]["container"] = true;

							if ($this->expand_all or in_array($object["parent"],$this->expanded)) {
								$this->format_options["$parent_index"]["tab"][($tab-2)] = 'minus';
							} else {
								$this->format_options["$parent_index"]["tab"][($tab-2)] = 'plus';
							}
						}
						++$counter;
						// stop recursion if 2. level beyond expanded nodes is reached
						if ($this->expand_all or in_array($object["parent"],$this->expanded) or ($object["parent"] == 0)) {
							// recursive
							$this->setOutput($object["child"],$a_depth);
						}
					} //if
				} //if FILTER
			} //foreach
		} //if
	} //function

	protected function createPrefixArray($counter, $tab) {
		for ($i = 0; $i < $tab; ++$i) {
			 $this->format_options["$counter"]["tab"][] = 'blank';
		}
	}

	protected function addObjectToOutputArray($counter, $tab, $object) {
		$this->format_options["$counter"]["parent"]		= $object["parent"];
		$this->format_options["$counter"]["child"]		= $object["child"];
		$this->format_options["$counter"]["title"]		= $object["title"];
		$this->format_options["$counter"]["c_type"]		= $object["c_type"];
		$this->format_options["$counter"]["obj_id"]		= $object["obj_id"];
		$this->format_options["$counter"]["desc"] 		= "obj_".$object["c_type"];
		$this->format_options["$counter"]["depth"]		= $tab;
		$this->format_options["$counter"]["container"]	= false;
		$this->format_options["$counter"]["visible"]	= true;
	}

	protected function makeObjectNodeExpandable($c_type, $obj_id) {
		if ($c_type =="sos") {
			$this->setExpand($obj_id);
		}
	}

	protected function getParentIndex($child) {
		if ($child != $this->tree->getRootId()) {
			return $this->getIndex($object);
		}
	}

	protected function setParentExpanded($parent) {
		if (!$this->expand_all && !in_array($parent, $this->expanded)) {
			$this->expanded[] = $parent;
		}
	}

	protected function shouldHideCurrentNode($child, $parent_index, $parent) {
		if ($child != $this->tree->getRootId() 
				&& (!$this->expand_all and !in_array($parent, $this->expanded)
					or !$this->format_options["$parent_index"]["visible"])) 
		{
			return true;
		}

		return false;
	}
}
?>