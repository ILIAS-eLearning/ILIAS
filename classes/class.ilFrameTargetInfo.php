<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
							return "bottom";
						}
						else
						{
							return "rep_content";
						}
						
					case "MainContent":
						return "bottom";
						
					// frame for external content (e.g. web bookmarks, external links) 
					case "ExternalContent":
						return "_new";
				}
		}
		
		return "";
	}

} // END class.ilFrameTargetInfo
?>
