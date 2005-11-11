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
* locator handling class
*
* This class supplies an implementation for the locator.
* The locator will send its output to ist own frame, enabling more flexibility in
* the design of the desktop.
*
* @author Arjan Ammerlaan <a.l.ammerlaan@web.de>
* @version $Id$
* 
* @package locator
*/
class ilLocatorGUI
{
	/**
	* template object
	* @var object tpl
	* @access private
	*/
	var $tpl;
	
	/**
	* language object
	* @var object lng
	* @access private
	*/
	var $lng;
	
	/**
	* array of locator data,
	* representing the full locator tree
	* @var array
	* @access private
	*/
	var $locator_data;
	
	/**
	* level in locator tree,
	* carries value of deepest
	* valid level in tree
	* @var int
	* @access private
	*/
	var $locator_level;
	
	/**
	* indicates if the locator
	* bar is displayed in a
	* frame environment or
	* not
	*/
	var $display_frame;
	
	/**
	* Constructor
	*
	* @param	boolean		$a_display_frame		DEPRECATED
	*/
	function ilLocatorGUI($a_display_frame = true)
	{
		global $tpl, $lng, $tree;

		$this->tpl		=& $tpl;
		$this->lng		=& $lng;	
		$this->display_frame = $a_display_frame;
		
		$this->items = array();
	}

	/**
	* add repository items
	*
	* @param	int		$a_ref_id	current ref id (optional);
	*								if empty $_GET["ref_id"] is used
	*/
	function addRepositoryItems($a_ref_id = 0)
	{
		global $tree;

		if ($a_ref_id == 0)
		{
			$a_ref_id = $_GET["ref_id"];
		}
		
		if ($_GET["ref_id"] > 0)
		{
			$path = $tree->getPathFull($_GET["ref_id"]);
			
			// add item for each node on path
			foreach ($path as $key => $row)
			{
				if (!in_array($row["type"], array("root", "cat", "fold", "grp")))
				{
					continue;
				}
				if ($row["title"] == "ILIAS")
				{
					$row["title"] = $this->lng->txt("repository");
				}
				$this->addItem($row["title"],
					"repository.php?cmd=frameset&amp;ref_id=".$row["child"],
					ilFrameTargetInfo::_getFrame("MainContent"));
			}
		}
	}
	
	/**
	* add locator item
	*
	* @param	string	$a_title		item title
	* @param	string	$a_link			item link
	* @param	string	$a_frame		frame target
	*/
	function addItem($a_title, $a_link, $a_frame = "")
	{
		$this->entries[] = array("title" => $a_title,
			"link" => $a_link, "frame" => $a_frame); 
	}
	
	/**
	* get all locator entries
	*/
	function getItems()
	{
		return $this->entries;
	}
	
	/**
	* DEPRECATED!
	*/
	function navigate($newLocLevel,$newLocName,$newLocLink,$newLocTarget)
	{
		if ($newLocLevel > -1)
		{
			// update local variables
			if ($this->display_frame) {
				$this->locator_data		= $_SESSION["locator_data"];
				$this->locator_level	= $_SESSION["locator_level"];
			}
			// navigate: check whether links should be deleted or added / updated
			if ($newLocLevel < $this->locator_level)
			{
				// remove link(s) of deeper levels (clean up array when leap-frogging ;)
				for ($i = $this->locator_level ; $i >= $newLocLevel ; $i --)
				{
					$this->locator_data[$i][0] = "";
					$this->locator_data[$i][1] = "";
					$this->locator_data[$i][2] = "";
				}
			}
			// add current link or update
			$this->locator_data[$newLocLevel][0] = $newLocName;
			$this->locator_data[$newLocLevel][1] = $newLocLink;
			$this->locator_data[$newLocLevel][2] = $newLocTarget;
			
			// set level current
			$this->locator_level = $newLocLevel;
			
			// update session variables
			if ($this->display_frame) {
				$_SESSION["locator_data"] = $this->locator_data;
				$_SESSION["locator_level"] = $this->locator_level;
			}
		}
	}
	
	/**
	* DEPRECATED!
	*/
	function output()
	{
		// update local variables
		if ($this->display_frame) {
			$this->locator_data		= $_SESSION["locator_data"];
			$this->locator_level	= $_SESSION["locator_level"];
		}
				
		// select the template
		if ($this->display_frame) {
			$this->tpl = new ilTemplate("tpl.locator_frame.html", true, true);
		} else {
			$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		}

		// locator title
		$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));		


		// walk through array and generate locator
		if ($this->locator_level < 0)
		{
			$this->tpl->setCurrentBlock("locator_text");
			$this->tpl->setVariable("ITEM", " - ERROR Locator array empty! -");		//  ######## LANG FILE ENTRY ###########		

			$this->tpl->parseCurrentBlock();
		}
		else 
		{
			for ($i = 0 ; $i <= $this->locator_level ; $i ++) 
			{
				// generate links, skip empty links
				if ( ($this->locator_data[$i][0] != "") & ($this->locator_data[$i][1] != "") )
				{	
					// locator entry
					if ($i == $this->locator_level)
					{
						if ($this->display_frame) {
							$this->tpl->setCurrentBlock("locator_text");
							$this->tpl->setVariable("ITEM", $this->locator_data[$i][0]);
							$this->tpl->parseCurrentBlock("locator_text");
						} else {
							$this->tpl->setCurrentBlock("locator_text");
							$this->tpl->setVariable("ITEM", $this->locator_data[$i][0]);
							$this->tpl->setVariable("LINK_ITEM", $this->locator_data[$i][1]);
							$this->tpl->setVariable("LINK_TARGET", $this->locator_data[$i][2]);
							$this->tpl->parseCurrentBlock("locator_text");
						}
					}
					else
					{
						if ($this->display_frame) {
							$this->tpl->setCurrentBlock("locator_link");
							$this->tpl->setVariable("ITEM", $this->locator_data[$i][0]);
							$this->tpl->setVariable("LINK_ITEM", $this->locator_data[$i][1]);
							$this->tpl->setVariable("LINK_TARGET", $this->locator_data[$i][2]);
							$this->tpl->parseCurrentBlock("locator_link");
						} else {
							$this->tpl->touchBlock("locator_separator");
							$this->tpl->setCurrentBlock("locator_item");
							$this->tpl->setVariable("ITEM", $this->locator_data[$i][0]);
							$this->tpl->setVariable("LINK_ITEM", $this->locator_data[$i][1]);
							$this->tpl->setVariable("LINK_TARGET", $this->locator_data[$i][2]);
							$this->tpl->parseCurrentBlock("locator_item");
						}
					}
				}
			}
		}
		
		// output
		if ($this->display_frame) {
			$this->tpl->show();
		}
	}
	
} // END class.LocatorGUI
?>
