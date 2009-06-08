<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* ilFrameTargetInfo
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*/
class ilFrameTargetInfo
{
	/**
	* get content frame name
	*/
	function _getFrame($a_class, $a_type = "")
	{
		switch($a_type)
		{
			default:
				switch($a_class)
				{
					case "RepositoryContent":
						if ($_SESSION["il_rep_mode"] == "flat" or !isset($_SESSION['il_rep_mode']))
						{
							//return "bottom";
							return "_top";
						}
						else
						{
							return "rep_content";
						}
						
					case "MainContent":
						//return "bottom";
						return "_top";
						
					// frame for external content (e.g. web bookmarks, external links) 
					case "ExternalContent":
						return "_blank";
				}
		}
		
		return "";
	}

} // END class.ilFrameTargetInfo
?>
